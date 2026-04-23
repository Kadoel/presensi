<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\TukarJadwalService;
use Hermawan\DataTables\DataTable;

class TukarJadwal extends BaseController
{
    protected TukarJadwalService $tukarJadwalService;

    public function __construct()
    {
        $this->tukarJadwalService = new TukarJadwalService();
    }

    public function index()
    {
        if ($this->request->getMethod() === 'GET') {
            return view('pages/admin/tukar/index', [
                'judul'   => 'Tukar Jadwal',
                'pegawai' => $this->tukarJadwalService->getPegawaiAktif(),
            ]);
        }

        $builder = $this->tukarJadwalService->dataTabel();

        return DataTable::of($builder)
            ->postQuery(function ($builder) {
                $builder->orderBy('tukar_jadwal.id', 'DESC');
            })
            ->edit('status', function ($row) {
                $map = [
                    'pending'   => 'warning',
                    'approved'  => 'success',
                    'rejected'  => 'danger',
                    'cancelled' => 'secondary',
                ];

                $badge = $map[strtolower((string) $row->status)] ?? 'secondary';

                return '<span class="badge bg-' . $badge . '">' . esc(strtoupper((string) $row->status)) . '</span>';
            })
            ->edit('tipe_pengajuan', function ($row) {
                return (string) $row->tipe_pengajuan === 'admin'
                    ? '<span class="badge bg-primary">ADMIN</span>'
                    : '<span class="badge bg-info">PEGAWAI</span>';
            })
            ->edit('tipe_swap', function ($row) {
                return (string) $row->tipe_swap === 'paired'
                    ? '<span class="badge bg-dark">PAIRED</span>'
                    : '<span class="badge bg-secondary">SIMPLE</span>';
            })
            ->add('action_button', function ($row) {
                $btn = '
                <button type="button" class="btn btn-sm btn-info" id="act-detail" data-id="' . $row->id . '">
                    <i class="fa fa-eye text-white"></i>
                </button>
            ';

                if ((string) ($row->status ?? '') === 'pending') {
                    $btn .= '
                    <button type="button" class="btn btn-sm btn-success" id="act-approve" data-id="' . $row->id . '">
                        <i class="fa fa-check"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" id="act-reject" data-id="' . $row->id . '">
                        <i class="fa fa-times"></i>
                    </button>
                ';
                }

                return $btn;
            })
            ->addNumbering('#')
            ->toJson(true);
    }

    public function detail()
    {
        $result = $this->tukarJadwalService->ambil((int) $this->request->getPost('id'));
        return $this->response->setJSON($result);
    }

    public function simpanLangsung()
    {
        $result = $this->tukarJadwalService->simpanLangsungAdmin($this->request->getPost());
        return $this->response->setJSON($result);
    }

    public function approve()
    {
        $result = $this->tukarJadwalService->approve(
            (int) $this->request->getPost('id'),
            $this->request->getPost('catatan_approval')
        );

        return $this->response->setJSON($result);
    }

    public function reject()
    {
        $result = $this->tukarJadwalService->reject(
            (int) $this->request->getPost('id'),
            $this->request->getPost('catatan_approval')
        );

        return $this->response->setJSON($result);
    }

    public function getSlotPegawai()
    {
        $result = $this->tukarJadwalService->getSlotPegawai((int) $this->request->getPost('pegawai_id'));
        return $this->response->setJSON($result);
    }
}
