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

        $this->presensiModel    = new PresensiModel();
        $this->jadwalKerjaModel = new JadwalKerjaModel();
    }

    /**
     * Generate ALPA harian berdasarkan jadwal kerja
     */
    public function generateAlpaHarian(?string $tanggal = null): array
    {
        return $this->transaksi(function () use ($tanggal) {
            $tanggal = $this->stringWajib($tanggal ?: date('Y-m-d'));

            // 1. Ambil semua pegawai yang HARUS kerja hari itu
            $jadwalKerjaList = $this->jadwalKerjaModel
                ->where('tanggal', $tanggal)
                ->where('status_hari', 'kerja')
                ->findAll();

            if (empty($jadwalKerjaList)) {
                return $this->hasilSukses('Tidak ada jadwal kerja pada tanggal ini', [
                    'jumlah_diproses' => 0,
                    'jumlah_alpa'     => 0,
                ]);
            }

            $jumlahDiproses = 0;
            $jumlahAlpa     = 0;

            foreach ($jadwalKerjaList as $jadwal) {
                $pegawaiId = (int) $jadwal->pegawai_id;

                // 2. Cek apakah sudah ada presensi
                $sudahAda = $this->presensiModel
                    ->sudahAdaPresensi($pegawaiId, $tanggal);

                if ($sudahAda) {
                    continue;
                }

                // 3. Insert sebagai ALPA
                $insert = $this->presensiModel->insert([
                    'pegawai_id'         => $pegawaiId,
                    'tanggal'            => $tanggal,
                    'jadwal_kerja_id'    => (int) $jadwal->id,
                    'shift_id'           => $this->intAtauNull($jadwal->shift_id),
                    'jam_datang'         => null,
                    'jam_pulang'         => null,
                    'status_datang'      => 'alpa',
                    'status_pulang'      => null,
                    'menit_telat'        => 0,
                    'menit_pulang_cepat' => 0,
                    'selfie_datang'      => null,
                    'selfie_pulang'      => null,
                    'barcode_datang'     => null,
                    'barcode_pulang'     => null,
                    'ip_address'         => null,
                    'user_agent'         => null,
                    'catatan_admin'      => 'Generate otomatis ALPA',
                    'is_manual'          => 1,
                ]);

                if ($insert) {
                    $jumlahAlpa++;
                }

                $jumlahDiproses++;
            }

            // Audit
            $this->catatAudit(
                'generate_alpa',
                'presensi',
                null,
                'Generate ALPA tanggal ' . $tanggal . ' sebanyak ' . $jumlahAlpa . ' pegawai'
            );

            return $this->hasilSukses('Generate ALPA selesai', [
                'tanggal'           => $tanggal,
                'jumlah_diproses'   => $jumlahDiproses,
                'jumlah_alpa'       => $jumlahAlpa,
            ]);
        });
    }
}
