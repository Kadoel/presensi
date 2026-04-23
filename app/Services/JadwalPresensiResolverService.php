<?php

namespace App\Services;

use App\Models\JadwalKerjaModel;
use App\Models\PegawaiModel;
use App\Models\ShiftModel;

class JadwalPresensiResolverService extends BaseService
{
    protected PegawaiModel $pegawaiModel;
    protected JadwalKerjaModel $jadwalKerjaModel;
    protected ShiftModel $shiftModel;

    public function __construct()
    {
        parent::__construct();

        $this->pegawaiModel     = new PegawaiModel();
        $this->jadwalKerjaModel = new JadwalKerjaModel();
        $this->shiftModel       = new ShiftModel();
    }

    public function resolveDariHasilScan(string $scanValue, ?string $tanggal = null): array
    {
        return $this->eksekusi(function () use ($scanValue, $tanggal) {
            $kodePegawai = $this->stringWajib($scanValue);
            $tanggalKerja = $this->stringWajib($tanggal ?: date('Y-m-d'));

            if ($kodePegawai === '') {
                return $this->hasilGagal([
                    'scan_value' => 'QR Code tidak valid'
                ], 'QR Code tidak valid');
            }

            $pegawai = $this->pegawaiModel
                ->where('kode_pegawai', $kodePegawai)
                ->where('is_active', 1)
                ->first();

            if (! is_object($pegawai)) {
                return $this->hasilGagal([
                    'scan_value' => 'Pegawai tidak ditemukan atau tidak aktif'
                ], 'Pegawai tidak ditemukan atau tidak aktif');
            }

            $jadwal = $this->jadwalKerjaModel
                ->getJadwalByPegawaiDanTanggal((int) $pegawai->id, $tanggalKerja);

            if (! is_object($jadwal)) {
                return $this->hasilData([
                    'pegawai'         => $pegawai,
                    'jadwal'          => null,
                    'shift'           => null,
                    'status_harian'   => 'tanpa_jadwal',
                    'boleh_presensi'  => false,
                    'tanggal_kerja'   => $tanggalKerja,
                    'window'          => null,
                ], 'Pegawai tidak memiliki jadwal kerja pada tanggal ini');
            }

            $statusHarian = (string) ($jadwal->status_hari ?? '');

            if (in_array($statusHarian, ['libur', 'izin', 'sakit'], true)) {
                return $this->hasilData([
                    'pegawai'         => $pegawai,
                    'jadwal'          => $jadwal,
                    'shift'           => null,
                    'status_harian'   => $statusHarian,
                    'boleh_presensi'  => false,
                    'tanggal_kerja'   => $tanggalKerja,
                    'window'          => null,
                ], 'Pegawai tidak dapat presensi karena status hari: ' . $statusHarian);
            }

            if ($statusHarian !== 'kerja') {
                return $this->hasilGagal([
                    'general' => 'Status hari pada jadwal kerja tidak valid'
                ], 'Status hari pada jadwal kerja tidak valid');
            }

            $shiftId = $this->intAtauNull($jadwal->shift_id ?? null);
            if ($shiftId === null) {
                return $this->hasilGagal([
                    'general' => 'Shift pada jadwal kerja tidak ditemukan'
                ], 'Shift pada jadwal kerja tidak ditemukan');
            }

            $shift = $this->shiftModel->getShiftById($shiftId);
            if (! is_object($shift)) {
                return $this->hasilGagal([
                    'general' => 'Data shift tidak ditemukan'
                ], 'Data shift tidak ditemukan');
            }

            if ((int) ($shift->is_active ?? 0) !== 1) {
                return $this->hasilGagal([
                    'general' => 'Shift tidak aktif'
                ], 'Shift tidak aktif');
            }

            return $this->hasilData([
                'pegawai'         => $pegawai,
                'jadwal'          => $jadwal,
                'shift'           => $shift,
                'status_harian'   => 'kerja',
                'boleh_presensi'  => true,
                'tanggal_kerja'   => $tanggalKerja,
                'window'          => [
                    'jam_masuk'             => (string) $shift->jam_masuk,
                    'batas_mulai_datang'    => (string) $shift->batas_mulai_datang,
                    'batas_akhir_datang'    => (string) $shift->batas_akhir_datang,
                    'jam_pulang'            => (string) $shift->jam_pulang,
                    'batas_mulai_pulang'    => (string) $shift->batas_mulai_pulang,
                    'batas_akhir_pulang'    => (string) $shift->batas_akhir_pulang,
                    'toleransi_telat_menit' => (int) $shift->toleransi_telat_menit,
                ],
            ]);
        });
    }
}
