<?php

namespace App\Services;

use App\Models\HariLiburModel;
use App\Models\JadwalKerjaModel;
use App\Models\PresensiModel;

class HariLiburService extends BaseService
{
    protected HariLiburModel $hariLiburModel;
    protected JadwalKerjaModel $jadwalKerjaModel;
    protected PresensiModel $presensiModel;

    public function __construct()
    {
        parent::__construct();
        $this->hariLiburModel   = new HariLiburModel();
        $this->jadwalKerjaModel = new JadwalKerjaModel();
        $this->presensiModel = new PresensiModel();
    }

    public function dataTabel()
    {
        return $this->hariLiburModel->selectData();
    }

    public function simpan(array $post): array
    {
        return $this->transaksi(function () use ($post) {
            $minTanggal = date('Y-m-01');

            $rules = [
                'tanggal' => [
                    'label' => 'Tanggal',
                    'rules' => [
                        'required',
                        'valid_date[Y-m-d]',
                        'is_unique[hari_libur.tanggal]',
                        static function ($value, array $data, ?string &$error) use ($minTanggal): bool {
                            if ($value < $minTanggal) {
                                $error = 'Tanggal minimal harus tanggal 1 bulan berjalan';
                                return false;
                            }

                            return true;
                        }
                    ],
                    'errors' => [
                        'required'   => '{field} harus diisi',
                        'valid_date' => '{field} tidak valid',
                        'is_unique'  => '{field} sudah terdaftar',
                    ]
                ],
                'nama_libur' => [
                    'label'  => 'Nama Hari Libur',
                    'rules'  => 'required|min_length[3]|max_length[150]',
                    'errors' => [
                        'required'   => '{field} harus diisi',
                        'min_length' => '{field} minimal 3 karakter',
                        'max_length' => '{field} maksimal 150 karakter',
                    ]
                ],
                'keterangan' => [
                    'label'  => 'Keterangan',
                    'rules'  => 'permit_empty|max_length[255]',
                    'errors' => [
                        'max_length' => '{field} maksimal 255 karakter',
                    ]
                ],
            ];

            $validasi = $this->validasi($rules, $post);

            if (! $validasi['sukses']) {
                return $validasi;
            }

            $tanggal = $this->stringWajib($post['tanggal'] ?? '');

            $cekJadwal = $this->validasiJadwalAdaPadaTanggal($tanggal, 'tanggal');

            if ($cekJadwal !== null) {
                return $cekJadwal;
            }

            $cekPresensi = $this->validasiBelumAdaPresensiPadaTanggal((string) $tanggal, (string) 'simpan');

            if ($cekPresensi !== null) {
                return $cekPresensi;
            }

            $insert = $this->hariLiburModel->insert([
                'tanggal'    => $tanggal,
                'nama_libur' => $this->stringWajib($post['nama_libur'] ?? ''),
                'keterangan' => $this->stringAtauNull($post['keterangan'] ?? ''),
            ]);

            if (! $insert) {
                return $this->hasilGagal([
                    'general' => 'Data hari libur gagal disimpan'
                ]);
            }

            $hariLiburId = (int) $this->hariLiburModel->getInsertID();

            $this->catatAudit(
                'create',
                'hari_libur',
                $hariLiburId,
                'Menambahkan data hari libur: ' . $this->stringWajib($post['nama_libur'] . ' di tanggal ' . $tanggal ?? '')
            );

            $pegawaiTerdampak = $this->jadwalKerjaModel->getJadwalKerjaAktifPadaTanggalUntukLibur($tanggal);

            if (empty($pegawaiTerdampak)) {
                return $this->hasilSukses('Data Hari Libur Berhasil Ditambahkan');
            }

            return $this->hasilSukses('Data Hari Libur Berhasil Ditambahkan', [
                'butuh_konfirmasi'  => true,
                'hari_libur_id'     => $hariLiburId,
                'tanggal'           => $tanggal,
                'pegawai_terdampak' => $pegawaiTerdampak,
            ]);
        });
    }

    public function konfirmasiOverride(array $post): array
    {
        return $this->transaksi(function () use ($post) {
            $hariLiburId = $this->intAtauNull($post['hari_libur_id'] ?? null);

            if ($hariLiburId === null) {
                return $this->hasilGagal([
                    'general' => 'ID hari libur tidak valid'
                ]);
            }

            $hariLibur = $this->hariLiburModel->getHariLiburById($hariLiburId);

            if ($hariLibur === null) {
                return $this->hasilTidakDitemukan('Data Hari Libur Tidak Ditemukan');
            }

            $tetapKerjaIds = $post['tetap_kerja_ids'] ?? [];

            if (! is_array($tetapKerjaIds)) {
                $tetapKerjaIds = [];
            }

            // 🔥 cegah double snapshot kalau method ini kepanggil lagi
            if ($this->sudahPernahOverride($hariLiburId)) {
                $rollback = $this->rollbackOverrideHariLibur($hariLiburId);

                if (! $rollback['sukses']) {
                    return $rollback;
                }
            }

            $override = $this->overrideJadwalKerjaMenjadiLibur(
                $hariLiburId,
                (string) $hariLibur->nama_libur,
                (string) $hariLibur->tanggal,
                $tetapKerjaIds
            );

            if (! $override['sukses']) {
                return $override;
            }

            return $this->hasilSukses('Override jadwal kerja karena hari libur berhasil diproses');
        });
    }

    public function ubah(int $id, array $post): array
    {
        return $this->transaksi(function () use ($id, $post) {
            $hariLibur = $this->hariLiburModel->getHariLiburById($id);

            if ($hariLibur === null) {
                return $this->hasilTidakDitemukan('Data Hari Libur Tidak Ditemukan');
            }

            $minTanggal = date('Y-m-01');

            $rules = [
                'edit-tanggal' => [
                    'label' => 'Tanggal',
                    'rules' => [
                        'required',
                        'valid_date[Y-m-d]',
                        "is_unique[hari_libur.tanggal,id,{$id}]",
                        static function ($value, array $data, ?string &$error) use ($minTanggal): bool {
                            if ($value < $minTanggal) {
                                $error = 'Tanggal minimal harus tanggal 1 bulan berjalan';
                                return false;
                            }

                            return true;
                        }
                    ],
                    'errors' => [
                        'required'   => '{field} harus diisi',
                        'valid_date' => '{field} tidak valid',
                        'is_unique'  => '{field} sudah terdaftar',
                    ]
                ],
                'edit-nama_libur' => [
                    'label'  => 'Nama Hari Libur',
                    'rules'  => 'required|min_length[3]|max_length[150]',
                    'errors' => [
                        'required'   => '{field} harus diisi',
                        'min_length' => '{field} minimal 3 karakter',
                        'max_length' => '{field} maksimal 150 karakter',
                    ]
                ],
                'edit-keterangan' => [
                    'label'  => 'Keterangan',
                    'rules'  => 'permit_empty|max_length[255]',
                    'errors' => [
                        'max_length' => '{field} maksimal 255 karakter',
                    ]
                ],
            ];

            $validasi = $this->validasi($rules, $post);

            if (! $validasi['sukses']) {
                return $validasi;
            }

            $tanggalLama = (string) $hariLibur->tanggal;
            $tanggalBaru = $this->stringWajib($post['edit-tanggal'] ?? '');

            $cekJadwal = $this->validasiJadwalAdaPadaTanggal($tanggalBaru, 'edit-tanggal');

            if ($cekJadwal !== null) {
                return $cekJadwal;
            }

            $cekPresensi = $this->validasiBelumAdaPresensiPadaTanggal((string) $tanggalBaru, (string) 'ubah');
            if ($cekPresensi !== null) {
                return $cekPresensi;
            }

            if ($tanggalLama !== $tanggalBaru) {
                return $this->hasilGagal([
                    'edit-tanggal' => 'Perubahan tanggal hari libur tidak didukung. Hapus lalu buat ulang.'
                ]);
            }

            $simpan = $this->hariLiburModel->save([
                'id'         => $id,
                'tanggal'    => $tanggalBaru,
                'nama_libur' => $this->stringWajib($post['edit-nama_libur'] ?? ''),
                'keterangan' => $this->stringAtauNull($post['edit-keterangan'] ?? ''),
            ]);

            if (! $simpan) {
                return $this->hasilGagal([
                    'general' => 'Data hari libur gagal diubah'
                ]);
            }

            // 🔥 rollback dulu ke kondisi bersih
            if ($this->sudahPernahOverride($id)) {
                $rollback = $this->rollbackOverrideHariLibur($id);

                if (! $rollback['sukses']) {
                    return $rollback;
                }
            }

            $tetapKerjaIds = $post['edit_tetap_kerja_ids'] ?? [];

            if (! is_array($tetapKerjaIds)) {
                $tetapKerjaIds = [];
            }

            // 🔥 apply ulang dari kondisi yang sudah bersih
            $override = $this->overrideJadwalKerjaMenjadiLibur($id, $this->stringWajib($post['edit-nama_libur'] ?? ''), $tanggalBaru, $tetapKerjaIds);

            if (! $override['sukses']) {
                return $override;
            }

            $this->catatAudit(
                'update',
                'hari_libur',
                $id,
                'Mengubah data hari libur: ' . $this->stringWajib($post['edit-nama_libur'] ?? '')
            );

            return $this->hasilSukses('Data Hari Libur Berhasil Diubah');
        });
    }

    public function ambil(int $id): array
    {
        return $this->eksekusi(function () use ($id) {
            $hariLibur = $this->hariLiburModel->getHariLiburById($id);

            if ($hariLibur === null) {
                return $this->hasilTidakDitemukan('Data Hari Libur Tidak Ditemukan');
            }

            $tanggal = (string) $hariLibur->tanggal;

            $cekPresensi = $this->validasiBelumAdaPresensiPadaTanggal((string) $tanggal, (string) 'ambil');
            if ($cekPresensi !== null) {
                return $cekPresensi;
            }

            // 1. Pegawai yang saat ini masih kerja pada tanggal tersebut
            $pegawaiKerjaSekarang = $this->jadwalKerjaModel->getJadwalKerjaAktifPadaTanggalUntukLibur($tanggal);

            // 2. Pegawai yang sebelumnya sudah dioverride menjadi libur oleh hari libur ini
            $pegawaiOverrideLibur = $this->jadwalKerjaModel->db->table('jadwal_kerja')
                ->select('
                jadwal_kerja.id,
                jadwal_kerja.pegawai_id,
                jadwal_kerja.tanggal,
                jadwal_kerja.shift_id_sebelumnya AS shift_id,
                jadwal_kerja.status_hari,
                jadwal_kerja.sumber_data,
                jadwal_kerja.catatan_sebelumnya AS catatan,
                pegawai.kode_pegawai,
                pegawai.nama_pegawai,
                shift.nama_shift
            ')
                ->join('pegawai', 'pegawai.id = jadwal_kerja.pegawai_id', 'left')
                ->join('shift', 'shift.id = jadwal_kerja.shift_id_sebelumnya', 'left')
                ->where('jadwal_kerja.hari_libur_id', $id)
                ->get()
                ->getResult();

            // Gabungkan keduanya tanpa duplikat pegawai
            $pegawaiTerdampakMap = [];

            foreach ($pegawaiKerjaSekarang as $item) {
                $pegawaiTerdampakMap[(int) $item->pegawai_id] = $item;
            }

            foreach ($pegawaiOverrideLibur as $item) {
                if (! isset($pegawaiTerdampakMap[(int) $item->pegawai_id])) {
                    $pegawaiTerdampakMap[(int) $item->pegawai_id] = $item;
                }
            }

            $pegawaiTerdampak = array_values($pegawaiTerdampakMap);

            // Pegawai tetap kerja = yang tampil di daftar terdampak
            // tapi TIDAK sedang dioverride oleh hari libur ini
            $pegawaiOverrideIds = [];
            foreach ($pegawaiOverrideLibur as $item) {
                $pegawaiOverrideIds[] = (int) $item->pegawai_id;
            }

            $pegawaiTetapKerjaIds = [];
            foreach ($pegawaiTerdampak as $item) {
                if (! in_array((int) $item->pegawai_id, $pegawaiOverrideIds, true)) {
                    $pegawaiTetapKerjaIds[] = (int) $item->pegawai_id;
                }
            }

            return $this->hasilData([
                'hari_libur'              => $hariLibur,
                'pegawai_terdampak'       => $pegawaiTerdampak,
                'pegawai_tetap_kerja_ids' => $pegawaiTetapKerjaIds,
            ]);
        });
    }

    public function hapus(int $id): array
    {
        return $this->transaksi(function () use ($id) {
            $hariLibur = $this->hariLiburModel->getHariLiburById($id);

            if ($hariLibur === null) {
                return $this->hasilTidakDitemukan('Data Hari Libur Tidak Ada Di Database');
            }

            $cekPresensi = $this->validasiBelumAdaPresensiPadaTanggal((string) $hariLibur->tanggal, (string) 'hapus');

            if ($cekPresensi !== null) {
                return $cekPresensi;
            }

            $rollback = $this->rollbackOverrideHariLibur($id);

            if (! $rollback['sukses']) {
                return $rollback;
            }

            $hapus = $this->hariLiburModel->delete($id);

            if (! $hapus) {
                return $this->hasilGagal([], 'Data Hari Libur Gagal Dihapus');
            }

            $this->catatAudit(
                'delete',
                'hari_libur',
                $id,
                'Menghapus data hari libur: ' . (string) $hariLibur->nama_libur . ' tanggal ' . (string) $hariLibur->tanggal
            );

            return $this->hasilSukses('Data Hari Libur Berhasil Dihapus');
        });
    }

    protected function overrideJadwalKerjaMenjadiLibur(int $hariLiburId, string $hariLiburNama, string $tanggal, array $tetapKerjaIds = []): array
    {
        $tetapKerjaIds = array_map('intval', $tetapKerjaIds);

        $jadwalList = $this->jadwalKerjaModel->getJadwalKerjaAktifPadaTanggalUntukLibur($tanggal);

        foreach ($jadwalList as $jadwal) {
            if (in_array((int) $jadwal->pegawai_id, $tetapKerjaIds, true)) {
                continue;
            }

            // guard tambahan
            if (($jadwal->sumber_data ?? 'manual') === 'hari_libur' && (int) ($jadwal->hari_libur_id ?? 0) === $hariLiburId) {
                continue;
            }

            $simpan = $this->jadwalKerjaModel->save([
                'id'                      => $jadwal->id,
                'pegawai_id'              => $jadwal->pegawai_id,
                'tanggal'                 => $jadwal->tanggal,
                'shift_id'                => null,
                'status_hari'             => 'libur',
                'sumber_data'             => 'hari_libur',
                'hari_libur_id'           => $hariLiburId,
                'catatan'                 => $hariLiburNama,

                'shift_id_sebelumnya'     => $jadwal->shift_id,
                'status_hari_sebelumnya'  => $jadwal->status_hari,
                'catatan_sebelumnya'      => $jadwal->catatan,
                'sumber_data_sebelumnya'  => $jadwal->sumber_data,
            ]);

            if (! $simpan) {
                return $this->hasilGagal([], 'Gagal override jadwal kerja ke status libur');
            }
        }

        return $this->hasilSukses();
    }

    protected function rollbackOverrideHariLibur(int $hariLiburId): array
    {
        $jadwalList = $this->jadwalKerjaModel->getJadwalByHariLiburId($hariLiburId);

        foreach ($jadwalList as $jadwal) {
            $restore = $this->jadwalKerjaModel->save([
                'id'                      => $jadwal->id,
                'pegawai_id'              => $jadwal->pegawai_id,
                'tanggal'                 => $jadwal->tanggal,
                'shift_id'                => $jadwal->shift_id_sebelumnya,
                'status_hari'             => $jadwal->status_hari_sebelumnya,
                'sumber_data'             => $jadwal->sumber_data_sebelumnya,
                'hari_libur_id'           => null,
                'pengajuan_izin_id'       => $jadwal->pengajuan_izin_id,
                'catatan'                 => $jadwal->catatan_sebelumnya,

                'shift_id_sebelumnya'     => null,
                'status_hari_sebelumnya'  => null,
                'catatan_sebelumnya'      => null,
                'sumber_data_sebelumnya'  => null,
            ]);

            if (! $restore) {
                return $this->hasilGagal([], 'Gagal rollback jadwal kerja dari hari libur');
            }
        }

        return $this->hasilSukses();
    }

    protected function getPegawaiKerjaPadaTanggal(string $tanggal): array
    {
        return $this->jadwalKerjaModel->getJadwalKerjaAktifPadaTanggalUntukLibur($tanggal);
    }

    protected function sudahPernahOverride(int $hariLiburId): bool
    {
        return count($this->jadwalKerjaModel->getJadwalByHariLiburId($hariLiburId)) > 0;
    }

    protected function validasiJadwalAdaPadaTanggal(string $tanggal, string $field = 'tanggal'): ?array
    {
        $jumlahJadwal = $this->jadwalKerjaModel->jumlahJadwalPadaTanggal($tanggal);

        if ($jumlahJadwal < 1) {
            return $this->hasilGagal([
                $field => 'Hari libur tidak dapat dibuat karena belum ada jadwal kerja pada tanggal tersebut'
            ]);
        }

        return null;
    }

    protected function validasiBelumAdaPresensiPadaTanggal(string $tanggal, string $method): ?array
    {
        $jumlahPresensi = $this->presensiModel->countByTanggal($tanggal);
        $mtd = match ($method) {
            'simpan'  => 'simpan',
            'ubah' => 'ubah',
            'ambil' => 'ubah',
            'hapus' => 'hapus',
            default => '',
        };

        $field = match ($method) {
            'simpan'  => 'tanggal',
            'ubah' => 'edit-tanggal',
            default => '',
        };
        if ($jumlahPresensi > 0) {
            if ($method == 'simpan' || $method == 'ubah') {
                return $this->hasilGagal([
                    $field => 'Hari libur tidak dapat di' . $mtd . ' karena sudah ada presensi pada tanggal tersebut'
                ]);
            }

            return $this->hasilGagal([], 'Hari libur tidak dapat di' . $mtd . ' karena sudah ada presensi pada tanggal tersebut');
        }

        return null;
    }
}
