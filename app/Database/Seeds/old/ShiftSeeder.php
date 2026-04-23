<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ShiftSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'kode_shift'            => 'pagi',
                'nama_shift'            => 'Pagi',
                'jam_masuk'             => '08:00:00',
                'batas_mulai_datang'    => '07:00:00',
                'batas_akhir_datang'    => '09:00:00',
                'jam_pulang'            => '16:00:00',
                'batas_mulai_pulang'    => '15:00:00',
                'batas_akhir_pulang'    => '18:00:00',
                'toleransi_telat_menit' => 15,
                'keterangan'            => 'Shift pagi',
                'is_active'             => 1,
                'created_at'            => date('Y-m-d H:i:s'),
                'updated_at'            => date('Y-m-d H:i:s'),
            ],
            [
                'kode_shift'            => 'siang',
                'nama_shift'            => 'Siang',
                'jam_masuk'             => '13:00:00',
                'batas_mulai_datang'    => '12:00:00',
                'batas_akhir_datang'    => '14:00:00',
                'jam_pulang'            => '21:00:00',
                'batas_mulai_pulang'    => '20:00:00',
                'batas_akhir_pulang'    => '22:00:00',
                'toleransi_telat_menit' => 15,
                'keterangan'            => 'Shift siang',
                'is_active'             => 1,
                'created_at'            => date('Y-m-d H:i:s'),
                'updated_at'            => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('shift')->insertBatch($data);
    }
}
