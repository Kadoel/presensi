<?php

namespace App\Controllers;

use App\Models\JadwalKerjaModel;

class Tes extends BaseController
{
    public function index()
    {
        helper(['string_helper', 'register_helper', 'pengaturan_helper', 'waktu_helper', 'printer_helper', 'filesystem']);

        $jadwalKerja = new JadwalKerjaModel();

        $jadwal = $jadwalKerja->selectData()->get()->getResultArray();

        dd($jadwal);
    }
}
