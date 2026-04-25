<?php

namespace App\Services;

use App\Models\PegawaiModel;
use App\Models\PresensiModel;
use App\Models\PengajuanIzinModel;
use App\Models\TukarJadwalModel;
use App\Models\AuditLogModel;

class BerandaService extends BaseService
{
    protected PegawaiModel $pegawaiModel;
    protected PresensiModel $presensiModel;
    protected PengajuanIzinModel $pengajuanIzinModel;
    protected TukarJadwalModel $tukarJadwalModel;
    protected AuditLogModel $auditLogModel;

    public function __construct()
    {
        parent::__construct();

        $this->pegawaiModel = new PegawaiModel();
        $this->presensiModel = new PresensiModel();
        $this->pengajuanIzinModel = new PengajuanIzinModel();
        $this->tukarJadwalModel = new TukarJadwalModel();
        $this->auditLogModel = new AuditLogModel();
    }

    public function getSummary(): array
    {
        $tanggal = date('Y-m-d');

        return [
            'total_pegawai'        => $this->pegawaiModel->countAktif(),

            'hadir_hari_ini'       => $this->presensiModel->countByTanggalDanHasilPresensi($tanggal, 'hadir'),
            'alpa_hari_ini'        => $this->presensiModel->countByTanggalDanHasilPresensi($tanggal, 'alpa'),
            'izin_hari_ini'        => $this->presensiModel->countByTanggalDanHasilPresensi($tanggal, 'izin'),
            'sakit_hari_ini'       => $this->presensiModel->countByTanggalDanHasilPresensi($tanggal, 'sakit'),
            'libur_hari_ini'       => $this->presensiModel->countByTanggalDanHasilPresensi($tanggal, 'libur'),

            'telat_hari_ini'       => $this->presensiModel->countByTanggalDanStatusDatang($tanggal, 'telat'),
            'pulang_cepat_hari_ini' => $this->presensiModel->countByTanggalDanStatusPulang($tanggal, 'pulang_cepat'),
            'belum_sinkron'        => $this->presensiModel->countByTanggalDanHasilPresensiNull($tanggal),

            'izin_pending'         => $this->pengajuanIzinModel->countPending(),
            'tukar_jadwal_pending' => $this->tukarJadwalModel->countPending(),
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
