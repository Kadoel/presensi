<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class JadwalKerjaSeeder extends Seeder
{
    private function shiftDefaultByPegawai(int $pegawaiId): int
    {
        return match ($pegawaiId) {
            1 => 1,
            2 => 2,
            3 => 3,
            4 => 1,
            5 => 2,
            default => 1,
        };
    }

    public function run()
    {
        $this->db->table('jadwal_kerja')->emptyTable();

        $year  = (int) date('Y');
        $start = new \DateTime(sprintf('%04d-04-01', $year));
        $end   = new \DateTime(sprintf('%04d-04-30', $year));
        $now   = date('Y-m-d H:i:s');

        $hariLiburMap = [
            sprintf('%04d-04-10', $year) => 1,
            sprintf('%04d-04-18', $year) => 2,
        ];

        $izinMap = [
            sprintf('%04d-04-08', $year) => ['pegawai_id' => 4, 'jenis' => 'izin', 'pengajuan_izin_id' => 1],
            sprintf('%04d-04-09', $year) => ['pegawai_id' => 4, 'jenis' => 'izin', 'pengajuan_izin_id' => 1],
            sprintf('%04d-04-22', $year) => ['pegawai_id' => 5, 'jenis' => 'sakit', 'pengajuan_izin_id' => 2],
            sprintf('%04d-04-23', $year) => ['pegawai_id' => 5, 'jenis' => 'sakit', 'pengajuan_izin_id' => 2],
        ];

        $rows = [];
        $id = 1;

        while ($start <= $end) {
            $tanggal = $start->format('Y-m-d');

            for ($pegawaiId = 1; $pegawaiId <= 5; $pegawaiId++) {
                $shiftId = $this->shiftDefaultByPegawai($pegawaiId);

                $row = [
                    'id' => $id++,
                    'pegawai_id' => $pegawaiId,
                    'tanggal' => $tanggal,
                    'shift_id' => $shiftId,
                    'status_hari' => 'kerja',
                    'sumber_data' => 'manual',
                    'pengajuan_izin_id' => null,
                    'hari_libur_id' => null,
                    'shift_id_sebelumnya' => null,
                    'status_hari_sebelumnya' => null,
                    'catatan_sebelumnya' => null,
                    'sumber_data_sebelumnya' => null,
                    'catatan' => 'Jadwal normal bulan April.',
                    'created_by' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if (isset($hariLiburMap[$tanggal])) {
                    $row['hari_libur_id'] = $hariLiburMap[$tanggal];
                    $row['shift_id_sebelumnya'] = $shiftId;
                    $row['status_hari_sebelumnya'] = 'kerja';
                    $row['catatan_sebelumnya'] = 'Jadwal normal sebelum override hari libur.';
                    $row['sumber_data_sebelumnya'] = 'manual';
                    $row['shift_id'] = null;
                    $row['status_hari'] = 'libur';
                    $row['sumber_data'] = 'hari_libur';
                    $row['catatan'] = 'Override jadwal karena hari libur.';
                }

                if (isset($izinMap[$tanggal]) && $izinMap[$tanggal]['pegawai_id'] === $pegawaiId) {
                    $jenis = $izinMap[$tanggal]['jenis'];
                    $row['pengajuan_izin_id'] = $izinMap[$tanggal]['pengajuan_izin_id'];
                    $row['shift_id_sebelumnya'] = $shiftId;
                    $row['status_hari_sebelumnya'] = 'kerja';
                    $row['catatan_sebelumnya'] = 'Jadwal normal sebelum override pengajuan izin.';
                    $row['sumber_data_sebelumnya'] = 'manual';
                    $row['shift_id'] = null;
                    $row['status_hari'] = $jenis;
                    $row['sumber_data'] = 'pengajuan_izin';
                    $row['catatan'] = 'Override jadwal dari pengajuan ' . $jenis . '.';
                }

                $rows[] = $row;
            }

            $start->modify('+1 day');
        }

        $this->db->table('jadwal_kerja')->insertBatch($rows);
    }
}
