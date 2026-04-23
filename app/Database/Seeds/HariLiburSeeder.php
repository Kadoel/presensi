<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class HariLiburSeeder extends Seeder
{
    public function run()
    {
        $this->db->table('hari_libur')->emptyTable();

        $year = (int) date('Y');
        $now  = date('Y-m-d H:i:s');

        $rows = [
            ['id' => 1, 'tanggal' => sprintf('%04d-04-10', $year), 'nama_libur' => 'Libur Internal Operasional', 'keterangan' => 'Libur internal untuk simulasi demo aplikasi.', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'tanggal' => sprintf('%04d-04-18', $year), 'nama_libur' => 'Hari Libur Nasional Demo', 'keterangan' => 'Libur nasional contoh untuk demo.', 'created_at' => $now, 'updated_at' => $now],
        ];

        $this->db->table('hari_libur')->insertBatch($rows);
    }
}
