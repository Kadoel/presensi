<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PresensiSeeder extends Seeder
{
    public function run()
    {
        $this->db->table('presensi')->emptyTable();

        $year  = (int) date('Y');
        $today = new \DateTime(date('Y-m-d'));
        $start = new \DateTime(sprintf('%04d-04-01', $year));
        $todayStr = $today->format('Y-m-d');

        $jadwalRows = $this->db->table('jadwal_kerja')->orderBy('id', 'ASC')->get()->getResultArray();
        $jadwalMap = [];
        foreach ($jadwalRows as $row) {
            $jadwalMap[$row['pegawai_id'] . '|' . $row['tanggal']] = $row;
        }

        $shiftRows = $this->db->table('shift')->get()->getResultArray();
        $shiftMap = [];
        foreach ($shiftRows as $row) {
            $shiftMap[$row['id']] = $row;
        }

        $rows = [];
        $id = 1;

        while ($start <= $today) {
            $tanggal = $start->format('Y-m-d');
            $dayNum  = (int) $start->format('d');

            for ($pegawaiId = 1; $pegawaiId <= 5; $pegawaiId++) {
                $key = $pegawaiId . '|' . $tanggal;
                if (!isset($jadwalMap[$key])) {
                    continue;
                }

                $jadwal = $jadwalMap[$key];
                $baseRow = [
                    'id' => $id++,
                    'pegawai_id' => $pegawaiId,
                    'tanggal' => $tanggal,
                    'jadwal_kerja_id' => $jadwal['id'],
                    'shift_id' => $jadwal['shift_id'],
                    'jam_datang' => null,
                    'jam_pulang' => null,
                    'status_datang' => null,
                    'status_pulang' => null,
                    'menit_telat' => 0,
                    'menit_pulang_cepat' => 0,
                    'selfie_datang' => null,
                    'selfie_pulang' => null,
                    'barcode_datang' => null,
                    'barcode_pulang' => null,
                    'ip_address' => null,
                    'user_agent' => null,
                    'catatan_admin' => null,
                    'is_manual' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];

                if ($jadwal['status_hari'] === 'izin') {
                    $baseRow['status_datang'] = 'izin';
                    $baseRow['is_manual'] = 1;
                    $baseRow['catatan_admin'] = 'Presensi otomatis karena izin.';
                    $rows[] = $baseRow;
                    continue;
                }

                if ($jadwal['status_hari'] === 'sakit') {
                    $baseRow['status_datang'] = 'sakit';
                    $baseRow['is_manual'] = 1;
                    $baseRow['catatan_admin'] = 'Presensi otomatis karena sakit.';
                    $rows[] = $baseRow;
                    continue;
                }

                if ($jadwal['status_hari'] === 'libur') {
                    $baseRow['status_datang'] = 'libur';
                    $baseRow['is_manual'] = 1;
                    $baseRow['catatan_admin'] = 'Presensi otomatis karena hari libur.';
                    $rows[] = $baseRow;
                    continue;
                }

                if (empty($jadwal['shift_id']) || !isset($shiftMap[$jadwal['shift_id']])) {
                    $baseRow['status_datang'] = 'alpa';
                    $baseRow['is_manual'] = 1;
                    $baseRow['catatan_admin'] = 'Shift tidak ditemukan.';
                    $rows[] = $baseRow;
                    continue;
                }

                $shift = $shiftMap[$jadwal['shift_id']];
                $jamMasuk = $shift['jam_masuk'];
                $jamPulang = $shift['jam_pulang'];
                $toleransi = (int) $shift['toleransi_telat_menit'];

                $statusDatang = 'hadir';
                $statusPulang = 'pulang';
                $menitTelat = 0;
                $menitPulangCepat = 0;

                if (($pegawaiId === 2 && in_array($dayNum, [2, 7, 15, 24], true)) || ($pegawaiId === 5 && in_array($dayNum, [5, 12, 19], true))) {
                    $statusDatang = 'telat';
                    $menitTelat = $toleransi + (($dayNum % 3) + 4);
                }

                if ($pegawaiId === 4 && in_array($dayNum, [6, 14, 28], true)) {
                    $statusDatang = 'alpa';
                    $statusPulang = null;
                }

                if ($tanggal === $todayStr && in_array($pegawaiId, [2, 5], true)) {
                    $statusPulang = 'belum_pulang';
                }

                if ($pegawaiId === 3 && in_array($dayNum, [4, 17, 25], true) && $tanggal !== $todayStr) {
                    $statusPulang = 'pulang_cepat';
                    $menitPulangCepat = 20;
                }

                if ($statusDatang === 'alpa') {
                    $rows[] = array_merge($baseRow, [
                        'status_datang' => 'alpa',
                        'catatan_admin' => 'Tidak hadir tanpa keterangan.',
                        'is_manual' => 1,
                    ]);
                    continue;
                }

                $jamDatang = new \DateTime($tanggal . ' ' . $jamMasuk);
                $jamDatang->modify('+' . $menitTelat . ' minutes');

                $jamPulangObj = new \DateTime($tanggal . ' ' . $jamPulang);
                if ($statusPulang === 'pulang_cepat') {
                    $jamPulangObj->modify('-' . $menitPulangCepat . ' minutes');
                }

                $rows[] = array_merge($baseRow, [
                    'jam_datang' => $jamDatang->format('Y-m-d H:i:s'),
                    'jam_pulang' => $statusPulang === 'belum_pulang' ? null : $jamPulangObj->format('Y-m-d H:i:s'),
                    'status_datang' => $statusDatang,
                    'status_pulang' => $statusPulang,
                    'menit_telat' => $menitTelat,
                    'menit_pulang_cepat' => $menitPulangCepat,
                    'selfie_datang' => 'uploads/selfie_presensi/' . date('Y/m/d', strtotime($tanggal)) . '/datang-pegawai-' . $pegawaiId . '-' . $dayNum . '.jpeg',
                    'selfie_pulang' => $statusPulang === 'belum_pulang' ? null : 'uploads/selfie_presensi/' . date('Y/m/d', strtotime($tanggal)) . '/pulang-pegawai-' . $pegawaiId . '-' . $dayNum . '.jpeg',
                    'barcode_datang' => 'SIMULASI-QRCODE-' . $pegawaiId,
                    'barcode_pulang' => $statusPulang === 'belum_pulang' ? null : 'SIMULASI-QRCODE-' . $pegawaiId,
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Demo Seeder Presensi',
                    'catatan_admin' => null,
                    'is_manual' => 0,
                ]);
            }

            $start->modify('+1 day');
        }

        $this->db->table('presensi')->insertBatch($rows);
    }
}
