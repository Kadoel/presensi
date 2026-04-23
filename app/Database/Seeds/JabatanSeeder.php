<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class JabatanSeeder extends Seeder
{
    public function run()
    {
        $this->db->table('jabatan')->emptyTable();

        $now = date('Y-m-d H:i:s');

        $rows = [
            ['id' => 1, 'nama_jabatan' => 'Staff Pos', 'deskripsi' => 'Petugas layanan pos dan administrasi kiriman.', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'nama_jabatan' => 'Juru Parkir', 'deskripsi' => 'Petugas pengaturan kendaraan dan area parkir.', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'nama_jabatan' => 'Admin Office', 'deskripsi' => 'Petugas administrasi kantor dan operasional internal.', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
        ];

        $this->db->table('jabatan')->insertBatch($rows);
    }
}
