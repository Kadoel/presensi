<?php

namespace App\Services\Pegawai;

use App\Models\PegawaiModel;
use App\Services\BaseService;

class ProfilService extends BaseService
{
    protected PegawaiModel $pegawaiModel;

    public function __construct()
    {
        parent::__construct();
        $this->pegawaiModel = new PegawaiModel();
    }

    public function getProfil(): array
    {
        return $this->eksekusi(function () {
            $pegawaiId = $this->intAtauNull(session()->get('pegawai_id'));

            if ($pegawaiId === null) {
                return $this->hasilGagal([], 'Session pegawai tidak ditemukan');
            }

            $pegawai = $this->pegawaiModel->getPegawaiById($pegawaiId);

            if ($pegawai === null) {
                return $this->hasilTidakDitemukan('Data Pegawai Tidak Ditemukan');
            }

            return $this->hasilData([
                'profil' => $pegawai,
            ]);
        });
    }

    public function update(array $post, $file): array
    {
        return $this->eksekusi(function () use ($post, $file) {
            $pegawaiId = $this->intAtauNull(session()->get('pegawai_id'));

            if ($pegawaiId === null) {
                return $this->hasilGagal([], 'Session pegawai tidak ditemukan');
            }

            $pegawai = $this->pegawaiModel->getPegawaiById($pegawaiId);

            if ($pegawai === null) {
                return $this->hasilTidakDitemukan('Data Pegawai Tidak Ditemukan');
            }

            $rules = [
                'no_hp' => [
                    'label'  => 'No HP',
                    'rules'  => 'required|max_length[30]|regex_match[/^\+?[0-9]+$/]',
                    'errors' => [
                        'required'    => '{field} harus diisi',
                        'max_length'  => '{field} maksimal 30 karakter',
                        'regex_match' => '{field} hanya boleh angka dan boleh diawali +',
                    ],
                ],
                'alamat' => [
                    'label'  => 'Alamat',
                    'rules'  => 'required|regex_match[/^[a-zA-Z0-9,.\s]+$/]',
                    'errors' => [
                        'required'    => '{field} harus diisi',
                        'regex_match' => '{field} hanya boleh diisi huruf, angka, titik, koma, dan spasi',
                    ],
                ],
            ];

            if ($file && $file->getError() != 4) {
                $rules['foto'] = [
                    'label'  => 'Foto',
                    'rules'  => 'is_image[foto]|mime_in[foto,image/png,image/jpeg,image/jpg]|max_size[foto,2048]|ext_in[foto,png,jpg,jpeg]',
                    'errors' => [
                        'is_image' => '{field} harus berupa gambar',
                        'mime_in'  => 'Format gambar harus png, jpg, atau jpeg',
                        'ext_in'   => 'Format gambar harus png, jpg, atau jpeg',
                        'max_size' => 'Ukuran maksimal 2MB',
                    ],
                ];
            }

            $validasi = $this->validasi($rules, $post);

            if (! $validasi['sukses']) {
                return $validasi;
            }

            $data = [
                'id'     => $pegawaiId,
                'no_hp'  => $this->stringAtauNull($post['no_hp'] ?? ''),
                'alamat' => $this->stringAtauNull($post['alamat'] ?? ''),
            ];

            if ($file && $file->getError() != 4) {
                $folderFoto = FCPATH . 'assets/media/pegawai' . DIRECTORY_SEPARATOR;

                if (! is_dir($folderFoto)) {
                    mkdir($folderFoto, 0775, true);
                }

                $this->hapusFotoPegawaiLama($pegawai->foto ?? null);

                $namaFoto = $this->generateNamaFoto((string) $pegawai->kode_pegawai, $file->getExtension());
                $file->move($folderFoto, $namaFoto, true);

                $data['foto'] = $namaFoto;
                session()->set('foto', $namaFoto);
            }

            $simpan = $this->pegawaiModel->save($data);

            if (! $simpan) {
                return $this->hasilGagal([
                    'general' => 'Profil gagal diubah',
                ]);
            }

            $this->catatAudit(
                'update',
                'pegawai',
                $pegawaiId,
                'Pegawai mengubah profil sendiri: ' . (string) $pegawai->nama_pegawai . ' dengan kode ' . (string) $pegawai->kode_pegawai
            );

            return $this->hasilSukses('Profil berhasil diperbarui');
        });
    }

    protected function generateNamaFoto(string $kodePegawai, string $ext): string
    {
        return strtolower($kodePegawai) . '.' . strtolower($ext);
    }

    protected function hapusFotoPegawaiLama(?string $namaFoto): void
    {
        if (empty($namaFoto) || $namaFoto === 'default.png') {
            return;
        }

        $path = FCPATH . 'assets/media/pegawai' . DIRECTORY_SEPARATOR . $namaFoto;

        if (is_file($path)) {
            @unlink($path);
        }
    }
}
