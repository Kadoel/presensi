<?php

namespace App\Services\Admin;

use App\Models\CutiModel;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\HTTP\Files\UploadedFile;
use App\Services\Admin\PengajuanIzinService;

class CutiService extends PengajuanIzinService
{
    public function __construct()
    {
        parent::__construct();
        $this->pengajuanIzinModel = new CutiModel();
    }

    public function dataTabel(): BaseBuilder
    {
        return $this->pengajuanIzinModel->selectData();
    }

    public function simpan(array $post, ?UploadedFile $file): array
    {
        $post['jenis'] = 'cuti';

        return parent::simpan($post, $file);
    }

    public function ubah(int $id, array $post, ?UploadedFile $file): array
    {
        $post['edit-jenis'] = 'cuti';

        return parent::ubah($id, $post, $file);
    }
}
