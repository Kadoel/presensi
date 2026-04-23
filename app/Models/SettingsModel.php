<?php

namespace App\Models;

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

    public function selectData()
    {
        return $this->select('
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

    public function getSettings()
    {
        return $this->first(); // karena biasanya cuma 1 row
    }

    public function getValue($field)
    {
        $settings = $this->first();
        return $settings->$field ?? null;
    }
}
