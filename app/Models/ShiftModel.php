<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;

class ShiftModel extends Model
{
    protected $table         = 'shift';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';

    protected $allowedFields = [
        'kode_shift',
        'nama_shift',
        'jam_masuk',
        'batas_mulai_datang',
        'batas_akhir_datang',
        'jam_pulang',
        'batas_mulai_pulang',
        'batas_akhir_pulang',
        'toleransi_telat_menit',
        'keterangan',
        'is_active',
    ];

    protected $useTimestamps = true;

    public function selectData(): BaseBuilder
    {
        return $this->db->table($this->table)
            ->select('
                id,
                kode_shift,
                nama_shift,
                jam_masuk,
                batas_mulai_datang,
                batas_akhir_datang,
                jam_pulang,
                batas_mulai_pulang,
                batas_akhir_pulang,
                toleransi_telat_menit,
                keterangan,
                is_active,
                created_at,
                updated_at
            ');
    }

    public function getShift(int $id): ?object
    {
        return $this->where([
            'id'        => $id,
            'is_active' => 1,
        ])->first();
    }

    public function getShiftById(int $id): ?object
    {
        return $this->where('id', $id)->first();
    }

    public function getShiftAktifById(int $id): ?object
    {
        return $this->where('id', $id)
            ->where('is_active', 1)
            ->first();
    }

    public function jumlahJadwalYangMemakai(int $id): int
    {
        return (int) $this->db->table('jadwal_kerja')
            ->where('shift_id', $id)
            ->countAllResults();
    }

    public function dipakaiJadwal(int $id): bool
    {
        return $this->jumlahJadwalYangMemakai($id) > 0;
    }

    public function getShiftDropdown(): array
    {
        return $this->db->table($this->table)
            ->select('shift.id, shift.kode_shift, shift.nama_shift')
            ->where('shift.is_active', 1)
            ->orderBy('shift.nama_shift', 'ASC')
            ->get()
            ->getResult();
    }
}
