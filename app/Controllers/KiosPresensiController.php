<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\Admin\PresensiService;

class KiosPresensiController extends BaseController
{
    protected PresensiService $presensiService;

    public function __construct()
    {
        $this->presensiService = new PresensiService();
    }

    /**
     * Halaman utama kios
     */
    public function index()
    {
        return view('pages/kios_presensi/index', [
            'pageTitle'   => 'Presensi BUPDA Batununggul',
            'namaUsaha'   => 'BUPDA BATUNUNGGUL',
            'subTitleApp' => 'Sistem Presensi Pegawai',
        ]);
    }

    /**
     * Preview hasil scan QR sebelum selfie / submit
     * Semua validasi bisnis utama dilakukan di sini.
     */
    public function preview()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON([
                'sukses' => false,
                'pesan'  => 'Metode request tidak valid',
                'errors' => [],
            ]);
        }

        $scanValue = trim((string) $this->request->getPost('scan_value'));
        $mode      = trim((string) $this->request->getPost('mode'));

        $hasil = $this->presensiService->previewScan($scanValue, $mode, [
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()?->getAgentString(),
        ]);

        return $this->response->setJSON($hasil);
    }

    /**
     * Submit presensi datang / pulang
     */
    public function submit()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON([
                'sukses' => false,
                'pesan'  => 'Metode request tidak valid',
                'errors' => [],
            ]);
        }

        $scanValue   = trim((string) $this->request->getPost('scan_value'));
        $mode        = trim((string) $this->request->getPost('mode'));
        $selfieBase64 = (string) $this->request->getPost('selfie_base64');

        $selfiePath = null;

        if ($selfieBase64 !== '') {
            $simpanSelfie = $this->simpanSelfieBase64($selfieBase64, $scanValue);

            if (! $simpanSelfie['sukses']) {
                return $this->response->setJSON($simpanSelfie);
            }

            $selfiePath = $simpanSelfie['path'] ?? null;
        }

        $hasil = $this->presensiService->prosesScan($scanValue, $selfiePath, [
            'mode'       => $mode,
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()?->getAgentString(),
        ]);

        return $this->response->setJSON($hasil);
    }

    /**
     * Simpan selfie dari base64
     */
    protected function simpanSelfieBase64(string $base64, string $scanValue): array
    {
        $base64 = trim($base64);

        if ($base64 === '') {
            return [
                'sukses' => false,
                'pesan'  => 'Selfie wajib diambil',
                'errors' => [
                    'selfie' => 'Selfie wajib diambil',
                ],
            ];
        }

        if (! preg_match('/^data:image\/(\w+);base64,/', $base64, $matches)) {
            return [
                'sukses' => false,
                'pesan'  => 'Format selfie tidak valid',
                'errors' => [
                    'selfie' => 'Format selfie tidak valid',
                ],
            ];
        }

        $extension = strtolower($matches[1]);
        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return [
                'sukses' => false,
                'pesan'  => 'Format gambar selfie tidak didukung',
                'errors' => [
                    'selfie' => 'Format gambar selfie tidak didukung',
                ],
            ];
        }

        $rawData = substr($base64, strpos($base64, ',') + 1);
        $binary  = base64_decode($rawData);

        if ($binary === false) {
            return [
                'sukses' => false,
                'pesan'  => 'Selfie gagal diproses',
                'errors' => [
                    'selfie' => 'Selfie gagal diproses',
                ],
            ];
        }

        $folder = FCPATH . 'uploads/selfie_presensi/' . date('Y/m/d') . '/';
        if (! is_dir($folder)) {
            mkdir($folder, 0775, true);
        }

        $filename = date('His')
            . '_'
            . preg_replace('/[^A-Za-z0-9\-]/', '_', $scanValue)
            . '_'
            . uniqid()
            . '.'
            . $extension;

        $fullPath = $folder . $filename;

        if (file_put_contents($fullPath, $binary) === false) {
            return [
                'sukses' => false,
                'pesan'  => 'Selfie gagal disimpan',
                'errors' => [
                    'selfie' => 'Selfie gagal disimpan',
                ],
            ];
        }

        $relativePath = 'uploads/selfie_presensi/' . date('Y/m/d') . '/' . $filename;

        return [
            'sukses' => true,
            'pesan'  => 'Selfie berhasil disimpan',
            'errors' => [],
            'path'   => $relativePath,
        ];
    }
}
