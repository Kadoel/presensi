<?php

namespace App\Services\Admin;

use App\Models\JabatanModel;
use App\Models\PengaturanGajiModel;
use App\Services\BaseService;
use CodeIgniter\Database\BaseBuilder;

class PengaturanGajiService extends BaseService
{
    protected PengaturanGajiModel $pengaturanGajiModel;
    protected JabatanModel $jabatanModel;

    public function __construct()
    {
        parent::__construct();

        $this->pengaturanGajiModel = new PengaturanGajiModel();
        $this->jabatanModel        = new JabatanModel();
    }

    public function dataTabel(): BaseBuilder
    {
        return $this->pengaturanGajiModel->selectData();
    }

    public function getJabatanDropdown(): array
    {
        return $this->jabatanModel->getJabatanSelect();
    }

    public function formatRupiah($value): string
    {
        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }

    public function simpan(array $post): array
    {
        return $this->transaksi(function () use ($post) {
            $validasi = $this->validasi($this->rules(), $post);

            if (! $validasi['sukses']) {
                return $validasi;
            }

            $jabatanId = (int) ($post['jabatan_id'] ?? 0);
            $isActive  = (int) ($post['is_active'] ?? 1);

            if ($isActive === 1) {
                $exists = $this->pengaturanGajiModel->getAktifByJabatan($jabatanId);

                if ($exists !== null) {
                    return $this->hasilGagal([
                        'jabatan_id' => 'Pengaturan gaji aktif untuk jabatan ini sudah ada',
                    ]);
                }
            }

            $insert = $this->pengaturanGajiModel->simpanPengaturan([
                'jabatan_id' => $jabatanId,
                'gaji_pokok' => $this->angkaRupiah($post['gaji_pokok'] ?? 0),
                'tunjangan' => $this->angkaRupiah($post['tunjangan'] ?? 0),
                'potongan_telat_per_menit' => $this->angkaRupiah($post['potongan_telat_per_menit'] ?? 0),
                'potongan_pulang_cepat_per_menit' => $this->angkaRupiah($post['potongan_pulang_cepat_per_menit'] ?? 0),
                'potongan_alpa_per_hari' => $this->angkaRupiah($post['potongan_alpa_per_hari'] ?? 0),
                'is_active' => $isActive,
            ]);

            if (! $insert) {
                return $this->hasilGagal([], 'Pengaturan gaji gagal disimpan');
            }

            $id = (int) $this->pengaturanGajiModel->getInsertID();

            $this->catatAudit(
                'create',
                'pengaturan_gaji',
                $id,
                'Menambahkan pengaturan gaji jabatan ID ' . $jabatanId
            );

            return $this->hasilSukses('Pengaturan gaji berhasil disimpan');
        });
    }

    public function ambil(int $id): array
    {
        return $this->eksekusi(function () use ($id) {
            $data = $this->pengaturanGajiModel->getById($id);

            if ($data === null) {
                return $this->hasilTidakDitemukan('Pengaturan gaji tidak ditemukan');
            }

            return $this->hasilData([
                'pengaturan_gaji' => $data,
            ]);
        });
    }

    public function ubah(int $id, array $post): array
    {
        return $this->transaksi(function () use ($id, $post) {
            $dataLama = $this->pengaturanGajiModel->getById($id);

            if ($dataLama === null) {
                return $this->hasilTidakDitemukan('Pengaturan gaji tidak ditemukan');
            }

            $validasi = $this->validasi($this->rules('edit-'), $post);

            if (! $validasi['sukses']) {
                return $validasi;
            }

            $jabatanId = (int) ($post['edit-jabatan_id'] ?? 0);
            $isActive  = (int) ($post['edit-is_active'] ?? 1);

            if ($isActive === 1) {
                $exists = $this->pengaturanGajiModel->getAktifByJabatanSelainId($jabatanId, $id);

                if ($exists !== null) {
                    return $this->hasilGagal([
                        'edit-jabatan_id' => 'Pengaturan gaji aktif untuk jabatan ini sudah ada',
                    ]);
                }
            }

            $update = $this->pengaturanGajiModel->ubahPengaturan($id, [
                'jabatan_id' => $jabatanId,
                'gaji_pokok' => $this->angkaRupiah($post['edit-gaji_pokok'] ?? 0),
                'tunjangan' => $this->angkaRupiah($post['edit-tunjangan'] ?? 0),
                'potongan_telat_per_menit' => $this->angkaRupiah($post['edit-potongan_telat_per_menit'] ?? 0),
                'potongan_pulang_cepat_per_menit' => $this->angkaRupiah($post['edit-potongan_pulang_cepat_per_menit'] ?? 0),
                'potongan_alpa_per_hari' => $this->angkaRupiah($post['edit-potongan_alpa_per_hari'] ?? 0),
                'is_active' => $isActive,
            ]);

            if (! $update) {
                return $this->hasilGagal([], 'Pengaturan gaji gagal diubah');
            }

            $this->catatAudit(
                'update',
                'pengaturan_gaji',
                $id,
                'Mengubah pengaturan gaji ID ' . $id
            );

            return $this->hasilSukses('Pengaturan gaji berhasil diubah');
        });
    }

    public function hapus(int $id): array
    {
        return $this->transaksi(function () use ($id) {
            $data = $this->pengaturanGajiModel->getById($id);

            if ($data === null) {
                return $this->hasilTidakDitemukan('Pengaturan gaji tidak ditemukan');
            }

            if (! $this->pengaturanGajiModel->hapusPengaturan($id)) {
                return $this->hasilGagal([], 'Pengaturan gaji gagal dihapus');
            }

            $this->catatAudit(
                'delete',
                'pengaturan_gaji',
                $id,
                'Menghapus pengaturan gaji ID ' . $id
            );

            return $this->hasilSukses('Pengaturan gaji berhasil dihapus');
        });
    }

    protected function rules(string $prefix = ''): array
    {
        return [
            $prefix . 'jabatan_id' => [
                'label' => 'Jabatan',
                'rules' => 'required|integer',
                'errors' => [
                    'required' => '{field} harus dipilih',
                    'integer'  => '{field} tidak valid',
                ],
            ],
            $prefix . 'gaji_pokok' => [
                'label' => 'Gaji Pokok',
                'rules' => 'required',
                'errors' => ['required' => '{field} harus diisi'],
            ],
            $prefix . 'tunjangan' => [
                'label' => 'Tunjangan',
                'rules' => 'required',
                'errors' => ['required' => '{field} harus diisi'],
            ],
            $prefix . 'potongan_telat_per_menit' => [
                'label' => 'Potongan Telat Per Menit',
                'rules' => 'required',
                'errors' => ['required' => '{field} harus diisi'],
            ],
            $prefix . 'potongan_pulang_cepat_per_menit' => [
                'label' => 'Potongan Pulang Cepat Per Menit',
                'rules' => 'required',
                'errors' => ['required' => '{field} harus diisi'],
            ],
            $prefix . 'potongan_alpa_per_hari' => [
                'label' => 'Potongan Alpa Per Hari',
                'rules' => 'required',
                'errors' => ['required' => '{field} harus diisi'],
            ],
            $prefix . 'is_active' => [
                'label' => 'Status',
                'rules' => 'required|in_list[0,1]',
                'errors' => [
                    'required' => '{field} harus dipilih',
                    'in_list'  => '{field} tidak valid',
                ],
            ],
        ];
    }

    protected function angkaRupiah($value): float
    {
        return (float) preg_replace('/[^0-9]/', '', (string) $value);
    }
}
