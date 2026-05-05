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
        'slip_token',
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
            penggajian.id,
            penggajian.pegawai_id,
            penggajian.jabatan_id,
            penggajian.bulan,
            penggajian.gaji_pokok,
            penggajian.tunjangan,
            penggajian.gaji_kotor,
            penggajian.total_hadir,
            penggajian.total_izin,
            penggajian.total_sakit,
            penggajian.total_libur,
            penggajian.total_cuti,
            penggajian.total_alpa,
            penggajian.total_menit_telat,
            penggajian.total_menit_pulang_cepat,
            penggajian.potongan_telat,
            penggajian.potongan_pulang_cepat,
            penggajian.potongan_alpa,
            penggajian.total_potongan,
            penggajian.gaji_bersih,
            penggajian.status,
            penggajian.updated_at,
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
        return $this->where('pegawai_id', $pegawaiId)->where('bulan', $bulan)->first();
    }

    public function adaFinalByBulan(string $bulan): bool
    {
        return $this->where('bulan', $bulan)->where('status', 'final')->countAllResults() > 0;
    }

    public function countDraftByBulan(string $bulan): int
    {
        return (int) $this->where('bulan', $bulan)->where('status', 'draft')->countAllResults();
    }

    public function deleteDraftByBulan(string $bulan): bool
    {
        $this->where('bulan', $bulan)->where('status', 'draft')->delete();
        return $this->db->affectedRows() >= 0;
    }

    public function insertBatchPenggajian(array $rows): bool
    {
        return ! empty($rows) && (bool) $this->insertBatch($rows);
    }

    public function finalkanByBulan(string $bulan, ?int $userId): bool
    {
        $rows = $this->where('bulan', $bulan)
            ->where('status', 'draft')
            ->findAll();

        if (empty($rows)) {
            return false;
        }

        foreach ($rows as $row) {
            $this->update($row->id, [
                'status'       => 'final',
                'slip_token'   => bin2hex(random_bytes(32)), // unik tiap row
                'finalized_by' => $userId,
                'finalized_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return true;
    }

    public function getDetailById(int $id): ?object
    {
        return $this->db->table($this->table)
            ->select('
                penggajian.*, 
                pegawai.kode_pegawai, 
                pegawai.nama_pegawai, 
                jabatan.nama_jabatan, 
                creator.username AS created_by_username, 
                finalizer.username AS finalized_by_username
            ')
            ->join('pegawai', 'pegawai.id = penggajian.pegawai_id', 'left')
            ->join('jabatan', 'jabatan.id = penggajian.jabatan_id', 'left')
            ->join('users creator', 'creator.id = penggajian.created_by', 'left')
            ->join('users finalizer', 'finalizer.id = penggajian.finalized_by', 'left')
            ->where('penggajian.id', $id)
            ->get()
            ->getRow();
    }

    public function getRingkasanByBulan(string $bulan): object
    {
        $row = $this->db->table($this->table)
            ->select('
                COUNT(id) AS total_data, COALESCE(SUM(gaji_kotor), 0) AS total_gaji_kotor, 
                COALESCE(SUM(total_potongan), 0) AS total_potongan, 
                COALESCE(SUM(gaji_bersih), 0) AS total_gaji_bersih, 
                SUM(CASE WHEN status = "draft" THEN 1 ELSE 0 END) AS total_draft, 
                SUM(CASE WHEN status = "final" THEN 1 ELSE 0 END) AS total_final
            ')
            ->where('bulan', $bulan)
            ->get()
            ->getRow();

        return (object) [
            'total_data'        => (int) ($row->total_data ?? 0),
            'total_gaji_kotor'  => (float) ($row->total_gaji_kotor ?? 0),
            'total_potongan'    => (float) ($row->total_potongan ?? 0),
            'total_gaji_bersih' => (float) ($row->total_gaji_bersih ?? 0),
            'total_draft'       => (int) ($row->total_draft ?? 0),
            'total_final'       => (int) ($row->total_final ?? 0),
        ];
    }

    public function adaDraftByBulan(string $bulan): bool
    {
        return $this->where('bulan', $bulan)
            ->where('status', 'draft')
            ->countAllResults() > 0;
    }

    public function getExportFinalByBulan(string $bulan): array
    {
        return $this->db->table($this->table)
            ->select('
            penggajian.*,
            pegawai.kode_pegawai,
            pegawai.nama_pegawai,
            jabatan.nama_jabatan
        ')
            ->join('pegawai', 'pegawai.id = penggajian.pegawai_id', 'left')
            ->join('jabatan', 'jabatan.id = penggajian.jabatan_id', 'left')
            ->where('penggajian.bulan', $bulan)
            ->where('penggajian.status', 'final')
            ->orderBy('pegawai.nama_pegawai', 'ASC')
            ->get()
            ->getResult();
    }

    public function getSlipById(int $id): ?object
    {
        return $this->db->table($this->table)
            ->select('
            penggajian.*,
            pegawai.kode_pegawai,
            pegawai.nama_pegawai,
            jabatan.nama_jabatan
        ')
            ->join('pegawai', 'pegawai.id = penggajian.pegawai_id', 'left')
            ->join('jabatan', 'jabatan.id = penggajian.jabatan_id', 'left')
            ->where('penggajian.id', $id)
            ->get()
            ->getRow();
    }

    public function getSlipFinalByBulan(string $bulan): array
    {
        return $this->db->table($this->table)
            ->select('
            penggajian.*,
            pegawai.kode_pegawai,
            pegawai.nama_pegawai,
            jabatan.nama_jabatan
        ')
            ->join('pegawai', 'pegawai.id = penggajian.pegawai_id', 'left')
            ->join('jabatan', 'jabatan.id = penggajian.jabatan_id', 'left')
            ->where('penggajian.bulan', $bulan)
            ->where('penggajian.status', 'final')
            ->orderBy('pegawai.nama_pegawai', 'ASC')
            ->get()
            ->getResult();
    }

    public function generateTokenFinalKosongByBulan(string $bulan): void
    {
        $rows = $this->where('bulan', $bulan)
            ->where('status', 'final')
            ->where('slip_token', null)
            ->findAll();

        foreach ($rows as $row) {
            $this->update($row->id, [
                'slip_token' => bin2hex(random_bytes(32)),
            ]);
        }
    }

    public function getSlipByToken(string $token): ?object
    {
        return $this->db->table($this->table)
            ->select('
            penggajian.*,
            pegawai.kode_pegawai,
            pegawai.nama_pegawai,
            jabatan.nama_jabatan
        ')
            ->join('pegawai', 'pegawai.id = penggajian.pegawai_id', 'left')
            ->join('jabatan', 'jabatan.id = penggajian.jabatan_id', 'left')
            ->where('penggajian.slip_token', $token)
            ->where('penggajian.status', 'final')
            ->get()
            ->getRow();
    }
}
