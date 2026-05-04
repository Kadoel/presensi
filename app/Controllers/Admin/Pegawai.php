<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\Admin\PegawaiService;
use CodeIgniter\Exceptions\PageNotFoundException;
use Hermawan\DataTables\DataTable;

class Pegawai extends BaseController
{
    protected PegawaiService $pegawaiService;

    public function __construct()
    {
        $this->pegawaiService = new PegawaiService();
    }

    public function index()
    {
        if ($this->request->getMethod() === 'GET') {
            return view('pages/admin/pegawai/index', [
                'judul'    => 'Pegawai',
                'validasi' => $this->validasi,
                'jabatans'  => $this->pegawaiService->dataJabatanSelect()
            ]);
        }

        $builder = $this->pegawaiService->dataTabel();

        return DataTable::of($builder)
            ->postQuery(function ($builder) {
                $builder->orderBy('pegawai.id', 'ASC');
            })
            ->edit('tanggal_lahir', function ($row) {
                return tanggal_indonesia($row->tanggal_lahir);
            })
            ->edit('is_active', function ($row) {
                return (int) $row->is_active === 1
                    ? '<span class="badge bg-success">Aktif</span>'
                    : '<span class="badge bg-danger">Nonaktif</span>';
            })
            ->add('action', function ($row) {
                return '
                    <a href="' . base_url("admin/pegawai/kartu/" . $row->id) . '" target="_blank" class="btn btn-sm btn-primary text-white">
                        <i class="fa fa-id-card"></i>
                    </a>
                    <a href="' . base_url("admin/pegawai/download/" . $row->id) . '" class="btn btn-sm btn-success">
                        <i class="fa fa-qrcode"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-info" id="act-detail" data-id="' . $row->id . '">
                        <i class="fa fa-eye text-white"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-warning" id="act-edit" data-id="' . $row->id . '">
                        <i class="fa fa-edit text-white"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" id="act-delete" data-id="' . $row->id . '" data-nama="' . esc($row->nama_pegawai) . '">
                        <i class="fa fa-trash-can"></i>
                    </button>
                ';
            })
            ->addNumbering('#')
            ->toJson(true);
    }

    public function simpan()
    {
        $result = $this->pegawaiService->simpan(
            $this->request->getPost(),
            $this->request->getFile('foto')
        );

        return $this->response->setJSON($result);
    }

    public function edit()
    {
        $result = $this->pegawaiService->ambil((int) $this->request->getVar('id'));
        return $this->response->setJSON($result);
    }

    public function update($id)
    {
        $result = $this->pegawaiService->ubah(
            (int) $id,
            $this->request->getPost(),
            $this->request->getFile('edit-foto')
        );

        return $this->response->setJSON($result);
    }

    public function delete()
    {
        $result = $this->pegawaiService->hapus((int) $this->request->getVar('id'));
        return $this->response->setJSON($result);
    }

    public function kartu(int $id)
    {
        $ukuran = $this->request->getGet('ukuran') ?? 'B1';

        $kartu = $this->pegawaiService->dataKartu((int) $id, $ukuran);

        if (empty($kartu)) {
            throw PageNotFoundException::forPageNotFound('Kartu pegawai tidak ditemukan');
        }

        return view('pages/admin/pegawai/kartu', [
            'judul' => 'Kartu Pegawai',
            'kartu' => $kartu,
        ]);
    }

    public function downloadQRCode(int $id)
    {
        $result = $this->pegawaiService->downloadQRCode((int) $id);

        if (! ($result['sukses'] ?? false)) {
            throw PageNotFoundException::forPageNotFound(
                $result['pesan'] ?? 'QR Code tidak ditemukan'
            );
        }

        return $this->response->download(
            $result['path'],
            null
        )->setFileName($result['nama_download']);
    }
}
