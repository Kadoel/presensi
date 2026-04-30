<?php

namespace App\Controllers\Pegawai;

use App\Controllers\BaseController;
use App\Services\Pegawai\JadwalService;

class Jadwal extends BaseController
{
    protected JadwalService $jadwalService;

    public function __construct()
    {
        $this->jadwalService = new JadwalService();
    }

    public function index()
    {
        return view('pages/pegawai/jadwal/index', [
            'judul'   => 'Jadwal Saya',
            'caption' => 'Kalender jadwal kerja saya',
        ]);
    }

    public function kalender()
    {
        return $this->response->setJSON(
            $this->jadwalService->kalender(
                $this->request->getGet('start'),
                $this->request->getGet('end')
            )
        );
    }
}
