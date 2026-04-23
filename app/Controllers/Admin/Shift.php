<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\ShiftService;
use Hermawan\DataTables\DataTable;

class Shift extends BaseController
{
    protected ShiftService $shiftService;

    public function __construct()
    {
        $this->shiftService = new ShiftService();
    }

    public function index()
    {
        if ($this->request->getMethod() === 'GET') {
            return view('pages/admin/shift/index', [
                'judul'    => 'Shift',
                'validasi' => $this->validasi,
            ]);
        }

        $builder = $this->shiftService->dataTabel();

        return DataTable::of($builder)
            ->postQuery(function ($builder) {
                $builder->orderBy('id', 'ASC');
            })
            ->edit('jam_masuk', function ($row) {
                return substr((string) $row->jam_masuk, 0, 5);
            })
            ->edit('batas_mulai_datang', function ($row) {
                return substr((string) $row->batas_mulai_datang, 0, 5);
            })
            ->edit('batas_akhir_datang', function ($row) {
                return substr((string) $row->batas_akhir_datang, 0, 5);
            })
            ->edit('jam_pulang', function ($row) {
                return substr((string) $row->jam_pulang, 0, 5);
            })
            ->edit('batas_mulai_pulang', function ($row) {
                return substr((string) $row->batas_mulai_pulang, 0, 5);
            })
            ->edit('batas_akhir_pulang', function ($row) {
                return substr((string) $row->batas_akhir_pulang, 0, 5);
            })
            ->edit('toleransi_telat_menit', function ($row) {
                return (int) $row->toleransi_telat_menit . ' menit';
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
                    <button type="button" class="btn btn-sm btn-danger" id="act-delete" data-id="' . $row->id . '" data-nama="' . esc($row->nama_shift) . '">
                        <i class="fa fa-trash-can"></i>
                    </button>
                ';
            })
            ->addNumbering('#')
            ->toJson(true);
    }

    public function simpan()
    {
        $result = $this->shiftService->simpan($this->request->getPost());

        return $this->response->setJSON([
            'sukses' => $result['sukses'],
            'errors' => $result['errors'] ?? [],
            'pesan'  => $result['pesan'] ?? ''
        ]);
    }

    public function edit()
    {
        $id = (int) $this->request->getVar('id');
        $result = $this->shiftService->ambil($id);

        return $this->response->setJSON($result);
    }

    public function update($id)
    {
        $result = $this->shiftService->ubah((int) $id, $this->request->getPost());

        return $this->response->setJSON($result);
    }

    public function delete()
    {
        $result = $this->shiftService->hapus((int) $this->request->getVar('id'));

        return $this->response->setJSON($result);
    }
}
