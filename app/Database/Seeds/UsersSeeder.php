<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UsersSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'username' => 'kadoel',
                'password_hash' => password_hash('Kadoel@10', PASSWORD_DEFAULT),
                'role' => 'admin',
                'pegawai_id' => null,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'username' => 'budi',
                'password_hash' => password_hash('123456', PASSWORD_DEFAULT),
                'role' => 'pegawai',
                'pegawai_id' => 1,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('users')->insertBatch($data);
    }
}
