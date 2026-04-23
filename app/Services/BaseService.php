<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use Config\Database;
use Config\Services;
use Throwable;

class BaseService
{
    protected $validation;
    protected BaseConnection $db;

    public function __construct()
    {
        $this->validation = Services::validation();
        $this->db = Database::connect();
    }

    protected function hasilSukses(string $pesan = '', array $tambahan = []): array
    {
        return array_merge([
            'sukses' => true,
            'pesan'  => $pesan,
            'errors' => [],
        ], $tambahan);
    }

    protected function hasilGagal(array $errors = [], string $pesan = ''): array
    {
        return [
            'sukses' => false,
            'pesan'  => $pesan,
            'errors' => $errors,
        ];
    }

    protected function hasilData(array $data = [], string $pesan = ''): array
    {
        return $this->hasilSukses($pesan, $data);
    }

    protected function hasilTidakDitemukan(string $pesan = 'Data tidak ditemukan'): array
    {
        return $this->hasilGagal([], $pesan);
    }

    protected function hasilException(Throwable $e, string $pesan = 'Terjadi kesalahan pada sistem'): array
    {
        if (ENVIRONMENT === 'development') {
            return $this->hasilGagal([
                'exception' => $e->getMessage(),
            ], $pesan);
        }

        return $this->hasilGagal([
            'general' => $pesan,
        ]);
    }

    protected function validasi(array $rules, array $data = []): array
    {
        $run = $this->validation->setRules($rules)->run($data);

        if (! $run) {
            return $this->hasilGagal($this->validation->getErrors());
        }

        return $this->hasilSukses();
    }

    protected function transaksi(callable $callback): array
    {
        $this->db->transBegin();

        try {
            $hasil = $callback();

            if ($this->db->transStatus() === false) {
                $this->db->transRollback();

                return $this->hasilGagal([
                    'general' => 'Transaksi database gagal'
                ]);
            }

            $this->db->transCommit();

            if (is_array($hasil)) {
                return $hasil;
            }

            return $this->hasilSukses();
        } catch (Throwable $e) {
            $this->db->transRollback();

            log_message('error', static::class . ' | ' . $e->getMessage());
            log_message('error', $e->getTraceAsString());

            return $this->hasilException($e);
        }
    }

    protected function eksekusi(callable $callback): array
    {
        try {
            $hasil = $callback();

            if (is_array($hasil)) {
                return $hasil;
            }

            return $this->hasilSukses();
        } catch (Throwable $e) {
            log_message('error', static::class . ' | ' . $e->getMessage());
            log_message('error', $e->getTraceAsString());

            return $this->hasilException($e);
        }
    }

    protected function catatAudit(
        string $action,
        ?string $tableName = null,
        ?int $rowId = null,
        ?string $description = null,
        ?int $userId = null
    ): void {
        $auditLogModel = new \App\Models\AuditLogModel();
        $request = service('request');

        $ipAddress = $request?->getIPAddress();

        if ($ipAddress === '::1') {
            $ipAddress = '127.0.0.1';
        }

        $auditLogModel->insert([
            'user_id'     => $userId ?? $this->intAtauNull(session()->get('user_id')),
            'action'      => $this->stringWajib($action),
            'table_name'  => $this->stringAtauNull($tableName),
            'row_id'      => $rowId,
            'description' => $this->stringAtauNull($description),
            'ip_address'  => $ipAddress,
            'user_agent'  => $request?->getUserAgent()?->getAgentString(),
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    protected function stringWajib($value): string
    {
        return trim((string) $value);
    }

    protected function stringAtauNull($value): ?string
    {
        $value = trim((string) $value);
        return $value !== '' ? $value : null;
    }

    protected function intVal($value, int $default = 0): int
    {
        return is_numeric($value) ? (int) $value : $default;
    }

    protected function intAtauNull($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    protected function boolInt($value, int $default = 0): int
    {
        if ($value === null || $value === '') {
            return $default;
        }

        return (int) ((bool) $value);
    }

    protected function formatJam(string $jam): string
    {
        $jam = trim($jam);

        if (preg_match('/^\d{2}:\d{2}$/', $jam)) {
            return $jam . ':00';
        }

        return $jam;
    }
}
