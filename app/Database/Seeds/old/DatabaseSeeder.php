<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call('JabatanSeeder');
        $this->call('PegawaiSeeder');
        $this->call('SettingsSeeder');
        $this->call('ShiftSeeder');
        $this->call('UsersSeeder');
        // $this->call('JadwalKerjaSeeder');
        // $this->call('PresensiSeeder');
    }
}
