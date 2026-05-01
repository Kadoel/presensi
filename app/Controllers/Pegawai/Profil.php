<?php

namespace App\Controllers\Pegawai;

use App\Controllers\BaseController;
use App\Services\Pegawai\ProfilService;

class Profil extends BaseController
{
    protected ProfilService $profilService;

    public function __construct()
    {
        $this->profilService = new ProfilService();
    }

    public function index()
    {
        return view('pages/pegawai/profil/index', [
            'judul'   => 'Profil Saya',
            'caption' => 'Detail data pribadi saya',
        ]);
    }

    public function data()
    {
        return $this->response->setJSON($this->profilService->getProfil());
    }

    public function update()
    {
        return $this->response->setJSON(
            $this->profilService->update(
                $this->request->getPost(),
                $this->request->getFile('foto')
            )
        );
    }
}
