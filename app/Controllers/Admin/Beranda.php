<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Beranda extends BaseController
{
    public function __construct() {}

    public function index()
    {
        // dd(session()->get());
        $data = [
            'judul'         => 'Beranda',
        ];
        return view('pages/admin/beranda/index', $data);
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/');
    }
}
