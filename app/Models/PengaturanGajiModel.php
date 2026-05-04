<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;

class PengaturanGajiModel extends Model
{
    protected $table      = 'pengaturan_gaji';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = [
        'jabatan_id',
        'gaji_pokok',
        'tunjangan',
        'potongan_telat_per_menit',
        'potongan_pulang_cepat_per_menit',
        'potongan_alpa_per_hari',
        'is_active',
    ];

    protected $useTimestamps = true;

    public function selectData(): BaseBuilder
    {
        return $this->db->table($this->table)
            ->select('
                pengaturan_gaji.id,
                pengaturan_gaji.jabatan_id,
                pengaturan_gaji.gaji_pokok,
                pengaturan_gaji.tunjangan,
                pengaturan_gaji.potongan_telat_per_menit,
                pengaturan_gaji.potongan_pulang_cepat_per_menit,
                pengaturan_gaji.potongan_alpa_per_hari,
                pengaturan_gaji.is_active,
                pengaturan_gaji.created_at,
                pengaturan_gaji.updated_at,
                jabatan.nama_jabatan
            ')
            ->join('jabatan', 'jabatan.id = pengaturan_gaji.jabatan_id', 'left');
    }

    public function getById(int $id): ?object
    {
        return $this->where('id', $id)->first();
    }

    public function getAktifByJabatan(int $jabatanId): ?object
    {
        return $this->where('jabatan_id', $jabatanId)
            ->where('is_active', 1)
            ->first();
    }

    public function getAktifByJabatanSelainId(int $jabatanId, int $excludeId): ?object
    {
        return $this->where('jabatan_id', $jabatanId)
            ->where('is_active', 1)
            ->where('id !=', $excludeId)
            ->first();
    }

    public function simpanPengaturan(array $data): bool
    {
        return (bool) $this->insert($data);
    }

    public function ubahPengaturan(int $id, array $data): bool
    {
        return (bool) $this->update($id, $data);
    }

    public function hapusPengaturan(int $id): bool
    {
        return (bool) $this->delete($id);
    }
}
