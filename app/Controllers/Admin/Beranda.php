<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\BerandaService;

class Beranda extends BaseController
{
    protected BerandaService $berandaService;

    public function __construct()
    {
        $this->berandaService = new BerandaService();
    }

    public function index()
    {
        $data = [
            'judul' => 'Beranda',
        ];

        return view('pages/admin/beranda/index', $data);
    }

    public function summary()
    {
        return $this->response->setJSON(
            $this->berandaService->getSummary()
        );
    }

    public function presensiHariIni()
    {
        return $this->response->setJSON(
            $this->berandaService->getPresensiHariIni()
        );
    }

    public function aktivitasTerbaru()
    {
        return $this->response->setJSON(
            $this->berandaService->getAktivitasTerbaru()
        );
    }

    public function grafikMingguan()
    {
        return $this->response->setJSON(
            $this->berandaService->getGrafikMingguan()
        );
    }

    public function grafikBulanan()
    {
        return $this->response->setJSON(
            $this->berandaService->getGrafikBulanan()
        );
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/');
    }
}
