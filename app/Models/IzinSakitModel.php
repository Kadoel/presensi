<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;

class IzinSakitModel extends PengajuanIzinModel
{
    protected array $jenisDiizinkan = ['izin', 'sakit'];

    public function selectData(): BaseBuilder
    {
        return parent::selectData()
            ->whereIn('pengajuan_izin.jenis', $this->jenisDiizinkan);
    }

    public function getPengajuanById(int $id): ?object
    {
        return $this->db->table($this->table)
            ->select('pengajuan_izin.*')
            ->where('pengajuan_izin.id', $id)
            ->whereIn('pengajuan_izin.jenis', $this->jenisDiizinkan)
            ->get()
            ->getRow();
    }
}
