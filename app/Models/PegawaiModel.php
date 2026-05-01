<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;

class PegawaiModel extends Model
{
    protected $table         = 'pegawai';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';

    protected $allowedFields = [
        'kode_pegawai',
        'nama_pegawai',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'no_hp',
        'alamat',
        'jabatan_id',
        'foto',
        'qrcode',
        'is_active',
    ];

    protected $useTimestamps = true;

    public function selectData(): BaseBuilder
    {
        return $this->db->table($this->table)
            ->select('
            pegawai.id,
            pegawai.kode_pegawai,
            pegawai.nama_pegawai,
            pegawai.jenis_kelamin,
            pegawai.tempat_lahir,
            pegawai.tanggal_lahir,
            pegawai.no_hp,
            pegawai.alamat,
            pegawai.jabatan_id,
            pegawai.foto,
            pegawai.qrcode,
            pegawai.is_active,
            jabatan.nama_jabatan,
            pegawai.created_at,
            pegawai.updated_at
        ')
            ->join('jabatan', 'jabatan.id = pegawai.jabatan_id', 'left');
    }

    public function getPegawai(int $id): ?object
    {
        return $this->where([
            'pegawai.id'        => $id,
            'pegawai.is_active' => 1,
        ])
            ->select('
                pegawai.id,
                pegawai.kode_pegawai,
                pegawai.nama_pegawai,
                pegawai.jenis_kelamin,
                pegawai.tempat_lahir,
                pegawai.tanggal_lahir,
                pegawai.no_hp,
                pegawai.alamat,
                pegawai.jabatan_id,
                pegawai.foto,
                pegawai.qrcode,
                pegawai.is_active,
                jabatan.nama_jabatan
            ')
            ->join('jabatan', 'jabatan.id = pegawai.jabatan_id', 'left')
            ->first();
    }

    public function getPegawaiById(int $id): ?object
    {
        return $this->where('pegawai.id', $id)
            ->select('
                pegawai.id,
                pegawai.kode_pegawai,
                pegawai.nama_pegawai,
                pegawai.jenis_kelamin,
                pegawai.tempat_lahir,
                pegawai.tanggal_lahir,
                pegawai.no_hp,
                pegawai.alamat,
                pegawai.jabatan_id,
                pegawai.foto,
                pegawai.qrcode,
                pegawai.is_active,
                jabatan.nama_jabatan
            ')
            ->join('jabatan', 'jabatan.id = pegawai.jabatan_id', 'left')
            ->first();
    }

    public function getLastKodePegawai(): ?string
    {
        $row = $this->select('kode_pegawai')
            ->like('kode_pegawai', 'PGW-', 'after')
            ->orderBy('id', 'DESC')
            ->first();

        return $row->kode_pegawai ?? null;
    }

    public function getPegawaiAktifUntukDropdown(?int $excludeUserId = null): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                pegawai.id,
                pegawai.kode_pegawai,
                pegawai.nama_pegawai
            ')
            ->join('users', 'users.pegawai_id = pegawai.id', 'left')
            ->where('pegawai.is_active', 1);

        if ($excludeUserId !== null) {
            $builder->groupStart()
                ->where('users.id IS NULL', null, false)
                ->orWhere('users.id', $excludeUserId)
                ->groupEnd();
        } else {
            $builder->where('users.id IS NULL', null, false);
        }

        return $builder
            ->orderBy('pegawai.nama_pegawai', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * Ambil seluruh foreign key yang mereferensikan tabel pegawai.id.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getRelasiReferensi(): array
    {
        $dbName = $this->db->getDatabase();
        $prefix = $this->db->getPrefix();
        $namaTabelPegawai = $prefix . $this->table;

        return $this->db->table('information_schema.KEY_COLUMN_USAGE')
            ->select([
                'TABLE_NAME',
                'COLUMN_NAME',
                'CONSTRAINT_NAME',
                'REFERENCED_TABLE_NAME',
                'REFERENCED_COLUMN_NAME',
            ])
            ->where('TABLE_SCHEMA', $dbName)
            ->where('REFERENCED_TABLE_NAME', $namaTabelPegawai)
            ->where('REFERENCED_COLUMN_NAME', 'id')
            ->orderBy('TABLE_NAME', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function jumlahRelasiYangMemakai(int $id): int
    {
        $jumlah = 0;
        $relasi = $this->getRelasiReferensi();

        foreach ($relasi as $item) {
            $namaTabel = (string) ($item['TABLE_NAME'] ?? '');
            $namaKolom = (string) ($item['COLUMN_NAME'] ?? '');

            if ($namaTabel === '' || $namaKolom === '') {
                continue;
            }

            $jumlah += (int) $this->db->table($namaTabel)
                ->where($namaKolom, $id)
                ->countAllResults();
        }

        return $jumlah;
    }

    /**
     * @return array<int, array{
     *     tabel: string,
     *     kolom: string,
     *     jumlah: int,
     *     label_tabel: string,
     *     label_kolom: string
     * }>
     */
    public function rincianRelasiYangMemakai(int $id): array
    {
        $hasil = [];
        $relasi = $this->getRelasiReferensi();

        foreach ($relasi as $item) {
            $namaTabel = (string) ($item['TABLE_NAME'] ?? '');
            $namaKolom = (string) ($item['COLUMN_NAME'] ?? '');

            if ($namaTabel === '' || $namaKolom === '') {
                continue;
            }

            $jumlah = (int) $this->db->table($namaTabel)
                ->where($namaKolom, $id)
                ->countAllResults();

            if ($jumlah > 0) {
                $hasil[] = [
                    'tabel'       => $namaTabel,
                    'kolom'       => $namaKolom,
                    'jumlah'      => $jumlah,
                    'label_tabel' => $this->formatNamaTabel($namaTabel),
                    'label_kolom' => $this->formatNamaKolom($namaKolom),
                ];
            }
        }

        return $hasil;
    }

    public function dipakaiRelasi(int $id): bool
    {
        return $this->jumlahRelasiYangMemakai($id) > 0;
    }

    protected function formatNamaTabel(string $namaTabel): string
    {
        return ucwords(str_replace('_', ' ', $namaTabel));
    }

    protected function formatNamaKolom(string $namaKolom): string
    {
        return ucwords(str_replace('_', ' ', $namaKolom));
    }

    public function getPegawaiAktifByKode(string $kodePegawai): ?object
    {
        return $this->where('kode_pegawai', $kodePegawai)
            ->where('is_active', 1)
            ->first();
    }

    public function countAktif(): int
    {
        return (int) $this->where('is_active', 1)->countAllResults();
    }

    public function getPegawaiDropdown(): array
    {
        return $this->db->table($this->table)
            ->select('pegawai.id, pegawai.kode_pegawai, pegawai.nama_pegawai')
            ->where('pegawai.is_active', 1)
            ->orderBy('pegawai.nama_pegawai', 'ASC')
            ->get()
            ->getResult();
    }
}
