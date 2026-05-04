<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;

class PenggajianModel extends Model
{
    protected $table = 'penggajian';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = [
        'pegawai_id',
        'jabatan_id',
        'bulan',
        'gaji_pokok',
        'tunjangan',
        'gaji_kotor',
        'total_hadir',
        'total_izin',
        'total_sakit',
        'total_libur',
        'total_cuti',
        'total_alpa',
        'total_menit_telat',
        'total_menit_pulang_cepat',
        'potongan_telat',
        'potongan_pulang_cepat',
        'potongan_alpa',
        'total_potongan',
        'gaji_bersih',
        'status',
        'created_by',
        'generated_at',
        'finalized_by',
        'finalized_at',
    ];

    protected $useTimestamps = true;

    public function selectData(?string $bulan = null): BaseBuilder
    {
        $builder = $this->db->table($this->table)
            ->select('
                penggajian.*,
                pegawai.kode_pegawai,
                pegawai.nama_pegawai,
                jabatan.nama_jabatan
            ')
            ->join('pegawai', 'pegawai.id = penggajian.pegawai_id', 'left')
            ->join('jabatan', 'jabatan.id = penggajian.jabatan_id', 'left');

        if (! empty($bulan)) {
            $builder->where('penggajian.bulan', $bulan);
        }

        return $builder;
    }

    public function getByPegawaiBulan(int $pegawaiId, string $bulan): ?object
    {
        return $this->where('pegawai_id', $pegawaiId)
            ->where('bulan', $bulan)
            ->first();
    }

    public function adaFinalByBulan(string $bulan): bool
    {
        return $this->where('bulan', $bulan)
            ->where('status', 'final')
            ->countAllResults() > 0;
    }

    public function deleteDraftByBulan(string $bulan): bool
    {
        return $this->where('bulan', $bulan)
            ->where('status', 'draft')
            ->delete();
    }
}
