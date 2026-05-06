<?php

namespace App\Services\Kios;

use App\Services\Kios\GoogleDriveService;
use App\Models\PresensiModel;
use App\Services\BaseService;

use DateTime;

class PresensiService extends BaseService
{
    protected PresensiModel $presensiModel;
    protected JadwalPresensiResolverService $resolverService;
    protected GoogleDriveService $googleDriveService;

    public function __construct()
    {
        parent::__construct();

        $this->presensiModel   = new PresensiModel();
        $this->resolverService = new JadwalPresensiResolverService();
        $this->googleDriveService = new GoogleDriveService();
    }

    public function previewScan(string $scanValue, string $mode, array $meta = []): array
    {
        return $this->eksekusi(function () use ($scanValue, $mode, $meta) {
            $mode = $this->stringWajib($mode);

            if (! in_array($mode, ['datang', 'pulang'], true)) {
                return $this->hasilGagal([
                    'mode' => 'Mode presensi tidak valid',
                ], 'Mode presensi tidak valid');
            }

            $resolved = $this->resolverService->resolveDariHasilScan($scanValue);

            if (! $resolved['sukses']) {
                return $resolved;
            }

            $pegawai       = $resolved['pegawai'] ?? null;
            $jadwal        = $resolved['jadwal'] ?? null;
            $shift         = $resolved['shift'] ?? null;
            $statusHarian  = $resolved['status_harian'] ?? 'tanpa_jadwal';
            $tanggalKerja  = $resolved['tanggal_kerja'] ?? date('Y-m-d');
            $bolehPresensi = (bool) ($resolved['boleh_presensi'] ?? false);

            if (! is_object($pegawai)) {
                return $this->hasilGagal([
                    'general' => 'Data pegawai tidak valid',
                ], 'Data pegawai tidak valid');
            }

            if (! $bolehPresensi) {
                $pesan = match ($statusHarian) {
                    'libur'        => 'Hari ini merupakan hari libur',
                    'izin'         => 'Pegawai sedang izin',
                    'sakit'        => 'Pegawai sedang sakit',
                    'cuti'         => 'Pegawai sedang cuti',
                    'tanpa_jadwal' => 'Pegawai tidak memiliki jadwal kerja hari ini',
                    default        => 'Pegawai tidak dapat melakukan presensi',
                };

                return $this->hasilGagal([
                    'status_harian' => $statusHarian,
                ], $pesan);
            }

            if (! is_object($jadwal) || ! is_object($shift)) {
                return $this->hasilGagal([
                    'general' => 'Data jadwal atau shift tidak valid',
                ], 'Data jadwal atau shift tidak valid');
            }

            $presensi = $this->presensiModel
                ->getPresensiByPegawaiDanTanggal((int) $pegawai->id, $tanggalKerja);

            if ($mode === 'datang') {
                return $this->previewDatang($pegawai, $jadwal, $shift, $presensi, $tanggalKerja);
            }

            return $this->previewPulang($pegawai, $jadwal, $shift, $presensi, $tanggalKerja);
        });
    }

    public function prosesScan(string $scanValue, ?string $selfiePath = null, array $meta = []): array
    {
        return $this->transaksi(function () use ($scanValue, $selfiePath, $meta) {
            $mode = $this->stringWajib($meta['mode'] ?? '');

            if (! in_array($mode, ['datang', 'pulang'], true)) {
                return $this->hasilGagal([
                    'mode' => 'Mode presensi tidak valid',
                ], 'Mode presensi tidak valid');
            }

            $preview = $this->previewScan($scanValue, $mode, $meta);

            if (! $preview['sukses']) {
                return $preview;
            }

            $data         = $preview['data'] ?? [];
            $pegawai      = $data['pegawai'] ?? null;
            $jadwal       = $data['jadwal'] ?? null;
            $shift        = $data['shift'] ?? null;
            $tanggalKerja = $data['tanggal_kerja'] ?? date('Y-m-d');

            if (! is_object($pegawai) || ! is_object($jadwal) || ! is_object($shift)) {
                return $this->hasilGagal([
                    'general' => 'Data preview presensi tidak valid',
                ], 'Data preview presensi tidak valid');
            }

            if ($mode === 'datang') {
                return $this->prosesDatangByMode(
                    $pegawai,
                    $jadwal,
                    $shift,
                    $tanggalKerja,
                    $scanValue,
                    $selfiePath,
                    $meta
                );
            }

            $presensi = $this->presensiModel
                ->getPresensiByPegawaiDanTanggal((int) $pegawai->id, $tanggalKerja);

            if (! is_object($presensi)) {
                return $this->hasilGagal([], 'Data presensi datang tidak ditemukan');
            }

            return $this->prosesPulangByMode(
                $pegawai,
                $presensi,
                $jadwal,
                $shift,
                $scanValue,
                $selfiePath,
                $meta
            );
        });
    }

    protected function previewDatang(
        object $pegawai,
        object $jadwal,
        object $shift,
        ?object $presensi,
        string $tanggalKerja
    ): array {
        if ($presensi !== null && $presensi->jam_datang !== null) {
            return $this->hasilGagal([], 'Pegawai sudah melakukan presensi datang');
        }

        $now = new DateTime();

        $batasMulaiDatang = $this->gabungTanggalJam($tanggalKerja, (string) $shift->batas_mulai_datang);
        $batasAkhirDatang = $this->gabungTanggalJam($tanggalKerja, (string) $shift->batas_akhir_datang);
        $jamMasuk         = $this->gabungTanggalJam($tanggalKerja, (string) $shift->jam_masuk);

        if ($now < $batasMulaiDatang) {
            return $this->hasilGagal([], 'Presensi datang belum dibuka');
        }

        if ($now > $batasAkhirDatang) {
            return $this->hasilGagal([], 'Batas presensi datang sudah lewat');
        }

        $toleransiMenit = (int) ($shift->toleransi_telat_menit ?? 0);
        $batasToleransi = (clone $jamMasuk)->modify('+' . $toleransiMenit . ' minutes');

        $statusPreview = 'tepat_waktu';
        $menitTelat    = 0;

        if ($now > $batasToleransi) {
            $statusPreview = 'telat';

            $selisihDetik = $now->getTimestamp() - $batasToleransi->getTimestamp();
            $menitTelat = max(1, (int) ceil($selisihDetik / 60));
        }

        return $this->hasilSukses('QRCode valid untuk presensi datang', [
            'data' => [
                'mode'            => 'datang',
                'pegawai'         => $pegawai,
                'jadwal'          => $jadwal,
                'shift'           => $shift,
                'status_harian'   => 'kerja',
                'boleh_presensi'  => true,
                'tanggal_kerja'   => $tanggalKerja,
                'preview_status'  => $statusPreview,
                'preview_telat'   => $menitTelat,
            ],
        ]);
    }

    protected function previewPulang(
        object $pegawai,
        object $jadwal,
        object $shift,
        ?object $presensi,
        string $tanggalKerja
    ): array {
        if ($presensi === null || $presensi->jam_datang === null) {
            return $this->hasilGagal([], 'Pegawai belum melakukan presensi datang');
        }

        if ($presensi->jam_pulang !== null) {
            return $this->hasilGagal([], 'Pegawai sudah melakukan presensi pulang');
        }

        $now = new DateTime();

        $batasMulaiPulang = $this->gabungTanggalJam($tanggalKerja, (string) $shift->batas_mulai_pulang);
        $batasAkhirPulang = $this->gabungTanggalJam($tanggalKerja, (string) $shift->batas_akhir_pulang);
        $jamPulang        = $this->gabungTanggalJam($tanggalKerja, (string) $shift->jam_pulang);

        if ($now < $batasMulaiPulang) {
            return $this->hasilGagal([], 'Presensi pulang belum dibuka');
        }

        if ($now > $batasAkhirPulang) {
            return $this->hasilGagal([], 'Batas presensi pulang sudah lewat');
        }

        $statusPreview = 'tepat_waktu';
        $menitPulangCepat = 0;

        // Untuk pulang cepat
        if ($now < $jamPulang) {
            $statusPreview = 'pulang_cepat';

            $selisihDetik = $jamPulang->getTimestamp() - $now->getTimestamp();
            $menitPulangCepat = max(1, (int) ceil($selisihDetik / 60));
        }

        return $this->hasilSukses('QRCode valid untuk presensi pulang', [
            'data' => [
                'mode'                 => 'pulang',
                'pegawai'              => $pegawai,
                'jadwal'               => $jadwal,
                'shift'                => $shift,
                'status_harian'        => 'kerja',
                'boleh_presensi'       => true,
                'tanggal_kerja'        => $tanggalKerja,
                'preview_status'       => $statusPreview,
                'preview_pulang_cepat' => $menitPulangCepat,
            ],
        ]);
    }

    protected function prosesDatangByMode(
        object $pegawai,
        object $jadwal,
        object $shift,
        string $tanggalKerja,
        string $scanValue,
        ?string $selfiePath,
        array $meta = []
    ): array {
        $now = new DateTime();

        $jamMasuk = $this->gabungTanggalJam($tanggalKerja, (string) $shift->jam_masuk);
        $toleransiMenit = (int) ($shift->toleransi_telat_menit ?? 0);
        $batasToleransi = (clone $jamMasuk)->modify('+' . $toleransiMenit . ' minutes');

        $statusDatang = 'tepat_waktu';
        $menitTelat   = 0;

        // Untuk telat datang
        if ($now > $batasToleransi) {
            $statusDatang = 'telat';

            $selisihDetik = $now->getTimestamp() - $batasToleransi->getTimestamp();
            $menitTelat = max(1, (int) ceil($selisihDetik / 60));
        }

        $driveId  = null;
        $driveUrl = null;
        $uploadStatus = 'pending';
        $uploadError = null;

        if (! empty($selfiePath)) {
            try {
                $fullPath = FCPATH . $selfiePath;

                if (is_file($fullPath)) {
                    $folderId = $this->googleDriveService->getOrCreateNestedFolder([
                        date('Y'),
                        date('m'),
                        date('d'),
                    ]);

                    $upload = $this->googleDriveService->uploadFile(
                        $fullPath,
                        'selfie-datang-' . $pegawai->kode_pegawai . '-' . date('YmdHis') . '.jpg',
                        mime_content_type($fullPath) ?: 'image/jpeg',
                        $folderId
                    );

                    $driveId  = $upload->id ?? null;
                    $driveUrl = $upload->web_view_link ?? null;

                    if (! empty($driveId)) {
                        $uploadStatus = 'success';
                        $this->hapusFileLokalJikaAda($selfiePath);
                    }
                }
            } catch (\Throwable $e) {
                $uploadStatus = 'failed';
                $uploadError = $e->getMessage();
                log_message('error', 'Upload selfie datang Google Drive gagal: ' . $e->getMessage());
            }
        }

        $insert = $this->presensiModel->simpanDatangScan(
            (int) $pegawai->id,
            $tanggalKerja,
            (int) $jadwal->id,
            $this->intAtauNull($jadwal->shift_id),
            $now->format('Y-m-d H:i:s'),
            $statusDatang,
            $menitTelat,
            $this->stringAtauNull($selfiePath),
            $this->stringWajib($scanValue),
            $this->stringAtauNull($meta['ip_address'] ?? service('request')->getIPAddress()),
            $this->stringAtauNull($meta['user_agent'] ?? service('request')->getUserAgent()?->getAgentString()),
            $driveId,
            $driveUrl,
            $uploadStatus,
            $uploadError
        );

        if (! $insert) {
            return $this->hasilGagal([], 'Presensi datang gagal disimpan');
        }

        $presensiId = (int) $this->presensiModel->getInsertID();

        $this->catatAudit(
            'scan datang',
            'presensi',
            $presensiId,
            'Presensi datang pegawai ID ' . (int) $pegawai->id
                . ' pada tanggal ' . $tanggalKerja
                . ' dengan status ' . $statusDatang
        );

        return $this->hasilSukses('Presensi datang berhasil', [
            'tipe'        => 'datang',
            'pegawai'     => $pegawai,
            'tanggal'     => $tanggalKerja,
            'status'      => $statusDatang,
            'menit_telat' => $menitTelat,
            'jam_scan'    => $now->format('Y-m-d H:i:s'),
        ]);
    }

    protected function prosesPulangByMode(
        object $pegawai,
        object $presensi,
        object $jadwal,
        object $shift,
        string $scanValue,
        ?string $selfiePath,
        array $meta = []
    ): array {
        $now = new DateTime();
        $tanggalKerja = (string) $presensi->tanggal;

        $jamPulang = $this->gabungTanggalJam($tanggalKerja, (string) $shift->jam_pulang);

        $statusPulang = 'tepat_waktu';
        $menitPulangCepat = 0;

        // Untuk pulang cepat
        if ($now < $jamPulang) {
            $statusPulang = 'pulang_cepat';

            $selisihDetik = $jamPulang->getTimestamp() - $now->getTimestamp();
            $menitPulangCepat = max(1, (int) ceil($selisihDetik / 60));
        }

        $driveId  = null;
        $driveUrl = null;
        $uploadStatus = 'pending';
        $uploadError = null;

        if (! empty($selfiePath)) {
            try {
                $fullPath = FCPATH . $selfiePath;

                if (is_file($fullPath)) {

                    // 📁 folder otomatis: YYYY/MM/DD
                    $folderId = $this->googleDriveService->getOrCreateNestedFolder([
                        date('Y'),
                        date('m'),
                        date('d'),
                    ]);

                    $upload = $this->googleDriveService->uploadFile(
                        $fullPath,
                        'selfie-pulang-' . $pegawai->kode_pegawai . '-' . date('YmdHis') . '.jpg',
                        mime_content_type($fullPath) ?: 'image/jpeg',
                        $folderId
                    );

                    $driveId  = $upload->id ?? null;
                    $driveUrl = $upload->web_view_link ?? null;

                    // optional: hapus file lokal kalau upload sukses
                    // unlink($fullPath);
                    if (! empty($driveId)) {
                        $uploadStatus = 'success';
                        $this->hapusFileLokalJikaAda($selfiePath);
                    }
                }
            } catch (\Throwable $e) {
                $uploadStatus = 'failed';
                $uploadError = $e->getMessage();
                log_message('error', 'Upload selfie pulang Google Drive gagal: ' . $e->getMessage());
            }
        }

        $update = $this->presensiModel->simpanPulangScan(
            (int) $presensi->id,
            $now->format('Y-m-d H:i:s'),
            $statusPulang,
            $menitPulangCepat,
            $this->stringAtauNull($selfiePath),
            $this->stringWajib($scanValue),
            $this->stringAtauNull($meta['ip_address'] ?? service('request')->getIPAddress()),
            $this->stringAtauNull($meta['user_agent'] ?? service('request')->getUserAgent()?->getAgentString()),
            $driveId,
            $driveUrl,
            $uploadStatus,
            $uploadError
        );

        if (! $update) {
            return $this->hasilGagal([], 'Presensi pulang gagal disimpan');
        }

        $this->catatAudit(
            'scan pulang',
            'presensi',
            (int) $presensi->id,
            'Presensi pulang pegawai ID ' . (int) $pegawai->id
                . ' pada tanggal ' . $tanggalKerja
                . ' dengan status ' . $statusPulang
        );

        return $this->hasilSukses('Presensi pulang berhasil', [
            'tipe'               => 'pulang',
            'pegawai'            => $pegawai,
            'tanggal'            => $tanggalKerja,
            'status'             => $statusPulang,
            'menit_pulang_cepat' => $menitPulangCepat,
            'jam_scan'           => $now->format('Y-m-d H:i:s'),
        ]);
    }

    protected function gabungTanggalJam(string $tanggal, string $jam): DateTime
    {
        return new DateTime($tanggal . ' ' . $jam);
    }

    protected function hapusFileLokalJikaAda(?string $relativePath): void
    {
        if (empty($relativePath)) {
            return;
        }

        $fullPath = FCPATH . ltrim($relativePath, '/');

        if (is_file($fullPath)) {
            unlink($fullPath);
        }
    }
}
