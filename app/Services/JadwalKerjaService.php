<?php

namespace App\Services;

use App\Models\HariLiburModel;
use App\Models\JadwalKerjaModel;
use App\Models\PegawaiModel;
use App\Models\PresensiModel;
use App\Models\ShiftModel;

class JadwalKerjaService extends BaseService
{
    protected JadwalKerjaModel $jadwalKerjaModel;
    protected PegawaiModel $pegawaiModel;
    protected ShiftModel $shiftModel;
    protected HariLiburModel $hariLiburModel;
    protected PresensiModel $presensiModel;

    public function __construct()
    {
        parent::__construct();

        $this->jadwalKerjaModel = new JadwalKerjaModel();
        $this->pegawaiModel     = new PegawaiModel();
        $this->shiftModel       = new ShiftModel();
        $this->hariLiburModel   = new HariLiburModel();
        $this->presensiModel    = new PresensiModel();
    }

    public function dataTabel()
    {
        return $this->jadwalKerjaModel->selectData();
    }

    public function getPegawaiDropdown(): array
    {
        return $this->eksekusi(function () {
            $pegawai = $this->pegawaiModel->getPegawaiDropdown();

            return $this->hasilData([
                'pegawai' => $pegawai,
            ]);
        });
    }

    public function getShiftDropdown(): array
    {
        return $this->eksekusi(function () {
            $shift = $this->shiftModel->getShiftDropdown();

            return $this->hasilData([
                'shift' => $shift,
            ]);
        });
    }

    /**
     * Generate jadwal kerja multiple tanggal.
     * Payload:
     * - tanggal: "YYYY-MM-DD,YYYY-MM-DD"
     * - shift_pegawai[shift_id][]: pegawai_id
     * - libur_pegawai[]: pegawai_id
     * - catatan: optional
     *
     * Rule utama:
     * - semua pegawai aktif wajib dipilih tepat satu section
     * - section boleh kosong
     */
    public function simpan(array $post): array
    {
        return $this->transaksi(function () use ($post) {
            $hariIni   = date('Y-m-d');
            $createdBy = $this->intAtauNull(session()->get('user_id'));
            $catatan   = $this->stringAtauNull($post['catatan'] ?? '');

            $tanggalList = $this->parseTanggalList($post['tanggal'] ?? '');

            if (empty($tanggalList)) {
                return $this->hasilGagal([
                    'tanggal' => 'Tanggal harus dipilih',
                ]);
            }

            foreach ($tanggalList as $tanggal) {
                if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
                    return $this->hasilGagal([
                        'tanggal' => 'Format tanggal tidak valid',
                    ]);
                }

                if ($tanggal < $hariIni) {
                    return $this->hasilGagal([
                        'tanggal' => 'Tidak diizinkan membuat jadwal sebelum hari ini',
                    ]);
                }
            }

            $items = $this->bangunItemGenerate($post['shift_pegawai'] ?? [], $post['libur_pegawai'] ?? []);

            $validasiCoverage = $this->validasiCoveragePegawaiAktif($items);
            if (! $validasiCoverage['sukses']) {
                return $validasiCoverage;
            }

            $validasiShift = $this->validasiSemuaShiftGenerate($items);
            if (! $validasiShift['sukses']) {
                return $validasiShift;
            }

            $duplikatJadwal = $this->cariDuplikatJadwalGenerate($items, $tanggalList);
            if (! empty($duplikatJadwal)) {
                return $this->hasilGagal([
                    'tanggal' => 'Sebagian pegawai sudah memiliki jadwal: ' . implode(', ', array_slice($duplikatJadwal, 0, 10)),
                ]);
            }

            $rows = [];
            $now  = date('Y-m-d H:i:s');

            foreach ($tanggalList as $tanggal) {
                foreach ($items as $item) {
                    $rows[] = [
                        'pegawai_id'             => (int) $item['pegawai_id'],
                        'tanggal'                => $tanggal,
                        'shift_id'               => $item['shift_id'],
                        'status_hari'            => $item['status_hari'],
                        'sumber_data'            => 'manual',
                        'pengajuan_izin_id'      => null,
                        'hari_libur_id'          => null,
                        'shift_id_sebelumnya'    => null,
                        'status_hari_sebelumnya' => null,
                        'catatan_sebelumnya'     => null,
                        'sumber_data_sebelumnya' => null,
                        'catatan'                => $catatan,
                        'created_by'             => $createdBy,
                        'created_at'             => $now,
                        'updated_at'             => $now,
                    ];
                }
            }

            if (! $this->jadwalKerjaModel->insertBatch($rows)) {
                return $this->hasilGagal([
                    'general' => 'Data jadwal kerja gagal disimpan',
                ]);
            }

            $this->catatAudit(
                'create',
                'jadwal_kerja',
                null,
                'Generate jadwal kerja untuk ' . count($tanggalList) . ' tanggal dan ' . count($items) . ' pegawai aktif'
            );

            $hariLiburTerdeteksi = [];
            foreach ($tanggalList as $tanggal) {
                $hariLibur = $this->getInfoHariLiburPadaTanggal($tanggal);

                if ($hariLibur !== null) {
                    $hariLiburTerdeteksi[] = [
                        'tanggal'    => $tanggal,
                        'nama_libur' => $hariLibur->nama_libur,
                    ];
                }
            }

            return $this->hasilSukses('Jadwal kerja berhasil digenerate', [
                'jumlah_tanggal'      => count($tanggalList),
                'jumlah_pegawai'      => count($items),
                'jumlah_data'         => count($rows),
                'warning_hari_libur'  => ! empty($hariLiburTerdeteksi),
                'hari_libur'          => $hariLiburTerdeteksi,
                'status_hari_kerja'   => true,
            ]);
        });
    }

    public function ubah(int $id, array $post): array
    {
        return $this->eksekusi(function () use ($id, $post) {
            $jadwal = $this->jadwalKerjaModel->getJadwalById($id);

            if ($jadwal === null) {
                return $this->hasilTidakDitemukan('Data Jadwal Kerja Tidak Ditemukan');
            }

            if (($jadwal->sumber_data ?? 'manual') !== 'manual') {
                return $this->hasilGagal([], 'Data jadwal hasil override sistem tidak dapat diubah manual');
            }

            if ($jadwal->tanggal < date('Y-m-d')) {
                return $this->hasilGagal([
                    'edit-tanggal' => 'Tidak diizinkan mengubah jadwal sebelum hari ini',
                ]);
            }

            $rules = $this->rulesUbah();

            $validasi = $this->validasi($rules, $post);
            if (! $validasi['sukses']) {
                return $validasi;
            }

            $pegawaiId   = $this->intAtauNull($post['edit-pegawai_id'] ?? null);
            $tanggal     = $this->stringWajib($post['edit-tanggal'] ?? '');
            $statusHari  = $this->stringWajib($post['edit-status_hari'] ?? '');
            $shiftId     = $this->intAtauNull($post['edit-shift_id'] ?? null);
            $catatan     = $this->stringAtauNull($post['edit-catatan'] ?? '');

            $validasiPegawai = $this->validasiPegawaiAktif($pegawaiId, 'edit-pegawai_id');
            if (! $validasiPegawai['sukses']) {
                return $validasiPegawai;
            }

            $validasiStatus = $this->validasiStatusDanShift($statusHari, $shiftId, 'edit-status_hari', 'edit-shift_id');
            if (! $validasiStatus['sukses']) {
                return $validasiStatus;
            }

            if ($statusHari === 'kerja') {
                $validasiShift = $this->validasiShiftAktif($shiftId, 'edit-shift_id');
                if (! $validasiShift['sukses']) {
                    return $validasiShift;
                }
            } else {
                $shiftId = null;
            }

            $bentrok = $this->jadwalKerjaModel->jumlahBentrokJadwal($pegawaiId, $tanggal, $id);
            if ($bentrok > 0) {
                return $this->hasilGagal([
                    'edit-tanggal' => 'Jadwal pegawai pada tanggal tersebut sudah ada',
                ]);
            }

            $simpan = $this->jadwalKerjaModel->save([
                'id'          => $id,
                'pegawai_id'  => $pegawaiId,
                'tanggal'     => $tanggal,
                'shift_id'    => $shiftId,
                'status_hari' => $statusHari,
                'catatan'     => $catatan,
            ]);

            if (! $simpan) {
                return $this->hasilGagal([
                    'general' => 'Data jadwal kerja gagal diubah',
                ]);
            }

            $this->catatAudit(
                'update',
                'jadwal_kerja',
                $id,
                'Mengubah data jadwal kerja pegawai ID ' . $pegawaiId . ' pada tanggal ' . $tanggal
            );

            $hariLiburTerdeteksi = [];
            $hariLibur = $this->getInfoHariLiburPadaTanggal($tanggal);

            if ($hariLibur !== null) {
                $hariLiburTerdeteksi[] = [
                    'tanggal'    => $tanggal,
                    'nama_libur' => $hariLibur->nama_libur,
                ];
            }

            return $this->hasilSukses('Data Jadwal Kerja Berhasil Diubah', [
                'warning_hari_libur' => ! empty($hariLiburTerdeteksi),
                'hari_libur'         => $hariLiburTerdeteksi,
                'status_hari_kerja'  => $statusHari === 'kerja',
            ]);
        });
    }

    public function ambil(int $id): array
    {
        return $this->eksekusi(function () use ($id) {
            $hariIni = date('Y-m-d');
            $jadwal = $this->jadwalKerjaModel->getJadwalById($id);

            if ($jadwal === null) {
                return $this->hasilTidakDitemukan('Data Jadwal Kerja Tidak Ditemukan');
            }

            if ($jadwal->tanggal < $hariIni) {
                return $this->hasilGagal([], 'Tidak diizinkan mengubah jadwal sebelum hari ini');
            }

            if ($this->presensiModel->sudahAdaPresensi($jadwal->pegawai_id, $jadwal->tanggal)) {
                return $this->hasilGagal([], 'Tidak diizinkan mengubah jadwal yang sudah presensi');
            }

            $hariLibur = $this->hariLiburModel->where('tanggal', $jadwal->tanggal)->first();

            return $this->hasilData([
                'jadwal'     => $jadwal,
                'hari_libur' => $hariLibur,
            ]);
        });
    }

    protected function parseTanggalList($value): array
    {
        return array_values(array_unique(array_filter(array_map('trim', explode(',', (string) $value)))));
    }

    protected function bangunItemGenerate(array $shiftPegawai, array $liburPegawai): array
    {
        $items = [];

        foreach ($shiftPegawai as $shiftId => $pegawaiIds) {
            $shiftId = (int) $shiftId;

            if ($shiftId <= 0) {
                continue;
            }

            foreach ((array) $pegawaiIds as $pegawaiId) {
                $pegawaiId = (int) $pegawaiId;

                if ($pegawaiId <= 0) {
                    continue;
                }

                $items[] = [
                    'pegawai_id'  => $pegawaiId,
                    'shift_id'    => $shiftId,
                    'status_hari' => 'kerja',
                ];
            }
        }

        foreach ((array) $liburPegawai as $pegawaiId) {
            $pegawaiId = (int) $pegawaiId;

            if ($pegawaiId <= 0) {
                continue;
            }

            $items[] = [
                'pegawai_id'  => $pegawaiId,
                'shift_id'    => null,
                'status_hari' => 'libur',
            ];
        }

        return $items;
    }

    protected function validasiCoveragePegawaiAktif(array $items): array
    {
        $pegawaiAktif = $this->pegawaiModel
            ->select('id, kode_pegawai, nama_pegawai')
            ->where('is_active', 1)
            ->orderBy('nama_pegawai', 'ASC')
            ->findAll();

        if (empty($pegawaiAktif)) {
            return $this->hasilGagal([
                'pegawai' => 'Tidak ada pegawai aktif untuk dijadwalkan',
            ]);
        }

        $pegawaiAktifIds = array_map(static fn($row) => (int) $row->id, $pegawaiAktif);
        $pegawaiTerpilih = array_map('intval', array_column($items, 'pegawai_id'));

        $duplikatPegawai = array_values(array_unique(array_diff_assoc($pegawaiTerpilih, array_unique($pegawaiTerpilih))));

        if (! empty($duplikatPegawai)) {
            return $this->hasilGagal([
                'pegawai' => 'Pegawai tidak boleh dipilih lebih dari satu section',
            ]);
        }

        $pegawaiTidakAktifDipilih = array_values(array_diff($pegawaiTerpilih, $pegawaiAktifIds));
        if (! empty($pegawaiTidakAktifDipilih)) {
            return $this->hasilGagal([
                'pegawai' => 'Terdapat pegawai tidak aktif atau tidak valid yang dipilih',
            ]);
        }

        $pegawaiBelumDipilih = array_values(array_diff($pegawaiAktifIds, $pegawaiTerpilih));
        if (! empty($pegawaiBelumDipilih)) {
            $namaBelumDipilih = [];

            foreach ($pegawaiAktif as $pegawai) {
                if (in_array((int) $pegawai->id, $pegawaiBelumDipilih, true)) {
                    $namaBelumDipilih[] = '<li>' . $pegawai->nama_pegawai . '</li>';
                }
            }

            return $this->hasilGagal([
                'pegawai' => '<strong>Semua pegawai aktif harus dijadwalkan. Belum dipilih: </strong><br /><ul>' . implode('', $namaBelumDipilih) . '</ul>',
            ]);
        }

        return $this->hasilSukses();
    }

    protected function validasiSemuaShiftGenerate(array $items): array
    {
        $shiftIds = [];

        foreach ($items as $item) {
            if (($item['status_hari'] ?? '') === 'kerja') {
                $shiftIds[] = (int) $item['shift_id'];
            }
        }

        $shiftIds = array_values(array_unique($shiftIds));

        foreach ($shiftIds as $shiftId) {
            $validasiShift = $this->validasiShiftAktif($shiftId, 'shift_pegawai');

            if (! $validasiShift['sukses']) {
                return $validasiShift;
            }
        }

        return $this->hasilSukses();
    }

    protected function cariDuplikatJadwalGenerate(array $items, array $tanggalList): array
    {
        $duplikat = [];

        foreach ($items as $item) {
            foreach ($tanggalList as $tanggal) {
                $bentrok = $this->jadwalKerjaModel->jumlahBentrokJadwal((int) $item['pegawai_id'], $tanggal);

                if ($bentrok > 0) {
                    $duplikat[] = $tanggal . ' - Pegawai ID ' . $item['pegawai_id'];
                }
            }
        }

        return array_values(array_unique($duplikat));
    }

    protected function rulesUbah(): array
    {
        return [
            'edit-pegawai_id' => [
                'label'  => 'Pegawai',
                'rules'  => 'required|integer',
                'errors' => [
                    'required' => '{field} harus dipilih',
                    'integer'  => '{field} tidak valid',
                ],
            ],
            'edit-tanggal' => [
                'label'  => 'Tanggal',
                'rules'  => 'required|valid_date[Y-m-d]',
                'errors' => [
                    'required'   => '{field} harus diisi',
                    'valid_date' => '{field} tidak valid',
                ],
            ],
            'edit-shift_id' => [
                'label'  => 'Shift',
                'rules'  => 'permit_empty|integer',
                'errors' => [
                    'integer' => '{field} tidak valid',
                ],
            ],
            'edit-status_hari' => [
                'label'  => 'Status Hari',
                'rules'  => 'required|in_list[kerja,libur]',
                'errors' => [
                    'required' => '{field} harus dipilih',
                    'in_list'  => '{field} tidak valid',
                ],
            ],
            'edit-catatan' => [
                'label'  => 'Catatan',
                'rules'  => 'permit_empty|max_length[255]',
                'errors' => [
                    'max_length' => '{field} maksimal 255 karakter',
                ],
            ],
        ];
    }

    protected function validasiPegawaiAktif(?int $pegawaiId, string $field): array
    {
        if ($pegawaiId === null) {
            return $this->hasilGagal([
                $field => 'Pegawai harus dipilih',
            ]);
        }

        $pegawai = $this->pegawaiModel->getPegawaiById($pegawaiId);

        if ($pegawai === null) {
            return $this->hasilGagal([
                $field => 'Data pegawai tidak ditemukan',
            ]);
        }

        if ((int) ($pegawai->is_active ?? 0) !== 1) {
            return $this->hasilGagal([
                $field => 'Pegawai tidak aktif',
            ]);
        }

        return $this->hasilSukses();
    }

    protected function validasiShiftAktif(?int $shiftId, string $field): array
    {
        if ($shiftId === null) {
            return $this->hasilGagal([
                $field => 'Shift harus dipilih',
            ]);
        }

        $shift = $this->shiftModel->getShiftById($shiftId);

        if ($shift === null) {
            return $this->hasilGagal([
                $field => 'Data shift tidak ditemukan',
            ]);
        }

        if ((int) ($shift->is_active ?? 0) !== 1) {
            return $this->hasilGagal([
                $field => 'Shift tidak aktif',
            ]);
        }

        return $this->hasilSukses();
    }

    protected function validasiStatusDanShift(
        string $statusHari,
        ?int $shiftId,
        string $fieldStatus,
        string $fieldShift
    ): array {
        if (! in_array($statusHari, ['kerja', 'libur'], true)) {
            return $this->hasilGagal([
                $fieldStatus => 'Status hari tidak valid',
            ]);
        }

        if ($statusHari === 'kerja' && $shiftId === null) {
            return $this->hasilGagal([
                $fieldShift => 'Shift wajib dipilih jika status hari kerja',
            ]);
        }

        if ($statusHari === 'libur' && $shiftId !== null) {
            return $this->hasilGagal([
                $fieldShift => 'Shift harus dikosongkan jika status hari libur',
            ]);
        }

        return $this->hasilSukses();
    }

    protected function getInfoHariLiburPadaTanggal(string $tanggal): ?object
    {
        return $this->hariLiburModel->where('tanggal', $tanggal)->first();
    }
}
