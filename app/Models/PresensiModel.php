<?php

namespace App\Models;

use CodeIgniter\Model;

class PresensiModel extends Model
{
    protected $table         = 'presensi';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $allowedFields = [
        'pegawai_id',
        'tanggal',
        'jadwal_kerja_id',
        'shift_id',
        'jam_datang',
        'jam_pulang',
        'status_datang',
        'status_pulang',
        'menit_telat',
        'menit_pulang_cepat',
        'selfie_datang',
        'selfie_pulang',
        'barcode_datang',
        'barcode_pulang',
        'ip_address',
        'user_agent',
        'catatan_admin',
        'is_manual',
    ];
    protected $useTimestamps = true;

    public function getPresensiByPegawaiDanTanggal(int $pegawaiId, string $tanggal)
    {
        return $this->where([
            'pegawai_id' => $pegawaiId,
            'tanggal'    => $tanggal,
        ])->first();
    }

    public function sudahAdaPresensi(int $pegawaiId, string $tanggal): bool
    {
        return $this->getPresensiByPegawaiDanTanggal($pegawaiId, $tanggal) !== null;
    }
}
