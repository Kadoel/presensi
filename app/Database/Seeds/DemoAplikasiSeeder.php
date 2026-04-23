<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DemoAplikasiSeeder extends Seeder
{
    public function run()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');

        // urutan insert: parent -> child
        $this->call('SettingsSeeder');
        $this->call('UsersSeeder');
        $this->call('JabatanSeeder');
        $this->call('ShiftSeeder');
        $this->call('PegawaiSeeder');
        $this->call('HariLiburSeeder');
        $this->call('PengajuanIzinSeeder');
        $this->call('JadwalKerjaSeeder');
        $this->call('PresensiSeeder');
        $this->call('TukarJadwalSeeder');

        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}
