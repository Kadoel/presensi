<?php

namespace App\Controllers;

use App\Models\JadwalKerjaModel;
use DateTime;

class Tes extends BaseController
{
    public function index()
    {
        helper(['string_helper', 'register_helper', 'pengaturan_helper', 'waktu_helper', 'printer_helper', 'filesystem']);

        $jadwalKerja = new JadwalKerjaModel();
        $now = new DateTime();
        $jamMasuk = new DateTime("2026-05-06 18:00:00");
        $toleransiMenit = (int) 0;
        $batasToleransi = (clone $jamMasuk)->modify('+' . $toleransiMenit . ' minutes');

        $statusPreview = 'tepat_waktu';
        $menitTelat    = 0;

        if ($now > $batasToleransi) {
            $statusPreview = 'telat';

            $selisihDetik = $now->getTimestamp() - $batasToleransi->getTimestamp();
            $menitTelat = max(1, (int) floor($selisihDetik / 60));
        }

        $ket = $statusPreview . ' ' . $menitTelat;

        dd($ket);
    }
}
