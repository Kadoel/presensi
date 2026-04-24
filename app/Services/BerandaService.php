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
            'total_pegawai' => $this->pegawaiModel->countAktif(),
            'hadir_hari_ini' => $this->presensiModel->countHadirByTanggal($tanggal),
            'terlambat_hari_ini' => $this->presensiModel->countTelatByTanggal($tanggal),
            'izin_sakit_hari_ini' => $this->presensiModel->countIzinSakitByTanggal($tanggal),
            'alpa_hari_ini' => $this->presensiModel->countAlpaByTanggal($tanggal),
            'izin_pending' => $this->pengajuanIzinModel->countPending(),
            'tukar_jadwal_pending' => $this->tukarJadwalModel->countPending(),
        ];
    }

    public function getPresensiHariIni(): array
    {
        return $this->presensiModel->getPresensiHariIni(date('Y-m-d'));
    }

    public function getAktivitasTerbaru(): array
    {
        return $this->auditLogModel->getAktivitasTerbaru();
    }
}
