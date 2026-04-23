<?php

namespace App\Models;

use CodeIgniter\Model;

class TukarJadwalModel extends Model
{
    protected $table         = 'tukar_jadwal';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $allowedFields = [
        'jadwal_kerja_a_id',
        'pegawai_a_id',
        'tanggal_a',
        'shift_a_id',
        'status_hari_a',
        'sumber_data_a',

        'jadwal_kerja_b_id',
        'pegawai_b_id',
        'tanggal_b',
        'shift_b_id',
        'status_hari_b',
        'sumber_data_b',

        'tipe_swap',
        'alasan',
        'status',
        'tipe_pengajuan',
        'catatan_approval',
        'diajukan_oleh',
        'disetujui_oleh',
        'disetujui_at',
    ];
    protected $useTimestamps = true;

    public function selectData()
    {
        return $this->db->table($this->table)
            ->select("
            tukar_jadwal.id AS id,
            tukar_jadwal.pegawai_a_id,
            tukar_jadwal.pegawai_b_id,
            tukar_jadwal.tanggal_a,
            tukar_jadwal.tanggal_b,
            tukar_jadwal.tipe_swap,
            tukar_jadwal.status,
            tukar_jadwal.tipe_pengajuan,
            tukar_jadwal.alasan,
            tukar_jadwal.catatan_approval,
            tukar_jadwal.disetujui_at,
            tukar_jadwal.created_at,

            pegawai_a.kode_pegawai AS kode_pegawai_a,
            pegawai_a.nama_pegawai AS nama_pegawai_a,
            shift_a.nama_shift AS nama_shift_a,

            pegawai_b.kode_pegawai AS kode_pegawai_b,
            pegawai_b.nama_pegawai AS nama_pegawai_b,
            shift_b.nama_shift AS nama_shift_b,

            COALESCE(user_pengaju.username, 'System') AS diajukan_oleh_username,
            COALESCE(user_approval.username, 'System') AS disetujui_oleh_username
        ", false)
            ->join('pegawai pegawai_a', 'pegawai_a.id = tukar_jadwal.pegawai_a_id', 'left')
            ->join('shift shift_a', 'shift_a.id = tukar_jadwal.shift_a_id', 'left')
            ->join('pegawai pegawai_b', 'pegawai_b.id = tukar_jadwal.pegawai_b_id', 'left')
            ->join('shift shift_b', 'shift_b.id = tukar_jadwal.shift_b_id', 'left')
            ->join('users user_pengaju', 'user_pengaju.id = tukar_jadwal.diajukan_oleh', 'left')
            ->join('users user_approval', 'user_approval.id = tukar_jadwal.disetujui_oleh', 'left');
    }

    public function getTukarJadwalById(int $id)
    {
        return $this->selectData()
            ->where('tukar_jadwal.id', $id)
            ->get()
            ->getRow();
    }

    public function getPendingById(int $id)
    {
        return $this->where([
            'id'     => $id,
            'status' => 'pending',
        ])->first();
    }
}
