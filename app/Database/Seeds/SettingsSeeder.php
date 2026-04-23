<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run()
    {
        $data = [
            'nama_usaha' => 'BUPDA BATUNUNGGUL',
            'alamat' => 'Jl. Tanjung Kerambitan, Sampalan, Desa batununggul',
            'telepon' => '08563832148',
            'email' => 'dbupda@gmail.com',
            'logo' => null,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->table('settings')->insert($data);
    }
}
