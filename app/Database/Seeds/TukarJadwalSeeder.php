<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TukarJadwalSeeder extends Seeder
{
    public function run()
    {
        $this->db->table('tukar_jadwal')->emptyTable();

        $year = (int) date('Y');
        $now  = date('Y-m-d H:i:s');

        $jadwalA = $this->db->table('jadwal_kerja')->where('pegawai_id', 1)->where('tanggal', sprintf('%04d-04-05', $year))->get()->getRowArray();
        $jadwalB = $this->db->table('jadwal_kerja')->where('pegawai_id', 4)->where('tanggal', sprintf('%04d-04-05', $year))->get()->getRowArray();
        $jadwalC = $this->db->table('jadwal_kerja')->where('pegawai_id', 2)->where('tanggal', sprintf('%04d-04-16', $year))->get()->getRowArray();
        $jadwalD = $this->db->table('jadwal_kerja')->where('pegawai_id', 5)->where('tanggal', sprintf('%04d-04-16', $year))->get()->getRowArray();

        $rows = [];

        if ($jadwalA && $jadwalB) {
            $rows[] = [
                'id' => 1,
                'jadwal_kerja_a_id' => $jadwalA['id'],
                'pegawai_a_id' => $jadwalA['pegawai_id'],
                'tanggal_a' => $jadwalA['tanggal'],
                'shift_a_id' => (int) $jadwalA['shift_id'],
                'status_hari_a' => $jadwalA['status_hari'],
                'sumber_data_a' => $jadwalA['sumber_data'],
                'jadwal_kerja_b_id' => $jadwalB['id'],
                'pegawai_b_id' => $jadwalB['pegawai_id'],
                'tanggal_b' => $jadwalB['tanggal'],
                'shift_b_id' => (int) $jadwalB['shift_id'],
                'status_hari_b' => $jadwalB['status_hari'],
                'sumber_data_b' => $jadwalB['sumber_data'],
                'tipe_swap' => 'simple',
                'alasan' => 'Tukar jadwal untuk keperluan pribadi.',
                'status' => 'approved',
                'tipe_pengajuan' => 'pegawai',
                'catatan_approval' => 'Disetujui untuk demo.',
                'diajukan_oleh' => 1,
                'disetujui_oleh' => 1,
                'disetujui_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($jadwalC && $jadwalD) {
            $rows[] = [
                'id' => 2,
                'jadwal_kerja_a_id' => $jadwalC['id'],
                'pegawai_a_id' => $jadwalC['pegawai_id'],
                'tanggal_a' => $jadwalC['tanggal'],
                'shift_a_id' => (int) $jadwalC['shift_id'],
                'status_hari_a' => $jadwalC['status_hari'],
                'sumber_data_a' => $jadwalC['sumber_data'],
                'jadwal_kerja_b_id' => $jadwalD['id'],
                'pegawai_b_id' => $jadwalD['pegawai_id'],
                'tanggal_b' => $jadwalD['tanggal'],
                'shift_b_id' => (int) $jadwalD['shift_id'],
                'status_hari_b' => $jadwalD['status_hari'],
                'sumber_data_b' => $jadwalD['sumber_data'],
                'tipe_swap' => 'paired',
                'alasan' => 'Permintaan pertukaran shift untuk demo approval pending.',
                'status' => 'pending',
                'tipe_pengajuan' => 'pegawai',
                'catatan_approval' => null,
                'diajukan_oleh' => 1,
                'disetujui_oleh' => 1,
                'disetujui_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($rows)) {
            $this->db->table('tukar_jadwal')->insertBatch($rows);
        }
    }
}
