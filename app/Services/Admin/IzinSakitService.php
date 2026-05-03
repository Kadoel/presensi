<?php

namespace App\Services\Admin;

use App\Models\IzinSakitModel;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\HTTP\Files\UploadedFile;

class IzinSakitService extends PengajuanIzinService
{
    public function __construct()
    {
        parent::__construct();
        $this->pengajuanIzinModel = new IzinSakitModel();
    }

    public function dataTabel(): BaseBuilder
    {
        return $this->pengajuanIzinModel->selectData();
    }

    public function simpan(array $post, ?UploadedFile $file): array
    {
        $jenis = (string) ($post['jenis'] ?? '');

        if (! in_array($jenis, ['izin', 'sakit'], true)) {
            return $this->hasilGagal([
                'jenis' => 'Jenis hanya boleh Izin atau Sakit'
            ]);
        }

        return parent::simpan($post, $file);
    }

    public function ubah(int $id, array $post, ?UploadedFile $file): array
    {
        $jenis = (string) ($post['edit-jenis'] ?? '');

        if (! in_array($jenis, ['izin', 'sakit'], true)) {
            return $this->hasilGagal([
                'edit-jenis' => 'Jenis hanya boleh Izin atau Sakit'
            ]);
        }

        return parent::ubah($id, $post, $file);
    }
}
