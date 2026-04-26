<?php

namespace App\Models;

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

    public function selectData()
    {
        return $this->select('
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

    public function getShift($id)
    {
        return $this->where([
            'id'        => $id,
            'is_active' => 1
        ])->first();
    }

    public function getShiftById($id)
    {
        return $this->where('id', $id)->first();
    }

    public function jumlahJadwalYangMemakai($id): int
    {
        return (int) $this->db->table('jadwal_kerja')
            ->where('shift_id', $id)
            ->countAllResults();
    }

    public function dipakaiJadwal($id): bool
    {
        return $this->jumlahJadwalYangMemakai($id) > 0;
    }

    public function getShiftAktifById(int $id)
    {
        return $this->where('id', $id)
            ->where('is_active', 1)
            ->first();
    }

    public function getShiftDropdown(): array
    {
        $shift = $this->db->table('shift')
            ->select('shift.id, shift.kode_shift, shift.nama_shift')
            ->where('shift.is_active', 1)
            ->orderBy('shift.nama_shift', 'ASC')
            ->get()
            ->getResult();

        return $shift;
    }
}
