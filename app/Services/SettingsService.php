<?php

namespace App\Services;

use App\Models\SettingsModel;

class SettingsService extends BaseService
{
    protected SettingsModel $pengaturanModel;
    protected ?object $cache = null;

    public function __construct()
    {
        parent::__construct();
        $this->pengaturanModel = new SettingsModel();
    }

    public function ambilData(): ?object
    {
        if ($this->cache === null) {
            $this->cache = $this->pengaturanModel->getSettings();
        }

        return $this->cache;
    }

    public function simpan(array $post, $file): array
    {
        return $this->eksekusi(function () use ($post, $file) {
            $rules = [
                'nama_usaha' => [
                    'label'  => 'Nama Usaha',
                    'rules'  => 'required|regex_match[/^[a-zA-Z0-9,\s]+$/]',
                    'errors' => [
                        'required'    => '{field} harus diisi',
                        'regex_match' => '{field} hanya boleh diisi huruf, angka, koma, dan spasi',
                    ]
                ],
                'alamat' => [
                    'label'  => 'Alamat',
                    'rules'  => 'required|regex_match[/^[a-zA-Z0-9,.\s]+$/]',
                    'errors' => [
                        'required'    => '{field} harus diisi',
                        'regex_match' => '{field} hanya boleh diisi huruf, angka, titik, koma, dan spasi',
                    ]
                ],
                'telepon' => [
                    'label'  => 'No. HP / Telepon',
                    'rules'  => 'required|regex_match[/^\+?[0-9]+$/]',
                    'errors' => [
                        'required'    => '{field} harus diisi',
                        'regex_match' => '{field} hanya boleh angka dan boleh diawali +',
                    ]
                ],
                'email' => [
                    'label'  => 'Email',
                    'rules'  => 'required|valid_email',
                    'errors' => [
                        'required'    => '{field} harus diisi',
                        'valid_email' => 'Masukkan email yang benar',
                    ]
                ]
            ];

            if ($file && $file->getError() != 4) {
                $rules['logo'] = [
                    'label'  => 'Logo',
                    'rules'  => 'is_image[logo]|mime_in[logo,image/png,image/jpeg]|max_size[logo,2048]|ext_in[logo,png,jpg,jpeg]',
                    'errors' => [
                        'is_image' => '{field} harus berupa gambar',
                        'mime_in'  => 'Format gambar harus png atau jpg',
                        'ext_in'   => 'Format gambar harus png atau jpg',
                        'max_size' => 'Ukuran maksimal 2MB'
                    ]
                ];
            }

            $validasi = $this->validasi($rules, $post);

            if (! $validasi['sukses']) {
                return $validasi;
            }

            $data = [
                'nama_usaha' => empty(hapus_spasi_lebih($post['nama_usaha'] ?? ''))
                    ? 'Nama Usaha'
                    : huruf_besar($this->stringWajib($post['nama_usaha'] ?? '')),
                'alamat'   => $this->stringAtauNull($post['alamat'] ?? ''),
                'telepon'  => $this->stringAtauNull($post['telepon'] ?? ''),
                'email'    => $this->stringAtauNull($post['email'] ?? ''),
            ];

            if ($file && $file->getError() != 4) {
                $namaLogo = 'logo.' . strtolower($file->getExtension());
                $file->move(FCPATH . 'assets/media/photos', $namaLogo, true);
                $data['logo'] = $namaLogo;
            }

            $settings = $this->pengaturanModel->getSettings();

            $simpan = $settings
                ? $this->pengaturanModel->update($settings->id, $data)
                : $this->pengaturanModel->insert($data);

            if (! $simpan) {
                return $this->hasilGagal([
                    'general' => 'Data pengaturan gagal disimpan'
                ]);
            }

            $this->cache = $this->pengaturanModel->getSettings();

            if ($this->cache) {
                session()->set([
                    'pengaturan_logo'       => $this->cache->logo ?? null,
                    'pengaturan_nama_usaha' => $this->cache->nama_usaha ?? null,
                    'pengaturan_alamat'     => $this->cache->alamat ?? null,
                ]);
            }

            $this->catatAudit(
                $settings ? 'update' : 'create',
                'settings',
                $this->cache->id ?? ($settings->id ?? null),
                'Menyimpan pengaturan usaha: ' . ($this->cache->nama_usaha ?? $data['nama_usaha'])
                    . ', email: ' . ($this->cache->email ?? $data['email'])
                    . ', telepon: ' . ($this->cache->telepon ?? $data['telepon'])
            );

            return $this->hasilSukses('Data Pengaturan Berhasil Disimpan');
        });
    }

    public function getLogoPath(): string
    {
        $settings = $this->ambilData();

        $logoFile = $settings->logo ?? 'default.png';
        $path = FCPATH . 'assets/media/photos/' . $logoFile;

        if (! empty($logoFile) && is_file($path)) {
            return $path;
        }

        return FCPATH . 'assets/media/photos/default.png';
    }

    public function getLogoFileName(): string
    {
        $settings = $this->ambilData();

        $logoFile = $settings->logo ?? 'default.png';
        $path = FCPATH . 'assets/media/photos/' . $logoFile;

        if (! empty($logoFile) && is_file($path)) {
            return $logoFile;
        }

        return 'default.png';
    }

    public function getNamaUsaha(): string
    {
        $settings = $this->ambilData();
        return $settings->nama_usaha ?? 'Nama Usaha';
    }
}
