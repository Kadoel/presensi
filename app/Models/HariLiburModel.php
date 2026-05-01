<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;

class HariLiburModel extends Model
{
    protected $table         = 'hari_libur';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $allowedFields = [
        'tanggal',
        'nama_libur',
        'keterangan',
    ];
    protected $useTimestamps = true;

    public function selectData(): BaseBuilder
    {
        return $this->db->table($this->table)
            ->select('
                hari_libur.id,
                hari_libur.tanggal,
                hari_libur.nama_libur,
                hari_libur.keterangan,
                hari_libur.created_at,
                hari_libur.updated_at
            ');
    }

    public function getHariLiburById(int $id): ?object
    {
        return $this->db->table($this->table)
            ->select('
                hari_libur.id,
                hari_libur.tanggal,
                hari_libur.nama_libur,
                hari_libur.keterangan
            ')
            ->where('hari_libur.id', $id)
            ->get()
            ->getRow();
    }
}
