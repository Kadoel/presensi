<?php

namespace App\Controllers\Pegawai;

use App\Controllers\BaseController;
use App\Services\Pegawai\CutiService;
use Hermawan\DataTables\DataTable;

class Cuti extends BaseController
{
    protected CutiService $cutiService;

    public function __construct()
    {
        $this->cutiService = new CutiService();
    }

    public function index()
    {
        if ($this->request->getMethod() === 'GET') {
            $saldo = $this->cutiService->getSaldoSaya();

            return view('pages/pegawai/cuti/index', [
                'judul'   => 'Pengajuan Cuti',
                'caption' => 'Ajukan dan pantau status cuti saya',
                'saldo'   => $saldo['saldo'] ?? null,
            ]);
        }

        $builder = $this->cutiService->dataTabel();

        return DataTable::of($builder)
            ->addNumbering('#')
            ->edit('tanggal_mulai', fn($row) => tanggal_indonesia_singkat($row->tanggal_mulai))
            ->edit('tanggal_selesai', fn($row) => tanggal_indonesia_singkat($row->tanggal_selesai))
            ->add('jumlah_hari', function ($row) {
                return (((int) floor((strtotime($row->tanggal_selesai) - strtotime($row->tanggal_mulai)) / 86400)) + 1) . ' hari';
            })
            ->edit('status', function ($row) {
                return match ($row->status) {
                    'approved' => '<span class="badge bg-success">✔ Disetujui</span>',
                    'rejected' => '<span class="badge bg-danger">✖ Ditolak</span>',
                    default    => '<span class="badge bg-warning text-white">⏳ Pending</span>',
                };
            })
            ->edit('alasan', function ($row) {
                $alasan = trim((string) ($row->alasan ?? ''));

                if ($alasan === '') {
                    return '-';
                }

                $singkat = mb_strimwidth($alasan, 0, 20, '...');

                return '<span data-bs-toggle="tooltip" title="' . esc($alasan) . '">' . esc($singkat) . '</span>';
            })
            ->add('lampiran_btn', function ($row) {
                if (empty($row->lampiran)) {
                    return '-';
                }

                return '<a href="' . base_url('assets/media/lampiran/' . $row->lampiran) . '" target="_blank" class="btn btn-sm btn-primary text-white">
                    <i class="fa fa-file-pdf"></i>
                </a>';
            })
            ->add('action', function ($row) {
                $today        = date('Y-m-d');
                $tanggalMulai = $row->tanggal_mulai ?? null;

                $isLewat    = $tanggalMulai !== null && $tanggalMulai < $today;
                $isApproved = ($row->status ?? '') === 'approved';

                if ($isApproved || $isLewat) {
                    return '-';
                }

                return '
                    <button type="button" class="btn btn-sm btn-warning" id="act-edit" data-id="' . $row->id . '">
                        <i class="fa fa-edit text-white"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" id="act-delete" data-id="' . $row->id . '">
                        <i class="fa fa-trash-can"></i>
                    </button>
                ';
            })
            ->toJson(true);
    }

    public function simpan()
    {
        return $this->response->setJSON(
            $this->cutiService->simpan(
                $this->request->getPost(),
                $this->request->getFile('lampiran')
            )
        );
    }

    public function edit()
    {
        return $this->response->setJSON(
            $this->cutiService->ambil(
                (int) $this->request->getPost('id')
            )
        );
    }

    public function update($id)
    {
        return $this->response->setJSON(
            $this->cutiService->ubah(
                (int) $id,
                $this->request->getPost(),
                $this->request->getFile('edit-lampiran')
            )
        );
    }

    public function delete()
    {
        return $this->response->setJSON(
            $this->cutiService->hapus(
                (int) $this->request->getPost('id')
            )
        );
    }
}
