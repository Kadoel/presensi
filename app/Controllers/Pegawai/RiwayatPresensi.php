<?php

namespace App\Controllers\Pegawai;

use App\Controllers\BaseController;
use App\Services\Pegawai\RiwayatPresensiService;

class RiwayatPresensi extends BaseController
{
    protected RiwayatPresensiService $riwayatPresensiService;

    public function __construct()
    {
        $this->riwayatPresensiService = new RiwayatPresensiService();
    }

    public function index()
    {
        return view('pages/pegawai/riwayat/index', [
            'judul'   => 'Riwayat Presensi',
            'caption' => 'Kalender riwayat presensi saya',
        ]);
    }

    public function kalender()
    {
        return $this->response->setJSON(
            $this->riwayatPresensiService->kalender(
                $this->request->getGet('start'),
                $this->request->getGet('end')
            )
        );
    }
}
