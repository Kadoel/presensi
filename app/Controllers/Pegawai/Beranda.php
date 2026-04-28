<?php

namespace App\Controllers\Pegawai;

use App\Controllers\BaseController;
use App\Services\Pegawai\BerandaService;

class Beranda extends BaseController
{
    protected BerandaService $berandaService;

    public function __construct()
    {
        $this->berandaService = new BerandaService();
    }

    public function index()
    {
        return view('pages/pegawai/beranda/index', [
            'judul'   => 'Beranda',
            'caption' => 'Ringkasan data presensi saya',
        ]);
    }

    public function summary()
    {
        return $this->response->setJSON(
            $this->berandaService->getSummary()
        );
    }

    public function riwayatPresensi()
    {
        return $this->response->setJSON(
            $this->berandaService->getRiwayatPresensi()
        );
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/');
    }
}
