<?php

namespace App\Services;

use App\Models\JadwalKerjaModel;
use App\Models\PegawaiModel;
use App\Models\PresensiModel;
use DateTime;

class PresensiAdminService extends BaseService
{
    protected PresensiModel $presensiModel;
    protected JadwalKerjaModel $jadwalKerjaModel;
    protected PegawaiModel $pegawaiModel;

    public function __construct()
    {
        parent::__construct();
        $this->presensiModel = new PresensiModel();
        $this->jadwalKerjaModel = new JadwalKerjaModel();
        $this->pegawaiModel = new PegawaiModel();
    }

    public function dataPegawaiSelect(): array
    {
        return $this->pegawaiModel
            ->select('id, kode_pegawai, nama_pegawai')
            ->where('is_active', 1)
            ->orderBy('nama_pegawai', 'ASC')
            ->findAll();
    }

    public function dataTabel(?string $tanggal = null)
    {
        return $this->presensiModel->dataTabel($tanggal);
    }

    public function detail(int $id): array
    {
        return $this->eksekusi(function () use ($id) {
            $presensi = $this->presensiModel->getDetailAdminById($id);

            if ($presensi === null) {
                return $this->hasilTidakDitemukan('Data presensi tidak ditemukan');
            }

            return $this->hasilData([
                'presensi' => $presensi,
            ]);
        });
    }

    public function ringkasanHarian(?string $tanggal = null): array
    {
        return $this->eksekusi(function () use ($tanggal) {
            $tanggal = $this->stringWajib($tanggal ?: date('Y-m-d'));

            $totalJadwal = (int) $this->jadwalKerjaModel
                ->where('tanggal', $tanggal)
                ->countAllResults();

            $totalPresensi = $this->presensiModel->countByTanggal($tanggal);

            return $this->hasilData([
                'ringkasan' => [
                    'tanggal'         => $tanggal,
                    'total_jadwal'    => $totalJadwal,
                    'total_presensi'  => $totalPresensi,
                    'belum_presensi'  => max($totalJadwal - $totalPresensi, 0),

                    'belum_sinkron'        => (int) $this->presensiModel->countByTanggalDanHasilPresensiNull($tanggal) + ((int) $totalJadwal - (int) $totalPresensi),

                    'hadir'                => $this->presensiModel->countByTanggalDanHasilPresensi($tanggal, 'hadir'),
                    'alpa'                 => $this->presensiModel->countByTanggalDanHasilPresensi($tanggal, 'alpa'),
                    'izin'                 => $this->presensiModel->countByTanggalDanHasilPresensi($tanggal, 'izin'),
                    'sakit'                => $this->presensiModel->countByTanggalDanHasilPresensi($tanggal, 'sakit'),
                    'libur'                => $this->presensiModel->countByTanggalDanHasilPresensi($tanggal, 'libur'),

                    'tepat_waktu_datang'   => $this->presensiModel->countByTanggalDanStatusDatang($tanggal, 'tepat_waktu'),
                    'telat'                => $this->presensiModel->countByTanggalDanStatusDatang($tanggal, 'telat'),
                    'tepat_waktu_pulang'   => $this->presensiModel->countByTanggalDanStatusPulang($tanggal, 'tepat_waktu'),
                    'pulang_cepat'         => $this->presensiModel->countByTanggalDanStatusPulang($tanggal, 'pulang_cepat'),
                ],
            ]);
        });
    }

    public function presensiTerbaru(?string $tanggal = null, int $limit = 10): array
    {
        return $this->eksekusi(function () use ($tanggal, $limit) {
            $tanggal = $this->stringWajib($tanggal ?: date('Y-m-d'));

            return $this->hasilData([
                'items' => $this->presensiModel->getPresensiTerbaru($tanggal, $limit),
            ]);
        });
    }

    public function sinkronPresensiHarian(?string $tanggal = null): array
    {
        return $this->transaksi(function () use ($tanggal) {
            $tanggal = $this->stringWajib($tanggal ?: date('Y-m-d'));

            $cek = $this->validasiWaktuSinkron($tanggal);

            if ($cek !== null) {
                return $cek;
            }

            $jadwalList = $this->jadwalKerjaModel
                ->where('tanggal', $tanggal)
                ->findAll();

            if (empty($jadwalList)) {
                return $this->hasilSukses('Tidak ada jadwal pada tanggal ini', [
                    'tanggal'         => $tanggal,
                    'jumlah_diproses' => 0,
                    'jumlah_dilewati' => 0,
                    'jumlah_hadir'    => 0,
                    'jumlah_alpa'     => 0,
                    'jumlah_izin'     => 0,
                    'jumlah_sakit'    => 0,
                    'jumlah_libur'    => 0,
                ]);
            }

            $jumlahDiproses = 0;
            $jumlahDilewati = 0;
            $jumlahHadir = 0;
            $jumlahAlpa = 0;
            $jumlahIzin = 0;
            $jumlahSakit = 0;
            $jumlahLibur = 0;

            foreach ($jadwalList as $jadwal) {
                $pegawaiId = (int) ($jadwal->pegawai_id ?? 0);

                if ($pegawaiId <= 0) {
                    $jumlahDilewati++;
                    continue;
                }

                $statusHari = (string) ($jadwal->status_hari ?? '');

                $presensiExisting = $this->presensiModel->getPresensiByPegawaiDanTanggal($pegawaiId, $tanggal);

                $hasilPresensi = $this->tentukanHasilPresensiSinkron($statusHari, $presensiExisting);

                if ($hasilPresensi === null) {
                    $jumlahDilewati++;
                    continue;
                }

                if (is_object($presensiExisting)) {
                    $update = $this->presensiModel->update((int) $presensiExisting->id, [
                        'hasil_presensi' => $hasilPresensi,
                        'catatan_admin'  => $this->catatanSinkron($presensiExisting->catatan_admin ?? null, $hasilPresensi),
                    ]);

                    if (! $update) {
                        $jumlahDilewati++;
                        continue;
                    }
                } else {
                    $insert = $this->presensiModel->insert([
                        'pegawai_id'         => $pegawaiId,
                        'tanggal'            => $tanggal,
                        'jadwal_kerja_id'    => (int) ($jadwal->id ?? 0),
                        'shift_id'           => $this->intAtauNull($jadwal->shift_id ?? null),
                        'jam_datang'         => null,
                        'jam_pulang'         => null,
                        'status_datang'      => null,
                        'status_pulang'      => null,
                        'hasil_presensi'     => $hasilPresensi,
                        'menit_telat'        => 0,
                        'menit_pulang_cepat' => 0,
                        'selfie_datang'      => null,
                        'selfie_pulang'      => null,
                        'barcode_datang'     => null,
                        'barcode_pulang'     => null,
                        'ip_address'         => null,
                        'user_agent'         => null,
                        'catatan_admin'      => 'Sinkron otomatis dari jadwal kerja',
                        'is_manual'          => 1,
                        'sumber_presensi'    => 'sinkron',
                    ]);

                    if (! $insert) {
                        $jumlahDilewati++;
                        continue;
                    }
                }

                $jumlahDiproses++;

                match ($hasilPresensi) {
                    'hadir' => $jumlahHadir++,
                    'alpa'  => $jumlahAlpa++,
                    'izin'  => $jumlahIzin++,
                    'sakit' => $jumlahSakit++,
                    'libur' => $jumlahLibur++,
                    default => null,
                };
            }

            $this->catatAudit(
                'sinkron_presensi',
                'presensi',
                null,
                'Sinkron presensi tanggal ' . tanggal_indonesia($tanggal) .
                    ' | hadir: ' . $jumlahHadir .
                    ', alpa: ' . $jumlahAlpa .
                    ', izin: ' . $jumlahIzin .
                    ', sakit: ' . $jumlahSakit .
                    ', libur: ' . $jumlahLibur .
                    ', dilewati: ' . $jumlahDilewati
            );

            return $this->hasilSukses('Sinkron presensi selesai', [
                'tanggal'         => $tanggal,
                'jumlah_diproses' => $jumlahDiproses,
                'jumlah_dilewati' => $jumlahDilewati,
                'jumlah_hadir'    => $jumlahHadir,
                'jumlah_alpa'     => $jumlahAlpa,
                'jumlah_izin'     => $jumlahIzin,
                'jumlah_sakit'    => $jumlahSakit,
                'jumlah_libur'    => $jumlahLibur,
            ]);
        });
    }

    protected function tentukanHasilPresensiSinkron(string $statusHari, ?object $presensi): ?string
    {
        return match ($statusHari) {
            'izin'  => 'izin',
            'sakit' => 'sakit',
            'libur' => 'libur',
            'kerja' => (
                is_object($presensi)
                && ! empty($presensi->status_datang)
                && ! empty($presensi->status_pulang)
            ) ? 'hadir' : 'alpa',
            default => null,
        };
    }

    protected function catatanSinkron(?string $catatanLama, string $hasilPresensi): string
    {
        $catatanLama = trim((string) $catatanLama);
        $catatanBaru = 'Sinkron hasil presensi: ' . $hasilPresensi;

        if ($catatanLama === '') {
            return $catatanBaru;
        }

        if (str_contains($catatanLama, 'Sinkron hasil presensi:')) {
            return $catatanLama;
        }

        return $catatanLama . ' | ' . $catatanBaru;
    }

    protected function validasiWaktuSinkron(string $tanggal): ?array
    {
        $hariIni = date('Y-m-d');

        // ❌ Tidak boleh masa depan
        if ($tanggal > $hariIni) {
            return $this->hasilGagal([], 'Sinkron presensi tidak boleh untuk tanggal setelah tanggal ' . tanggal_indonesia($hariIni));
        }

        // ✅ Masa lalu aman
        if ($tanggal < $hariIni) {
            return null;
        }

        // 🔥 Hari ini → cek batas akhir pulang
        $batasAkhir = $this->jadwalKerjaModel->getBatasAkhirPulangTerakhir($tanggal);

        if (empty($batasAkhir)) {
            return $this->hasilGagal([], 'Batas akhir pulang tidak ditemukan');
        }

        $batasTimestamp = strtotime($tanggal . ' ' . $batasAkhir);
        $sekarang = time();

        if ($sekarang <= $batasTimestamp) {
            return $this->hasilGagal(
                [],
                'Sinkron presensi hari ini hanya bisa setelah batas akhir pulang: ' . substr($batasAkhir, 0, 5)
            );
        }

        return null;
    }

    public function generateAlpaHarian(?string $tanggal = null): array
    {
        return $this->sinkronPresensiHarian($tanggal);
    }

    public function simpanLupaPresensi(array $post): array
    {
        return $this->transaksi(function () use ($post) {
            $rules = [
                'pegawai_id' => [
                    'label' => 'Pegawai',
                    'rules' => 'required|integer',
                    'errors' => [
                        'required' => '{field} harus diisi',
                        'integer'  => '{field} tidak valid',
                    ],
                ],
                'tanggal' => [
                    'label' => 'Tanggal',
                    'rules' => 'required|valid_date[Y-m-d]',
                    'errors' => [
                        'required'   => '{field} harus diisi',
                        'valid_date' => '{field} harus berformat YYYY-MM-DD',
                    ],
                ],
                'jam_datang' => [
                    'label' => 'Jam Datang',
                    'rules' => 'required|regex_match[/^\d{2}:\d{2}$/]',
                    'errors' => [
                        'required'    => '{field} harus diisi',
                        'regex_match' => '{field} harus berformat HH:MM',
                    ],
                ],
                'jam_pulang' => [
                    'label' => 'Jam Pulang',
                    'rules' => 'permit_empty|regex_match[/^\d{2}:\d{2}$/]',
                    'errors' => [
                        'regex_match' => '{field} harus berformat HH:MM',
                    ],
                ],
                'catatan_admin' => [
                    'label' => 'Catatan Admin',
                    'rules' => 'required|max_length[255]',
                    'errors' => [
                        'required'   => '{field} harus diisi',
                        'max_length' => '{field} maksimal 255 karakter',
                    ],
                ],
            ];

            $validasi = $this->validasi($rules, $post);
            if (! $validasi['sukses']) {
                return $validasi;
            }

            $pegawaiId = $this->intVal($post['pegawai_id'] ?? 0);
            $tanggal = $this->stringWajib($post['tanggal'] ?? '');

            if ($tanggal > date('Y-m-d')) {
                return $this->hasilGagal([], 'Lupa presensi tidak boleh untuk tanggal setelah tanggal ' . tanggal_indonesia(date('Y-m-d')));
            }

            $jadwal = $this->jadwalKerjaModel->getJadwalDetailByPegawaiDanTanggal($pegawaiId, $tanggal);

            if ($jadwal === null) {
                return $this->hasilGagal([], 'Jadwal kerja pegawai pada tanggal ini tidak ditemukan');
            }

            if (($jadwal->status_hari ?? '') !== 'kerja') {
                return $this->hasilGagal([], 'Lupa presensi hanya bisa untuk pegawai yang memiliki jadwal kerja');
            }

            if ($this->presensiModel->sudahAdaPresensi($pegawaiId, $tanggal)) {
                return $this->hasilGagal([], 'Presensi pegawai pada tanggal ini sudah ada');
            }

            $jamDatang = $this->formatDateTimePresensi($tanggal, $post['jam_datang']);
            $jamPulangInput = $this->stringAtauNull($post['jam_pulang'] ?? null);
            $jamPulang = $jamPulangInput ? $this->formatDateTimePresensi($tanggal, $jamPulangInput) : null;

            $statusDatang = $this->hitungStatusDatangLupa($tanggal, $post['jam_datang'], $jadwal);
            $statusPulang = $jamPulangInput ? $this->hitungStatusPulangLupa($tanggal, $jamPulangInput, $jadwal) : [
                'status' => 'belum_pulang',
                'menit_pulang_cepat' => 0,
            ];

            $insert = $this->presensiModel->insert([
                'pegawai_id'         => $pegawaiId,
                'tanggal'            => $tanggal,
                'jadwal_kerja_id'    => (int) $jadwal->id,
                'shift_id'           => $this->intAtauNull($jadwal->shift_id),
                'jam_datang'         => $jamDatang,
                'jam_pulang'         => $jamPulang,
                'status_datang'      => $statusDatang['status'],
                'status_pulang'      => $statusPulang['status'],
                'menit_telat'        => $statusDatang['menit_telat'],
                'menit_pulang_cepat' => $statusPulang['menit_pulang_cepat'],
                'selfie_datang'      => null,
                'selfie_pulang'      => null,
                'barcode_datang'     => null,
                'barcode_pulang'     => null,
                'ip_address'         => $this->stringAtauNull(service('request')->getIPAddress()),
                'user_agent'         => $this->stringAtauNull(service('request')->getUserAgent()?->getAgentString()),
                'catatan_admin'      => $this->stringWajib($post['catatan_admin']),
                'is_manual'          => 1,
                'sumber_presensi'    => 'lupa_presensi',
            ]);

            if (! $insert) {
                return $this->hasilGagal([], 'Lupa presensi gagal disimpan');
            }

            $this->catatAudit(
                'create_lupa_presensi',
                'presensi',
                (int) $this->presensiModel->getInsertID(),
                'Input lupa presensi pegawai ID ' . $pegawaiId . ' tanggal ' . $tanggal
            );

            return $this->hasilSukses('Lupa presensi berhasil disimpan');
        });
    }

    public function updateLupaPresensi(int $id, array $post): array
    {
        return $this->transaksi(function () use ($id, $post) {
            $presensi = $this->presensiModel->find($id);

            if ($presensi === null) {
                return $this->hasilTidakDitemukan('Data presensi tidak ditemukan');
            }

            $cek = $this->pastikanLupaPresensi($presensi);
            if ($cek !== null) {
                return $cek;
            }

            $rules = [
                'edit-jam_datang' => [
                    'label' => 'Jam Datang',
                    'rules' => 'required|regex_match[/^\d{2}:\d{2}$/]',
                    'errors' => [
                        'required'    => '{field} harus diisi',
                        'regex_match' => '{field} harus berformat HH:MM',
                    ],
                ],
                'edit-jam_pulang' => [
                    'label' => 'Jam Pulang',
                    'rules' => 'permit_empty|regex_match[/^\d{2}:\d{2}$/]',
                    'errors' => [
                        'regex_match' => '{field} harus berformat HH:MM',
                    ],
                ],
                'edit-catatan_admin' => [
                    'label' => 'Catatan Admin',
                    'rules' => 'required|max_length[255]',
                    'errors' => [
                        'required'   => '{field} harus diisi',
                        'max_length' => '{field} maksimal 255 karakter',
                    ],
                ],
            ];

            $validasi = $this->validasi($rules, $post);
            if (! $validasi['sukses']) {
                return $validasi;
            }

            $jadwal = $this->jadwalKerjaModel->getJadwalDetailByPegawaiDanTanggal(
                (int) $presensi->pegawai_id,
                (string) $presensi->tanggal
            );

            if ($jadwal === null || ($jadwal->status_hari ?? '') !== 'kerja') {
                return $this->hasilGagal([], 'Jadwal kerja tidak valid');
            }

            $tanggal = (string) $presensi->tanggal;
            $jamDatang = $this->formatDateTimePresensi($tanggal, $post['edit-jam_datang']);
            $jamPulangInput = $this->stringAtauNull($post['edit-jam_pulang'] ?? null);
            $jamPulang = $jamPulangInput ? $this->formatDateTimePresensi($tanggal, $jamPulangInput) : null;

            if ($tanggal > date('Y-m-d')) {
                return $this->hasilGagal([], 'Lupa presensi tanggal setelah hari ini tidak dapat diubah');
            }

            $statusDatang = $this->hitungStatusDatangLupa($tanggal, $post['edit-jam_datang'], $jadwal);
            $statusPulang = $jamPulangInput ? $this->hitungStatusPulangLupa($tanggal, $jamPulangInput, $jadwal) : [
                'status' => 'belum_pulang',
                'menit_pulang_cepat' => 0,
            ];

            $update = $this->presensiModel->update($id, [
                'jam_datang'         => $jamDatang,
                'jam_pulang'         => $jamPulang,
                'status_datang'      => $statusDatang['status'],
                'status_pulang'      => $statusPulang['status'],
                'menit_telat'        => $statusDatang['menit_telat'],
                'menit_pulang_cepat' => $statusPulang['menit_pulang_cepat'],
                'catatan_admin'      => $this->stringWajib($post['edit-catatan_admin']),
            ]);

            if (! $update) {
                return $this->hasilGagal([], 'Lupa presensi gagal diubah');
            }

            $this->catatAudit('update_lupa_presensi', 'presensi', $id, 'Update lupa presensi ID ' . $id);

            return $this->hasilSukses('Lupa presensi berhasil diubah');
        });
    }

    public function hapusLupaPresensi(int $id): array
    {
        return $this->transaksi(function () use ($id) {
            $presensi = $this->presensiModel->find($id);

            if ($presensi === null) {
                return $this->hasilTidakDitemukan('Data presensi tidak ditemukan');
            }

            $cek = $this->pastikanLupaPresensi($presensi);
            if ($cek !== null) {
                return $cek;
            }

            if (! $this->presensiModel->delete($id)) {
                return $this->hasilGagal([], 'Lupa presensi gagal dihapus');
            }

            $this->catatAudit('delete_lupa_presensi', 'presensi', $id, 'Hapus lupa presensi ID ' . $id);

            return $this->hasilSukses('Lupa presensi berhasil dihapus');
        });
    }

    protected function pastikanLupaPresensi(object $presensi): ?array
    {
        if (($presensi->sumber_presensi ?? '') !== 'lupa_presensi') {
            return $this->hasilGagal([], 'Data hasil scan atau sinkron tidak dapat diubah/dihapus');
        }

        return null;
    }

    protected function mapStatusDatangDariJadwal(string $statusHari): ?string
    {
        return match ($statusHari) {
            'kerja' => 'alpa',
            'izin'  => 'izin',
            'sakit' => 'sakit',
            'libur' => 'libur',
            default => null,
        };
    }

    protected function formatDateTimePresensi(string $tanggal, string $jam): string
    {
        return $tanggal . ' ' . $this->formatJam($jam);
    }

    protected function hitungStatusDatangLupa(string $tanggal, string $jamDatang, object $jadwal): array
    {
        $scan = new DateTime($this->formatDateTimePresensi($tanggal, $jamDatang));
        $jamMasuk = new DateTime($tanggal . ' ' . (string) $jadwal->jam_masuk);
        $toleransi = (int) ($jadwal->toleransi_telat_menit ?? 0);
        $batasToleransi = (clone $jamMasuk)->modify('+' . $toleransi . ' minutes');

        if ($scan > $batasToleransi) {
            return [
                'status' => 'telat',
                'menit_telat' => (int) floor(($scan->getTimestamp() - $batasToleransi->getTimestamp()) / 60),
            ];
        }

        return [
            'status' => 'hadir',
            'menit_telat' => 0,
        ];
    }

    protected function hitungStatusPulangLupa(string $tanggal, string $jamPulang, object $jadwal): array
    {
        $scan = new DateTime($this->formatDateTimePresensi($tanggal, $jamPulang));
        $jamPulangShift = new DateTime($tanggal . ' ' . (string) $jadwal->jam_pulang);

        if ($scan < $jamPulangShift) {
            return [
                'status' => 'pulang_cepat',
                'menit_pulang_cepat' => (int) floor(($jamPulangShift->getTimestamp() - $scan->getTimestamp()) / 60),
            ];
        }

        return [
            'status' => 'pulang',
            'menit_pulang_cepat' => 0,
        ];
    }

    public function badgeStatusDatang(?string $status): string
    {
        return match ($status) {
            'tepat_waktu' => '<span class="badge bg-success">Tepat Waktu</span>',
            'telat' => '<span class="badge bg-warning">Telat</span>',
            default => '<span class="badge bg-secondary">-</span>',
        };
    }

    public function badgeStatusPulang(?string $status): string
    {
        return match ($status) {
            'tepat_waktu'  => '<span class="badge bg-success">Tepat Waktu</span>',
            'pulang_cepat' => '<span class="badge bg-warning">Pulang Cepat</span>',
            default        => '<span class="badge bg-secondary">-</span>',
        };
    }

    public function badgeSumberPresensi(?string $sumber): string
    {
        return match ($sumber) {
            'scan'          => '<span class="badge bg-dark">Scan</span>',
            'sinkron'       => '<span class="badge bg-info">Sinkron</span>',
            'lupa_presensi' => '<span class="badge bg-warning">Lupa Presensi</span>',
            default         => '<span class="badge bg-secondary">-</span>',
        };
    }

    public function badgeHasilPresensi(?string $hasil): string
    {
        return match ($hasil) {
            'hadir' => '<span class="badge bg-success">Hadir</span>',
            'alpa'  => '<span class="badge bg-danger">Alpa</span>',
            'izin'  => '<span class="badge bg-info">Izin</span>',
            'sakit' => '<span class="badge bg-primary">Sakit</span>',
            'libur' => '<span class="badge bg-secondary">Libur</span>',
            default => '<span class="badge bg-light text-dark">Belum Sinkron</span>',
        };
    }
}
