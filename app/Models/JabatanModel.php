<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;

class JabatanModel extends Model
{
    protected $table         = 'jabatan';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $allowedFields = [
        'nama_jabatan',
        'deskripsi',
        'is_active'
    ];
    protected $useTimestamps = true;

    public function selectData(): BaseBuilder
    {
        return $this->db->table($this->table)->select('id, nama_jabatan, deskripsi, is_active, created_at, updated_at');
    }

    public function getJabatan(int $id): ?object
    {
        return $this->where([
            'id'        => $id,
            'is_active' => 1
        ])->first();
    }

    public function getJabatanById(int $id): ?object
    {
        return $this->where('id', $id)->first();
    }

    public function getJabatanSelect(): array
    {
        return $this->select('id, nama_jabatan')
            ->where('is_active', 1)
            ->orderBy('nama_jabatan', 'ASC')
            ->findAll();
    }

    public function jumlahPegawaiYangMemakai(int $id): int
    {
        return (int) $this->db->table('pegawai')
            ->where('jabatan_id', $id)
            ->countAllResults();
    }

    public function dipakaiPegawai(int $id): bool
    {
        return $this->jumlahPegawaiYangMemakai($id) > 0;
    }
}
