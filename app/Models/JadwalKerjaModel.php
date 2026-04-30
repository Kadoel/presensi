<?php

namespace App\Models;

use CodeIgniter\Model;

class JadwalKerjaModel extends Model
{
    protected $table      = 'jadwal_kerja';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = [
        'pegawai_id',
        'tanggal',
        'shift_id',
        'status_hari',
        'sumber_data',
        'pengajuan_izin_id',
        'hari_libur_id',
        'shift_id_sebelumnya',
        'status_hari_sebelumnya',
        'catatan_sebelumnya',
        'sumber_data_sebelumnya',
        'catatan',
        'created_by',
    ];

    protected $useTimestamps = true;

    public function selectData(?string $bulan = null)
    {
        $builder = $this->db->table('jadwal_kerja')
            ->select('
            jadwal_kerja.id,
            jadwal_kerja.pegawai_id,
            jadwal_kerja.tanggal,
            jadwal_kerja.shift_id,
            jadwal_kerja.status_hari,
            jadwal_kerja.sumber_data,
            jadwal_kerja.pengajuan_izin_id,
            jadwal_kerja.catatan,
            jadwal_kerja.created_by,
            jadwal_kerja.created_at,
            jadwal_kerja.updated_at,
            pegawai.kode_pegawai,
            pegawai.nama_pegawai,
            shift.kode_shift,
            shift.nama_shift,
            users.username AS dibuat_oleh,
            DATE_FORMAT(jadwal_kerja.tanggal, "%Y-%m") AS bulan_jadwal
        ')
            ->join('pegawai', 'pegawai.id = jadwal_kerja.pegawai_id', 'left')
            ->join('shift', 'shift.id = jadwal_kerja.shift_id', 'left')
            ->join('users', 'users.id = jadwal_kerja.created_by', 'left');

        if (! empty($bulan)) {
            $builder->where('DATE_FORMAT(jadwal_kerja.tanggal, "%Y-%m") =', $bulan);
        }

        return $builder;
    }

    public function getJadwalById(int $id)
    {
        return $this->db->table('jadwal_kerja')
            ->select('
                jadwal_kerja.*,
                pegawai.kode_pegawai,
                pegawai.nama_pegawai,
                shift.kode_shift,
                shift.nama_shift
            ')
            ->join('pegawai', 'pegawai.id = jadwal_kerja.pegawai_id', 'left')
            ->join('shift', 'shift.id = jadwal_kerja.shift_id', 'left')
            ->where('jadwal_kerja.id', $id)
            ->get()
            ->getRow();
    }

    public function getJadwalByPegawaiDanTanggal(int $pegawaiId, string $tanggal): ?object
    {
        return $this->where('pegawai_id', $pegawaiId)
            ->where('tanggal', $tanggal)
            ->first();
    }

    public function getJadwalDetailByPegawaiDanTanggal(int $pegawaiId, string $tanggal): ?object
    {
        return $this->db->table('jadwal_kerja')
            ->select('
                jadwal_kerja.*,
                pegawai.kode_pegawai,
                pegawai.nama_pegawai,
                shift.kode_shift,
                shift.nama_shift,
                shift.jam_masuk,
                shift.jam_pulang,
                shift.toleransi_telat_menit
            ')
            ->join('pegawai', 'pegawai.id = jadwal_kerja.pegawai_id', 'left')
            ->join('shift', 'shift.id = jadwal_kerja.shift_id', 'left')
            ->where('jadwal_kerja.pegawai_id', $pegawaiId)
            ->where('jadwal_kerja.tanggal', $tanggal)
            ->get()
            ->getRow();
    }

    public function getJadwalByPengajuanIzinId(int $pengajuanIzinId): array
    {
        return $this->where('pengajuan_izin_id', $pengajuanIzinId)
            ->findAll();
    }

    public function jumlahBentrokJadwal(int $pegawaiId, string $tanggal, ?int $excludeId = null): int
    {
        $builder = $this->db->table('jadwal_kerja')
            ->where('pegawai_id', $pegawaiId)
            ->where('tanggal', $tanggal);

        if ($excludeId !== null) {
            $builder->where('id !=', $excludeId);
        }

        return (int) $builder->countAllResults();
    }

    public function getJadwalKerjaAktifPadaTanggalUntukLibur(string $tanggal): array
    {
        return $this->db->table('jadwal_kerja')
            ->select('
                jadwal_kerja.id,
                jadwal_kerja.pegawai_id,
                jadwal_kerja.tanggal,
                jadwal_kerja.shift_id,
                jadwal_kerja.status_hari,
                jadwal_kerja.sumber_data,
                jadwal_kerja.hari_libur_id,
                jadwal_kerja.catatan,
                pegawai.kode_pegawai,
                pegawai.nama_pegawai,
                shift.nama_shift
            ')
            ->join('pegawai', 'pegawai.id = jadwal_kerja.pegawai_id', 'left')
            ->join('shift', 'shift.id = jadwal_kerja.shift_id', 'left')
            ->where('jadwal_kerja.tanggal', $tanggal)
            ->where('jadwal_kerja.status_hari', 'kerja')
            ->get()
            ->getResult();
    }

    public function getJadwalByHariLiburId(int $hariLiburId): array
    {
        return $this->where('hari_libur_id', $hariLiburId)->findAll();
    }

    public function getBatasAkhirPulangTerakhir(string $tanggal): ?string
    {
        $row = $this->db->table('jadwal_kerja')
            ->select('MAX(shift.batas_akhir_pulang) as batas_akhir_pulang')
            ->join('shift', 'shift.id = jadwal_kerja.shift_id', 'left')
            ->where('jadwal_kerja.tanggal', $tanggal)
            ->where('jadwal_kerja.status_hari', 'kerja')
            ->where('jadwal_kerja.shift_id IS NOT NULL')
            ->get()
            ->getRow();

        return $row->batas_akhir_pulang ?? null;
    }

    public function jumlahJadwalPadaTanggal(string $tanggal): int
    {
        return (int) $this->where('tanggal', $tanggal)->countAllResults();
    }

    public function getJadwalPegawaiDalamRentang(int $pegawaiId, string $tanggalMulai, string $tanggalSelesai): array
    {
        return $this->where('pegawai_id', $pegawaiId)
            ->where('tanggal >=', $tanggalMulai)
            ->where('tanggal <=', $tanggalSelesai)
            ->orderBy('tanggal', 'ASC')
            ->findAll();
    }

    public function countStatusKerjaByTanggal($tanggal)
    {
        return $this
            ->where('tanggal', $tanggal)
            ->where('status_hari', 'kerja')
            ->countAllResults();
    }

    public function jumlahJadwalSelainPegawaiPadaTanggal(int $pegawaiId, string $tanggal): int
    {
        return (int) $this->where('tanggal', $tanggal)
            ->where('pegawai_id !=', $pegawaiId)
            ->countAllResults();
    }

    public function countByPegawaiDanBulan(int $pegawaiId, string $bulan): int
    {
        return (int) $this->where('pegawai_id', $pegawaiId)
            ->where('DATE_FORMAT(tanggal, "%Y-%m") =', $bulan)
            ->countAllResults();
    }

    public function getKalenderRingkasan(string $start, string $end): array
    {
        return $this->db->table($this->table)
            ->select('
            tanggal,
            COUNT(*) AS total_jadwal,
            SUM(CASE WHEN status_hari = "kerja" THEN 1 ELSE 0 END) AS total_kerja,
            SUM(CASE WHEN status_hari = "libur" THEN 1 ELSE 0 END) AS total_libur,
            SUM(CASE WHEN status_hari = "izin" THEN 1 ELSE 0 END) AS total_izin,
            SUM(CASE WHEN status_hari = "sakit" THEN 1 ELSE 0 END) AS total_sakit
        ')
            ->where('tanggal >=', $start)
            ->where('tanggal <=', $end)
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'ASC')
            ->get()
            ->getResult();
    }

    public function getDetailByTanggal(string $tanggal): array
    {
        return $this->db->table($this->table)
            ->select('
            jadwal_kerja.id,
            jadwal_kerja.tanggal,
            jadwal_kerja.status_hari,
            jadwal_kerja.sumber_data,
            jadwal_kerja.catatan,
            pegawai.kode_pegawai,
            pegawai.nama_pegawai,
            shift.nama_shift
        ')
            ->join('pegawai', 'pegawai.id = jadwal_kerja.pegawai_id', 'left')
            ->join('shift', 'shift.id = jadwal_kerja.shift_id', 'left')
            ->where('jadwal_kerja.tanggal', $tanggal)
            ->orderBy('pegawai.nama_pegawai', 'ASC')
            ->get()
            ->getResult();
    }

    public function countStatusHariIni(string $tanggal, string $status): int
    {
        return (int) $this
            ->where('tanggal', $tanggal)
            ->where('status_hari', $status)
            ->countAllResults();
    }

    public function countJadwalHariIni(string $tanggal): int
    {
        return (int) $this
            ->where('tanggal', $tanggal)
            ->countAllResults();
    }

    public function getGrafikJadwalMingguan(string $tanggalMulai, string $tanggalSelesai): array
    {
        return $this->db->table($this->table)
            ->select('tanggal, COUNT(*) AS total_jadwal')
            ->where('tanggal >=', $tanggalMulai)
            ->where('tanggal <=', $tanggalSelesai)
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'ASC')
            ->get()
            ->getResult();
    }

    public function getJadwalKalenderPegawai(int $pegawaiId, string $start, string $end): array
    {
        return $this->db->table($this->table)
            ->select('
            jadwal_kerja.id,
            jadwal_kerja.pegawai_id,
            jadwal_kerja.shift_id,
            jadwal_kerja.tanggal,
            jadwal_kerja.status_hari,
            jadwal_kerja.sumber_data,
            jadwal_kerja.catatan,
            shift.kode_shift,
            shift.nama_shift,
            shift.jam_masuk,
            shift.jam_pulang
        ')
            ->join('shift', 'shift.id = jadwal_kerja.shift_id', 'left')
            ->where('jadwal_kerja.pegawai_id', $pegawaiId)
            ->where('jadwal_kerja.tanggal >=', $start)
            ->where('jadwal_kerja.tanggal <=', $end)
            ->orderBy('jadwal_kerja.tanggal', 'ASC')
            ->get()
            ->getResult();
    }

    public function getSlotTukarJadwalPegawai(int $pegawaiId): array
    {
        return $this->db->table($this->table)
            ->select('
            jadwal_kerja.id,
            jadwal_kerja.pegawai_id,
            jadwal_kerja.tanggal,
            jadwal_kerja.shift_id,
            jadwal_kerja.status_hari,
            jadwal_kerja.sumber_data,
            shift.kode_shift,
            shift.nama_shift,
            shift.jam_masuk,
            shift.jam_pulang
        ')
            ->join('shift', 'shift.id = jadwal_kerja.shift_id', 'left')
            ->where('jadwal_kerja.pegawai_id', $pegawaiId)
            ->where('jadwal_kerja.tanggal >=', date('Y-m-d'))
            ->where('jadwal_kerja.status_hari', 'kerja')
            ->where('jadwal_kerja.sumber_data', 'manual')
            ->where('jadwal_kerja.shift_id IS NOT NULL', null, false)
            ->orderBy('jadwal_kerja.tanggal', 'ASC')
            ->get()
            ->getResult();
    }
}
