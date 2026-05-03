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
                return esc($row->nama_pegawai ?? '-');
            })
            ->edit('tahun', function ($row) {
                return (string) ($row->tahun ?? '-');
            })
            ->edit('jatah', function ($row) {
                return (int) ($row->jatah ?? 0) . ' hari';
            })
            ->edit('terpakai', function ($row) {
                $jatah = (int) ($row->jatah ?? 0);
                $setengah_saldo = floor(((int) ($row->jatah ?? 0)) / 2); //5 -> 2
                $terpakai = (int) ($row->terpakai ?? 0);
                $badge = '<span class="badge bg-secondary">Unknown</span>';

                if ($terpakai == $jatah) {
                    $badge = '<span class="badge bg-danger text-white">' . (int) ($row->terpakai ?? 0) . ' hari</span>';
                }
                if ($terpakai != $jatah && $terpakai > $setengah_saldo) {
                    $badge = '<span class="badge bg-danger text-white">' . (int) ($row->terpakai ?? 0) . ' hari</span>';
                }
                if ($terpakai != $jatah && $terpakai <= $setengah_saldo) {
                    $badge = '<span class="badge bg-info text-white">' . (int) ($row->terpakai ?? 0) . ' hari</span>';
                }
                if ($terpakai != $jatah && $terpakai === 0) {
                    $badge = '<span class="badge bg-success text-white">' . (int) ($row->terpakai ?? 0) . ' hari</span>';
                }
                return $badge;
            })
            ->edit('sisa', function ($row) {
                $jatah = (int) ($row->jatah ?? 0);
                $setengah_saldo = ceil(((int) ($row->jatah ?? 0)) / 2);
                $sisa = (int) ($row->sisa ?? 0);
                $badge = '<span class="badge bg-secondary">Unknown</span>';

                if ($jatah == $sisa) {
                    $badge = '<span class="badge bg-success text-white">' . (int) ($row->sisa ?? 0) . ' hari</span>';
                }
                if ($sisa != $jatah && $sisa >= $setengah_saldo) {
                    $badge = '<span class="badge bg-info text-white">' . (int) ($row->sisa ?? 0) . ' hari</span>';
                }
                if ($sisa != $jatah && $sisa < $setengah_saldo) {
                    $badge = '<span class="badge bg-danger text-white">' . (int) ($row->sisa ?? 0) . ' hari</span>';
                }
                return $badge;
            })
            ->add('action', function ($row) {
                return '
                    <button type="button" class="btn btn-sm btn-warning" id="act-edit" data-id="' . $row->id . '">
                        <i class="fa fa-edit text-white"></i>
                    </button>
                ';
            })
            ->toJson(true);
    }

    public function generate()
    {
        return $this->response->setJSON(
            $this->saldoCutiService->generate($this->request->getPost())
        );
    }

    public function edit()
    {
        $id = (int) $this->request->getPost('id');

        return $this->response->setJSON(
            $this->saldoCutiService->ambil($id)
        );
    }

    public function ubah(int $id)
    {
        return $this->response->setJSON(
            $this->saldoCutiService->ubah((int) $id, $this->request->getPost())
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
