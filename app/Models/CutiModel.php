<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;

class CutiModel extends PengajuanIzinModel
{
    public function selectData(): BaseBuilder
    {
        return parent::selectData()
            ->where('pengajuan_izin.jenis', 'cuti');
    }

    public function getPengajuanById(int $id): ?object
    {
        return $this->db->table($this->table)
            ->select('pengajuan_izin.*')
            ->where('pengajuan_izin.id', $id)
            ->where('pengajuan_izin.jenis', 'cuti')
            ->get()
            ->getRow();
    }
}
