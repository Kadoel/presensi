<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\Admin\UsersService;
use Hermawan\DataTables\DataTable;

class Users extends BaseController
{
    protected UsersService $usersService;

    public function __construct()
    {
        $this->usersService = new UsersService();
    }

    public function index()
    {
        if ($this->request->getMethod() === 'GET') {
            $pegawaiDropdown = $this->usersService->getPegawaiDropdown();

            return view('pages/admin/pengguna/index', [
                'judul'    => 'Pengguna',
                'validasi' => $this->validasi,
                'pegawai' => $pegawaiDropdown['pegawai'] ?? [],
            ]);
        }

        $builder = $this->usersService->dataTabel();

        return DataTable::of($builder)
            ->edit('pegawai_id', function ($row) {
                if ($row->role === 'admin') {
                    return '-';
                }

                $nama = $row->nama_pegawai ?? '-';

                return $nama;
            })
            ->edit('role', function ($row) {
                return ucfirst($row->role);
            })
            ->edit('is_active', function ($row) {
                return (int) $row->is_active === 1
                    ? '<span class="badge bg-success">Aktif</span>'
                    : '<span class="badge bg-danger">Nonaktif</span>';
            })
            ->edit('last_login_at', function ($row) {
                return $row->last_login_at ?: '-';
            })
            ->add('action', function ($row) {
                return '
                    <button type="button" class="btn btn-sm btn-warning" id="act-edit" data-id="' . $row->id . '">
                        <i class="fa fa-edit text-white"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" id="act-delete" data-id="' . $row->id . '" data-nama="' . esc($row->username) . '">
                        <i class="fa fa-trash-can"></i>
                    </button>
                ';
            })
            ->addNumbering('#')
            ->toJson(true);
    }

    public function dropdownPegawai()
    {
        return $this->response->setJSON(
            $this->usersService->getPegawaiDropdown()
        );
    }

    public function dropdownPegawaiEdit($id)
    {
        return $this->response->setJSON(
            $this->usersService->getPegawaiDropdown((int) $id)
        );
    }

    public function simpan()
    {
        return $this->response->setJSON(
            $this->usersService->simpan($this->request->getPost())
        );
    }

    public function edit()
    {
        $id = (int) $this->request->getPost('id');

        return $this->response->setJSON(
            $this->usersService->ambil($id)
        );
    }

    public function ubah($id)
    {
        return $this->response->setJSON(
            $this->usersService->ubah((int) $id, $this->request->getPost())
        );
    }

    public function hapus()
    {
        $id = (int) $this->request->getPost('id');

        return $this->response->setJSON(
            $this->usersService->hapus($id)
        );
    }
}
