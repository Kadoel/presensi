<?php

namespace App\Services\Admin;

use App\Models\JadwalKerjaModel;
use App\Models\PegawaiModel;
use App\Models\PengajuanIzinModel;
use App\Models\PresensiModel;
use App\Services\BaseService;
use CodeIgniter\Database\BaseBuilder;
use DateTime;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use App\Services\Kios\GoogleDriveService;

class PresensiAdminService extends BaseService
{
    protected PresensiModel $presensiModel;
    protected JadwalKerjaModel $jadwalKerjaModel;
    protected PegawaiModel $pegawaiModel;
    protected PengajuanIzinModel $pengajuanIzinModel;
    protected GoogleDriveService $googleDriveService;

    public function __construct()
    {
        parent::__construct();
        $this->presensiModel = new PresensiModel();
        $this->jadwalKerjaModel = new JadwalKerjaModel();
        $this->pegawaiModel = new PegawaiModel();
        $this->pengajuanIzinModel = new PengajuanIzinModel();
        $this->googleDriveService = new GoogleDriveService();
    }

    public function dataPegawaiSelect(): array
    {
        return $this->pegawaiModel
            ->select('id, kode_pegawai, nama_pegawai')
            ->where('is_active', 1)
            ->orderBy('nama_pegawai', 'ASC')
            ->findAll();
    }

    public function dataTabel(?string $tanggal = null): BaseBuilder
    {
        return $this->presensiModel->dataTabel($tanggal);
    }

    public function dataRekapBulanan(?string $bulan = null): BaseBuilder
    {
        return $this->presensiModel->dataRekapBulanan($bulan ?: date('Y-m'));
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
                    'cuti'                => $this->presensiModel->countByTanggalDanHasilPresensi($tanggal, 'cuti'),

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

            $belumSinkronSebelumHariIni = $this->validasiBelumSinkronSebelumHariIni($tanggal);
            if ($belumSinkronSebelumHariIni !== null) {
                return $belumSinkronSebelumHariIni;
            }

            $cekWaktu = $this->validasiWaktuSinkron($tanggal);
            if ($cekWaktu !== null) {
                return $cekWaktu;
            }

            $validasiJumlahJadwal = $this->validasiJumlahJadwalSesuaiPegawaiAktif($tanggal);
            if ($validasiJumlahJadwal !== null) {
                return $validasiJumlahJadwal;
            }

            $tanggal = $this->stringWajib($tanggal ?: date('Y-m-d'));
            // 🔥 VALIDASI BARU
            $validasiPengajuan = $this->validasiPengajuanIzinPendingPadaTanggal($tanggal);
            if ($validasiPengajuan !== null) {
                return $validasiPengajuan;
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
                'sinkron presensi',
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
            'cuti' => 'cuti',
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

        if (preg_match('/Sinkron hasil presensi: [a-z_]+/i', $catatanLama)) {
            return preg_replace('/Sinkron hasil presensi: [a-z_]+/i', $catatanBaru, $catatanLama);
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

        $totalJadwalKerja = (int) $this->jadwalKerjaModel->countStatusKerjaByTanggal($tanggal);
        if ($totalJadwalKerja < 1) {
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

            if ($jamPulangInput !== null && $jamPulang <= $jamDatang) {
                return $this->hasilGagal([
                    'jam_pulang' => 'Jam pulang harus lebih besar dari jam datang'
                ]);
            }

            $statusDatang = $this->hitungStatusDatangLupa($tanggal, $post['jam_datang'], $jadwal);
            $statusPulang = $jamPulangInput ? $this->hitungStatusPulangLupa($tanggal, $jamPulangInput, $jadwal) : [
                'status' => null,
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
                'hasil_presensi'     => null,
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
                'create lupa presensi',
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

            if ($jamPulangInput !== null && $jamPulang <= $jamDatang) {
                return $this->hasilGagal([
                    'edit-jam_pulang' => 'Jam pulang harus lebih besar dari jam datang'
                ]);
            }

            if ($tanggal > date('Y-m-d')) {
                return $this->hasilGagal([], 'Lupa presensi tanggal setelah hari ini tidak dapat diubah');
            }

            $statusDatang = $this->hitungStatusDatangLupa($tanggal, $post['edit-jam_datang'], $jadwal);
            $statusPulang = $jamPulangInput ? $this->hitungStatusPulangLupa($tanggal, $jamPulangInput, $jadwal) : [
                'status' => null,
                'menit_pulang_cepat' => 0,
            ];

            $update = $this->presensiModel->update($id, [
                'jam_datang'         => $jamDatang,
                'jam_pulang'         => $jamPulang,
                'status_datang'      => $statusDatang['status'],
                'status_pulang'      => $statusPulang['status'],
                'menit_telat'        => $statusDatang['menit_telat'],
                'menit_pulang_cepat' => $statusPulang['menit_pulang_cepat'],
                'hasil_presensi'     => null,
                'catatan_admin'      => $this->stringWajib($post['edit-catatan_admin']),
            ]);

            if (! $update) {
                return $this->hasilGagal([], 'Lupa presensi gagal diubah');
            }

            $this->catatAudit('update lupa presensi', 'presensi', $id, 'Update lupa presensi ID ' . $id);

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

            $this->catatAudit('delete lupa presensi', 'presensi', $id, 'Hapus lupa presensi ID ' . $id);

            return $this->hasilSukses('Lupa presensi berhasil dihapus');
        });
    }

    public function exportBulanan(string $bulan)
    {
        $bulan = preg_match('/^\d{4}-\d{2}$/', $bulan) ? $bulan : date('Y-m');

        $rows = $this->presensiModel->getExportBulanan($bulan);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Presensi ' . $bulan);

        $headers = [
            'No',
            'Tanggal',
            'Kode Pegawai',
            'Nama Pegawai',
            'Shift',
            'Jam Datang',
            'Status Datang',
            'Jam Pulang',
            'Status Pulang',
            'Menit Telat',
            'Menit Pulang Cepat',
            'Hasil Presensi',
            'Sumber',
            'Catatan Admin',
        ];

        $sheet->fromArray($headers, null, 'A1');

        $styleHeader = [
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];

        $styleBorder = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];

        $styleIndent = [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'indent'     => 1,
            ],
        ];

        $sheet->getStyle('A1:N1')->applyFromArray($styleHeader);

        $rowExcel = 2;
        $no = 1;
        $tanggalGroups = [];
        $summaryPegawai = [];

        foreach ($rows as $row) {
            $tanggalRaw = (string) $row->tanggal;
            $tanggal    = tanggal_indonesia($tanggalRaw);

            if (! isset($tanggalGroups[$tanggalRaw])) {
                $tanggalGroups[$tanggalRaw] = [
                    'start' => $rowExcel,
                    'end'   => $rowExcel,
                ];
            } else {
                $tanggalGroups[$tanggalRaw]['end'] = $rowExcel;
            }

            $hasil = (string) ($row->hasil_presensi ?? '');
            $kodePegawai = (string) ($row->kode_pegawai ?? '');

            if (! isset($summaryPegawai[$kodePegawai])) {
                $summaryPegawai[$kodePegawai] = [
                    'kode_pegawai' => $row->kode_pegawai ?? '-',
                    'nama_pegawai' => $row->nama_pegawai ?? '-',
                    'hadir'        => 0,
                    'izin'         => 0,
                    'sakit'        => 0,
                    'cuti'         => 0,
                    'libur'        => 0,
                    'alpa'         => 0,
                    'telat'        => 0,
                    'pulang_cepat' => 0,
                ];
            }

            if (isset($summaryPegawai[$kodePegawai][$hasil])) {
                $summaryPegawai[$kodePegawai][$hasil]++;
            }

            if (($row->status_datang ?? '') === 'telat') {
                $summaryPegawai[$kodePegawai]['telat']++;
            }

            if (($row->status_pulang ?? '') === 'pulang_cepat') {
                $summaryPegawai[$kodePegawai]['pulang_cepat']++;
            }

            $sheet->fromArray([
                $no++,
                $tanggal,
                $row->kode_pegawai ?? '-',
                $row->nama_pegawai ?? '-',
                $row->nama_shift ?: '-',
                $this->formatJamExcel($row->jam_datang ?? null),
                $row->status_datang ?: '-',
                $this->formatJamExcel($row->jam_pulang ?? null),
                $row->status_pulang ?: '-',
                (int) ($row->menit_telat ?? 0),
                (int) ($row->menit_pulang_cepat ?? 0),
                $hasil ?: '-',
                $row->sumber_presensi ?: '-',
                $row->catatan_admin ?: '-',
            ], null, 'A' . $rowExcel);

            $sheet->getStyle('A' . $rowExcel . ':N' . $rowExcel)->applyFromArray($styleBorder);
            // kolom text → indent
            $sheet->getStyle('C' . $rowExcel . ':E' . $rowExcel)->applyFromArray($styleIndent);
            $sheet->getStyle('N' . $rowExcel)->applyFromArray($styleIndent);

            $sheet->getStyle('A' . $rowExcel)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $rowExcel)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $rowExcel . ':M' . $rowExcel)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->getStyle('L' . $rowExcel)
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB($this->warnaHasilPresensi($hasil));

            $sheet->getStyle('L' . $rowExcel)->getFont()->setBold(true);
            $sheet->getStyle('L' . $rowExcel)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $rowExcel++;
        }

        foreach ($tanggalGroups as $range) {
            if ($range['start'] === $range['end']) {
                continue;
            }

            $cellRange = 'B' . $range['start'] . ':B' . $range['end'];

            $sheet->mergeCells($cellRange);
            $sheet->getStyle($cellRange)->applyFromArray([
                'font' => [
                    'bold' => true,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ]);
        }

        $rowExcel += 2;

        $sheet->setCellValue('A' . $rowExcel, 'SUMMARY PRESENSI PEGAWAI');
        $sheet->mergeCells('A' . $rowExcel . ':K' . $rowExcel);

        $sheet->getStyle('A' . $rowExcel . ':K' . $rowExcel)->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'D9EAF7',
                ],
            ],
        ]);

        $rowExcel++;

        $summaryHeaders = [
            'No',
            'Kode Pegawai',
            'Nama Pegawai',
            'Hadir',
            'Izin',
            'Sakit',
            'Cuti',
            'Libur',
            'Alpa',
            'Telat',
            'Pulang Cepat',
        ];

        $sheet->fromArray($summaryHeaders, null, 'A' . $rowExcel);
        $sheet->getStyle('A' . $rowExcel . ':K' . $rowExcel)->applyFromArray($styleHeader);

        $rowExcel++;
        $noSummary = 1;

        foreach ($summaryPegawai as $summary) {
            $sheet->fromArray([
                $noSummary++,
                $summary['kode_pegawai'],
                $summary['nama_pegawai'],
                $summary['hadir'],
                $summary['izin'],
                $summary['sakit'],
                $summary['cuti'],
                $summary['libur'],
                $summary['alpa'],
                $summary['telat'],
                $summary['pulang_cepat'],
            ], null, 'A' . $rowExcel);

            $sheet->getStyle('A' . $rowExcel . ':K' . $rowExcel)->applyFromArray($styleBorder);
            // kode pegawai & nama pegawai → indent (tidak center)
            $sheet->getStyle('B' . $rowExcel . ':C' . $rowExcel)->applyFromArray($styleIndent);
            $sheet->getStyle('D' . $rowExcel . ':K' . $rowExcel)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $rowExcel++;
        }

        foreach (range('A', 'N') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->freezePane('A2');

        $filename = 'export-presensi-' . $bulan . '.xlsx';

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    protected function formatJamExcel(?string $value): string
    {
        if (empty($value)) {
            return '-';
        }

        return date('H:i', strtotime($value));
    }

    protected function warnaHasilPresensi(string $hasil): string
    {
        return match ($hasil) {
            'hadir' => 'C6EFCE',
            'izin'  => 'D9EAF7',
            'sakit' => 'D9E1F2',
            'cuti'  => 'E4DFEC',
            'libur' => 'FFF2CC',
            'alpa'  => 'F4CCCC',
            default => 'FFFFFF',
        };
    }

    protected function pastikanLupaPresensi(object $presensi): ?array
    {
        if (($presensi->sumber_presensi ?? '') !== 'lupa_presensi') {
            return $this->hasilGagal([], 'Data hasil scan atau sinkron tidak dapat diubah/dihapus');
        }

        return null;
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
            'status' => 'tepat_waktu',
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
            'status' => 'tepat_waktu',
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
            'cuti' => '<span class="badge bg-secondary">Cuti</span>',
            'libur' => '<span class="badge bg-secondary">Libur</span>',
            default => '<span class="badge bg-light text-dark">Belum Sinkron</span>',
        };
    }

    protected function validasiJumlahJadwalSesuaiPegawaiAktif(string $tanggal): ?array
    {
        $jumlahPegawaiAktif = $this->pegawaiModel->countAktif();

        $jumlahJadwal = $this->jadwalKerjaModel->jumlahJadwalPadaTanggal($tanggal);

        if ($tanggal == date('Y-m-d') && $jumlahJadwal !== $jumlahPegawaiAktif) {
            return $this->hasilGagal(
                [],
                'Sinkron ditolak. Jumlah jadwal pada tanggal ' . tanggal_indonesia($tanggal) .
                    ' tidak sesuai dengan jumlah pegawai aktif. Jadwal: ' . $jumlahJadwal .
                    ', Pegawai aktif: ' . $jumlahPegawaiAktif
            );
        }

        return null;
    }

    protected function validasiBelumSinkronSebelumHariIni(string $tanggal): ?array
    {
        $rowsBelumSinkron = $this->jadwalKerjaModel->getTanggalBelumSinkronSebelumHariIni($tanggal);

        if ($tanggal === date('Y-m-d') && ! empty($rowsBelumSinkron)) {
            $tanggalBelumSinkron = array_map(
                fn($row) => tanggal_indonesia($row->tanggal),
                $rowsBelumSinkron
            );

            return $this->hasilGagal(
                [],
                'Sinkron ditolak. Presensi sebelumnya belum disinkronkan pada tanggal: ' . implode(', ', $tanggalBelumSinkron)
            );
        }

        return null;
    }

    protected function validasiPengajuanIzinPendingPadaTanggal(string $tanggal): ?array
    {
        $jumlahPending = $this->pengajuanIzinModel->countPendingByTanggal($tanggal);

        if ($jumlahPending > 0) {
            return $this->hasilGagal(
                [],
                'Sinkron ditolak. Masih ada ' . $jumlahPending . ' pengajuan izin/sakit/cuti yang belum diproses pada tanggal ' . tanggal_indonesia($tanggal) . '. Silakan setujui atau tolak terlebih dahulu.'
            );
        }

        return null;
    }

    public function ambilSelfieDrive(string $fileId): ?object
    {
        try {
            return $this->googleDriveService->downloadFile($fileId);
        } catch (\Throwable $e) {
            log_message('error', 'Download selfie Drive gagal: ' . $e->getMessage());
            return null;
        }
    }
}
