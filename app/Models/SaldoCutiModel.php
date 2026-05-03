<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;

class SaldoCutiModel extends Model
{
    protected $table      = 'saldo_cuti';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = [
        'pegawai_id',
        'tahun',
        'jatah',
        'terpakai',
        'sisa',
    ];

    protected $useTimestamps = true;

    public function selectData(): BaseBuilder
    {
        return $this->db->table($this->table)
            ->select('
                saldo_cuti.id,
                saldo_cuti.pegawai_id,
                saldo_cuti.tahun,
                saldo_cuti.jatah,
                saldo_cuti.terpakai,
                saldo_cuti.sisa,
                saldo_cuti.created_at,
                saldo_cuti.updated_at,
                pegawai.kode_pegawai,
                pegawai.nama_pegawai,
                pegawai.is_active
            ')
            ->join('pegawai', 'pegawai.id = saldo_cuti.pegawai_id', 'left');
    }

    public function getByPegawaiTahun(int $pegawaiId, int $tahun): ?object
    {
        return $this->where('pegawai_id', $pegawaiId)
            ->where('tahun', $tahun)
            ->first();
    }

    public function getRingkasanByTahun(int $tahun): object
    {
        $row = $this->db->table($this->table)
            ->select('
                COUNT(id) AS total_pegawai,
                COALESCE(SUM(jatah), 0) AS total_jatah,
                COALESCE(SUM(terpakai), 0) AS total_terpakai,
                COALESCE(SUM(sisa), 0) AS total_sisa
            ')
            ->where('tahun', $tahun)
            ->get()
            ->getRow();

        return (object) [
            'total_pegawai'  => (int) ($row->total_pegawai ?? 0),
            'total_jatah'    => (int) ($row->total_jatah ?? 0),
            'total_terpakai' => (int) ($row->total_terpakai ?? 0),
            'total_sisa'     => (int) ($row->total_sisa ?? 0),
        ];
    }
}
