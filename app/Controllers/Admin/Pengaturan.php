<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\SettingsService;

class Pengaturan extends BaseController
{
    protected $pengaturanModel;
    protected $settingsService;

    public function __construct()
    {
        $this->settingsService = new SettingsService();
    }

    public function index()
    {
        $data = [
            'judul'      => 'Pengaturan',
            'pengaturan' => $this->settingsService->ambilData(),
            'validasi'   => $this->validasi
        ];

        return view('pages/admin/pengaturan/index', $data);
    }

    public function simpan()
    {
        $result = $this->settingsService->simpan(
            $this->request->getPost(),
            $this->request->getFile('logo')
        );

        return $this->response->setJSON([
            'sukses' => $result['sukses'],
            'errors' => $result['errors'] ?? [],
            'pesan'  => $result['pesan'] ?? ''
        ]);
    }
}
