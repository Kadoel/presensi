<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Services\SettingsService;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Logo\Logo;

class PegawaiSeeder extends Seeder
{
    public function run()
    {
        $faker = \Faker\Factory::create('id_ID');
        $settingsService = new SettingsService();

        $tempatLahirList = [
            'Denpasar',
            'Singaraja',
            'Tabanan',
            'Badung',
            'Gianyar',
            'Bangli',
            'Klungkung',
            'Karangasem',
            'Negara'
        ];

        $jabatanIds = array_column(
            $this->db->table('jabatan')->select('id')->get()->getResultArray(),
            'id'
        );

        $this->bersihkanFolderQRCode();

        $data = [];

        for ($i = 1; $i <= 5; $i++) {
            $jk = $faker->randomElement(['L', 'P']);
            $nama = $jk === 'L'
                ? $faker->name('male')
                : $faker->name('female');

            $createdAt = $faker->dateTimeBetween('-90 days', 'now');

            $kodePegawai = 'PGW-' . $createdAt->format('ymdHis') . str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            $qrcodeFile = $this->generateQRCode($kodePegawai, $settingsService);

            $data[] = [
                'kode_pegawai'  => $kodePegawai,
                'nama_pegawai'  => $nama,
                'jenis_kelamin' => $jk,
                'tempat_lahir'  => $faker->randomElement($tempatLahirList),
                'tanggal_lahir' => $faker->dateTimeBetween('-45 years', '-20 years')->format('Y-m-d'),
                'no_hp'         => '08' . $faker->numerify('##########'),
                'alamat'        => $faker->address(),
                'jabatan_id'    => $faker->randomElement($jabatanIds),
                'foto'          => 'default.png',
                'qrcode'        => $qrcodeFile,
                'is_active'     => 1,
                'created_at'    => $createdAt->format('Y-m-d H:i:s'),
                'updated_at'    => $createdAt->format('Y-m-d H:i:s'),
            ];
        }

        $this->db->table('pegawai')->insertBatch($data);
    }

    protected function generateQRCode(string $kodePegawai, SettingsService $settingsService): string
    {
        $qr = new QrCode($kodePegawai);
        $qr->setSize(300);
        $qr->setMargin(10);
        $qr->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh());

        $writer = new PngWriter();

        $logoPath = $settingsService->getLogoPath();

        $logo = null;
        if (! empty($logoPath) && is_file($logoPath)) {
            $logo = new Logo($logoPath, 60, 60);
        }

        $result = $writer->write(
            $qr,
            $logo,
            null,
            ['margin' => 10]
        );

        $folder = FCPATH . 'assets/media/qrcode' . DIRECTORY_SEPARATOR;

        if (! is_dir($folder)) {
            mkdir($folder, 0755, true);
        }

        $fileName = 'qr_' . strtolower($kodePegawai) . '.png';
        $result->saveToFile($folder . $fileName);

        return $fileName;
    }

    protected function bersihkanFolderQRCode(): void
    {
        $folder = FCPATH . 'assets/media/qrcode' . DIRECTORY_SEPARATOR;

        if (! is_dir($folder)) {
            return;
        }

        $files = glob($folder . '*.png');

        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }
}
