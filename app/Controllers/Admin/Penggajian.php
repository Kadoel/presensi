<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\Admin\PenggajianService;
use Hermawan\DataTables\DataTable;

class Penggajian extends BaseController
{
    protected PenggajianService $penggajianService;

    public function __construct()
    {
        $this->penggajianService = new PenggajianService();
        helper('slip');
    }

    public function index()
    {
        if ($this->request->getMethod() === 'GET') {
            return view('pages/admin/penggajian/index', [
                'judul'    => 'Penggajian',
                'validasi' => $this->validasi,
            ]);
        }

        $bulan = (string) ($this->request->getPost('bulan') ?: date('Y-m'));

        return DataTable::of($this->penggajianService->dataTabel($bulan))
            ->addNumbering('#')
            ->edit('gaji_pokok', fn($row) => $this->rupiah((float) $row->gaji_pokok))
            ->edit('tunjangan', fn($row) => $this->rupiah((float) $row->tunjangan))
            ->edit('total_potongan', fn($row) => $this->rupiah((float) $row->total_potongan))
            ->edit('gaji_bersih', fn($row) => '<b>' . $this->rupiah((float) $row->gaji_bersih) . '</b>')
            ->edit('status', function ($row) {
                return ($row->status ?? 'draft') === 'final'
                    ? '<span class="badge bg-success">Final</span>'
                    : '<span class="badge bg-warning text-white">Draft</span>';
            })
            ->add('action', function ($row) {
                $button = '
                    <button type="button" class="btn btn-sm btn-info" id="act-detail" data-id="' . $row->id . '">
                        <i class="fa fa-eye text-white"></i>
                    </button>
                ';

                if (($row->status ?? 'draft') === 'final') {
                    $button .= '
                        <button type="button" class="btn btn-sm btn-primary" id="act-preview-slip" data-id="' . $row->id . '">
                            <i class="fa fa-file-lines text-white"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" id="act-pdf-slip" data-id="' . $row->id . '">
                            <i class="fa fa-file-pdf text-white"></i>
                        </button>
                    ';
                }

                return $button;
            })
            ->toJson(true);
    }

    public function ringkasan()
    {
        return $this->response->setJSON(
            $this->penggajianService->ringkasan((string) ($this->request->getPost('bulan') ?: date('Y-m')))
        );
    }

    public function generate()
    {
        return $this->response->setJSON(
            $this->penggajianService->generate((string) ($this->request->getPost('bulan') ?: date('Y-m')))
        );
    }

    public function finalkan()
    {
        return $this->response->setJSON(
            $this->penggajianService->finalkan((string) ($this->request->getPost('bulan') ?: date('Y-m')))
        );
    }

    public function detail()
    {
        return $this->response->setJSON(
            $this->penggajianService->detail((int) $this->request->getPost('id'))
        );
    }

    public function export()
    {
        return $this->penggajianService->exportFinal(
            (string) ($this->request->getGet('bulan') ?: date('Y-m'))
        );
    }

    public function previewSlip(int $id)
    {
        return $this->penggajianService->previewSlip((int) $id);
    }

    public function exportSlipPdf(int $id)
    {
        return $this->penggajianService->exportSlipPdf((int) $id);
    }

    public function exportSlipBulk()
    {
        return $this->penggajianService->exportSlipBulk(
            (string) ($this->request->getGet('bulan') ?: date('Y-m'))
        );
    }

    private function rupiah(int $value): string
    {
        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }
}
