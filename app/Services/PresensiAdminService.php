<?php

namespace App\Services;

use App\Models\JadwalKerjaModel;
use App\Models\PresensiModel;

class PresensiAdminService extends BaseService
{
    protected PresensiModel $presensiModel;
    protected JadwalKerjaModel $jadwalKerjaModel;

    public function __construct()
    {
        parent::__construct();
        $this->presensiModel = new PresensiModel();
        $this->jadwalKerjaModel = new JadwalKerjaModel();
    }

    public function dataTabel(?string $tanggal = null)
    {
        return $this->presensiModel->dataTabel($tanggal);
    }

    public function detail(int $id): array
    {
        return $this->eksekusi(function () use ($id) {
            $presensi = $this->presensiModel->getDetailAdminById($id);

            if ($presensi === null) {
                return $this->hasilTidakDitemukan('Data presensi tidak ditemukan');
            }

            return $this->hasilData([
                'presensi' => $presensi,
            ]);
        });
    }

    public function ringkasanHarian(?string $tanggal = null): array
    {
        return $this->eksekusi(function () use ($tanggal) {
            $tanggal = $this->stringWajib($tanggal ?: date('Y-m-d'));

            $totalPresensi = $this->presensiModel->countByTanggal($tanggal);

            $totalJadwal = (int) $this->jadwalKerjaModel
                ->where('tanggal', $tanggal)
                ->countAllResults();

            $belumPresensi = max($totalJadwal - $totalPresensi, 0);

            return $this->hasilData([
                'ringkasan' => [
                    'tanggal'         => $tanggal,

                    'total_jadwal'    => $totalJadwal,
                    'total_presensi'  => $totalPresensi,
                    'belum_presensi'  => $belumPresensi,

                    'hadir'           => $this->presensiModel->countByTanggalDanStatusDatang($tanggal, 'hadir'),
                    'telat'           => $this->presensiModel->countByTanggalDanStatusDatang($tanggal, 'telat'),
                    'alpa'            => $this->presensiModel->countByTanggalDanStatusDatang($tanggal, 'alpa'),
                    'izin'            => $this->presensiModel->countByTanggalDanStatusDatang($tanggal, 'izin'),
                    'sakit'           => $this->presensiModel->countByTanggalDanStatusDatang($tanggal, 'sakit'),
                    'libur'           => $this->presensiModel->countByTanggalDanStatusDatang($tanggal, 'libur'),

                    'belum_pulang'    => $this->presensiModel->countByTanggalDanStatusPulang($tanggal, 'belum_pulang'),
                    'pulang'          => $this->presensiModel->countByTanggalDanStatusPulang($tanggal, 'pulang'),
                    'pulang_cepat'    => $this->presensiModel->countByTanggalDanStatusPulang($tanggal, 'pulang_cepat'),
                ],
            ]);
        });
    }

    public function presensiTerbaru(?string $tanggal = null, int $limit = 10): array
    {
        return $this->eksekusi(function () use ($tanggal, $limit) {
            $tanggal = $this->stringWajib($tanggal ?: date('Y-m-d'));

            return $this->hasilData([
                'items' => $this->presensiModel->getPresensiTerbaru($tanggal, $limit),
            ]);
        });
    }

    public function sinkronPresensiHarian(?string $tanggal = null): array
    {
        return $this->transaksi(function () use ($tanggal) {
            $tanggal = $this->stringWajib($tanggal ?: date('Y-m-d'));

            $jadwalList = $this->jadwalKerjaModel
                ->where('tanggal', $tanggal)
                ->findAll();

            if (empty($jadwalList)) {
                return $this->hasilSukses('Tidak ada jadwal pada tanggal ini', [
                    'tanggal'          => $tanggal,
                    'jumlah_diproses'  => 0,
                    'jumlah_dilewati'  => 0,
                    'jumlah_alpa'      => 0,
                    'jumlah_izin'      => 0,
                    'jumlah_sakit'     => 0,
                    'jumlah_libur'     => 0,
                ]);
            }

            $jumlahDiproses = 0;
            $jumlahDilewati = 0;
            $jumlahAlpa = 0;
            $jumlahIzin = 0;
            $jumlahSakit = 0;
            $jumlahLibur = 0;

            foreach ($jadwalList as $jadwal) {
                $pegawaiId = (int) ($jadwal->pegawai_id ?? 0);

                if ($pegawaiId <= 0) {
                    $jumlahDilewati++;
                    continue;
                }

                if ($this->presensiModel->sudahAdaPresensi($pegawaiId, $tanggal)) {
                    $jumlahDilewati++;
                    continue;
                }

                $statusHari = $this->stringWajib($jadwal->status_hari ?? '');
                $statusDatang = $this->mapStatusDatangDariJadwal($statusHari);

                if ($statusDatang === null) {
                    $jumlahDilewati++;
                    continue;
                }

                $insert = $this->presensiModel->insert([
                    'pegawai_id'          => $pegawaiId,
                    'tanggal'             => $tanggal,
                    'jadwal_kerja_id'     => (int) ($jadwal->id ?? 0),
                    'shift_id'            => $this->intAtauNull($jadwal->shift_id ?? null),
                    'jam_datang'          => null,
                    'jam_pulang'          => null,
                    'status_datang'       => $statusDatang,
                    'status_pulang'       => $statusDatang === 'alpa' ? null : null,
                    'menit_telat'         => 0,
                    'menit_pulang_cepat'  => 0,
                    'selfie_datang'       => null,
                    'selfie_pulang'       => null,
                    'barcode_datang'      => null,
                    'barcode_pulang'      => null,
                    'ip_address'          => null,
                    'user_agent'          => null,
                    'catatan_admin'       => 'Sinkron otomatis dari jadwal kerja',
                    'is_manual'           => 1,
                ]);

                if (! $insert) {
                    $jumlahDilewati++;
                    continue;
                }

                $jumlahDiproses++;

                match ($statusDatang) {
                    'alpa'  => $jumlahAlpa++,
                    'izin'  => $jumlahIzin++,
                    'sakit' => $jumlahSakit++,
                    'libur' => $jumlahLibur++,
                    default => null,
                };
            }

            $this->catatAudit(
                'sinkron_presensi',
                'presensi',
                null,
                'Sinkron presensi tanggal ' . $tanggal .
                    ' | alpa: ' . $jumlahAlpa .
                    ', izin: ' . $jumlahIzin .
                    ', sakit: ' . $jumlahSakit .
                    ', libur: ' . $jumlahLibur .
                    ', dilewati: ' . $jumlahDilewati
            );

            return $this->hasilSukses('Sinkron presensi selesai', [
                'tanggal'          => $tanggal,
                'jumlah_diproses'  => $jumlahDiproses,
                'jumlah_dilewati'  => $jumlahDilewati,
                'jumlah_alpa'      => $jumlahAlpa,
                'jumlah_izin'      => $jumlahIzin,
                'jumlah_sakit'     => $jumlahSakit,
                'jumlah_libur'     => $jumlahLibur,
            ]);
        });
    }

    public function generateAlpaHarian(?string $tanggal = null): array
    {
        return $this->sinkronPresensiHarian($tanggal);
    }

    protected function mapStatusDatangDariJadwal(string $statusHari): ?string
    {
        return match ($statusHari) {
            'kerja' => 'alpa',
            'izin'  => 'izin',
            'sakit' => 'sakit',
            'libur' => 'libur',
            default => null,
        };
    }

    public function badgeStatusDatang(?string $status): string
    {
        return match ($status) {
            'hadir' => '<span class="badge bg-success">Hadir</span>',
            'telat' => '<span class="badge bg-warning">Telat</span>',
            'alpa'  => '<span class="badge bg-danger">Alpa</span>',
            'izin'  => '<span class="badge bg-info">Izin</span>',
            'sakit' => '<span class="badge bg-primary">Sakit</span>',
            'libur' => '<span class="badge bg-secondary">Libur</span>',
            default => '<span class="badge bg-secondary">-</span>',
        };
    }

    public function badgeStatusPulang(?string $status): string
    {
        return match ($status) {
            'belum_pulang' => '<span class="badge bg-secondary">Belum Pulang</span>',
            'pulang'       => '<span class="badge bg-success">Pulang</span>',
            'pulang_cepat' => '<span class="badge bg-warning">Pulang Cepat</span>',
            default        => '<span class="badge bg-secondary">-</span>',
        };
    }
}
