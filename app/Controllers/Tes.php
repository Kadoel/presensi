<?php

namespace App\Controllers;

use App\Models\JadwalKerjaModel;

class Tes extends BaseController
{
    public function index()
    {
        helper(['string_helper', 'register_helper', 'pengaturan_helper', 'waktu_helper', 'printer_helper', 'filesystem']);

        $jadwalKerja = new JadwalKerjaModel();

        $sinkron = $jadwalKerja->db->table('jadwal_kerja')
            ->select('
            jadwal_kerja.tanggal,
            COUNT(jadwal_kerja.id) AS total_jadwal,
            COUNT(presensi.id) AS total_presensi,
            SUM(CASE WHEN presensi.hasil_presensi IS NOT NULL THEN 1 ELSE 0 END) AS total_sudah_sinkron
        ')
            ->join(
                'presensi',
                'presensi.pegawai_id = jadwal_kerja.pegawai_id 
            AND presensi.tanggal = jadwal_kerja.tanggal',
                'left'
            )
            ->where('jadwal_kerja.tanggal <', date('Y-m') . '-02')
            ->groupBy('jadwal_kerja.tanggal')
            ->having('total_sudah_sinkron < total_jadwal')
            ->orderBy('jadwal_kerja.tanggal', 'ASC')
            ->get()
            ->getResult();

        dd($sinkron);
    }
}
