<?php

namespace App\Controllers\Pegawai;

use App\Controllers\BaseController;
use App\Services\Pegawai\TukarJadwalService;
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
            return view('pages/pegawai/tukar/index', [
                'judul'   => 'Tukar Jadwal',
                'caption' => 'Ajukan tukar jadwal kerja saya',
                'pegawai' => $this->tukarJadwalService->getPegawaiTujuan(),
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
            ->edit('tipe_swap', function ($row) {
                return (string) $row->tipe_swap === 'paired'
                    ? '<span class="badge bg-dark">PAIRED</span>'
                    : '<span class="badge bg-secondary">SIMPLE</span>';
            })
            ->add('action_button', function ($row) {
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
        return $this->response->setJSON(
            $this->tukarJadwalService->ambil((int) $this->request->getPost('id'))
        );
    }

    public function simpan()
    {
        return $this->response->setJSON(
            $this->tukarJadwalService->simpan($this->request->getPost())
        );
    }

    public function getSlotSaya()
    {
        return $this->response->setJSON(
            $this->tukarJadwalService->getSlotSaya()
        );
    }

    public function getSlotPegawai()
    {
        return $this->response->setJSON(
            $this->tukarJadwalService->getSlotPegawai(
                (int) $this->request->getPost('pegawai_id')
            )
        );
    }
}
