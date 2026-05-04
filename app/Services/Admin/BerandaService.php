<?php

namespace App\Services\Admin;

use App\Models\AuditLogModel;
use App\Models\JadwalKerjaModel;
use App\Models\PegawaiModel;
use App\Models\PengajuanIzinModel;
use App\Models\PresensiModel;
use App\Models\TukarJadwalModel;
use App\Services\BaseService;

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

        // 🔥 FIX DI SINI (pakai jadwal, bukan presensi)
        $rowsBelumSinkron = $this->jadwalKerjaModel
            ->getTanggalBelumSinkronSebelumHariIni($tanggal);

        $tanggalBelumSinkron = array_map(function ($row) {
            return $row->tanggal;
        }, $rowsBelumSinkron);

        return [
            'tanggal'              => $tanggal,
            'tanggal_belum_sinkron' => $tanggalBelumSinkron,

            // KPI
            'total_pegawai'        => $totalPegawai,
            'izin_pending'         => $this->pengajuanIzinModel->countPending(),
            'tukar_jadwal_pending' => $this->tukarJadwalModel->countPending(),

            // Jadwal Hari Ini
            'jadwal_kerja'         => $this->jadwalKerjaModel->countStatusHariIni($tanggal, 'kerja'),
            'jadwal_izin'          => $this->jadwalKerjaModel->countStatusHariIni($tanggal, 'izin'),
            'jadwal_sakit'         => $this->jadwalKerjaModel->countStatusHariIni($tanggal, 'sakit'),
            'jadwal_libur'         => $this->jadwalKerjaModel->countStatusHariIni($tanggal, 'libur'),
            'jadwal_cuti'          => $this->jadwalKerjaModel->countStatusHariIni($tanggal, 'cuti'),
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
            'cuti'                 => $this->presensiModel->countByTanggalDanHasilPresensi($tanggal, 'cuti'),
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

    public function getGrafikMingguan(): array
    {
        $tanggalSelesai = date('Y-m-d');
        $tanggalMulai   = date('Y-m-d', strtotime('-6 days'));

        $jadwalRows   = $this->jadwalKerjaModel->getGrafikJadwalMingguan($tanggalMulai, $tanggalSelesai);
        $presensiRows = $this->presensiModel->getGrafikPresensiMingguan($tanggalMulai, $tanggalSelesai);

        $jadwalMap = [];
        foreach ($jadwalRows as $row) {
            $jadwalMap[$row->tanggal] = (int) $row->total_jadwal;
        }

        $presensiMap = [];
        foreach ($presensiRows as $row) {
            $presensiMap[$row->tanggal] = (int) $row->total_presensi;
        }

        $labels = [];
        $jadwal = [];
        $presensi = [];

        for ($i = 6; $i >= 0; $i--) {
            $tanggal = date('Y-m-d', strtotime("-{$i} days"));

            $labels[]   = date('d/m', strtotime($tanggal));
            $jadwal[]   = $jadwalMap[$tanggal] ?? 0;
            $presensi[] = $presensiMap[$tanggal] ?? 0;
        }

        return [
            'labels'   => $labels,
            'jadwal'   => $jadwal,
            'presensi' => $presensi,
        ];
    }

    public function getGrafikBulanan(): array
    {
        $bulan = date('Y-m');

        $rows = $this->presensiModel->getGrafikHasilPresensiBulanan($bulan);

        $data = [
            'hadir' => 0,
            'izin'  => 0,
            'sakit' => 0,
            'libur' => 0,
            'cuti'  => 0,
            'alpa'  => 0,
        ];

        foreach ($rows as $row) {
            if (array_key_exists($row->hasil_presensi, $data)) {
                $data[$row->hasil_presensi] = (int) $row->total;
            }
        }

        return [
            'bulan'  => $bulan,
            'labels' => ['Hadir', 'Izin', 'Sakit', 'Libur', 'Cuti', 'Alpa'],
            'data'   => [
                $data['hadir'],
                $data['izin'],
                $data['sakit'],
                $data['libur'],
                $data['cuti'],
                $data['alpa'],
            ],
        ];
    }
}
