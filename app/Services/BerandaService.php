<?php

namespace App\Services;

use App\Models\AuditLogModel;
use App\Models\JadwalKerjaModel;
use App\Models\PegawaiModel;
use App\Models\PengajuanIzinModel;
use App\Models\PresensiModel;
use App\Models\TukarJadwalModel;

class BerandaService extends BaseService
{
    protected PegawaiModel $pegawaiModel;
    protected JadwalKerjaModel $jadwalKerjaModel;
    protected PresensiModel $presensiModel;
    protected PengajuanIzinModel $pengajuanIzinModel;
    protected TukarJadwalModel $tukarJadwalModel;
    protected AuditLogModel $auditLogModel;

    public function __construct()
    {
        parent::__construct();

        $this->pegawaiModel       = new PegawaiModel();
        $this->jadwalKerjaModel   = new JadwalKerjaModel();
        $this->presensiModel      = new PresensiModel();
        $this->pengajuanIzinModel = new PengajuanIzinModel();
        $this->tukarJadwalModel   = new TukarJadwalModel();
        $this->auditLogModel      = new AuditLogModel();
    }

    public function getSummary(): array
    {
        $tanggal = date('Y-m-d');

        $totalPegawai = (int) $this->pegawaiModel->countAktif();

        $totalJadwal = (int) $this->jadwalKerjaModel->countJadwalHariIni($tanggal);

        $totalPresensi = (int) $this->presensiModel->countByTanggal($tanggal);

        $belumSinkron = (int) $this->presensiModel->countByTanggalDanHasilPresensiNull($tanggal)
            + max($totalJadwal - $totalPresensi, 0);

        $sudahSinkron = max($totalJadwal - $belumSinkron, 0);

        return [
            'tanggal'              => $tanggal,

            // KPI
            'total_pegawai'        => $totalPegawai,
            'izin_pending'         => $this->pengajuanIzinModel->countPending(),
            'tukar_jadwal_pending' => $this->tukarJadwalModel->countPending(),

            // Jadwal Hari Ini
            'jadwal_kerja'         => $this->jadwalKerjaModel->countStatusHariIni($tanggal, 'kerja'),
            'jadwal_izin'          => $this->jadwalKerjaModel->countStatusHariIni($tanggal, 'izin'),
            'jadwal_sakit'         => $this->jadwalKerjaModel->countStatusHariIni($tanggal, 'sakit'),
            'jadwal_libur'         => $this->jadwalKerjaModel->countStatusHariIni($tanggal, 'libur'),
            'total_jadwal'         => $totalJadwal,

            // Presensi Hari Ini
            'total_presensi'       => $totalPresensi,
            'tepat_datang'         => $this->presensiModel->countByTanggalDanStatusDatang($tanggal, 'tepat_waktu'),
            'telat_datang'         => $this->presensiModel->countByTanggalDanStatusDatang($tanggal, 'telat'),
            'tepat_pulang'         => $this->presensiModel->countByTanggalDanStatusPulang($tanggal, 'tepat_waktu'),
            'pulang_cepat'         => $this->presensiModel->countByTanggalDanStatusPulang($tanggal, 'pulang_cepat'),

            // Sinkron Hari Ini
            'belum_sinkron'        => $belumSinkron,
            'sudah_sinkron'        => $sudahSinkron,
            'hadir'                => $this->presensiModel->countByTanggalDanHasilPresensi($tanggal, 'hadir'),
            'izin'                 => $this->presensiModel->countByTanggalDanHasilPresensi($tanggal, 'izin'),
            'sakit'                => $this->presensiModel->countByTanggalDanHasilPresensi($tanggal, 'sakit'),
            'libur'                => $this->presensiModel->countByTanggalDanHasilPresensi($tanggal, 'libur'),
            'alpa'                 => $this->presensiModel->countByTanggalDanHasilPresensi($tanggal, 'alpa'),

            // Progress
            'progress_presensi'    => $totalJadwal > 0 ? round(($totalPresensi / $totalJadwal) * 100) : 0,
            'progress_sinkron'     => $totalJadwal > 0 ? round(($sudahSinkron / $totalJadwal) * 100) : 0,
        ];
    }

    public function getPresensiHariIni(): array
    {
        return $this->presensiModel->getPresensiHariIni(date('Y-m-d'), 10);
    }

    public function getAktivitasTerbaru(): array
    {
        return $this->auditLogModel->getAktivitasTerbaru(10);
    }
}
