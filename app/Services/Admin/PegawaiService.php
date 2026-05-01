<?php

namespace App\Services\Admin;

use App\Models\JabatanModel;
use App\Models\JadwalKerjaModel;
use App\Models\PegawaiModel;
use App\Models\PresensiModel;
use App\Services\BaseService;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\HTTP\Files\UploadedFile;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Logo\Logo;

class PegawaiService extends BaseService
{
    protected PegawaiModel $pegawaiModel;
    protected JabatanModel $jabatanModel;
    protected SettingsService $settingsService;
    protected PresensiModel $presensiModel;
    protected JadwalKerjaModel $jadwalKerjaModel;

    public function __construct()
    {
        parent::__construct();
        $this->pegawaiModel = new PegawaiModel();
        $this->jabatanModel = new JabatanModel();
        $this->settingsService = new SettingsService();
        $this->presensiModel = new PresensiModel();
        $this->jadwalKerjaModel = new JadwalKerjaModel();
    }

    public function dataJabatanSelect(): array
    {
        return $this->jabatanModel->getJabatanSelect();
    }

    public function dataTabel(): BaseBuilder
    {
        return $this->pegawaiModel->selectData();
    }

    public function simpan(array $post, ?UploadedFile $file): array
    {
        return $this->eksekusi(function () use ($post, $file) {
            $maxTanggal = date('Y-m-d');
            $rules = [
                'nama_pegawai' => [
                    'label'  => 'Nama Pegawai',
                    'rules'  => 'required|regex_match[/^[a-zA-Z\s]+$/]|max_length[150]',
                    'errors' => [
                        'required'    => '{field} harus diisi',
                        'regex_match' => '{field} hanya boleh diisi huruf, dan spasi',
                        'max_length'  => '{field} maksimal 150 karakter',
                    ]
                ],
                'jenis_kelamin' => [
                    'label'  => 'Jenis Kelamin',
                    'rules'  => 'required|in_list[L,P]',
                    'errors' => [
                        'required' => '{field} harus diisi',
                        'in_list'  => '{field} tidak valid',
                    ]
                ],
                'tempat_lahir' => [
                    'label'  => 'Tempat Lahir',
                    'rules'  => 'required|regex_match[/^[a-zA-Z0-9,.\s]+$/]|max_length[100]',
                    'errors' => [
                        'required'    => '{field} harus diisi',
                        'regex_match' => '{field} hanya boleh diisi huruf, angka, titik, koma, dan spasi',
                        'max_length'  => '{field} maksimal 100 karakter',
                    ]
                ],
                'tanggal_lahir' => [
                    'label'  => 'Tanggal Lahir',
                    'rules' => [
                        'required',
                        'valid_date[Y-m-d]',
                        static function ($value, array $data, ?string &$error) use ($maxTanggal): bool {
                            if ($value > $maxTanggal) {
                                $error = 'Tanggal maksimal harus hari ini';
                                return false;
                            }

                            return true;
                        }
                    ],
                    'errors' => [
                        'required'   => '{field} harus diisi',
                        'valid_date' => '{field} harus berformat YYYY-MM-DD',
                    ]
                ],
                'no_hp' => [
                    'label'  => 'No HP',
                    'rules'  => 'required|max_length[30]|regex_match[/^\+?[0-9]+$/]',
                    'errors' => [
                        'required'    => '{field} harus diisi',
                        'max_length'  => '{field} maksimal 30 karakter',
                        'regex_match' => '{field} hanya boleh angka dan boleh diawali +',
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
                'jabatan_id' => [
                    'label'  => 'Jabatan',
                    'rules'  => 'required|integer',
                    'errors' => [
                        'required' => '{field} harus diisi',
                        'integer'  => '{field} tidak valid',
                    ]
                ],
                'is_active' => [
                    'label'  => 'Status Aktif',
                    'rules'  => 'required|in_list[0,1]',
                    'errors' => [
                        'required' => '{field} harus diisi',
                        'in_list'  => '{field} tidak valid',
                    ]
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
                        'max_size' => 'Ukuran maksimal 2MB'
                    ]
                ];
            }

            $validasi = $this->validasi($rules, $post);

            if (! $validasi['sukses']) {
                return $validasi;
            }

            $kodePegawai = $this->generateKodePegawai();
            $qrcode = $this->generateQRCode($kodePegawai);

            $data = [
                'kode_pegawai'  => $kodePegawai,
                'nama_pegawai'  => $this->stringWajib($post['nama_pegawai'] ?? ''),
                'jenis_kelamin' => $this->stringWajib($post['jenis_kelamin'] ?? ''),
                'tempat_lahir'  => $this->stringAtauNull($post['tempat_lahir'] ?? ''),
                'tanggal_lahir' => $this->stringAtauNull($post['tanggal_lahir'] ?? ''),
                'no_hp'         => $this->stringAtauNull($post['no_hp'] ?? ''),
                'alamat'        => $this->stringAtauNull($post['alamat'] ?? ''),
                'jabatan_id'    => $this->intAtauNull($post['jabatan_id'] ?? null),
                'qrcode'        => $qrcode,
                'is_active'     => $this->intVal($post['is_active'] ?? 1, 1),
            ];

            if ($file && $file->getError() != 4) {
                $folderFoto = FCPATH . 'assets/media/pegawai' . DIRECTORY_SEPARATOR;
                $namaFoto = $this->generateNamaFoto($kodePegawai, $file->getExtension());
                $file->move($folderFoto, $namaFoto, true);
                $data['foto'] = $namaFoto;
            } else {
                $data['foto'] = 'default.png';
            }

            $simpan = $this->pegawaiModel->save($data);

            if (! $simpan) {
                return $this->hasilGagal([
                    'general' => 'Data pegawai gagal disimpan'
                ]);
            }

            $this->catatAudit(
                'create',
                'pegawai',
                (int) $this->pegawaiModel->getInsertID(),
                'Menambahkan data pegawai: ' . $data['nama_pegawai'] . ' dengan kode ' . $kodePegawai
            );

            return $this->hasilSukses('Data Pegawai Berhasil Ditambahkan');
        });
    }

    public function ubah(int $id, array $post, ?UploadedFile $file): array
    {
        return $this->eksekusi(function () use ($id, $post, $file) {
            $maxTanggal = date('Y-m-d');
            $rules = [
                'edit-nama_pegawai' => [
                    'label'  => 'Nama Pegawai',
                    'rules'  => 'required|regex_match[/^[a-zA-Z\s]+$/]|max_length[150]',
                    'errors' => [
                        'required'    => '{field} harus diisi',
                        'regex_match' => '{field} hanya boleh diisi huruf, dan spasi',
                        'max_length'  => '{field} maksimal 150 karakter',
                    ]
                ],
                'edit-jenis_kelamin' => [
                    'label'  => 'Jenis Kelamin',
                    'rules'  => 'required|in_list[L,P]',
                    'errors' => [
                        'required' => '{field} harus diisi',
                        'in_list'  => '{field} tidak valid',
                    ]
                ],
                'edit-tempat_lahir' => [
                    'label'  => 'Tempat Lahir',
                    'rules'  => 'required|regex_match[/^[a-zA-Z0-9,.\s]+$/]|max_length[100]',
                    'errors' => [
                        'required'    => '{field} harus diisi',
                        'regex_match' => '{field} hanya boleh diisi huruf, angka, titik, koma, dan spasi',
                        'max_length'  => '{field} maksimal 100 karakter',
                    ]
                ],
                'edit-tanggal_lahir' => [
                    'label'  => 'Tanggal Lahir',
                    'rules' => [
                        'required',
                        'valid_date[Y-m-d]',
                        static function ($value, array $data, ?string &$error) use ($maxTanggal): bool {
                            if ($value > $maxTanggal) {
                                $error = 'Tanggal maksimal harus hari ini';
                                return false;
                            }

                            return true;
                        }
                    ],
                    'errors' => [
                        'required'   => '{field} harus diisi',
                        'valid_date' => '{field} harus berformat YYYY-MM-DD',
                    ]
                ],
                'edit-no_hp' => [
                    'label'  => 'No HP',
                    'rules'  => 'required|max_length[30]|regex_match[/^\+?[0-9]+$/]',
                    'errors' => [
                        'required'    => '{field} harus diisi',
                        'max_length'  => '{field} maksimal 30 karakter',
                        'regex_match' => '{field} hanya boleh angka dan boleh diawali +',
                    ]
                ],
                'edit-alamat' => [
                    'label'  => 'Alamat',
                    'rules'  => 'required|regex_match[/^[a-zA-Z0-9,.\s]+$/]',
                    'errors' => [
                        'required'    => '{field} harus diisi',
                        'regex_match' => '{field} hanya boleh diisi huruf, angka, titik, koma, dan spasi',
                    ]
                ],
                'edit-jabatan_id' => [
                    'label'  => 'Jabatan',
                    'rules'  => 'required|integer',
                    'errors' => [
                        'required' => '{field} harus diisi',
                        'integer'  => '{field} tidak valid',
                    ]
                ],
                'edit-is_active' => [
                    'label'  => 'Status Aktif',
                    'rules'  => 'required|in_list[0,1]',
                    'errors' => [
                        'required' => '{field} harus diisi',
                        'in_list'  => '{field} tidak valid',
                    ]
                ],
            ];

            if ($file && $file->getError() != 4) {
                $rules['edit-foto'] = [
                    'label'  => 'Foto',
                    'rules'  => 'is_image[edit-foto]|mime_in[edit-foto,image/png,image/jpeg,image/jpg]|max_size[edit-foto,2048]|ext_in[edit-foto,png,jpg,jpeg]',
                    'errors' => [
                        'is_image' => '{field} harus berupa gambar',
                        'mime_in'  => 'Format gambar harus png, jpg, atau jpeg',
                        'ext_in'   => 'Format gambar harus png, jpg, atau jpeg',
                        'max_size' => 'Ukuran maksimal 2MB'
                    ]
                ];
            }

            $validasi = $this->validasi($rules, $post);

            if (! $validasi['sukses']) {
                return $validasi;
            }

            $pegawai = $this->pegawaiModel->getPegawaiById($id);

            if ($pegawai === null) {
                return $this->hasilTidakDitemukan('Data Pegawai Tidak Ditemukan');
            }

            $statusLama = (int) ($pegawai->is_active ?? 0);
            $statusBaru = $this->intVal($post['edit-is_active'] ?? 1, 1);

            if ($statusLama === 1 && $statusBaru === 0) {
                $bulanIni = date('Y-m');
                $jumlahJadwalBulanIni = $this->jadwalKerjaModel->countByPegawaiDanBulan($id, $bulanIni);
                if ($jumlahJadwalBulanIni > 0) {
                    return $this->hasilGagal([
                        'edit-is_active' => 'Pegawai tidak dapat dinonaktifkan karena masih memiliki jadwal kerja pada bulan ini'
                    ]);
                }

                $jumlahPresensiBulanIni = $this->presensiModel->countByPegawaiDanBulan($id, $bulanIni);
                if ($jumlahPresensiBulanIni > 0) {
                    return $this->hasilGagal([
                        'edit-is_active' => 'Pegawai tidak dapat dinonaktifkan karena sudah memiliki data presensi pada bulan ini'
                    ]);
                }
            }

            $data = [
                'id'            => $post['edit-id'] ?? $id,
                'nama_pegawai'  => $this->stringWajib($post['edit-nama_pegawai'] ?? ''),
                'jenis_kelamin' => $this->stringWajib($post['edit-jenis_kelamin'] ?? ''),
                'tempat_lahir'  => $this->stringAtauNull($post['edit-tempat_lahir'] ?? ''),
                'tanggal_lahir' => $this->stringAtauNull($post['edit-tanggal_lahir'] ?? ''),
                'no_hp'         => $this->stringAtauNull($post['edit-no_hp'] ?? ''),
                'alamat'        => $this->stringAtauNull($post['edit-alamat'] ?? ''),
                'jabatan_id'    => $this->intAtauNull($post['edit-jabatan_id'] ?? null),
                'is_active'     => $statusBaru,
            ];

            if ($file && $file->getError() != 4) {
                $folderFoto = FCPATH . 'assets/media/pegawai' . DIRECTORY_SEPARATOR;

                $this->hapusFotoPegawaiLama($pegawai->foto ?? null);

                $namaFoto = $this->generateNamaFoto($pegawai->kode_pegawai, $file->getExtension());
                $file->move($folderFoto, $namaFoto, true);
                $data['foto'] = $namaFoto;
            }

            $simpan = $this->pegawaiModel->save($data);

            if (! $simpan) {
                return $this->hasilGagal([
                    'general' => 'Data pegawai gagal diubah'
                ]);
            }

            $this->catatAudit(
                'update',
                'pegawai',
                $id,
                'Mengubah data pegawai: ' . $data['nama_pegawai'] . ' dengan kode ' . (string) $pegawai->kode_pegawai
            );

            return $this->hasilSukses('Data Pegawai Berhasil Diubah');
        });
    }

    public function ambil(int $id): array
    {
        return $this->eksekusi(function () use ($id) {
            $pegawai = $this->pegawaiModel->getPegawaiById($id);

            if ($pegawai === null) {
                return $this->hasilTidakDitemukan('Data Pegawai Tidak Ditemukan');
            }

            return $this->hasilData([
                'pegawai' => $pegawai
            ]);
        });
    }

    public function hapus(int $id): array
    {
        return $this->eksekusi(function () use ($id) {
            $pegawai = $this->pegawaiModel->getPegawaiById($id);

            if ($pegawai === null) {
                return $this->hasilTidakDitemukan('Data Pegawai Tidak Ada Di Database');
            }

            $jumlahDipakai = $this->pegawaiModel->jumlahRelasiYangMemakai($id);

            if ($jumlahDipakai > 0) {
                $rincianRelasi = $this->pegawaiModel->rincianRelasiYangMemakai($id);

                $daftarDipakai = [];
                foreach ($rincianRelasi as $item) {
                    $daftarDipakai[] = $item['label_tabel'] . ' (' . $item['jumlah'] . ')';
                }

                $pesan = 'Data Pegawai tidak dapat dihapus karena masih dipakai';
                if (! empty($daftarDipakai)) {
                    $pesan .= ' pada: ' . implode(', ', $daftarDipakai);
                }

                return $this->hasilGagal([], $pesan);
            }

            $this->hapusFotoPegawaiLama($pegawai->foto ?? null);

            $hapus = $this->pegawaiModel->delete($id);

            if (! $hapus) {
                return $this->hasilGagal([], 'Data Pegawai Gagal Dihapus');
            }

            $this->catatAudit(
                'delete',
                'pegawai',
                $id,
                'Menghapus data pegawai: ' . (string) $pegawai->nama_pegawai . ' dengan kode ' . (string) $pegawai->kode_pegawai
            );

            return $this->hasilSukses('Data Pegawai Berhasil Dihapus');
        });
    }

    protected function generateKodePegawai(): string
    {
        return 'PGW-' . date('ymdHis');
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

    protected function generateQRCode(string $kodePegawai): string
    {
        $qr = new QrCode($kodePegawai);
        $qr->setSize(300);
        $qr->setMargin(10);
        $qr->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh());

        $writer = new PngWriter();

        $logoPath = $this->settingsService->getLogoPath();

        $logo = null;

        if (!empty($logoPath) && is_file($logoPath)) {
            $logo = new Logo(
                $logoPath,
                60,
                60
            );
        }

        $result = $writer->write(
            $qr,
            $logo,
            null,
            ['margin' => 10]
        );

        $fileName = 'qr_' . strtolower($kodePegawai) . '.png';
        $path = FCPATH . 'assets/media/qrcode' . DIRECTORY_SEPARATOR;

        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $result->saveToFile($path . $fileName);

        return $fileName;
    }

    public function dataKartu(int $id, string $ukuran = 'B1'): array
    {
        $pegawai = $this->pegawaiModel->getPegawaiById($id);

        if ($pegawai === null) {
            return [];
        }

        $ukuran = strtoupper(trim($ukuran));
        $ukuranMap = $this->getUkuranKartuMap();

        if (! array_key_exists($ukuran, $ukuranMap)) {
            $ukuran = 'B1';
        }

        $setting = $this->settingsService->ambilData();

        $logoFile = $this->settingsService->getLogoFileName();
        $namaUsaha = $this->settingsService->getNamaUsaha();

        $fotoFile = ! empty($pegawai->foto) ? $pegawai->foto : 'default.png';
        $fotoPath = FCPATH . 'assets/media/pegawai/' . $fotoFile;

        if (! is_file($fotoPath)) {
            $fotoFile = 'default.png';
        }

        $qrFile = ! empty($pegawai->qrcode) ? $pegawai->qrcode : null;
        $qrPath = $qrFile ? FCPATH . 'assets/media/qrcode/' . $qrFile : null;

        if (empty($qrFile) || ! is_file($qrPath)) {
            return [];
        }

        $jabatan = $pegawai->jabatan ?? $pegawai->nama_jabatan ?? $pegawai->jabatan_nama ?? '-';

        return [
            'pegawai' => [
                'id'            => $pegawai->id,
                'kode_pegawai'  => $pegawai->kode_pegawai ?? '-',
                'nama_pegawai'  => $pegawai->nama_pegawai ?? '-',
                'jabatan'       => $jabatan,
                'foto_url'      => base_url('assets/media/pegawai/' . $fotoFile),
                'qr_url'        => base_url('assets/media/qrcode/' . $qrFile),
            ],
            'usaha' => [
                'nama_usaha' => $namaUsaha,
                'logo_url'   => base_url('assets/media/photos/' . $logoFile),
                'alamat'     => $setting->alamat ?? '',
            ],
            'ukuran' => [
                'kode'        => $ukuran,
                'label'       => $ukuranMap[$ukuran]['label'],
                'width_mm'    => $ukuranMap[$ukuran]['width_mm'],
                'height_mm'   => $ukuranMap[$ukuran]['height_mm'],
                'orientation' => 'portrait',
            ],
        ];
    }

    protected function getUkuranKartuMap(): array
    {
        return [
            'B1' => [
                'label'     => 'B1',
                'width_mm'  => 65,
                'height_mm' => 102,
            ],
            'B2' => [
                'label'     => 'B2',
                'width_mm'  => 79,
                'height_mm' => 126,
            ],
        ];
    }

    public function downloadQRCode(int $id): array
    {
        return $this->eksekusi(function () use ($id) {
            $pegawai = $this->pegawaiModel->getPegawaiById($id);

            if ($pegawai === null) {
                return $this->hasilTidakDitemukan('Data Pegawai Tidak Ditemukan');
            }

            $fileQr = $pegawai->qrcode ?? null;

            if (empty($fileQr)) {
                return $this->hasilTidakDitemukan('QR Code pegawai tidak ditemukan');
            }

            $path = FCPATH . 'assets/media/qrcode' . DIRECTORY_SEPARATOR . $fileQr;

            if (! is_file($path)) {
                return $this->hasilTidakDitemukan('File QR Code tidak ditemukan');
            }

            $namaDownload = 'QR_' . ($pegawai->kode_pegawai ?? ('pegawai_' . $pegawai->id)) . '.png';

            return [
                'sukses'        => true,
                'path'          => $path,
                'nama_download' => $namaDownload,
                'mime'          => 'image/png',
            ];
        });
    }
}
