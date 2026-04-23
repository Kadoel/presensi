<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ShiftSeeder extends Seeder
{
    private function slug(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim($text, '-');
    }

    public function run()
    {
        $this->db->table('shift')->emptyTable();

        $now = date('Y-m-d H:i:s');

        $rows = [
            [
                'id' => 1,
                'kode_shift' => $this->slug('Shift Pagi'),
                'nama_shift' => 'Shift Pagi',
                'jam_masuk' => '07:00:00',
                'batas_mulai_datang' => '06:00:00',
                'batas_akhir_datang' => '08:00:00',
                'jam_pulang' => '15:00:00',
                'batas_mulai_pulang' => '14:00:00',
                'batas_akhir_pulang' => '18:00:00',
                'toleransi_telat_menit' => 10,
                'keterangan' => 'Shift operasional pagi.',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'kode_shift' => $this->slug('Shift Siang'),
                'nama_shift' => 'Shift Siang',
                'jam_masuk' => '13:00:00',
                'batas_mulai_datang' => '12:00:00',
                'batas_akhir_datang' => '14:00:00',
                'jam_pulang' => '21:00:00',
                'batas_mulai_pulang' => '20:00:00',
                'batas_akhir_pulang' => '23:00:00',
                'toleransi_telat_menit' => 10,
                'keterangan' => 'Shift operasional siang.',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'kode_shift' => $this->slug('Shift Office'),
                'nama_shift' => 'Shift Office',
                'jam_masuk' => '08:00:00',
                'batas_mulai_datang' => '07:00:00',
                'batas_akhir_datang' => '09:00:00',
                'jam_pulang' => '16:00:00',
                'batas_mulai_pulang' => '15:00:00',
                'batas_akhir_pulang' => '19:00:00',
                'toleransi_telat_menit' => 15,
                'keterangan' => 'Shift khusus admin office.',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        $this->db->table('shift')->insertBatch($rows);
    }
}
