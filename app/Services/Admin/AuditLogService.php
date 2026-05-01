<?php

namespace App\Services\Admin;

use App\Models\AuditLogModel;
use App\Services\BaseService;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\I18n\Time;

class AuditLogService extends BaseService
{
    protected AuditLogModel $auditLogModel;

    public function __construct()
    {
        parent::__construct();
        $this->auditLogModel = new AuditLogModel();
    }

    public function dataTabel(array $filter = []): BaseBuilder
    {
        return $this->auditLogModel->selectData($filter);
    }

    public function getTimelineTerbaru(int $limit = 10): array
    {
        $rows = $this->auditLogModel->getTimelineTerbaru($limit);

        foreach ($rows as $row) {
            $map = $this->mapTimelineStyle((string) $row->action);
            $row->icon = $map['icon'];
            $row->bg_class = $map['bg_class'];

            $row->created_at_human = Time::parse($row->created_at)->humanize();
        }

        return $rows;
    }

    protected function mapTimelineStyle(string $action): array
    {
        $action = strtolower(trim($action));

        $map = [
            'create' => [
                'icon' => 'fa fa-plus',
                'bg_class' => 'bg-success'
            ],
            'update' => [
                'icon' => 'fa fa-pen',
                'bg_class' => 'bg-warning'
            ],
            'delete' => [
                'icon' => 'fa fa-trash',
                'bg_class' => 'bg-danger'
            ],
            'approve' => [
                'icon' => 'fa fa-check',
                'bg_class' => 'bg-primary'
            ],
            'reject' => [
                'icon' => 'fa fa-times',
                'bg_class' => 'bg-danger'
            ],
            'override' => [
                'icon' => 'fa fa-rotate',
                'bg_class' => 'bg-info'
            ],
            'cancel_approve' => [
                'icon' => 'fa fa-rotate-left',
                'bg_class' => 'bg-secondary'
            ],
            'login' => [
                'icon' => 'fa fa-right-to-bracket',
                'bg_class' => 'bg-dark'
            ],
            'logout' => [
                'icon' => 'fa fa-right-from-bracket',
                'bg_class' => 'bg-secondary'
            ],
            'checkin' => [
                'icon' => 'fa fa-clock',
                'bg_class' => 'bg-success'
            ],
            'checkout' => [
                'icon' => 'fa fa-clock',
                'bg_class' => 'bg-primary'
            ],
        ];

        return $map[$action] ?? [
            'icon' => 'fa fa-circle-info',
            'bg_class' => 'bg-gray-darker'
        ];
    }

    public function ambil(int $id): array
    {
        return $this->eksekusi(function () use ($id) {
            $auditLog = $this->auditLogModel->getAuditLogById($id);

            if ($auditLog === null) {
                return $this->hasilTidakDitemukan('Data Audit Log Tidak Ditemukan');
            }

            return $this->hasilData([
                'audit_log' => $auditLog
            ]);
        });
    }

    public function getFilterData(): array
    {
        return $this->eksekusi(function () {
            return $this->hasilData([
                'users'       => $this->auditLogModel->getUserFilter(),
                'actions'     => $this->auditLogModel->getActionFilter(),
                'table_names' => $this->auditLogModel->getTableFilter(),
            ]);
        });
    }
}
