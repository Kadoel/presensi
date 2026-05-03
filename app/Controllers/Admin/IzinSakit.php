<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\Admin\IzinSakitService;
use Hermawan\DataTables\DataTable;

class IzinSakit extends BaseController
{
    protected IzinSakitService $pengajuanIzinService;

    public function __construct()
    {
        $this->pengajuanIzinService = new IzinSakitService();
    }

    public function index()
    {
        if ($this->request->getMethod() === 'GET') {
            $pegawaiDropdown = $this->pengajuanIzinService->getPegawaiDropdown();

            return view('pages/admin/izin_sakit/index', [
                'judul'    => 'Izin & Sakit',
                'validasi' => $this->validasi,
                'pegawai' => $pegawaiDropdown['pegawai'] ?? [],
            ]);
        }

        $builder = $this->pengajuanIzinService->dataTabel();

        return DataTable::of($builder)
            ->addNumbering('#')
            ->edit('pegawai_id', function ($row) {
                return ($row->nama_pegawai ?? '-');
            })
            ->edit('jenis', function ($row) {
                return ucfirst($row->jenis);
            })
            ->edit('tanggal_mulai', function ($row) {
                return tanggal_indonesia_singkat($row->tanggal_mulai);
            })
            ->edit('tanggal_selesai', function ($row) {
                return tanggal_indonesia_singkat($row->tanggal_selesai);
            })
            ->edit('status', function ($row) {
                if ($row->status === 'approved') {
                    return '<span class="badge bg-success text-white">✔ Disetujui</span>';
                }
                if ($row->status === 'rejected') {
                    return '<span class="badge bg-danger text-white">✖ Ditolak</span>';
                }
                return '<span class="badge bg-warning text-white">⏳ Pending</span>';
            })
            ->edit('alasan', function ($row) {
                $alasan = trim((string) ($row->alasan ?? ''));

                if ($alasan === '') {
                    return '-';
                }

                $singkat = mb_strimwidth($alasan, 0, 7, '...');

                return '<span data-bs-toggle="tooltip" title="' . esc($alasan) . '">' . esc($singkat) . '</span>';
            })
            ->add('lampiran_btn', function ($row) {
                if (empty($row->lampiran)) {
                    return '-';
                }

                return '<a href="' . base_url('assets/media/lampiran/' . $row->lampiran) . '" target="_blank" class="btn btn-sm btn-primary text-white">
                <i class="fa fa-file-pdf"></i></a>';
            })
            ->add('action', function ($row) {
                $buttons = '';

                $today = date('Y-m-d');
                $tanggalMulai = $row->tanggal_mulai ?? null;

                $isLewat = $tanggalMulai !== null && $tanggalMulai < $today;
                $isApproved = $row->status === 'approved';
                $isPending = $row->status === 'pending';

                // ✏️ edit (hanya jika belum approved & belum lewat)
                if (! $isApproved && ! $isLewat) {
                    $buttons .= '
                        <button type="button" class="btn btn-sm btn-warning" id="act-edit" data-id="' . $row->id . '">
                            <i class="fa fa-edit text-white"></i>
                        </button>
                    ';
                }

                // ✅ approve (tetap boleh walaupun lewat)
                if ($isPending) {
                    $buttons .= '
                        <button type="button" class="btn btn-sm btn-success" id="act-approve" data-id="' . $row->id . '">
                            <i class="fa fa-check"></i>
                        </button>
                    ';
                }

                // ❌ reject (tetap boleh walaupun lewat)
                if ($isPending) {
                    $buttons .= '
                        <button type="button" class="btn btn-sm btn-secondary" id="act-reject" data-id="' . $row->id . '">
                            <i class="fa fa-times"></i>
                        </button>
                    ';
                }

                // 🔁 cancel approve
                if ($isApproved) {
                    $buttons .= '
                        <button type="button" class="btn btn-sm btn-dark" id="act-cancel-approve" data-id="' . $row->id . '">
                            <i class="fa fa-undo"></i>
                        </button>
                    ';
                }

                // 🗑 delete (hanya jika belum approved & belum lewat)
                if (! $isApproved && ! $isLewat) {
                    $buttons .= '
                        <button type="button" class="btn btn-sm btn-danger" id="act-delete" data-id="' . $row->id . '" data-nama="' . esc($row->nama_pegawai) . '">
                            <i class="fa fa-trash-can"></i>
                        </button>
                    ';
                }

                return $buttons;
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
        $id = (int) $this->request->getPost('id');

        return $this->response->setJSON(
            $this->pengajuanIzinService->ambil($id)
        );
    }

    public function ubah($id)
    {
        return $this->response->setJSON(
            $this->pengajuanIzinService->ubah(
                (int) $id,
                $this->request->getPost(),
                $this->request->getFile('edit-lampiran')
            )
        );
    }

    public function hapus()
    {
        $id = (int) $this->request->getPost('id');

        return $this->response->setJSON(
            $this->pengajuanIzinService->hapus($id)
        );
    }

    public function approve()
    {
        $id = (int) $this->request->getPost('id');
        $catatan = (string) $this->request->getPost('catatan_approval');

        return $this->response->setJSON(
            $this->pengajuanIzinService->approve(
                $id,
                (int) session()->get('user_id'),
                $catatan
            )
        );
    }

    public function reject()
    {
        $id = (int) $this->request->getPost('id');
        $catatan = (string) $this->request->getPost('catatan_approval');

        return $this->response->setJSON(
            $this->pengajuanIzinService->reject(
                $id,
                (int) session()->get('user_id'),
                $catatan
            )
        );
    }

    public function cancelApprove()
    {
        $id = (int) $this->request->getPost('id');

        return $this->response->setJSON(
            $this->pengajuanIzinService->cancelApprove($id)
        );
    }
}
