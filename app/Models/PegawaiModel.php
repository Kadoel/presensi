<?php

namespace App\Models;

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
        'is_active'
    ];
    protected $useTimestamps = true;

    public function selectData()
    {
        return $this->select('
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

    public function getPegawai($id)
    {
        return $this->where([
            'pegawai.id'        => $id,
            'pegawai.is_active' => 1
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

    public function getPegawaiById($id)
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

        return $row?->kode_pegawai;
    }

    public function getPegawaiAktifUntukDropdown(?int $excludeUserId = null)
    {
        $builder = $this->db->table('pegawai')
            ->select('
            pegawai.id,
            pegawai.kode_pegawai,
            pegawai.nama_pegawai
        ')
            ->join('users', 'users.pegawai_id = pegawai.id', 'left')
            ->where('pegawai.is_active', 1);

        if ($excludeUserId !== null) {
            $builder->groupStart()
                ->where('users.id IS NULL')
                ->orWhere('users.id', $excludeUserId) // 🔥 tetap tampilkan milik user ini
                ->groupEnd();
        } else {
            $builder->where('users.id IS NULL');
        }

        return $builder
            ->orderBy('pegawai.nama_pegawai', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * Ambil seluruh foreign key yang mereferensikan tabel pegawai.id
     * Cocok untuk MySQL / MariaDB
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

    /**
     * Hitung total semua relasi yang masih memakai pegawai tertentu
     */
    public function jumlahRelasiYangMemakai(int $id): int
    {
        $jumlah = 0;
        $relasi = $this->getRelasiReferensi();

        foreach ($relasi as $item) {
            $namaTabel = $item['TABLE_NAME'];
            $namaKolom = $item['COLUMN_NAME'];

            $jumlah += (int) $this->db->table($namaTabel)
                ->where($namaKolom, $id)
                ->countAllResults();
        }

        return $jumlah;
    }

    /**
     * Ambil rincian tabel relasi yang masih memakai pegawai tertentu
     * Format hasil sudah siap dipakai untuk membuat pesan
     */
    public function rincianRelasiYangMemakai(int $id): array
    {
        $hasil = [];
        $relasi = $this->getRelasiReferensi();

        foreach ($relasi as $item) {
            $namaTabel = $item['TABLE_NAME'];
            $namaKolom = $item['COLUMN_NAME'];

            $jumlah = (int) $this->db->table($namaTabel)
                ->where($namaKolom, $id)
                ->countAllResults();

            if ($jumlah > 0) {
                $hasil[] = [
                    'tabel'        => $namaTabel,
                    'kolom'        => $namaKolom,
                    'jumlah'       => $jumlah,
                    'label_tabel'  => $this->formatNamaTabel($namaTabel),
                    'label_kolom'  => $this->formatNamaKolom($namaKolom),
                ];
            }
        }

        return $hasil;
    }

    public function dipakaiRelasi(int $id): bool
    {
        return $this->jumlahRelasiYangMemakai($id) > 0;
    }

    /**
     * Ubah nama tabel menjadi label yang lebih manusiawi
     * contoh: jadwal_kerja => Jadwal Kerja
     */
    protected function formatNamaTabel(string $namaTabel): string
    {
        return ucwords(str_replace('_', ' ', $namaTabel));
    }

    /**
     * Ubah nama kolom menjadi label yang lebih manusiawi
     * contoh: pegawai_id => Pegawai Id
     */
    protected function formatNamaKolom(string $namaKolom): string
    {
        return ucwords(str_replace('_', ' ', $namaKolom));
    }

    public function getPegawaiAktifByKode(string $kodePegawai)
    {
        return $this->where('kode_pegawai', $kodePegawai)
            ->where('is_active', 1)
            ->first();
    }
}
