<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;

class SettingsModel extends Model
{
    protected $table         = 'settings';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';

    protected $allowedFields = [
        'id',
        'nama_usaha',
        'alamat',
        'telepon',
        'email',
        'logo',
        'wajib_selfie',
        'wajib_barcode',
        'prefix_kode_pegawai',
        'panjang_nomor_pegawai'
    ];

    protected $useTimestamps = true;

    public function selectData(): BaseBuilder
    {
        return $this->db->table($this->table)
            ->select('
                id,
                nama_usaha,
                alamat,
                telepon,
                email,
                logo,
                wajib_selfie,
                wajib_barcode,
                prefix_kode_pegawai,
                panjang_nomor_pegawai
            ');
    }

    public function getSettings(): ?object
    {
        return $this->first(); // biasanya hanya 1 row
    }

    public function getValue(string $field): mixed
    {
        $settings = $this->first();

        if (! $settings) {
            return null;
        }

        return $settings->{$field} ?? null;
    }
}
