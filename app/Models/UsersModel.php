<?php

namespace App\Models;

use CodeIgniter\Model;

class UsersModel extends Model
{
    protected $table         = 'users';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $allowedFields = [
        'id',
        'pegawai_id',
        'username',
        'password_hash',
        'role',
        'is_active',
        'last_login_at'
    ];
    protected $useTimestamps = true;

    public function selectData()
    {
        return $this->db->table('users')
            ->select('
                users.id,
                users.pegawai_id,
                users.username,
                users.role,
                users.is_active,
                users.last_login_at,
                pegawai.kode_pegawai,
                pegawai.nama_pegawai,
                jabatan.nama_jabatan
            ')
            ->join('pegawai', 'pegawai.id = users.pegawai_id', 'left')
            ->join('jabatan', 'jabatan.id = pegawai.jabatan_id', 'left');
    }

    public function selectAuth()
    {
        return $this->db->table('users')
            ->select('
                users.id,
                users.pegawai_id,
                users.username,
                users.password_hash,
                users.role,
                users.is_active,
                users.last_login_at,

                pegawai.id AS pegawai_row_id,
                pegawai.kode_pegawai,
                pegawai.nama_pegawai,
                pegawai.jabatan_id,
                pegawai.foto,
                pegawai.is_active AS pegawai_is_active
            ')
            ->join('pegawai', 'pegawai.id = users.pegawai_id', 'left');
    }

    public function getAuth($username)
    {
        return $this->selectAuth()
            ->where('users.username', $username)
            ->where('users.is_active', 1)
            ->get()
            ->getRow();
    }

    public function updateLastLogin($id)
    {
        return $this->update($id, [
            'last_login_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function getUserById(int $id)
    {
        return $this->db->table('users')
            ->select('
                users.id,
                users.pegawai_id,
                users.username,
                users.role,
                users.is_active,
                users.last_login_at,
                pegawai.kode_pegawai,
                pegawai.nama_pegawai
            ')
            ->join('pegawai', 'pegawai.id = users.pegawai_id', 'left')
            ->where('users.id', $id)
            ->get()
            ->getRow();
    }
}
