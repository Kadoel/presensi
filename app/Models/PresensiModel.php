<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
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
        'selfie_datang_drive_id',
        'selfie_datang_drive_url',
        'selfie_datang_upload_status',
        'selfie_datang_upload_error',
        'selfie_pulang_drive_id',
        'selfie_pulang_drive_url',
        'selfie_pulang_upload_status',
        'selfie_pulang_upload_error',
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

    public function dataTabel(?string $tanggal = null): BaseBuilder
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
                shift.jam_masuk AS jam_masuk_shift,
                shift.jam_pulang AS jam_pulang_shift,
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
        return (int) $this->where('tanggal', $tanggal)
            ->countAllResults();
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
        return $this->db->table($this->table)
            ->select('
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
            ->get()
            ->getResult();
    }

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

    public function countByTanggalDanHasilPresensiNull(string $tanggal): int
    {
        return (int) $this->where('tanggal', $tanggal)
            ->where('hasil_presensi', null)
            ->countAllResults();
    }

    public function getPresensiPegawaiDalamRentang(int $pegawaiId, string $tanggalMulai, string $tanggalSelesai): array
    {
        return $this->where('pegawai_id', $pegawaiId)
            ->where('tanggal >=', $tanggalMulai)
            ->where('tanggal <=', $tanggalSelesai)
            ->orderBy('tanggal', 'ASC')
            ->findAll();
    }

    public function countByPegawaiDanBulan(int $pegawaiId, string $bulan): int
    {
        return (int) $this->where('pegawai_id', $pegawaiId)
            ->where('DATE_FORMAT(tanggal, "%Y-%m") =', $bulan)
            ->countAllResults();
    }

    public function countSudahSinkronByTanggal(string $tanggal): int
    {
        return (int) $this->where('tanggal', $tanggal)
            ->where('hasil_presensi IS NOT NULL', null, false)
            ->countAllResults();
    }

    public function getGrafikPresensiMingguan(string $tanggalMulai, string $tanggalSelesai): array
    {
        return $this->db->table($this->table)
            ->select('tanggal, COUNT(*) AS total_presensi')
            ->where('tanggal >=', $tanggalMulai)
            ->where('tanggal <=', $tanggalSelesai)
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'ASC')
            ->get()
            ->getResult();
    }

    public function getGrafikHasilPresensiBulanan(string $bulan): array
    {
        return $this->db->table($this->table)
            ->select('
                hasil_presensi,
                COUNT(*) AS total
            ')
            ->where('DATE_FORMAT(tanggal, "%Y-%m") =', $bulan)
            ->where('hasil_presensi IS NOT NULL', null, false)
            ->groupBy('hasil_presensi')
            ->get()
            ->getResult();
    }

    public function countByPegawaiBulanDanHasil(int $pegawaiId, string $bulan, string $hasil): int
    {
        return (int) $this->where('pegawai_id', $pegawaiId)
            ->where('DATE_FORMAT(tanggal, "%Y-%m") =', $bulan)
            ->where('hasil_presensi', $hasil)
            ->countAllResults();
    }

    public function countByPegawaiBulanDanStatusDatang(int $pegawaiId, string $bulan, string $status): int
    {
        return (int) $this->where('pegawai_id', $pegawaiId)
            ->where('DATE_FORMAT(tanggal, "%Y-%m") =', $bulan)
            ->where('status_datang', $status)
            ->countAllResults();
    }

    public function countByPegawaiBulanDanStatusPulang(int $pegawaiId, string $bulan, string $status): int
    {
        return (int) $this->where('pegawai_id', $pegawaiId)
            ->where('DATE_FORMAT(tanggal, "%Y-%m") =', $bulan)
            ->where('status_pulang', $status)
            ->countAllResults();
    }

    public function getRiwayatByPegawai(int $pegawaiId, int $limit = 5): array
    {
        return $this->db->table($this->table)
            ->select('
                presensi.id,
                presensi.tanggal,
                presensi.jam_datang,
                presensi.jam_pulang,
                presensi.status_datang,
                presensi.status_pulang,
                presensi.hasil_presensi,
                shift.nama_shift
            ')
            ->join('shift', 'shift.id = presensi.shift_id', 'left')
            ->where('presensi.pegawai_id', $pegawaiId)
            ->orderBy('presensi.tanggal', 'DESC')
            ->limit($limit)
            ->get()
            ->getResult();
    }

    public function dataRekapBulanan(?string $bulan = null): BaseBuilder
    {
        $bulan = $bulan ?: date('Y-m');

        return $this->db->table('pegawai')
            ->select("
                pegawai.id,
                pegawai.nama_pegawai,
                COALESCE(SUM(CASE WHEN presensi.hasil_presensi = 'hadir' THEN 1 ELSE 0 END), 0) AS hadir,
                COALESCE(SUM(CASE WHEN presensi.hasil_presensi = 'izin' THEN 1 ELSE 0 END), 0) AS izin,
                COALESCE(SUM(CASE WHEN presensi.hasil_presensi = 'sakit' THEN 1 ELSE 0 END), 0) AS sakit,
                COALESCE(SUM(CASE WHEN presensi.hasil_presensi = 'libur' THEN 1 ELSE 0 END), 0) AS libur,
                COALESCE(SUM(CASE WHEN presensi.hasil_presensi = 'cuti' THEN 1 ELSE 0 END), 0) AS cuti,
                COALESCE(SUM(CASE WHEN presensi.hasil_presensi = 'alpa' THEN 1 ELSE 0 END), 0) AS alpa
            ")
            ->join(
                'presensi',
                'presensi.pegawai_id = pegawai.id 
                AND DATE_FORMAT(presensi.tanggal, "%Y-%m") = ' . $this->db->escape($bulan),
                'left'
            )
            ->where('pegawai.is_active', 1)
            ->groupBy('pegawai.id, pegawai.nama_pegawai');
    }

    public function countBelumSinkronSebelumTanggal(string $tanggal): int
    {
        return (int) $this->where('tanggal <', $tanggal)
            ->where('hasil_presensi', null)
            ->countAllResults();
    }

    public function getTanggalBelumSinkronSebelumHariIni(string $tanggal): array
    {
        return $this->db->table($this->table)
            ->select('tanggal')
            ->where('tanggal <', $tanggal)
            ->where('hasil_presensi', null)
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'ASC')
            ->get()
            ->getResult();
    }

    public function getRiwayatKalenderPegawai(int $pegawaiId, string $start, string $end): array
    {
        return $this->db->table($this->table)
            ->select('
                presensi.id,
                presensi.pegawai_id,
                presensi.tanggal,
                presensi.jadwal_kerja_id,
                presensi.shift_id,
                presensi.jam_datang,
                presensi.jam_pulang,
                presensi.status_datang,
                presensi.status_pulang,
                presensi.menit_telat,
                presensi.menit_pulang_cepat,
                presensi.hasil_presensi,
                presensi.sumber_presensi,
                presensi.catatan_admin,
                presensi.is_manual,
                shift.kode_shift,
                shift.nama_shift,
                shift.jam_masuk,
                shift.jam_pulang AS jam_pulang_shift
            ')
            ->join('shift', 'shift.id = presensi.shift_id', 'left')
            ->where('presensi.pegawai_id', $pegawaiId)
            ->where('presensi.tanggal >=', $start)
            ->where('presensi.tanggal <=', $end)
            ->orderBy('presensi.tanggal', 'ASC')
            ->get()
            ->getResult();
    }

    public function getExportBulanan(string $bulan): array
    {
        return $this->db->table($this->table)
            ->select('
            presensi.tanggal,
            pegawai.kode_pegawai,
            pegawai.nama_pegawai,
            shift.nama_shift,
            presensi.jam_datang,
            presensi.jam_pulang,
            presensi.status_datang,
            presensi.status_pulang,
            presensi.menit_telat,
            presensi.menit_pulang_cepat,
            presensi.hasil_presensi,
            presensi.sumber_presensi,
            presensi.catatan_admin
        ')
            ->join('pegawai', 'pegawai.id = presensi.pegawai_id', 'left')
            ->join('shift', 'shift.id = presensi.shift_id', 'left')
            ->where('DATE_FORMAT(presensi.tanggal, "%Y-%m") =', $bulan)
            ->where('presensi.hasil_presensi IS NOT NULL', null, false)
            ->orderBy('presensi.tanggal', 'ASC')
            ->orderBy('pegawai.nama_pegawai', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * Tambahkan method ini ke app/Models/PresensiModel.php
     */
    public function getRekapPenggajianPegawai(int $pegawaiId, string $bulan): ?object
    {
        return $this->db->table($this->table)
            ->select('
                COALESCE(SUM(CASE WHEN hasil_presensi = "hadir" THEN 1 ELSE 0 END), 0) AS total_hadir, 
                COALESCE(SUM(CASE WHEN hasil_presensi = "izin" THEN 1 ELSE 0 END), 0) AS total_izin, 
                COALESCE(SUM(CASE WHEN hasil_presensi = "sakit" THEN 1 ELSE 0 END), 0) AS total_sakit, 
                COALESCE(SUM(CASE WHEN hasil_presensi = "libur" THEN 1 ELSE 0 END), 0) AS total_libur, 
                COALESCE(SUM(CASE WHEN hasil_presensi = "cuti" THEN 1 ELSE 0 END), 0) AS total_cuti, 
                COALESCE(SUM(CASE WHEN hasil_presensi = "alpa" THEN 1 ELSE 0 END), 0) AS total_alpa, 
                COALESCE(SUM(menit_telat), 0) AS total_menit_telat, 
                COALESCE(SUM(menit_pulang_cepat), 0) AS total_menit_pulang_cepat
            ')
            ->where('pegawai_id', $pegawaiId)
            ->where('DATE_FORMAT(tanggal, "%Y-%m") =', $bulan)
            ->where('hasil_presensi IS NOT NULL', null, false)
            ->get()
            ->getRow();
    }

    public function simpanDatangScan(
        int $pegawaiId,
        string $tanggal,
        int $jadwalKerjaId,
        ?int $shiftId,
        string $jamDatang,
        string $statusDatang,
        int $menitTelat,
        ?string $selfieDatang,
        string $barcodeDatang,
        ?string $ipAddress,
        ?string $userAgent,
        ?string $driveId,
        ?string $driveUrl,
        ?string $uploadStatus,
        ?string $uploadError,
    ): bool {
        return (bool) $this->insert([
            'pegawai_id'         => $pegawaiId,
            'tanggal'            => $tanggal,
            'jadwal_kerja_id'    => $jadwalKerjaId,
            'shift_id'           => $shiftId,
            'jam_datang'         => $jamDatang,
            'jam_pulang'         => null,
            'status_datang'      => $statusDatang,
            'status_pulang'      => null,
            'hasil_presensi'     => null,
            'menit_telat'        => $menitTelat,
            'menit_pulang_cepat' => 0,
            'selfie_datang'      => $selfieDatang,
            'selfie_pulang'      => null,
            'barcode_datang'     => $barcodeDatang,
            'barcode_pulang'     => null,
            'ip_address'         => $ipAddress,
            'user_agent'         => $userAgent,
            'catatan_admin'      => null,
            'is_manual'          => 0,
            'selfie_datang_drive_id'    => $driveId,
            'selfie_datang_drive_url'   => $driveUrl,
            'selfie_datang_upload_status'  => $uploadStatus,
            'selfie_datang_upload_error'   => $uploadError,
            'sumber_presensi'    => 'scan',
        ]);
    }

    public function simpanPulangScan(
        int $id,
        string $jamPulang,
        string $statusPulang,
        int $menitPulangCepat,
        ?string $selfiePulang,
        string $barcodePulang,
        ?string $ipAddress,
        ?string $userAgent,
        ?string $driveId,
        ?string $driveUrl,
        ?string $uploadStatus,
        ?string $uploadError,
    ): bool {
        return (bool) $this->update($id, [
            'jam_pulang'           => $jamPulang,
            'status_pulang'        => $statusPulang,
            'menit_pulang_cepat'   => $menitPulangCepat,
            'selfie_pulang'        => $selfiePulang,
            'barcode_pulang'       => $barcodePulang,
            'selfie_pulang_drive_id'  => $driveId,
            'selfie_pulang_drive_url' => $driveUrl,
            'selfie_pulang_upload_status'  => $uploadStatus,
            'selfie_pulang_upload_error'   => $uploadError,
            'ip_address'           => $ipAddress,
            'user_agent'           => $userAgent,
        ]);
    }
}
