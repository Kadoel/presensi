<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\Admin\SaldoCutiService;
use Hermawan\DataTables\DataTable;

class SaldoCuti extends BaseController
{
    protected SaldoCutiService $saldoCutiService;

    public function __construct()
    {
        $this->saldoCutiService = new SaldoCutiService();
    }

    public function index()
    {
        if ($this->request->getMethod() === 'GET') {
            return view('pages/admin/saldo_cuti/index', [
                'judul'    => 'Saldo Cuti',
                'validasi' => $this->validasi,
                'tahun'    => (int) date('Y'),
            ]);
        }

        $tahun = (int) ($this->request->getPost('tahun_filter') ?: date('Y'));
        $builder = $this->saldoCutiService->dataTabel($tahun);

        return DataTable::of($builder)
            ->addNumbering('#')
            ->edit('pegawai_id', function ($row) {
                return esc(($row->kode_pegawai ?? '-') . ' - ' . ($row->nama_pegawai ?? '-'));
            })
            ->edit('tahun', function ($row) {
                return (string) ($row->tahun ?? '-');
            })
            ->edit('jatah', function ($row) {
                return (int) ($row->jatah ?? 0) . ' hari';
            })
            ->edit('terpakai', function ($row) {
                return (int) ($row->terpakai ?? 0) . ' hari';
            })
            ->edit('sisa', function ($row) {
                return '<span class="badge bg-success text-white">' . (int) ($row->sisa ?? 0) . ' hari</span>';
            })
            ->edit('is_active', function ($row) {
                if ((int) ($row->is_active ?? 0) === 1) {
                    return '<span class="badge bg-success text-white">Aktif</span>';
                }

                return '<span class="badge bg-secondary text-white">Tidak Aktif</span>';
            })
            ->toJson(true);
    }

    public function generate()
    {
        return $this->response->setJSON(
            $this->saldoCutiService->generate($this->request->getPost())
        );
    }

    public function ringkasan()
    {
        $tahun = (int) ($this->request->getPost('tahun') ?: date('Y'));

        return $this->response->setJSON(
            $this->saldoCutiService->ringkasan($tahun)
        );
    }
}
