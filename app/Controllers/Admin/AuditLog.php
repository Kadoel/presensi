<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\AuditLogService;
use Hermawan\DataTables\DataTable;

class AuditLog extends BaseController
{
    protected AuditLogService $auditLogService;

    public function __construct()
    {
        $this->auditLogService = new AuditLogService();
    }

    public function index()
    {
        if ($this->request->getMethod() === 'GET') {
            $filterData = $this->auditLogService->getFilterData();
            $timelineLogs = $this->auditLogService->getTimelineTerbaru(10);

            return view('pages/admin/log/index', [
                'judul'         => 'Audit Log',
                'filterUsers'   => $filterData['users'] ?? [],
                'filterActions' => $filterData['actions'] ?? [],
                'filterTables'  => $filterData['table_names'] ?? [],
                'timelineLogs'  => $timelineLogs,
            ]);
        }

        $filter = [
            'user_id'       => $this->request->getPost('filter_user_id'),
            'action'        => $this->request->getPost('filter_action'),
            'table_name'    => $this->request->getPost('filter_table_name'),
            'tanggal_awal'  => $this->request->getPost('filter_tanggal_awal'),
            'tanggal_akhir' => $this->request->getPost('filter_tanggal_akhir'),
        ];

        $builder = $this->auditLogService->dataTabel($filter);

        return DataTable::of($builder)
            ->postQuery(function ($builder) {
                $builder->orderBy('audit_logs.id', 'DESC');
            })
            ->edit('user_nama', function ($row) {
                if (! empty($row->username)) {
                    return esc($row->username) . ' <small class="text-muted">(ID: ' . (int) $row->user_id . ')</small>';
                }

                if (! empty($row->user_id)) {
                    return '<span class="text-warning">User ID ' . (int) $row->user_id . '</span>';
                }

                return '<span class="text-muted">System</span>';
            })
            ->edit('action', function ($row) {
                $action = strtolower((string) $row->action);

                $badgeMap = [
                    'create'         => 'bg-success',
                    'update'         => 'bg-warning',
                    'delete'         => 'bg-danger',
                    'approve'        => 'bg-primary',
                    'reject'         => 'bg-danger',
                    'override'       => 'bg-info',
                    'cancel_approve' => 'bg-secondary',
                    'checkin'        => 'bg-success',
                    'checkout'       => 'bg-primary',
                    'login'          => 'bg-dark',
                    'logout'         => 'bg-secondary',
                ];

                $badge = $badgeMap[$action] ?? 'bg-secondary';

                return '<span class="badge ' . $badge . '">' . esc(strtoupper($row->action)) . '</span>';
            })
            ->edit('table_name', function ($row) {
                return ! empty($row->table_name) ? esc($row->table_name) : '-';
            })
            ->edit('row_id', function ($row) {
                return ! empty($row->row_id) ? (int) $row->row_id : '-';
            })
            ->edit('description', function ($row) {
                return ! empty($row->description) ? esc($row->description) : '-';
            })
            ->edit('ip_address', function ($row) {
                return ! empty($row->ip_address) ? esc($row->ip_address) : '-';
            })
            ->edit('created_at', function ($row) {
                return ! empty($row->created_at)
                    ? date('d-m-Y H:i:s', strtotime($row->created_at))
                    : '-';
            })
            ->add('action_button', function ($row) {
                return '
                    <button type="button" class="btn btn-sm btn-info" id="act-detail" data-id="' . $row->id . '">
                        <i class="fa fa-eye text-white"></i>
                    </button>
                ';
            })
            ->addNumbering('#')
            ->toJson(true);
    }

    public function detail()
    {
        $result = $this->auditLogService->ambil((int) $this->request->getVar('id'));
        return $this->response->setJSON($result);
    }
}
