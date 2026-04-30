<?php

namespace App\Controllers\Pegawai;

use App\Controllers\BaseController;
use App\Services\Pegawai\PengajuanIzinService;
use Hermawan\DataTables\DataTable;

class PengajuanIzin extends BaseController
{
    protected PengajuanIzinService $pengajuanIzinService;

    public function __construct()
    {
        $this->pengajuanIzinService = new PengajuanIzinService();
    }

    public function index()
    {
        if ($this->request->getMethod() === 'GET') {
            return view('pages/pegawai/izin/index', [
                'judul'   => 'Pengajuan Izin / Sakit',
                'caption' => 'Ajukan dan pantau status izin atau sakit saya',
            ]);
        }

        $builder = $this->pengajuanIzinService->dataTabel();

        return DataTable::of($builder)
            ->addNumbering('#')
            ->edit('jenis', fn($row) => ucfirst($row->jenis ?? '-'))
            ->edit('tanggal_mulai', fn($row) => tanggal_indonesia_singkat($row->tanggal_mulai))
            ->edit('tanggal_selesai', fn($row) => tanggal_indonesia_singkat($row->tanggal_selesai))
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
            $this->pengajuanIzinService->simpan(
                $this->request->getPost(),
                $this->request->getFile('lampiran')
            )
        );
    }

    public function edit()
    {
        return $this->response->setJSON(
            $this->pengajuanIzinService->ambil(
                (int) $this->request->getPost('id')
            )
        );
    }

    public function update($id)
    {
        return $this->response->setJSON(
            $this->pengajuanIzinService->ubah(
                (int) $id,
                $this->request->getPost(),
                $this->request->getFile('edit-lampiran')
            )
        );
    }

    public function delete()
    {
        return $this->response->setJSON(
            $this->pengajuanIzinService->hapus(
                (int) $this->request->getPost('id')
            )
        );
    }
}
