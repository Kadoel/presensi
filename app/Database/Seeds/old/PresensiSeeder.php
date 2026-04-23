<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PresensiSeeder extends Seeder
{
    public function run()
    {
        $faker = \Faker\Factory::create('id_ID');

        $jadwalList = $this->db->table('jadwal_kerja jk')
            ->select('
                jk.id,
                jk.pegawai_id,
                jk.tanggal,
                jk.shift_id,
                jk.status_hari,
                s.jam_masuk,
                s.jam_pulang,
                s.toleransi_telat_menit
            ')
            ->join('shift s', 's.id = jk.shift_id', 'left')
            ->get()
            ->getResult();

        if (empty($jadwalList)) {
            return;
        }

        $data = [];

        foreach ($jadwalList as $jadwal) {
            $statusDatang = null;
            $statusPulang = null;
            $jamDatang = null;
            $jamPulang = null;
            $menitTelat = 0;
            $menitPulangCepat = 0;

            if ($jadwal->status_hari === 'kerja') {
                $mode = $faker->randomElement(['hadir', 'hadir', 'hadir', 'telat', 'alpa']);

                if ($mode === 'hadir') {
                    $offsetMasuk = rand(-10, 10);
                    $offsetPulang = rand(-5, 20);

                    $jamDatangObj = strtotime($jadwal->tanggal . ' ' . $jadwal->jam_masuk . " {$offsetMasuk} minutes");
                    $jamPulangObj = strtotime($jadwal->tanggal . ' ' . $jadwal->jam_pulang . " {$offsetPulang} minutes");

                    $jamDatang = date('Y-m-d H:i:s', $jamDatangObj);
                    $jamPulang = date('Y-m-d H:i:s', $jamPulangObj);

                    $statusDatang = 'hadir';
                    $statusPulang = 'pulang';

                    if ($offsetMasuk > 0) {
                        $menitTelat = $offsetMasuk;
                        if ($offsetMasuk > (int) $jadwal->toleransi_telat_menit) {
                            $statusDatang = 'telat';
                        }
                    }

                    if ($offsetPulang < 0) {
                        $statusPulang = 'pulang_cepat';
                        $menitPulangCepat = abs($offsetPulang);
                    }
                } elseif ($mode === 'telat') {
                    $offsetMasuk = rand(16, 45);
                    $offsetPulang = rand(0, 20);

                    $jamDatangObj = strtotime($jadwal->tanggal . ' ' . $jadwal->jam_masuk . " +{$offsetMasuk} minutes");
                    $jamPulangObj = strtotime($jadwal->tanggal . ' ' . $jadwal->jam_pulang . " +{$offsetPulang} minutes");

                    $jamDatang = date('Y-m-d H:i:s', $jamDatangObj);
                    $jamPulang = date('Y-m-d H:i:s', $jamPulangObj);

                    $statusDatang = 'telat';
                    $statusPulang = 'pulang';
                    $menitTelat = $offsetMasuk;
                } else {
                    $statusDatang = 'alpa';
                    $statusPulang = 'belum_pulang';
                }
            } elseif ($jadwal->status_hari === 'izin') {
                $statusDatang = 'izin';
                $statusPulang = 'belum_pulang';
            } elseif ($jadwal->status_hari === 'sakit') {
                $statusDatang = 'sakit';
                $statusPulang = 'belum_pulang';
            } elseif ($jadwal->status_hari === 'libur') {
                $statusDatang = 'libur';
                $statusPulang = 'belum_pulang';
            }

            $data[] = [
                'pegawai_id'           => $jadwal->pegawai_id,
                'tanggal'              => $jadwal->tanggal,
                'jadwal_kerja_id'      => $jadwal->id,
                'shift_id'             => $jadwal->shift_id,
                'jam_datang'           => $jamDatang,
                'jam_pulang'           => $jamPulang,
                'status_datang'        => $statusDatang,
                'status_pulang'        => $statusPulang,
                'menit_telat'          => $menitTelat,
                'menit_pulang_cepat'   => $menitPulangCepat,
                'selfie_datang'        => null,
                'selfie_pulang'        => null,
                'barcode_datang'       => null,
                'barcode_pulang'       => null,
                'ip_address'           => $faker->ipv4(),
                'user_agent'           => $faker->userAgent(),
                'catatan_admin'        => null,
                'is_manual'            => 0,
                'created_at'           => date('Y-m-d H:i:s'),
                'updated_at'           => date('Y-m-d H:i:s'),
            ];
        }

        $this->db->table('presensi')->insertBatch($data);
    }
}
