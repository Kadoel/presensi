<?php

namespace App\Services;

use App\Models\JadwalKerjaModel;
use App\Models\PegawaiModel;
use App\Models\PengajuanIzinModel;
use App\Models\HariLiburModel;

class PengajuanIzinService extends BaseService
{
    protected PengajuanIzinModel $pengajuanIzinModel;
    protected PegawaiModel $pegawaiModel;
    protected JadwalKerjaModel $jadwalKerjaModel;
    protected HariLiburModel $hariLiburModel;


    public function __construct()
    {
        parent::__construct();
        $this->pengajuanIzinModel = new PengajuanIzinModel();
        $this->pegawaiModel       = new PegawaiModel();
        $this->jadwalKerjaModel   = new JadwalKerjaModel();
        $this->hariLiburModel   = new HariLiburModel();
    }

    public function dataTabel()
    {
        return $this->pengajuanIzinModel->selectData();
    }

    public function getPegawaiDropdown(): array
    {
        return $this->eksekusi(function () {
            return $this->hasilData([
                'pegawai' => $this->pegawaiModel->db->table('pegawai')
                    ->select('pegawai.id, pegawai.kode_pegawai, pegawai.nama_pegawai')
                    ->where('pegawai.is_active', 1)
                    ->orderBy('pegawai.nama_pegawai', 'ASC')
                    ->get()
                    ->getResult()
            ]);
        });
    }

    public function simpan(array $post, $file): array
    {
        return $this->transaksi(function () use ($post, $file) {
            $rules =  [
                'pegawai_id' => [
                    'label'  => 'Pegawai',
                    'rules'  => 'required|integer',
                    'errors' => [
                        'required' => '{field} harus dipilih',
                        'integer'  => '{field} tidak valid',
                    ]
                ],
                'jenis' => [
                    'label'  => 'Jenis',
                    'rules'  => 'required|in_list[izin,sakit]',
                    'errors' => [
                        'required' => '{field} harus dipilih',
                        'in_list'  => '{field} tidak valid',
                    ]
                ],
                'tanggal_mulai' => [
                    'label'  => 'Tanggal Mulai',
                    'rules'  => 'required|valid_date[Y-m-d]',
                    'errors' => [
                        'required'   => '{field} harus diisi',
                        'valid_date' => '{field} tidak valid',
                    ]
                ],
                'tanggal_selesai' => [
                    'label'  => 'Tanggal Selesai',
                    'rules'  => 'required|valid_date[Y-m-d]',
                    'errors' => [
                        'required'   => '{field} harus diisi',
                        'valid_date' => '{field} tidak valid',
                    ]
                ],
                'alasan' => [
                    'label'  => 'Alasan',
                    'rules'  => 'required|min_length[5]',
                    'errors' => [
                        'required'   => '{field} harus diisi',
                        'min_length' => '{field} minimal 5 karakter',
                    ]
                ]
            ];

            if ($file && $file->getError() != 4) {
                $rules['lampiran'] = [
                    'label'  => 'Lampiran',
                    'rules'  => 'max_size[lampiran,2048]|mime_in[lampiran,application/pdf]|ext_in[lampiran,pdf]',
                    'errors' => [
                        'uploaded' => '{field} gagal diupload',
                        'mime_in'  => '{field} harus berupa file PDF',
                        'ext_in'   => '{field} harus berformat PDF',
                        'max_size' => 'Ukuran {field} harus maksimal 2MB'
                    ]
                ];
            }

            $validasi = $this->validasi($rules, $post);

            if (! $validasi['sukses']) {
                return $validasi;
            }

            $pegawaiId      = $this->intAtauNull($post['pegawai_id'] ?? null);
            $tanggalMulai   = $this->stringWajib($post['tanggal_mulai'] ?? '');
            $tanggalSelesai = $this->stringWajib($post['tanggal_selesai'] ?? '');

            $validasiPegawai = $this->validasiPegawaiAktif($pegawaiId, 'pegawai_id');
            if (! $validasiPegawai['sukses']) {
                return $validasiPegawai;
            }

            $validasiTanggal = $this->validasiRentangTanggal(
                $tanggalMulai,
                $tanggalSelesai,
                'tanggal_mulai',
                'tanggal_selesai'
            );
            if (! $validasiTanggal['sukses']) {
                return $validasiTanggal;
            }

            $validasiHariLibur = $this->validasiBukanHariLibur(
                $tanggalMulai,
                $tanggalSelesai,
                'tanggal_mulai',
                'tanggal_selesai'
            );

            if (! $validasiHariLibur['sukses']) {
                return $validasiHariLibur;
            }

            $bentrok = $this->pengajuanIzinModel->jumlahBentrokTanggal(
                $pegawaiId,
                $tanggalMulai,
                $tanggalSelesai
            );

            if ($bentrok > 0) {
                return $this->hasilGagal([
                    'tanggal_mulai'   => 'Rentang tanggal pengajuan bentrok dengan data lain',
                    'tanggal_selesai' => 'Rentang tanggal pengajuan bentrok dengan data lain',
                ]);
            }

            $isAdmin    = session()->get('role') === 'admin';
            $approvedBy = $isAdmin ? $this->intAtauNull(session()->get('user_id')) : null;

            $data = [
                'pegawai_id'        => $pegawaiId,
                'jenis'             => $this->stringWajib($post['jenis'] ?? ''),
                'tanggal_mulai'     => $tanggalMulai,
                'tanggal_selesai'   => $tanggalSelesai,
                'alasan'            => $this->stringWajib($post['alasan'] ?? ''),
                'status'            => $isAdmin ? 'approved' : 'pending',
                'catatan_approval'  => $isAdmin ? 'Input oleh admin' : null,
                'approved_by'       => $approvedBy,
                'approved_at'       => $isAdmin ? date('Y-m-d H:i:s') : null,
            ];

            $namaFile = $this->simpanLampiran($file, $pegawaiId);
            if ($namaFile !== null) {
                $data['lampiran'] = $namaFile;
            }

            $insert = $this->pengajuanIzinModel->insert($data);

            if (! $insert) {
                if ($namaFile !== null) {
                    $this->hapusFileLampiran($namaFile);
                }

                return $this->hasilGagal([
                    'general' => 'Data pengajuan izin gagal disimpan'
                ]);
            }

            $idPengajuan = (int) $this->pengajuanIzinModel->getInsertID();

            if ($idPengajuan <= 0) {
                if ($namaFile !== null) {
                    $this->hapusFileLampiran($namaFile);
                }

                return $this->hasilGagal([
                    'general' => 'ID data pengajuan izin gagal didapatkan'
                ]);
            }

            if ($isAdmin) {
                $pengajuan = $this->pengajuanIzinModel->getPengajuanById($idPengajuan);

                if ($pengajuan === null) {
                    if ($namaFile !== null) {
                        $this->hapusFileLampiran($namaFile);
                    }

                    return $this->hasilGagal([
                        'general' => 'Data pengajuan berhasil disimpan tetapi gagal ditemukan kembali'
                    ]);
                }

                $sinkron = $this->sinkronkanApproveKeJadwalKerja($pengajuan, $approvedBy);

                if (! $sinkron['sukses']) {
                    if ($namaFile !== null) {
                        $this->hapusFileLampiran($namaFile);
                    }

                    return $sinkron;
                }
            }

            $this->catatAudit(
                'create',
                'pengajuan_izin',
                $idPengajuan,
                'Menambahkan pengajuan ' . $data['jenis'] . ' untuk pegawai ID ' . $pegawaiId
                    . ' pada tanggal ' . $tanggalMulai . ' s.d. ' . $tanggalSelesai
                    . ' dengan status ' . $data['status']
            );

            return $this->hasilSukses('Data Pengajuan Izin Berhasil Ditambahkan');
        });
    }

    public function ubah(int $id, array $post, $file): array
    {
        return $this->eksekusi(function () use ($id, $post, $file) {
            $pengajuan = $this->pengajuanIzinModel->getPengajuanById($id);

            if ($pengajuan === null) {
                return $this->hasilTidakDitemukan('Data Pengajuan Izin Tidak Ditemukan');
            }

            if (($pengajuan->status ?? 'pending') === 'approved') {
                return $this->hasilGagal([], 'Data yang sudah disetujui tidak dapat diubah');
            }

            $tanggalHariIni = date('Y-m-d');

            if (($pengajuan->tanggal_mulai ?? '') < $tanggalHariIni) {
                return $this->hasilGagal([], 'Data pengajuan yang tanggal mulainya sudah lewat tidak dapat diubah');
            }

            $rules = [
                'edit-pegawai_id' => [
                    'label'  => 'Pegawai',
                    'rules'  => 'required|integer',
                    'errors' => [
                        'required' => '{field} harus dipilih',
                        'integer'  => '{field} tidak valid',
                    ]
                ],
                'edit-jenis' => [
                    'label'  => 'Jenis',
                    'rules'  => 'required|in_list[izin,sakit]',
                    'errors' => [
                        'required' => '{field} harus dipilih',
                        'in_list'  => '{field} tidak valid',
                    ]
                ],
                'edit-tanggal_mulai' => [
                    'label'  => 'Tanggal Mulai',
                    'rules'  => 'required|valid_date[Y-m-d]',
                    'errors' => [
                        'required'   => '{field} harus diisi',
                        'valid_date' => '{field} tidak valid',
                    ]
                ],
                'edit-tanggal_selesai' => [
                    'label'  => 'Tanggal Selesai',
                    'rules'  => 'required|valid_date[Y-m-d]',
                    'errors' => [
                        'required'   => '{field} harus diisi',
                        'valid_date' => '{field} tidak valid',
                    ]
                ],
                'edit-alasan' => [
                    'label'  => 'Alasan',
                    'rules'  => 'required|min_length[5]',
                    'errors' => [
                        'required'   => '{field} harus diisi',
                        'min_length' => '{field} minimal 5 karakter',
                    ]
                ]
            ];

            if ($file && $file->getError() != 4) {
                $rules['edit-lampiran'] = [
                    'label'  => 'Lampiran',
                    'rules'  => 'mime_in[edit-lampiran,application/pdf]|ext_in[edit-lampiran,pdf]|max_size[edit-lampiran,2048]',
                    'errors' => [
                        'uploaded' => '{field} gagal diupload',
                        'mime_in'  => '{field} harus berupa file PDF',
                        'ext_in'   => '{field} harus berformat PDF',
                        'max_size' => 'Ukuran maksimal 2MB'
                    ]
                ];
            }

            $validasi = $this->validasi($rules, $post);

            if (! $validasi['sukses']) {
                return $validasi;
            }

            $pegawaiId      = $this->intAtauNull($post['edit-pegawai_id'] ?? null);
            $tanggalMulai   = $this->stringWajib($post['edit-tanggal_mulai'] ?? '');
            $tanggalSelesai = $this->stringWajib($post['edit-tanggal_selesai'] ?? '');

            $validasiPegawai = $this->validasiPegawaiAktif($pegawaiId, 'edit-pegawai_id');
            if (! $validasiPegawai['sukses']) {
                return $validasiPegawai;
            }

            $validasiTanggal = $this->validasiRentangTanggal($tanggalMulai, $tanggalSelesai, 'edit-tanggal_mulai', 'edit-tanggal_selesai');
            if (! $validasiTanggal['sukses']) {
                return $validasiTanggal;
            }

            $validasiHariLibur = $this->validasiBukanHariLibur(
                $tanggalMulai,
                $tanggalSelesai,
                'edit-tanggal_mulai',
                'edit-tanggal_selesai'
            );

            if (! $validasiHariLibur['sukses']) {
                return $validasiHariLibur;
            }

            $bentrok = $this->pengajuanIzinModel->jumlahBentrokTanggal(
                $pegawaiId,
                $tanggalMulai,
                $tanggalSelesai,
                $id
            );

            if ($bentrok > 0) {
                return $this->hasilGagal([
                    'edit-tanggal_mulai' => 'Rentang tanggal pengajuan bentrok dengan data lain',
                    'edit-tanggal_selesai' => 'Rentang tanggal pengajuan bentrok dengan data lain',
                ]);
            }

            $statusBaru = $pengajuan->status;
            if ($statusBaru === 'rejected') {
                $statusBaru = 'pending';
            }

            $data = [
                'id'                => $id,
                'pegawai_id'        => $pegawaiId,
                'jenis'             => $this->stringWajib($post['edit-jenis'] ?? ''),
                'tanggal_mulai'     => $tanggalMulai,
                'tanggal_selesai'   => $tanggalSelesai,
                'alasan'            => $this->stringWajib($post['edit-alasan'] ?? ''),

                // 🔥 reset status kalau sebelumnya rejected
                'status'            => $statusBaru,
                'catatan_approval'  => null,
                'approved_by'       => null,
                'approved_at'       => null,
            ];

            $namaFile = $this->simpanLampiran($file, $pegawaiId);
            if ($namaFile !== null) {
                $data['lampiran'] = $namaFile;
            }

            $simpan = $this->pengajuanIzinModel->save($data);

            if (! $simpan) {
                return $this->hasilGagal([
                    'general' => 'Data pengajuan izin gagal diubah'
                ]);
            }

            if ($namaFile !== null && ! empty($pengajuan->lampiran)) {
                $this->hapusFileLampiran($pengajuan->lampiran);
            }

            $this->catatAudit(
                'update',
                'pengajuan_izin',
                $id,
                'Mengubah pengajuan ' . $data['jenis'] . ' untuk pegawai ID ' . $pegawaiId
                    . ' pada tanggal ' . $tanggalMulai . ' s.d. ' . $tanggalSelesai
            );

            return $this->hasilSukses('Data Pengajuan Izin Berhasil Diubah');
        });
    }

    public function ambil(int $id): array
    {
        return $this->eksekusi(function () use ($id) {
            $pengajuan = $this->pengajuanIzinModel->getPengajuanById($id);

            if ($pengajuan === null) {
                return $this->hasilTidakDitemukan('Data Pengajuan Izin Tidak Ditemukan');
            }

            return $this->hasilData([
                'pengajuan_izin' => $pengajuan
            ]);
        });
    }

    public function hapus(int $id): array
    {
        return $this->eksekusi(function () use ($id) {
            $pengajuan = $this->pengajuanIzinModel->getPengajuanById($id);

            if ($pengajuan === null) {
                return $this->hasilTidakDitemukan('Data Pengajuan Izin Tidak Ada Di Database');
            }

            if (($pengajuan->status ?? 'pending') === 'approved') {
                return $this->hasilGagal([], 'Data yang sudah disetujui tidak dapat dihapus');
            }

            $hapus = $this->pengajuanIzinModel->delete($id);

            if (! $hapus) {
                return $this->hasilGagal([], 'Data Pengajuan Izin Gagal Dihapus');
            }

            if (! empty($pengajuan->lampiran)) {
                $this->hapusFileLampiran($pengajuan->lampiran);
            }

            $this->catatAudit(
                'delete',
                'pengajuan_izin',
                $id,
                'Menghapus pengajuan ' . (string) $pengajuan->jenis . ' untuk pegawai ID ' . (string) $pengajuan->pegawai_id
                    . ' pada tanggal ' . (string) $pengajuan->tanggal_mulai . ' s.d. ' . (string) $pengajuan->tanggal_selesai
            );

            return $this->hasilSukses('Data Pengajuan Izin Berhasil Dihapus');
        });
    }

    public function approve(int $id, ?int $approvedBy, ?string $catatanApproval = null): array
    {
        return $this->transaksi(function () use ($id, $approvedBy, $catatanApproval) {
            $pengajuan = $this->pengajuanIzinModel->getPengajuanById($id);

            if ($pengajuan === null) {
                return $this->hasilTidakDitemukan('Data Pengajuan Izin Tidak Ditemukan');
            }

            if (($pengajuan->status ?? 'pending') === 'approved') {
                return $this->hasilGagal([], 'Data pengajuan sudah disetujui sebelumnya');
            }

            $update = $this->pengajuanIzinModel->update($id, [
                'status'           => 'approved',
                'catatan_approval' => $this->stringAtauNull($catatanApproval),
                'approved_by'      => $this->intAtauNull($approvedBy),
                'approved_at'      => date('Y-m-d H:i:s'),
            ]);

            if (! $update) {
                return $this->hasilGagal([], 'Data pengajuan gagal disetujui');
            }

            $sinkron = $this->sinkronkanApproveKeJadwalKerja($pengajuan, $approvedBy);

            if (! $sinkron['sukses']) {
                return $sinkron;
            }

            $this->catatAudit(
                'approve',
                'pengajuan_izin',
                $id,
                'Menyetujui pengajuan ' . (string) $pengajuan->jenis . ' untuk pegawai ID ' . (string) $pengajuan->pegawai_id
                    . ' pada tanggal ' . (string) $pengajuan->tanggal_mulai . ' s.d. ' . (string) $pengajuan->tanggal_selesai
            );

            return $this->hasilSukses('Data Pengajuan Izin Berhasil Disetujui');
        });
    }

    public function reject(int $id, ?int $approvedBy, ?string $catatanApproval = null): array
    {
        return $this->eksekusi(function () use ($id, $approvedBy, $catatanApproval) {
            $pengajuan = $this->pengajuanIzinModel->getPengajuanById($id);

            if ($pengajuan === null) {
                return $this->hasilTidakDitemukan('Data Pengajuan Izin Tidak Ditemukan');
            }

            if (($pengajuan->status ?? 'pending') === 'rejected') {
                return $this->hasilGagal([], 'Data pengajuan sudah ditolak sebelumnya');
            }

            $update = $this->pengajuanIzinModel->update($id, [
                'status'           => 'rejected',
                'catatan_approval' => $this->stringAtauNull($catatanApproval),
                'approved_by'      => $this->intAtauNull($approvedBy),
                'approved_at'      => date('Y-m-d H:i:s'),
            ]);

            if (! $update) {
                return $this->hasilGagal([], 'Data pengajuan gagal ditolak');
            }

            $this->catatAudit(
                'reject',
                'pengajuan_izin',
                $id,
                'Menolak pengajuan ' . (string) $pengajuan->jenis . ' untuk pegawai ID ' . (string) $pengajuan->pegawai_id
                    . ' pada tanggal ' . (string) $pengajuan->tanggal_mulai . ' s.d. ' . (string) $pengajuan->tanggal_selesai
            );

            return $this->hasilSukses('Data Pengajuan Izin Berhasil Ditolak');
        });
    }

    public function cancelApprove(int $id): array
    {
        return $this->transaksi(function () use ($id) {
            $pengajuan = $this->pengajuanIzinModel->getPengajuanById($id);

            if ($pengajuan === null) {
                return $this->hasilTidakDitemukan('Data Pengajuan Izin Tidak Ditemukan');
            }

            if (($pengajuan->status ?? 'pending') !== 'approved') {
                return $this->hasilGagal([], 'Hanya data yang sudah disetujui yang dapat dibatalkan');
            }

            $rollback = $this->rollbackApproveDariJadwalKerja($id);

            if (! $rollback['sukses']) {
                return $rollback;
            }

            $update = $this->pengajuanIzinModel->update($id, [
                'status'           => 'pending',
                'catatan_approval' => null,
                'approved_by'      => null,
                'approved_at'      => null,
            ]);

            if (! $update) {
                return $this->hasilGagal([], 'Gagal membatalkan persetujuan');
            }

            $update = $this->pengajuanIzinModel->update($id, [
                'status'           => 'pending',
                'catatan_approval' => null,
                'approved_by'      => null,
                'approved_at'      => null,
            ]);

            if (! $update) {
                return $this->hasilGagal([], 'Gagal membatalkan persetujuan');
            }

            $this->catatAudit(
                'cancel',
                'pengajuan_izin',
                $id,
                'Membatalkan persetujuan pengajuan ' . (string) $pengajuan->jenis . ' untuk pegawai ID ' . (string) $pengajuan->pegawai_id
                    . ' pada tanggal ' . (string) $pengajuan->tanggal_mulai . ' s.d. ' . (string) $pengajuan->tanggal_selesai
            );

            return $this->hasilSukses('Persetujuan berhasil dibatalkan');
        });
    }

    protected function validasiPegawaiAktif(?int $pegawaiId, string $field): array
    {
        if ($pegawaiId === null) {
            return $this->hasilGagal([
                $field => 'Pegawai harus dipilih'
            ]);
        }

        $pegawai = $this->pegawaiModel->getPegawaiById($pegawaiId);

        if ($pegawai === null) {
            return $this->hasilGagal([
                $field => 'Data pegawai tidak ditemukan'
            ]);
        }

        if ((int) ($pegawai->is_active ?? 0) !== 1) {
            return $this->hasilGagal([
                $field => 'Pegawai tidak aktif'
            ]);
        }

        return $this->hasilSukses();
    }

    protected function validasiRentangTanggal(
        string $tanggalMulai,
        string $tanggalSelesai,
        string $fieldMulai,
        string $fieldSelesai
    ): array {
        if ($tanggalMulai > $tanggalSelesai) {
            return $this->hasilGagal([
                $fieldMulai   => 'Tanggal mulai tidak boleh melebihi tanggal selesai',
                $fieldSelesai => 'Tanggal selesai tidak boleh lebih kecil dari tanggal mulai',
            ]);
        }

        return $this->hasilSukses();
    }

    protected function simpanLampiran($file, int $pegawaiId): ?string
    {
        if (! $file || $file->getError() == 4) {
            return null;
        }

        $folderLampiran = FCPATH . 'assets/media/lampiran' . DIRECTORY_SEPARATOR;

        if (! is_dir($folderLampiran)) {
            mkdir($folderLampiran, 0775, true);
        }

        $namaFile = $pegawaiId . '_' . date('YmdHis') . '.' . strtolower($file->getExtension());
        $file->move($folderLampiran, $namaFile, true);

        return $namaFile;
    }

    protected function hapusFileLampiran(?string $namaFile): void
    {
        if ($namaFile === null || $namaFile === '') {
            return;
        }

        $path = FCPATH . 'assets/media/lampiran' . DIRECTORY_SEPARATOR . $namaFile;

        if (is_file($path)) {
            @unlink($path);
        }
    }

    protected function sinkronkanApproveKeJadwalKerja(object $pengajuan, ?int $approvedBy): array
    {
        $pegawaiId      = (int) $pengajuan->pegawai_id;
        $tanggalMulai   = (string) $pengajuan->tanggal_mulai;
        $tanggalSelesai = (string) $pengajuan->tanggal_selesai;
        $statusHariBaru = (string) $pengajuan->jenis; // izin / sakit
        $catatanBaru    = $this->stringAtauNull($pengajuan->alasan ?? '');
        $approvedBy     = $this->intAtauNull($approvedBy);

        $tanggal = $tanggalMulai;

        while ($tanggal <= $tanggalSelesai) {
            $jadwal = $this->jadwalKerjaModel->getJadwalByPegawaiDanTanggal($pegawaiId, $tanggal);

            if ($jadwal === null) {
                $simpan = $this->jadwalKerjaModel->save([
                    'pegawai_id'             => $pegawaiId,
                    'tanggal'                => $tanggal,
                    'shift_id'               => null,
                    'status_hari'            => $statusHariBaru,
                    'sumber_data'            => 'pengajuan_izin',
                    'pengajuan_izin_id'      => $pengajuan->id,
                    'shift_id_sebelumnya'    => null,
                    'status_hari_sebelumnya' => null,
                    'catatan_sebelumnya'     => null,
                    'sumber_data_sebelumnya' => null,
                    'catatan'                => $catatanBaru,
                    'created_by'             => $approvedBy,
                ]);

                if (! $simpan) {
                    return $this->hasilGagal([], 'Gagal sinkron jadwal kerja pada tanggal ' . $tanggal);
                }
            } else {
                $simpan = $this->jadwalKerjaModel->save([
                    'id'                      => $jadwal->id,
                    'pegawai_id'              => $jadwal->pegawai_id,
                    'tanggal'                 => $jadwal->tanggal,

                    // snapshot lama
                    'shift_id_sebelumnya'     => $jadwal->shift_id,
                    'status_hari_sebelumnya'  => $jadwal->status_hari,
                    'catatan_sebelumnya'      => $jadwal->catatan,
                    'sumber_data_sebelumnya'  => $jadwal->sumber_data,

                    // override baru
                    'shift_id'                => $jadwal->shift_id,
                    'status_hari'             => $statusHariBaru,
                    'sumber_data'             => 'pengajuan_izin',
                    'pengajuan_izin_id'       => $pengajuan->id,
                    'catatan'                 => $catatanBaru,
                ]);

                if (! $simpan) {
                    return $this->hasilGagal([], 'Gagal override jadwal kerja pada tanggal ' . $tanggal);
                }
            }

            $tanggal = date('Y-m-d', strtotime($tanggal . ' +1 day'));
        }

        return $this->hasilSukses();
    }

    protected function rollbackApproveDariJadwalKerja(int $pengajuanIzinId): array
    {
        $jadwalList = $this->jadwalKerjaModel->getJadwalByPengajuanIzinId($pengajuanIzinId);

        foreach ($jadwalList as $jadwal) {
            $punyaSnapshot =
                $jadwal->shift_id_sebelumnya !== null ||
                $jadwal->status_hari_sebelumnya !== null ||
                $jadwal->catatan_sebelumnya !== null ||
                $jadwal->sumber_data_sebelumnya !== null;

            if (! $punyaSnapshot) {
                $hapus = $this->jadwalKerjaModel->delete($jadwal->id);

                if (! $hapus) {
                    return $this->hasilGagal([], 'Gagal menghapus jadwal kerja hasil approval');
                }

                continue;
            }

            $restore = $this->jadwalKerjaModel->save([
                'id'                      => $jadwal->id,
                'pegawai_id'              => $jadwal->pegawai_id,
                'tanggal'                 => $jadwal->tanggal,
                'shift_id'                => $jadwal->shift_id_sebelumnya,
                'status_hari'             => $jadwal->status_hari_sebelumnya,
                'sumber_data'             => $jadwal->sumber_data_sebelumnya,
                'pengajuan_izin_id'       => null,
                'catatan'                 => $jadwal->catatan_sebelumnya,

                // kosongkan snapshot
                'shift_id_sebelumnya'     => null,
                'status_hari_sebelumnya'  => null,
                'catatan_sebelumnya'      => null,
                'sumber_data_sebelumnya'  => null,
            ]);

            if (! $restore) {
                return $this->hasilGagal([], 'Gagal mengembalikan jadwal kerja sebelumnya');
            }
        }

        return $this->hasilSukses();
    }

    protected function validasiBukanHariLibur(
        string $tanggalMulai,
        string $tanggalSelesai,
        string $fieldMulai,
        string $fieldSelesai
    ): array {
        $tanggal = $tanggalMulai;
        $tanggalLibur = [];

        while ($tanggal <= $tanggalSelesai) {
            $libur = $this->hariLiburModel->where('tanggal', $tanggal)->first();

            if ($libur !== null) {
                $tanggalLibur[] = $tanggal;
            }

            $tanggal = date('Y-m-d', strtotime($tanggal . ' +1 day'));
        }

        if (! empty($tanggalLibur)) {
            return $this->hasilGagal([
                $fieldMulai   => 'Rentang tanggal mengandung hari libur global: ' . implode(', ', $tanggalLibur),
                $fieldSelesai => 'Rentang tanggal mengandung hari libur global: ' . implode(', ', $tanggalLibur),
            ]);
        }

        return $this->hasilSukses();
    }
}
