<?php

namespace App\Models;

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

    public function selectData()
    {
        return $this->db->table('hari_libur')
            ->select('
                hari_libur.id,
                hari_libur.tanggal,
                hari_libur.nama_libur,
                hari_libur.keterangan,
                hari_libur.created_at,
                hari_libur.updated_at
            ');
    }

    public function getHariLiburById(int $id)
    {
        return $this->db->table('hari_libur')
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
