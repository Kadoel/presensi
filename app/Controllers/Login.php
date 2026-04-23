<?php

namespace App\Controllers;

use App\Models\UsersModel;

class Login extends BaseController
{
    protected $users_model;

    public function __construct()
    {
        $this->users_model = new UsersModel();
    }

    public function index()
    {
        if (session()->get('is_login') === true) {
            $redirect = '/';

            if (session()->get('role') === 'admin') {
                $redirect = '/admin';
            } elseif (session()->get('role') === 'pegawai') {
                $redirect = '/pegawai';
            }

            return redirect()->to($redirect);
        }

        return view('pages/login/index', [
            'validasi' => $this->validasi
        ]);
    }

    public function auth()
    {
        if (! $this->validate([
            'username' => [
                'label'  => 'Username',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} Harus Diisi'
                ]
            ],
            'password' => [
                'label'  => 'Password',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} Harus Diisi'
                ]
            ]
        ])) {
            return redirect()->to('/')->withInput();
        }

        $usernameInput = trim((string) $this->request->getVar('username'));
        $passwordInput = (string) $this->request->getVar('password');

        $auth = $this->users_model->getAuth($usernameInput);

        if ($auth === null) {
            return redirect()->to('/')
                ->withInput()
                ->with('gagal-login', 'Username atau Password Salah');
        }

        if (! password_verify($passwordInput, $auth->password_hash)) {
            return redirect()->to('/')
                ->withInput()
                ->with('gagal-login', 'Username atau Password Salah');
        }

        // Khusus role pegawai, wajib lolos pengecekan data pegawai aktif
        if ($auth->role === 'pegawai') {
            if (empty($auth->pegawai_id)) {
                return redirect()->to('/')
                    ->withInput()
                    ->with('gagal-login', 'Akun pegawai belum terhubung ke data pegawai');
            }

            if (empty($auth->pegawai_row_id)) {
                return redirect()->to('/')
                    ->withInput()
                    ->with('gagal-login', 'Data pegawai tidak ditemukan');
            }

            if ((int) $auth->pegawai_is_active !== 1) {
                return redirect()->to('/')
                    ->withInput()
                    ->with('gagal-login', 'Data pegawai nonaktif');
            }
        }

        $this->users_model->updateLastLogin($auth->id);

        $sesi_user = [
            'is_login'      => true,
            'user_id'       => $auth->id,
            'pegawai_id'    => $auth->pegawai_id,
            'username'      => $auth->username,
            'role'          => $auth->role,
            'last_login_at' => $auth->last_login_at,

            // data tambahan pegawai
            'kode_pegawai'  => $auth->kode_pegawai ?? null,
            'nama_pegawai'  => $auth->nama_pegawai ?? null,
            'jabatan_id'    => $auth->jabatan_id ?? null,
            'foto'          => $auth->foto ?? null,
        ];

        session()->set($sesi_user);

        $redirect = '/';

        if ($auth->role === 'admin') {
            $redirect = '/admin';
        } elseif ($auth->role === 'pegawai') {
            $redirect = '/pegawai';
        }

        return redirect()->to($redirect);
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to('/')->with('sukses', 'Berhasil logout');
    }
}
