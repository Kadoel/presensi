<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class JadwalKerjaSeeder extends Seeder
{
    public function run()
    {
        $faker = \Faker\Factory::create('id_ID');

        $pegawaiList = $this->db->table('pegawai')->get()->getResult();
        $shiftList   = $this->db->table('shift')->where('is_active', 1)->get()->getResult();

        if (empty($pegawaiList) || empty($shiftList)) {
            return;
        }

        $statusHariList = ['kerja', 'kerja', 'kerja', 'kerja', 'libur', 'izin', 'sakit'];

        $data = [];

        foreach ($pegawaiList as $pegawai) {
            for ($i = 6; $i >= 0; $i--) {
                $tanggal = date('Y-m-d', strtotime("-{$i} days"));
                $statusHari = $faker->randomElement($statusHariList);
                $shift = $faker->randomElement($shiftList);

                $data[] = [
                    'pegawai_id'  => $pegawai->id,
                    'tanggal'     => $tanggal,
                    'shift_id'    => $shift->id,
                    'status_hari' => $statusHari,
                    'catatan'     => $statusHari === 'kerja' ? null : ucfirst($statusHari),
                    'created_by'  => null,
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ];
            }
        }

        $this->db->table('jadwal_kerja')->insertBatch($data);
    }
}
