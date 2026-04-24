<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\PresensiAdminService;
use Hermawan\DataTables\DataTable;

class Presensi extends BaseController
{
    protected PresensiAdminService $presensiAdminService;

    public function __construct()
    {
        $this->presensiAdminService = new PresensiAdminService();
    }

    public function index()
    {
        if ($this->request->getMethod() === 'GET') {
            $ringkasanResult = $this->presensiAdminService->ringkasanHarian(date('Y-m-d'));

            return view('pages/admin/presensi/index', [
                'judul'     => 'Presensi',
                'validasi'  => $this->validasi,
                'ringkasan' => $ringkasanResult['ringkasan'] ?? [],
            ]);
        }

        $builder = $this->presensiAdminService->dataTabel(
            $this->request->getPost('tanggal')
        );

        return DataTable::of($builder)
            ->postQuery(function ($builder) {
                $builder->orderBy('presensi.id', 'DESC');
            })
            ->edit('tanggal', function ($row) {
                return tanggal_indonesia($row->tanggal);
            })
            ->edit('jam_datang', function ($row) {
                return $row->jam_datang ?: '-';
            })
            ->edit('jam_pulang', function ($row) {
                return $row->jam_pulang ?: '-';
            })
            ->edit('status_datang', function ($row) {
                return $this->presensiAdminService->badgeStatusDatang($row->status_datang);
            })
            ->edit('status_pulang', function ($row) {
                return $this->presensiAdminService->badgeStatusPulang($row->status_pulang);
            })
            ->edit('is_manual', function ($row) {
                return (int) $row->is_manual === 1
                    ? '<span class="badge bg-info">Manual</span>'
                    : '<span class="badge bg-dark">Scan</span>';
            })
            ->add('action', function ($row) {
                return '
                    <button type="button" class="btn btn-sm btn-info" id="act-detail" data-id="' . $row->id . '">
                        <i class="fa fa-eye text-white"></i>
                    </button>
                ';
            })
            ->addNumbering('#')
            ->toJson(true);
    }

    public function detail()
    {
        $result = $this->presensiAdminService->detail(
            (int) $this->request->getVar('id')
        );

        return $this->response->setJSON($result);
    }

    public function sinkron()
    {
        $result = $this->presensiAdminService->sinkronPresensiHarian(
            $this->request->getPost('tanggal') ?: date('Y-m-d')
        );

        return $this->response->setJSON($result);
    }

    public function generateAlpa()
    {
        $result = $this->presensiAdminService->generateAlpaHarian(
            $this->request->getPost('tanggal') ?: date('Y-m-d')
        );

        return $this->response->setJSON($result);
    }

    public function ringkasan()
    {
        $result = $this->presensiAdminService->ringkasanHarian(
            $this->request->getVar('tanggal') ?: date('Y-m-d')
        );

        return $this->response->setJSON($result);
    }
}
