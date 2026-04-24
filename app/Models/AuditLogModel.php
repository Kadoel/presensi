<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
    protected $table         = 'audit_logs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $allowedFields = [
        'user_id',
        'action',
        'table_name',
        'row_id',
        'description',
        'ip_address',
        'user_agent',
        'created_at',
    ];
    protected $useTimestamps = false;

    public function selectData(array $filter = [])
    {
        $builder = $this->db->table('audit_logs')
            ->select('
                audit_logs.id,
                audit_logs.user_id,
                audit_logs.action,
                audit_logs.table_name,
                audit_logs.row_id,
                audit_logs.description,
                audit_logs.ip_address,
                audit_logs.user_agent,
                audit_logs.created_at,
                users.username,
                users.username AS user_nama
            ')
            ->join('users', 'users.id = audit_logs.user_id', 'left');

        if (! empty($filter['user_id'])) {
            $builder->where('audit_logs.user_id', (int) $filter['user_id']);
        }

        if (! empty($filter['action'])) {
            $builder->where('audit_logs.action', trim((string) $filter['action']));
        }

        if (! empty($filter['table_name'])) {
            $builder->where('audit_logs.table_name', trim((string) $filter['table_name']));
        }

        if (! empty($filter['tanggal_awal'])) {
            $builder->where('DATE(audit_logs.created_at) >=', $filter['tanggal_awal']);
        }

        if (! empty($filter['tanggal_akhir'])) {
            $builder->where('DATE(audit_logs.created_at) <=', $filter['tanggal_akhir']);
        }

        return $builder;
    }

    public function getAuditLogById(int $id)
    {
        return $this->db->table('audit_logs')
            ->select('
                audit_logs.id,
                audit_logs.user_id,
                audit_logs.action,
                audit_logs.table_name,
                audit_logs.row_id,
                audit_logs.description,
                audit_logs.ip_address,
                audit_logs.user_agent,
                audit_logs.created_at,
                users.username
            ')
            ->join('users', 'users.id = audit_logs.user_id', 'left')
            ->where('audit_logs.id', $id)
            ->get()
            ->getRow();
    }

    public function getUserFilter()
    {
        return $this->db->table('audit_logs')
            ->select('audit_logs.user_id, users.username')
            ->join('users', 'users.id = audit_logs.user_id', 'left')
            ->where('audit_logs.user_id IS NOT NULL')
            ->groupBy('audit_logs.user_id, users.username')
            ->orderBy('users.username', 'ASC')
            ->get()
            ->getResult();
    }

    public function getActionFilter()
    {
        return $this->db->table('audit_logs')
            ->select('action')
            ->where('action IS NOT NULL')
            ->where('action !=', '')
            ->groupBy('action')
            ->orderBy('action', 'ASC')
            ->get()
            ->getResult();
    }

    public function getTableFilter()
    {
        return $this->db->table('audit_logs')
            ->select('table_name')
            ->where('table_name IS NOT NULL')
            ->where('table_name !=', '')
            ->groupBy('table_name')
            ->orderBy('table_name', 'ASC')
            ->get()
            ->getResult();
    }

    public function getTimelineTerbaru(int $limit = 10)
    {
        $rows = $this->db->table('audit_logs')
            ->select('
            audit_logs.id,
            audit_logs.user_id,
            audit_logs.action,
            audit_logs.table_name,
            audit_logs.row_id,
            audit_logs.description,
            audit_logs.ip_address,
            audit_logs.created_at,
            users.username
        ')
            ->join('users', 'users.id = audit_logs.user_id', 'left')
            ->orderBy('audit_logs.id', 'DESC')
            ->limit($limit)
            ->get()
            ->getResult();
        return $rows;
    }

    public function getAktivitasTerbaru(int $limit = 10): array
    {
        return $this->select('
            audit_logs.id,
            audit_logs.user_id,
            audit_logs.action,
            audit_logs.table_name,
            audit_logs.description,
            audit_logs.ip_address,
            audit_logs.created_at,
            users.username
        ')
            ->join('users', 'users.id = audit_logs.user_id', 'left')
            ->orderBy('audit_logs.created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}
