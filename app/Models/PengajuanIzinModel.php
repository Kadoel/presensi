<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;

class PengajuanIzinModel extends Model
{
    protected $table         = 'pengajuan_izin';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';

    protected $allowedFields = [
        'pegawai_id',
        'jenis',
        'tanggal_mulai',
        'tanggal_selesai',
        'alasan',
        'lampiran',
        'status',
        'catatan_approval',
        'approved_by',
        'approved_at',
    ];

    protected $useTimestamps = true;

    public function selectData(): BaseBuilder
    {
        return $this->db->table($this->table)
            ->select('
                pengajuan_izin.id,
                pengajuan_izin.pegawai_id,
                pengajuan_izin.jenis,
                pengajuan_izin.tanggal_mulai,
                pengajuan_izin.tanggal_selesai,
                pengajuan_izin.alasan,
                pengajuan_izin.lampiran,
                pengajuan_izin.status,
                pengajuan_izin.catatan_approval,
                pengajuan_izin.approved_by,
                pengajuan_izin.approved_at,
                pengajuan_izin.created_at,
                pengajuan_izin.updated_at,
                pegawai.kode_pegawai,
                pegawai.nama_pegawai,
                users.username AS approved_by_username
            ')
            ->join('pegawai', 'pegawai.id = pengajuan_izin.pegawai_id', 'left')
            ->join('users', 'users.id = pengajuan_izin.approved_by', 'left');
    }

    public function selectDataPegawai(int $pegawaiId): BaseBuilder
    {
        return $this->db->table($this->table)
            ->select('
                pengajuan_izin.id,
                pengajuan_izin.pegawai_id,
                pengajuan_izin.jenis,
                pengajuan_izin.tanggal_mulai,
                pengajuan_izin.tanggal_selesai,
                pengajuan_izin.alasan,
                pengajuan_izin.lampiran,
                pengajuan_izin.status,
                pengajuan_izin.catatan_approval,
                pengajuan_izin.approved_by,
                pengajuan_izin.approved_at,
                pengajuan_izin.created_at,
                pengajuan_izin.updated_at,
                users.username AS approved_by_username
            ')
            ->join('users', 'users.id = pengajuan_izin.approved_by', 'left')
            ->where('pengajuan_izin.pegawai_id', $pegawaiId);
    }

    public function getPengajuanById(int $id): ?object
    {
        return $this->db->table($this->table)
            ->select('pengajuan_izin.*')
            ->where('pengajuan_izin.id', $id)
            ->get()
            ->getRow();
    }

    public function jumlahBentrokTanggal(
        int $pegawaiId,
        string $tanggalMulai,
        string $tanggalSelesai,
        ?int $excludeId = null
    ): int {
        $builder = $this->db->table($this->table)
            ->where('pegawai_id', $pegawaiId)
            ->groupStart()
            ->where('tanggal_mulai <=', $tanggalSelesai)
            ->where('tanggal_selesai >=', $tanggalMulai)
            ->where('status !=', 'rejected')
            ->groupEnd();

        if ($excludeId !== null) {
            $builder->where('id !=', $excludeId);
        }

        return (int) $builder->countAllResults();
    }

    public function countApprovedAktifByTanggal(string $tanggal): int
    {
        return (int) $this->where('status', 'approved')
            ->where('tanggal_mulai <=', $tanggal)
            ->where('tanggal_selesai >=', $tanggal)
            ->countAllResults();
    }

    public function countPending(): int
    {
        return (int) $this->where('status', 'pending')
            ->countAllResults();
    }

    public function countPendingByTanggal(string $tanggal): int
    {
        return (int) $this->where('status', 'pending')
            ->where('tanggal_mulai <=', $tanggal)
            ->where('tanggal_selesai >=', $tanggal)
            ->countAllResults();
    }

    /**
     * Tambahkan method ini ke app/Models/PengajuanIzinModel.php
     */
    public function countPendingByPeriode(string $tanggalMulai, string $tanggalSelesai): int
    {
        return (int) $this->where('status', 'pending')
            ->whereIn('jenis', ['izin', 'sakit', 'cuti'])
            ->where('tanggal_mulai <=', $tanggalSelesai)
            ->where('tanggal_selesai >=', $tanggalMulai)
            ->countAllResults();
    }
}
