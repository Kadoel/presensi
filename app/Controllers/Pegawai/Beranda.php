<?php

namespace App\Controllers\Pegawai;

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
        return view('pages/pegawai/beranda/index', [
            'judul'     => 'Beranda',
            'caption'   => 'Selamat datang di beranda'
        ]);
    }
}
