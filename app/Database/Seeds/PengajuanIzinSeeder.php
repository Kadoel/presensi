<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PengajuanIzinSeeder extends Seeder
{
    public function run()
    {
        $this->db->table('pengajuan_izin')->emptyTable();

        $year = (int) date('Y');
        $now  = date('Y-m-d H:i:s');

        $rows = [
            ['id' => 1, 'pegawai_id' => 4, 'jenis' => 'izin', 'tanggal_mulai' => sprintf('%04d-04-08', $year), 'tanggal_selesai' => sprintf('%04d-04-09', $year), 'alasan' => 'Keperluan keluarga di luar kota.', 'lampiran' => null, 'status' => 'approved', 'catatan_approval' => 'Disetujui untuk demo override jadwal.', 'approved_by' => null, 'approved_at' => $now, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'pegawai_id' => 5, 'jenis' => 'sakit', 'tanggal_mulai' => sprintf('%04d-04-22', $year), 'tanggal_selesai' => sprintf('%04d-04-23', $year), 'alasan' => 'Istirahat karena demam.', 'lampiran' => 'uploads/lampiran/surat-sakit-pegawai-5.pdf', 'status' => 'approved', 'catatan_approval' => 'Disetujui berdasarkan surat sakit.', 'approved_by' => null, 'approved_at' => $now, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'pegawai_id' => 2, 'jenis' => 'izin', 'tanggal_mulai' => sprintf('%04d-04-27', $year), 'tanggal_selesai' => sprintf('%04d-04-27', $year), 'alasan' => 'Acara keluarga.', 'lampiran' => null, 'status' => 'pending', 'catatan_approval' => null, 'approved_by' => null, 'approved_at' => null, 'created_at' => $now, 'updated_at' => $now],
        ];

        $this->db->table('pengajuan_izin')->insertBatch($rows);
    }
}
