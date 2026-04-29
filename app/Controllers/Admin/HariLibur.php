<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\Admin\HariLiburService;
use Hermawan\DataTables\DataTable;

class HariLibur extends BaseController
{
    protected HariLiburService $hariLiburService;

    public function __construct()
    {
        $this->hariLiburService = new HariLiburService();
    }

    public function index()
    {
        if ($this->request->getMethod() === 'GET') {
            return view('pages/admin/libur/index', [
                'judul'    => 'Hari Libur',
                'validasi' => $this->validasi
            ]);
        }

        $builder = $this->hariLiburService->dataTabel();

        return DataTable::of($builder)
            ->edit('tanggal', function ($row) {
                return tanggal_indonesia($row->tanggal);
            })
            ->add('action', function ($row) {
                return '
                            <button type="button" class="btn btn-sm btn-warning" id="act-edit" data-id="' . $row->id . '">
                                <i class="fa fa-edit text-white"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" id="act-delete" data-id="' . $row->id . '" data-nama="' . esc($row->nama_libur) . '">
                                <i class="fa fa-trash-can"></i>
                            </button>
                    ';
            })
            ->addNumbering('#')
            ->toJson(true);
    }

    public function simpan()
    {
        return $this->response->setJSON(
            $this->hariLiburService->simpan($this->request->getPost())
        );
    }

    public function edit()
    {
        $id = (int) $this->request->getPost('id');

        return $this->response->setJSON(
            $this->hariLiburService->ambil($id)
        );
    }

    public function update($id)
    {
        return $this->response->setJSON(
            $this->hariLiburService->ubah((int) $id, $this->request->getPost())
        );
    }

    public function hapus()
    {
        $id = (int) $this->request->getPost('id');

        return $this->response->setJSON(
            $this->hariLiburService->hapus($id)
        );
    }

    public function konfirmasiOverride()
    {
        return $this->response->setJSON(
            $this->hariLiburService->konfirmasiOverride($this->request->getPost())
        );
    }
}
