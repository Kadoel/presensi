<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\Admin\PengaturanGajiService;
use Hermawan\DataTables\DataTable;

class PengaturanGaji extends BaseController
{
    protected PengaturanGajiService $pengaturanGajiService;

    public function __construct()
    {
        $this->pengaturanGajiService = new PengaturanGajiService();
    }

    public function index()
    {
        if ($this->request->getMethod() === 'GET') {
            return view('pages/admin/pengaturan_gaji/index', [
                'judul'    => 'Pengaturan Gaji',
                'validasi' => $this->validasi,
                'jabatan'  => $this->pengaturanGajiService->getJabatanDropdown(),
            ]);
        }

        return DataTable::of($this->pengaturanGajiService->dataTabel())
            ->addNumbering('#')
            ->edit('gaji_pokok', fn($row) => $this->pengaturanGajiService->formatRupiah($row->gaji_pokok ?? 0))
            ->edit('tunjangan', fn($row) => $this->pengaturanGajiService->formatRupiah($row->tunjangan ?? 0))
            ->edit('potongan_telat_per_menit', fn($row) => $this->pengaturanGajiService->formatRupiah($row->potongan_telat_per_menit ?? 0))
            ->edit('potongan_pulang_cepat_per_menit', fn($row) => $this->pengaturanGajiService->formatRupiah($row->potongan_pulang_cepat_per_menit ?? 0))
            ->edit('potongan_alpa_per_hari', fn($row) => $this->pengaturanGajiService->formatRupiah($row->potongan_alpa_per_hari ?? 0))
            ->edit('is_active', function ($row) {
                return (int) ($row->is_active ?? 0) === 1
                    ? '<span class="badge bg-success">Aktif</span>'
                    : '<span class="badge bg-secondary">Nonaktif</span>';
            })
            ->add('action', function ($row) {
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
            $this->pengaturanGajiService->simpan($this->request->getPost())
        );
    }

    public function edit()
    {
        return $this->response->setJSON(
            $this->pengaturanGajiService->ambil((int) $this->request->getPost('id'))
        );
    }

    public function update($id)
    {
        return $this->response->setJSON(
            $this->pengaturanGajiService->ubah((int) $id, $this->request->getPost())
        );
    }

    public function delete()
    {
        return $this->response->setJSON(
            $this->pengaturanGajiService->hapus((int) $this->request->getPost('id'))
        );
    }
}
