<?php

namespace App\Services\Pegawai;

use App\Models\HariLiburModel;
use App\Models\JadwalKerjaModel;
use App\Models\PengajuanIzinModel;
use App\Models\PresensiModel;
use App\Services\BaseService;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\HTTP\Files\UploadedFile;

class PengajuanIzinService extends BaseService
{
    protected PengajuanIzinModel $pengajuanIzinModel;
    protected JadwalKerjaModel $jadwalKerjaModel;
    protected HariLiburModel $hariLiburModel;
    protected PresensiModel $presensiModel;

    public function __construct()
    {
        parent::__construct();

        $this->pengajuanIzinModel = new PengajuanIzinModel();
        $this->jadwalKerjaModel   = new JadwalKerjaModel();
        $this->hariLiburModel     = new HariLiburModel();
        $this->presensiModel      = new PresensiModel();
    }

    public function dataTabel(): BaseBuilder
    {
        $pegawaiId = $this->intAtauNull(session()->get('pegawai_id'));

        return $this->pengajuanIzinModel->selectDataPegawai((int) $pegawaiId);
    }

    public function simpan(array $post, ?UploadedFile $file): array
    {
        return $this->transaksi(function () use ($post, $file) {
            $pegawaiId = $this->intAtauNull(session()->get('pegawai_id'));

            if ($pegawaiId === null) {
                return $this->hasilGagal([], 'Data pegawai tidak ditemukan pada session');
            }

            $rules = $this->rulesSimpan($file);
            $validasi = $this->validasi($rules, $post);

            if (! $validasi['sukses']) {
                return $validasi;
            }

            $jenis          = $this->stringWajib($post['jenis'] ?? '');
            $tanggalMulai  = $this->stringWajib($post['tanggal_mulai'] ?? '');
            $tanggalSelesai = $this->stringWajib($post['tanggal_selesai'] ?? '');
            $alasan        = $this->stringWajib($post['alasan'] ?? '');

            $validasiProses = $this->validasiProsesPengajuan(
                $pegawaiId,
                $tanggalMulai,
                $tanggalSelesai
            );

            if (! $validasiProses['sukses']) {
                return $validasiProses;
            }

            $namaFile = $this->simpanLampiran($file, $pegawaiId);

            $data = [
                'pegawai_id'        => $pegawaiId,
                'jenis'             => $jenis,
                'tanggal_mulai'     => $tanggalMulai,
                'tanggal_selesai'   => $tanggalSelesai,
                'alasan'            => $alasan,
                'status'            => 'pending',
                'catatan_approval'  => null,
                'approved_by'       => null,
                'approved_at'       => null,
            ];

            if ($namaFile !== null) {
                $data['lampiran'] = $namaFile;
            }

            $insert = $this->pengajuanIzinModel->insert($data);

            if (! $insert) {
                if ($namaFile !== null) {
                    $this->hapusFileLampiran($namaFile);
                }

                return $this->hasilGagal([], 'Pengajuan gagal disimpan');
            }

            $id = (int) $this->pengajuanIzinModel->getInsertID();

            $this->catatAudit(
                'create',
                'pengajuan_izin',
                $id,
                'Pegawai mengajukan ' . $jenis . ' tanggal ' . $tanggalMulai . ' s.d. ' . $tanggalSelesai
            );

            return $this->hasilSukses('Pengajuan berhasil dikirim');
        });
    }

    public function ambil(int $id): array
    {
        return $this->eksekusi(function () use ($id) {
            $pegawaiId = $this->intAtauNull(session()->get('pegawai_id'));
            $pengajuan = $this->pengajuanIzinModel->getPengajuanById($id);

            if ($pengajuan === null || (int) $pengajuan->pegawai_id !== (int) $pegawaiId) {
                return $this->hasilTidakDitemukan('Data pengajuan tidak ditemukan');
            }

            return $this->hasilData([
                'pengajuan_izin' => $pengajuan,
            ]);
        });
    }

    public function ubah(int $id, array $post, ?UploadedFile $file): array
    {
        return $this->transaksi(function () use ($id, $post, $file) {
            $pegawaiId = $this->intAtauNull(session()->get('pegawai_id'));
            $pengajuan = $this->pengajuanIzinModel->getPengajuanById($id);

            if ($pengajuan === null || (int) $pengajuan->pegawai_id !== (int) $pegawaiId) {
                return $this->hasilTidakDitemukan('Data pengajuan tidak ditemukan');
            }

            $cekBoleh = $this->pastikanBolehDiubahAtauDihapus($pengajuan);

            if (! $cekBoleh['sukses']) {
                return $cekBoleh;
            }

            $rules = $this->rulesUbah($file);
            $validasi = $this->validasi($rules, $post);

            if (! $validasi['sukses']) {
                return $validasi;
            }

            $jenis          = $this->stringWajib($post['edit-jenis'] ?? '');
            $tanggalMulai  = $this->stringWajib($post['edit-tanggal_mulai'] ?? '');
            $tanggalSelesai = $this->stringWajib($post['edit-tanggal_selesai'] ?? '');
            $alasan        = $this->stringWajib($post['edit-alasan'] ?? '');

            $validasiProses = $this->validasiProsesPengajuan(
                (int) $pegawaiId,
                $tanggalMulai,
                $tanggalSelesai,
                $id
            );

            if (! $validasiProses['sukses']) {
                return $validasiProses;
            }

            $statusBaru = ($pengajuan->status ?? 'pending') === 'rejected'
                ? 'pending'
                : $pengajuan->status;

            $data = [
                'id'                => $id,
                'pegawai_id'        => $pegawaiId,
                'jenis'             => $jenis,
                'tanggal_mulai'     => $tanggalMulai,
                'tanggal_selesai'   => $tanggalSelesai,
                'alasan'            => $alasan,
                'status'            => $statusBaru,
                'catatan_approval'  => null,
                'approved_by'       => null,
                'approved_at'       => null,
            ];

            $namaFile = $this->simpanLampiran($file, (int) $pegawaiId);

            if ($namaFile !== null) {
                $data['lampiran'] = $namaFile;
            }

            $simpan = $this->pengajuanIzinModel->save($data);

            if (! $simpan) {
                if ($namaFile !== null) {
                    $this->hapusFileLampiran($namaFile);
                }

                return $this->hasilGagal([], 'Pengajuan gagal diubah');
            }

            if ($namaFile !== null && ! empty($pengajuan->lampiran)) {
                $this->hapusFileLampiran($pengajuan->lampiran);
            }

            $this->catatAudit(
                'update',
                'pengajuan_izin',
                $id,
                'Pegawai mengubah pengajuan ' . $jenis . ' tanggal ' . $tanggalMulai . ' s.d. ' . $tanggalSelesai
            );

            return $this->hasilSukses('Pengajuan berhasil diubah');
        });
    }

    public function hapus(int $id): array
    {
        return $this->transaksi(function () use ($id) {
            $pegawaiId = $this->intAtauNull(session()->get('pegawai_id'));
            $pengajuan = $this->pengajuanIzinModel->getPengajuanById($id);

            if ($pengajuan === null || (int) $pengajuan->pegawai_id !== (int) $pegawaiId) {
                return $this->hasilTidakDitemukan('Data pengajuan tidak ditemukan');
            }

            $cekBoleh = $this->pastikanBolehDiubahAtauDihapus($pengajuan);

            if (! $cekBoleh['sukses']) {
                return $cekBoleh;
            }

            if (! $this->pengajuanIzinModel->delete($id)) {
                return $this->hasilGagal([], 'Pengajuan gagal dihapus');
            }

            if (! empty($pengajuan->lampiran)) {
                $this->hapusFileLampiran($pengajuan->lampiran);
            }

            $this->catatAudit(
                'delete',
                'pengajuan_izin',
                $id,
                'Pegawai menghapus pengajuan ' . $pengajuan->jenis
            );

            return $this->hasilSukses('Pengajuan berhasil dihapus');
        });
    }

    protected function rulesSimpan(?UploadedFile $file): array
    {
        $rules = [
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

        return $rules;
    }

    protected function rulesUbah(?UploadedFile $file): array
    {
        $rules = [
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

        return $rules;
    }

    protected function validasiProsesPengajuan(
        int $pegawaiId,
        string $tanggalMulai,
        string $tanggalSelesai,
        ?int $excludeId = null
    ): array {
        if ($tanggalMulai > $tanggalSelesai) {
            return $this->hasilGagal([
                'tanggal_mulai' => 'Tanggal mulai tidak boleh melebihi tanggal selesai',
                'tanggal_selesai' => 'Tanggal selesai tidak boleh lebih kecil dari tanggal mulai',
                'edit-tanggal_mulai' => 'Tanggal mulai tidak boleh melebihi tanggal selesai',
                'edit-tanggal_selesai' => 'Tanggal selesai tidak boleh lebih kecil dari tanggal mulai',
            ]);
        }

        if ($tanggalMulai < date('Y-m-d')) {
            return $this->hasilGagal([
                'tanggal_mulai' => 'Tanggal mulai tidak boleh sebelum hari ini',
                'edit-tanggal_mulai' => 'Tanggal mulai tidak boleh sebelum hari ini',
            ]);
        }

        if ($this->mengandungHariLibur($tanggalMulai, $tanggalSelesai)) {
            return $this->hasilGagal([
                'tanggal_mulai' => 'Rentang tanggal mengandung hari libur global',
                'tanggal_selesai' => 'Rentang tanggal mengandung hari libur global',
                'edit-tanggal_mulai' => 'Rentang tanggal mengandung hari libur global',
                'edit-tanggal_selesai' => 'Rentang tanggal mengandung hari libur global',
            ]);
        }

        $jadwalList = $this->jadwalKerjaModel->getJadwalPegawaiDalamRentang(
            $pegawaiId,
            $tanggalMulai,
            $tanggalSelesai
        );

        $jumlahHari = $this->hitungJumlahHari($tanggalMulai, $tanggalSelesai);

        if (count($jadwalList) !== $jumlahHari) {
            return $this->hasilGagal([
                'tanggal_mulai' => 'Jadwal kerja pada rentang tanggal tersebut belum lengkap',
                'tanggal_selesai' => 'Jadwal kerja pada rentang tanggal tersebut belum lengkap',
                'edit-tanggal_mulai' => 'Jadwal kerja pada rentang tanggal tersebut belum lengkap',
                'edit-tanggal_selesai' => 'Jadwal kerja pada rentang tanggal tersebut belum lengkap',
            ]);
        }

        foreach ($jadwalList as $jadwal) {
            if (($jadwal->status_hari ?? '') !== 'kerja') {
                return $this->hasilGagal([
                    'tanggal_mulai' => 'Pengajuan hanya bisa pada jadwal kerja',
                    'tanggal_selesai' => 'Pengajuan hanya bisa pada jadwal kerja',
                    'edit-tanggal_mulai' => 'Pengajuan hanya bisa pada jadwal kerja',
                    'edit-tanggal_selesai' => 'Pengajuan hanya bisa pada jadwal kerja',
                ]);
            }
        }

        foreach ($jadwalList as $jadwal) {
            if ($this->presensiModel->sudahAdaPresensi($pegawaiId, $jadwal->tanggal)) {
                return $this->hasilGagal([
                    'tanggal_mulai' => 'Sudah ada presensi pada salah satu tanggal pengajuan',
                    'tanggal_selesai' => 'Sudah ada presensi pada salah satu tanggal pengajuan',
                    'edit-tanggal_mulai' => 'Sudah ada presensi pada salah satu tanggal pengajuan',
                    'edit-tanggal_selesai' => 'Sudah ada presensi pada salah satu tanggal pengajuan',
                ]);
            }
        }

        $bentrok = $this->pengajuanIzinModel->jumlahBentrokTanggal(
            $pegawaiId,
            $tanggalMulai,
            $tanggalSelesai,
            $excludeId
        );

        if ($bentrok > 0) {
            return $this->hasilGagal([
                'tanggal_mulai' => 'Rentang tanggal pengajuan bentrok dengan pengajuan lain',
                'tanggal_selesai' => 'Rentang tanggal pengajuan bentrok dengan pengajuan lain',
                'edit-tanggal_mulai' => 'Rentang tanggal pengajuan bentrok dengan pengajuan lain',
                'edit-tanggal_selesai' => 'Rentang tanggal pengajuan bentrok dengan pengajuan lain',
            ]);
        }

        return $this->hasilSukses();
    }

    protected function pastikanBolehDiubahAtauDihapus(object $pengajuan): array
    {
        if (($pengajuan->status ?? 'pending') === 'approved') {
            return $this->hasilGagal([], 'Pengajuan yang sudah disetujui tidak dapat diubah atau dihapus');
        }

        if (($pengajuan->tanggal_mulai ?? '') < date('Y-m-d')) {
            return $this->hasilGagal([], 'Pengajuan yang tanggal mulainya sudah lewat tidak dapat diubah atau dihapus');
        }

        return $this->hasilSukses();
    }

    protected function mengandungHariLibur(string $tanggalMulai, string $tanggalSelesai): bool
    {
        $tanggal = $tanggalMulai;

        while ($tanggal <= $tanggalSelesai) {
            if ($this->hariLiburModel->where('tanggal', $tanggal)->first() !== null) {
                return true;
            }

            $tanggal = date('Y-m-d', strtotime($tanggal . ' +1 day'));
        }

        return false;
    }

    protected function hitungJumlahHari(string $tanggalMulai, string $tanggalSelesai): int
    {
        return ((int) floor((strtotime($tanggalSelesai) - strtotime($tanggalMulai)) / 86400)) + 1;
    }

    protected function simpanLampiran(?UploadedFile $file, int $pegawaiId): ?string
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
}
