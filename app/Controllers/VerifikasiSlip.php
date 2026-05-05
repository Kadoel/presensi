<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PenggajianModel;

class VerifikasiSlip extends BaseController
{
    protected PenggajianModel $penggajianModel;

    public function __construct()
    {
        $this->penggajianModel = new PenggajianModel();
        helper('slip');
    }

    public function index($token)
    {
        $token = trim((string) $token);

        if (! preg_match('/^[a-f0-9]{64}$/', $token)) {
            return view('pages/verifikasi_slip/index', [
                'judul' => 'Verifikasi Slip Gaji',
                'valid' => false,
                'slip'  => null,
            ]);
        }

        $slip = $this->penggajianModel->getSlipByToken($token);

        return view('pages/verifikasi_slip/index', [
            'judul' => 'Verifikasi Slip Gaji',
            'valid' => $slip !== null,
            'slip'  => $slip,
        ]);
    }
}
