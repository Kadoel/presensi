<?php

namespace App\Models;

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

    public function selectData()
    {
        return $this->select('id, nama_jabatan, deskripsi, is_active, created_at, updated_at');
    }

    public function getJabatan($id)
    {
        return $this->where([
            'id'        => $id,
            'is_active' => 1
        ])->first();
    }

    public function getJabatanSelect()
    {
        return $this->select('id, nama_jabatan')
            ->where('is_active', 1)
            ->findAll();
    }

    public function getJabatanById($id)
    {
        return $this->where('id', $id)->first();
    }

    public function jumlahPegawaiYangMemakai($id): int
    {
        return (int) $this->db->table('pegawai')
            ->where('jabatan_id', $id)
            ->countAllResults();
    }

    public function dipakaiPegawai($id): bool
    {
        return $this->jumlahPegawaiYangMemakai($id) > 0;
    }
}
