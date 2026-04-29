<?php

namespace App\Services\Admin;

use App\Models\PegawaiModel;
use App\Models\UsersModel;
use App\Services\BaseService;

class UsersService extends BaseService
{
    protected UsersModel $usersModel;
    protected PegawaiModel $pegawaiModel;

    public function __construct()
    {
        parent::__construct();
        $this->usersModel   = new UsersModel();
        $this->pegawaiModel = new PegawaiModel();
    }

    public function getPegawaiDropdown(?int $userId = null): array
    {
        return $this->eksekusi(function () use ($userId) {
            return $this->hasilData([
                'pegawai' => $this->pegawaiModel->getPegawaiAktifUntukDropdown($userId)
            ]);
        });
    }

    public function dataTabel()
    {
        return $this->usersModel->selectData();
    }

    public function simpan(array $post): array
    {
        return $this->eksekusi(function () use ($post) {
            $role = $this->stringWajib($post['role'] ?? '');

            $rules = [
                'username' => [
                    'label'  => 'Username',
                    'rules'  => 'required|min_length[3]|max_length[100]|is_unique[users.username]',
                    'errors' => [
                        'required'   => '{field} harus diisi',
                        'min_length' => '{field} minimal 3 karakter',
                        'max_length' => '{field} maksimal 100 karakter',
                        'is_unique'  => '{field} sudah terdaftar',
                    ]
                ],
                'password' => [
                    'label'  => 'Password',
                    'rules'  => 'required|min_length[6]',
                    'errors' => [
                        'required'   => '{field} harus diisi',
                        'min_length' => '{field} minimal 6 karakter',
                    ]
                ],
                'role' => [
                    'label'  => 'Role',
                    'rules'  => 'required|in_list[admin,pegawai]',
                    'errors' => [
                        'required' => '{field} harus diisi',
                        'in_list'  => '{field} tidak valid',
                    ]
                ],
                'is_active' => [
                    'label'  => 'Status Aktif',
                    'rules'  => 'required|in_list[0,1]',
                    'errors' => [
                        'required' => '{field} harus diisi',
                        'in_list'  => '{field} tidak valid',
                    ]
                ],
            ];

            if ($role === 'pegawai') {
                $rules['pegawai_id'] = [
                    'label'  => 'Pegawai',
                    'rules'  => 'required|integer|is_unique[users.pegawai_id]',
                    'errors' => [
                        'required'  => '{field} harus dipilih',
                        'integer'   => '{field} tidak valid',
                        'is_unique' => '{field} sudah terhubung ke user lain',
                    ]
                ];
            } else {
                $rules['pegawai_id'] = [
                    'label' => 'Pegawai',
                    'rules' => 'permit_empty',
                ];
            }

            $validasi = $this->validasi($rules, $post);

            if (! $validasi['sukses']) {
                return $validasi;
            }

            $pegawaiId = null;

            if ($role === 'pegawai') {
                $pegawaiId = $this->intAtauNull($post['pegawai_id'] ?? null);

                $pegawai = $this->pegawaiModel->getPegawaiById($pegawaiId);

                if ($pegawai === null) {
                    return $this->hasilGagal([
                        'pegawai_id' => 'Data pegawai tidak ditemukan'
                    ]);
                }

                if ((int) $pegawai->is_active !== 1) {
                    return $this->hasilGagal([
                        'pegawai_id' => 'Pegawai tidak aktif'
                    ]);
                }
            }

            $simpan = $this->usersModel->save([
                'pegawai_id'    => $pegawaiId,
                'username'      => $this->stringWajib($post['username'] ?? ''),
                'password_hash' => password_hash((string) ($post['password'] ?? ''), PASSWORD_DEFAULT),
                'role'          => $role,
                'is_active'     => $this->intVal($post['is_active'] ?? 1, 1),
            ]);

            if (! $simpan) {
                return $this->hasilGagal([
                    'general' => 'Data user gagal disimpan'
                ]);
            }

            $this->catatAudit(
                'create',
                'users',
                (int) $this->usersModel->getInsertID(),
                'Menambahkan user dengan username ' . $this->stringWajib($post['username'] ?? '')
                    . ', role ' . $role
                    . ', pegawai ID ' . ($pegawaiId ?? 'null')
            );

            return $this->hasilSukses('Data User Berhasil Ditambahkan');
        });
    }

    public function ubah(int $id, array $post): array
    {
        return $this->eksekusi(function () use ($id, $post) {
            $user = $this->usersModel->find($id);

            if ($user === null) {
                return $this->hasilTidakDitemukan('Data User Tidak Ditemukan');
            }

            $role = $this->stringWajib($post['edit-role'] ?? '');

            $rules = [
                'edit-username' => [
                    'label'  => 'Username',
                    'rules'  => "required|min_length[3]|max_length[100]|is_unique[users.username,id,{$id}]",
                    'errors' => [
                        'required'   => '{field} harus diisi',
                        'min_length' => '{field} minimal 3 karakter',
                        'max_length' => '{field} maksimal 100 karakter',
                        'is_unique'  => '{field} sudah terdaftar',
                    ]
                ],
                'edit-password' => [
                    'label'  => 'Password',
                    'rules'  => 'permit_empty|min_length[6]',
                    'errors' => [
                        'min_length' => '{field} minimal 6 karakter',
                    ]
                ],
                'edit-role' => [
                    'label'  => 'Role',
                    'rules'  => 'required|in_list[admin,pegawai]',
                    'errors' => [
                        'required' => '{field} harus diisi',
                        'in_list'  => '{field} tidak valid',
                    ]
                ],
                'edit-is_active' => [
                    'label'  => 'Status Aktif',
                    'rules'  => 'required|in_list[0,1]',
                    'errors' => [
                        'required' => '{field} harus diisi',
                        'in_list'  => '{field} tidak valid',
                    ]
                ],
            ];

            if ($role === 'pegawai') {
                $rules['edit-pegawai_id'] = [
                    'label'  => 'Pegawai',
                    'rules'  => "required|integer|is_unique[users.pegawai_id,id,{$id}]",
                    'errors' => [
                        'required'  => '{field} harus dipilih',
                        'integer'   => '{field} tidak valid',
                        'is_unique' => '{field} sudah terhubung ke user lain',
                    ]
                ];
            } else {
                $rules['edit-pegawai_id'] = [
                    'label' => 'Pegawai',
                    'rules' => 'permit_empty',
                ];
            }

            $validasi = $this->validasi($rules, $post);

            if (! $validasi['sukses']) {
                return $validasi;
            }

            $pegawaiId = null;

            if ($role === 'pegawai') {
                $pegawaiId = $this->intAtauNull($post['edit-pegawai_id'] ?? null);

                $pegawai = $this->pegawaiModel->getPegawaiById($pegawaiId);

                if ($pegawai === null) {
                    return $this->hasilGagal([
                        'edit-pegawai_id' => 'Data pegawai tidak ditemukan'
                    ]);
                }

                if ((int) $pegawai->is_active !== 1) {
                    return $this->hasilGagal([
                        'edit-pegawai_id' => 'Pegawai tidak aktif'
                    ]);
                }
            }

            $dataSimpan = [
                'id'         => $id,
                'pegawai_id' => $pegawaiId,
                'username'   => $this->stringWajib($post['edit-username'] ?? ''),
                'role'       => $role,
                'is_active'  => $this->intVal($post['edit-is_active'] ?? 1, 1),
            ];

            $passwordBaru = $this->stringAtauNull($post['edit-password'] ?? '');

            if ($passwordBaru !== null) {
                $dataSimpan['password_hash'] = password_hash($passwordBaru, PASSWORD_DEFAULT);
            }

            $simpan = $this->usersModel->save($dataSimpan);

            if (! $simpan) {
                return $this->hasilGagal([
                    'general' => 'Data user gagal diubah'
                ]);
            }

            $this->catatAudit(
                'update',
                'users',
                $id,
                'Mengubah user dengan username ' . $dataSimpan['username']
                    . ', role ' . $role
                    . ', pegawai ID ' . ($pegawaiId ?? 'null')
            );

            return $this->hasilSukses('Data User Berhasil Diubah');
        });
    }

    public function ambil(int $id): array
    {
        return $this->eksekusi(function () use ($id) {
            $user = $this->usersModel->getUserById($id);

            if ($user === null) {
                return $this->hasilTidakDitemukan('Data User Tidak Ditemukan');
            }

            return $this->hasilData([
                'user' => $user
            ]);
        });
    }

    public function hapus(int $id): array
    {
        return $this->eksekusi(function () use ($id) {
            $user = $this->usersModel->find($id);

            if ($user === null) {
                return $this->hasilTidakDitemukan('Data User Tidak Ada Di Database');
            }

            $hapus = $this->usersModel->delete($id);

            if (! $hapus) {
                return $this->hasilGagal([], 'Data User Gagal Dihapus');
            }

            $this->catatAudit(
                'delete',
                'users',
                $id,
                'Menghapus user dengan username ' . (string) $user->username
                    . ', role ' . (string) $user->role
                    . ', pegawai ID ' . ($user->pegawai_id ?? 'null')
            );

            return $this->hasilSukses('Data User Berhasil Dihapus');
        });
    }
}
