<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $isLogin = session()->get('is_login');
        $role    = session()->get('role');

        if ($isLogin !== true || !$role) {
            return redirect()->to('/')->with('error', 'Silakan login terlebih dahulu.');
        }

        if (empty($arguments)) {
            return redirect()->to('/')->with('error', 'Akses tidak valid.');
        }

        if (!in_array($role, $arguments, true)) {
            return redirect()->to('/')->with('error', 'Anda tidak memiliki akses.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
