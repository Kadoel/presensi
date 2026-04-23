<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class JabatanSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'nama_jabatan' => 'Juru Parkir',
                'deskripsi'    => 'Mengatur Parkir',
                'is_active'    => 1,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],
            [
                'nama_jabatan' => 'Staff Pos',
                'deskripsi'    => 'Admin Pos Keluar',
                'is_active'    => 1,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('jabatan')->insertBatch($data);
    }
}
