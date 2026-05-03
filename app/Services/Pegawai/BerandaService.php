<?php

namespace App\Services\Pegawai;

use App\Models\JadwalKerjaModel;
use App\Models\PegawaiModel;
use App\Models\PresensiModel;
use App\Services\BaseService;

class BerandaService extends BaseService
{
    protected PegawaiModel $pegawaiModel;
    protected JadwalKerjaModel $jadwalKerjaModel;
    protected PresensiModel $presensiModel;

    public function __construct()
    {
        parent::__construct();

        $this->pegawaiModel     = new PegawaiModel();
        $this->jadwalKerjaModel = new JadwalKerjaModel();
        $this->presensiModel    = new PresensiModel();
    }

    public function getSummary(): array
    {
        $pegawaiId = $this->intAtauNull(session()->get('pegawai_id'));
        $tanggal   = date('Y-m-d');
        $bulan     = date('Y-m');

        if ($pegawaiId === null) {
            return $this->hasilGagal([], 'Session pegawai tidak valid');
        }

        return $this->hasilData([
            'tanggal'          => $tanggal,
            'pegawai'          => $this->pegawaiModel->getPegawaiById($pegawaiId),
            'jadwal_hari_ini'  => $this->jadwalKerjaModel->getJadwalDetailByPegawaiDanTanggal($pegawaiId, $tanggal),
            'presensi_hari_ini' => $this->presensiModel->getPresensiByPegawaiDanTanggal($pegawaiId, $tanggal),
            'ringkasan_bulan'  => [
                'hadir'        => $this->presensiModel->countByPegawaiBulanDanHasil($pegawaiId, $bulan, 'hadir'),
                'izin'         => $this->presensiModel->countByPegawaiBulanDanHasil($pegawaiId, $bulan, 'izin'),
                'sakit'        => $this->presensiModel->countByPegawaiBulanDanHasil($pegawaiId, $bulan, 'sakit'),
                'libur'        => $this->presensiModel->countByPegawaiBulanDanHasil($pegawaiId, $bulan, 'libur'),
                'cuti'        => $this->presensiModel->countByPegawaiBulanDanHasil($pegawaiId, $bulan, 'cuti'),
                'alpa'         => $this->presensiModel->countByPegawaiBulanDanHasil($pegawaiId, $bulan, 'alpa'),
                'telat'        => $this->presensiModel->countByPegawaiBulanDanStatusDatang($pegawaiId, $bulan, 'telat'),
                'pulang_cepat' => $this->presensiModel->countByPegawaiBulanDanStatusPulang($pegawaiId, $bulan, 'pulang_cepat'),
            ],
        ]);
    }

    public function getRiwayatPresensi(): array
    {
        $pegawaiId = $this->intAtauNull(session()->get('pegawai_id'));

        if ($pegawaiId === null) {
            return [];
        }

        return $this->presensiModel->getRiwayatByPegawai($pegawaiId, 5);
    }
}
