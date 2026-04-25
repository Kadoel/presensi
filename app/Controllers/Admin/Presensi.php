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
                'pegawais'  => $this->presensiAdminService->dataPegawaiSelect(),
            ]);
        }

        $builder = $this->presensiAdminService->dataTabel($this->request->getPost('tanggal'));

        return DataTable::of($builder)
            ->postQuery(function ($builder) {
                $builder->orderBy('presensi.id', 'DESC');
            })
            ->edit('tanggal', fn($row) => tanggal_indonesia($row->tanggal))
            ->edit('jam_datang', fn($row) => $row->jam_datang ?: '-')
            ->edit('jam_pulang', fn($row) => $row->jam_pulang ?: '-')
            ->edit('status_datang', fn($row) => $this->presensiAdminService->badgeStatusDatang($row->status_datang))
            ->edit('status_pulang', fn($row) => $this->presensiAdminService->badgeStatusPulang($row->status_pulang))
            ->edit('sumber_presensi', fn($row) => $this->presensiAdminService->badgeSumberPresensi($row->sumber_presensi ?? 'scan'))
            ->edit('hasil_presensi', fn($row) => $this->presensiAdminService->badgeHasilPresensi($row->hasil_presensi))
            ->add('action', function ($row) {
                $button = '
                    <button type="button" class="btn btn-sm btn-info" id="act-detail" data-id="' . $row->id . '">
                        <i class="fa fa-eye text-white"></i>
                    </button>
                ';

                if (($row->sumber_presensi ?? '') === 'lupa_presensi') {
                    $button .= '
                        <button type="button" class="btn btn-sm btn-warning" id="act-edit-lupa" data-id="' . $row->id . '">
                            <i class="fa fa-edit text-white"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" id="act-delete-lupa" data-id="' . $row->id . '" data-nama="' . esc($row->nama_pegawai) . '">
                            <i class="fa fa-trash-can"></i>
                        </button>
                    ';
                }

                return $button;
            })
            ->addNumbering('#')
            ->toJson(true);
    }

    public function detail()
    {
        return $this->response->setJSON(
            $this->presensiAdminService->detail((int) $this->request->getVar('id'))
        );
    }

    public function ringkasan()
    {
        return $this->response->setJSON(
            $this->presensiAdminService->ringkasanHarian($this->request->getVar('tanggal') ?: date('Y-m-d'))
        );
    }

    public function sinkron()
    {
        return $this->response->setJSON(
            $this->presensiAdminService->sinkronPresensiHarian($this->request->getPost('tanggal') ?: date('Y-m-d'))
        );
    }

    public function generateAlpa()
    {
        return $this->response->setJSON(
            $this->presensiAdminService->generateAlpaHarian($this->request->getPost('tanggal') ?: date('Y-m-d'))
        );
    }

    public function simpanLupa()
    {
        return $this->response->setJSON(
            $this->presensiAdminService->simpanLupaPresensi($this->request->getPost())
        );
    }

    public function updateLupa($id)
    {
        return $this->response->setJSON(
            $this->presensiAdminService->updateLupaPresensi((int) $id, $this->request->getPost())
        );
    }

    public function deleteLupa()
    {
        return $this->response->setJSON(
            $this->presensiAdminService->hapusLupaPresensi((int) $this->request->getVar('id'))
        );
    }
}
