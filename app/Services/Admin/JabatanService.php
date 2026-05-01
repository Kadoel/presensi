<?php

namespace App\Services\Admin;

use App\Models\JabatanModel;
use App\Services\BaseService;
use CodeIgniter\Database\BaseBuilder;

class JabatanService extends BaseService
{
    protected JabatanModel $jabatanModel;

    public function __construct()
    {
        parent::__construct();
        $this->jabatanModel = new JabatanModel();
    }

    public function dataTabel(): BaseBuilder
    {
        return $this->jabatanModel->selectData();
    }

    public function simpan(array $post): array
    {
        return $this->eksekusi(function () use ($post) {
            $rules = [
                'nama_jabatan' => [
                    'label'  => 'Nama Jabatan',
                    'rules'  => 'required|regex_match[/^[a-zA-Z\s]+$/]|is_unique[jabatan.nama_jabatan]',
                    'errors' => [
                        'required'    => '{field} harus diisi',
                        'regex_match' => '{field} hanya boleh diisi huruf, dan spasi',
                        'is_unique'   => '{field} sudah terdaftar'
                    ]
                ],
                'deskripsi' => [
                    'label'  => 'Deskripsi',
                    'rules'  => 'permit_empty|regex_match[/^[a-zA-Z\s]+$/]',
                    'errors' => [
                        'regex_match' => '{field} hanya boleh diisi huruf, dan spasi',
                    ]
                ],
                'is_active' => [
                    'label'  => 'Status',
                    'rules'  => 'required|in_list[0,1]',
                    'errors' => [
                        'required' => '{field} harus diisi',
                        'in_list'  => '{field} tidak valid'
                    ]
                ],
            ];

            $validasi = $this->validasi($rules, $post);

            if (! $validasi['sukses']) {
                return $validasi;
            }

            $simpan = $this->jabatanModel->save([
                'nama_jabatan' => $this->stringWajib($post['nama_jabatan'] ?? ''),
                'deskripsi'    => $this->stringAtauNull($post['deskripsi'] ?? ''),
                'is_active'    => $this->intVal($post['is_active'] ?? 1, 1),
            ]);

            if (! $simpan) {
                return $this->hasilGagal([
                    'general' => 'Data jabatan gagal disimpan'
                ]);
            }

            $this->catatAudit(
                'create',
                'jabatan',
                (int) $this->jabatanModel->getInsertID(),
                'Menambahkan data jabatan: ' . $this->stringWajib($post['nama_jabatan'] ?? '')
            );

            return $this->hasilSukses('Data Jabatan Berhasil Ditambahkan');
        });
    }

    public function ubah(int $id, array $post): array
    {
        return $this->eksekusi(function () use ($id, $post) {
            $rules = [
                'edit-nama_jabatan' => [
                    'label'  => 'Nama Jabatan',
                    'rules'  => "required|regex_match[/^[a-zA-Z\s]+$/]|is_unique[jabatan.nama_jabatan,id,{$id}]",
                    'errors' => [
                        'required'    => '{field} harus diisi',
                        'regex_match' => '{field} hanya boleh diisi huruf, dan spasi',
                        'is_unique'   => '{field} sudah terdaftar'
                    ]
                ],
                'edit-deskripsi' => [
                    'label'  => 'Deskripsi',
                    'rules'  => 'permit_empty|regex_match[/^[a-zA-Z\s]+$/]',
                    'errors' => [
                        'regex_match' => '{field} hanya boleh diisi huruf, dan spasi',
                    ]
                ],
                'edit-is_active' => [
                    'label'  => 'Status Aktif',
                    'rules'  => 'required|in_list[0,1]',
                    'errors' => [
                        'required' => '{field} harus diisi',
                        'in_list'  => '{field} tidak valid'
                    ]
                ],
            ];

            $validasi = $this->validasi($rules, $post);

            if (! $validasi['sukses']) {
                return $validasi;
            }

            $validasiNonAktifJabatan = $this->validasiNonAktifJabatan($id, $post);
            if (! $validasiNonAktifJabatan['sukses']) {
                return $validasiNonAktifJabatan;
            }

            $simpan = $this->jabatanModel->save([
                'id'           => $post['edit-id'] ?? $id,
                'nama_jabatan' => $this->stringWajib($post['edit-nama_jabatan'] ?? ''),
                'deskripsi'    => $this->stringAtauNull($post['edit-deskripsi'] ?? ''),
                'is_active'    => $this->intVal($post['edit-is_active'] ?? 1, 1),
            ]);

            if (! $simpan) {
                return $this->hasilGagal([
                    'general' => 'Data jabatan gagal diubah'
                ]);
            }

            $this->catatAudit(
                'update',
                'jabatan',
                $id,
                'Mengubah data jabatan: ' . $this->stringWajib($post['edit-nama_jabatan'] ?? '')
            );

            return $this->hasilSukses('Data Jabatan Berhasil Diubah');
        });
    }

    public function ambil(int $id): array
    {
        return $this->eksekusi(function () use ($id) {
            $jabatan = $this->jabatanModel->getJabatanById($id);

            if ($jabatan === null) {
                return $this->hasilTidakDitemukan('Data Jabatan Tidak Ditemukan');
            }

            return $this->hasilData([
                'jabatan' => $jabatan
            ]);
        });
    }

    public function hapus(int $id): array
    {
        return $this->eksekusi(function () use ($id) {
            $jabatan = $this->jabatanModel->getJabatanById($id);

            if ($jabatan === null) {
                return $this->hasilTidakDitemukan('Data Jabatan Tidak Ada Di Database');
            }

            $jumlahDipakai = $this->jabatanModel->jumlahPegawaiYangMemakai($id);

            if ($jumlahDipakai > 0) {
                return $this->hasilGagal([], 'Data Jabatan tidak dapat dihapus karena masih dipakai oleh ' . $jumlahDipakai . ' pegawai');
            }

            $hapus = $this->jabatanModel->delete($id);

            if (! $hapus) {
                return $this->hasilGagal([], 'Data Jabatan Gagal Dihapus');
            }

            $this->catatAudit(
                'delete',
                'jabatan',
                $id,
                'Menghapus data jabatan: ' . (string) $jabatan->nama_jabatan
            );

            return $this->hasilSukses('Data Jabatan Berhasil Dihapus');
        });
    }

    protected function validasiNonAktifJabatan(int $id, array $post): array
    {
        $jabatan = $this->jabatanModel->getJabatanById($id);

        if ($jabatan === null) {
            return $this->hasilTidakDitemukan('Data Jabatan Tidak Ditemukan');
        }

        $statusLama = (int) ($jabatan->is_active ?? 0);
        $statusBaru = $this->intVal($post['edit-is_active'] ?? 1, 1);

        // hanya validasi jika dari aktif -> nonaktif
        if ($statusLama === 1 && $statusBaru === 0) {
            $jumlahDipakai = $this->jabatanModel->jumlahPegawaiYangMemakai($id);

            if ($jumlahDipakai > 0) {
                return $this->hasilGagal([
                    'edit-is_active' => 'Jabatan tidak dapat dinonaktifkan karena masih dipakai oleh ' . $jumlahDipakai . ' pegawai',
                ]);
            }
        }

        return $this->hasilSukses();
    }
}
