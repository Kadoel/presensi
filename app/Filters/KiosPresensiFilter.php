<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class KiosPresensiFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $isLogin = session()->get('is_login');
        $role    = session()->get('role');

        if ($isLogin === true && $role === 'admin') {
            return null;
        }

        if ($isLogin === true && $role === 'kios') {
            return null;
        }

        if ($isLogin === true && $role === 'pegawai') {
            return redirect()->to('/pegawai')->with('error', 'Anda tidak memiliki akses ke scan presensi.');
        }

        return redirect()->to('/presensi/login');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
