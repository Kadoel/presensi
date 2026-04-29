<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\Admin\JabatanService;
use Hermawan\DataTables\DataTable;

class Jabatan extends BaseController
{
    protected JabatanService $jabatanService;

    public function __construct()
    {
        $this->jabatanService = new JabatanService();
    }

    public function index()
    {
        if ($this->request->getMethod() === 'GET') {
            return view('pages/admin/jabatan/index', [
                'judul'    => 'Jabatan',
                'validasi' => $this->validasi
            ]);
        }

        $builder = $this->jabatanService->dataTabel();

        return DataTable::of($builder)
            ->postQuery(function ($builder) {
                $builder->orderBy('id', 'ASC');
            })
            ->edit('is_active', function ($row) {
                return (int) $row->is_active === 1
                    ? '<span class="badge bg-success">Aktif</span>'
                    : '<span class="badge bg-danger">Nonaktif</span>';
            })
            ->add('action', function ($row) {
                return '
                    <button type="button" class="btn btn-sm btn-warning" id="act-edit" data-id="' . $row->id . '">
                        <i class="fa fa-edit text-white"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" id="act-delete" data-id="' . $row->id . '" data-nama="' . esc($row->nama_jabatan) . '">
                        <i class="fa fa-trash-can"></i>
                    </button>
                ';
            })
            ->addNumbering('#')
            ->toJson(true);
    }

    public function simpan()
    {
        $result = $this->jabatanService->simpan($this->request->getPost());
        return $this->response->setJSON($result);
    }

    public function edit()
    {
        $result = $this->jabatanService->ambil((int) $this->request->getVar('id'));
        return $this->response->setJSON($result);
    }

    public function update($id)
    {
        $result = $this->jabatanService->ubah((int) $id, $this->request->getPost());
        return $this->response->setJSON($result);
    }

    public function delete()
    {
        $result = $this->jabatanService->hapus((int) $this->request->getVar('id'));
        return $this->response->setJSON($result);
    }
}
