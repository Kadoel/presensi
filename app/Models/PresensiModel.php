<?php

namespace App\Models;

use CodeIgniter\Model;

class PresensiModel extends Model
{
    protected $table            = 'presensi';
    protected $primaryKey       = 'id';
    protected $returnType       = 'object';
    protected $useAutoIncrement = true;
    protected $protectFields    = true;

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
        'sumber_presensi',
        'hasil_presensi',
    ];

    public function getPresensiByPegawaiDanTanggal(int $pegawaiId, string $tanggal): ?object
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

    public function dataTabel(?string $tanggal = null)
    {
        $builder = $this->db->table($this->table)
            ->select('
                presensi.id,
                presensi.tanggal,
                presensi.pegawai_id,
                presensi.jadwal_kerja_id,
                presensi.shift_id,
                presensi.jam_datang,
                presensi.jam_pulang,
                presensi.status_datang,
                presensi.status_pulang,
                presensi.menit_telat,
                presensi.menit_pulang_cepat,
                presensi.catatan_admin,
                presensi.is_manual,
                presensi.sumber_presensi,
                presensi.hasil_presensi,
                pegawai.kode_pegawai,
                pegawai.nama_pegawai,
                shift.kode_shift,
                shift.nama_shift
            ')
            ->join('pegawai', 'pegawai.id = presensi.pegawai_id', 'left')
            ->join('shift', 'shift.id = presensi.shift_id', 'left');

        if (! empty($tanggal)) {
            $builder->where('presensi.tanggal', $tanggal);
        }

        return $builder;
    }

    public function getDetailAdminById(int $id): ?object
    {
        return $this->db->table($this->table)
            ->select('
                presensi.*,
                pegawai.kode_pegawai,
                pegawai.nama_pegawai,
                pegawai.no_hp,
                pegawai.alamat,
                pegawai.jenis_kelamin,
                shift.kode_shift,
                shift.nama_shift,
                shift.jam_masuk,
                shift.jam_pulang,
                shift.toleransi_telat_menit
            ')
            ->join('pegawai', 'pegawai.id = presensi.pegawai_id', 'left')
            ->join('shift', 'shift.id = presensi.shift_id', 'left')
            ->where('presensi.id', $id)
            ->get()
            ->getRow();
    }

    public function countByTanggalDanStatusDatang(string $tanggal, string $status): int
    {
        return (int) $this->where([
            'tanggal'       => $tanggal,
            'status_datang' => $status,
        ])->countAllResults();
    }

    public function countByTanggalDanStatusPulang(string $tanggal, string $status): int
    {
        return (int) $this->where([
            'tanggal'       => $tanggal,
            'status_pulang' => $status,
        ])->countAllResults();
    }

    public function countByTanggal(string $tanggal): int
    {
        return (int) $this->where('tanggal', $tanggal)->countAllResults();
    }

    public function getPresensiTerbaru(string $tanggal, int $limit = 10): array
    {
        return $this->db->table($this->table)
            ->select('
                presensi.id,
                presensi.tanggal,
                presensi.jam_datang,
                presensi.jam_pulang,
                presensi.status_datang,
                presensi.status_pulang,
                presensi.sumber_presensi,
                presensi.hasil_presensi,
                pegawai.kode_pegawai,
                pegawai.nama_pegawai,
                shift.nama_shift
            ')
            ->join('pegawai', 'pegawai.id = presensi.pegawai_id', 'left')
            ->join('shift', 'shift.id = presensi.shift_id', 'left')
            ->where('presensi.tanggal', $tanggal)
            ->orderBy('presensi.id', 'DESC')
            ->limit($limit)
            ->get()
            ->getResult();
    }

    public function getPresensiHariIni(string $tanggal, int $limit = 10): array
    {
        return $this->select('
            presensi.id,
            presensi.pegawai_id,
            presensi.tanggal,
            presensi.jam_datang,
            presensi.jam_pulang,
            presensi.status_datang,
            presensi.status_pulang,
            presensi.menit_telat,
            pegawai.kode_pegawai,
            pegawai.nama_pegawai,
            presensi.hasil_presensi,
            shift.nama_shift
        ')
            ->join('pegawai', 'pegawai.id = presensi.pegawai_id', 'left')
            ->join('shift', 'shift.id = presensi.shift_id', 'left')
            ->where('presensi.tanggal', $tanggal)
            ->orderBy('presensi.jam_datang', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    //
    public function countByTanggalDanHasilPresensi(string $tanggal, string $hasil): int
    {
        return (int) $this->where([
            'tanggal'         => $tanggal,
            'hasil_presensi' => $hasil,
        ])->countAllResults();
    }

    public function countTelatByTanggal(string $tanggal): int
    {
        return (int) $this->where('tanggal', $tanggal)
            ->where('status_datang', 'telat')
            ->countAllResults();
    }

    public function countPulangCepatByTanggal(string $tanggal): int
    {
        return (int) $this->where('tanggal', $tanggal)
            ->where('status_pulang', 'pulang_cepat')
            ->countAllResults();
    }
}
