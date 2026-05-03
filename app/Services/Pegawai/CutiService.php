<?php

namespace App\Services\Pegawai;

use App\Models\PengajuanIzinModel;
use App\Models\SaldoCutiModel;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\HTTP\Files\UploadedFile;

class CutiService extends PengajuanIzinService
{
    protected SaldoCutiModel $saldoCutiModel;

    public function __construct()
    {
        parent::__construct();

        $this->pengajuanIzinModel = new PengajuanIzinModel();
        $this->saldoCutiModel     = new SaldoCutiModel();
    }

    public function dataTabel(): BaseBuilder
    {
        $pegawaiId = $this->intAtauNull(session()->get('pegawai_id'));

        return $this->pengajuanIzinModel
            ->selectDataPegawai((int) $pegawaiId)
            ->where('pengajuan_izin.jenis', 'cuti');
    }

    public function getSaldoSaya(): array
    {
        return $this->eksekusi(function () {
            $pegawaiId = $this->intAtauNull(session()->get('pegawai_id'));

            if ($pegawaiId === null) {
                return $this->hasilGagal([], 'Data pegawai tidak ditemukan pada session');
            }

            $tahun = (int) date('Y');
            $saldo = $this->saldoCutiModel->getByPegawaiTahun((int) $pegawaiId, $tahun);

            return $this->hasilData([
                'saldo' => $saldo,
            ]);
        });
    }

    public function simpan(array $post, ?UploadedFile $file): array
    {
        $post['jenis'] = 'cuti';

        $pegawaiId      = (int) session()->get('pegawai_id');
        $tanggalMulai   = (string) ($post['tanggal_mulai'] ?? '');
        $tanggalSelesai = (string) ($post['tanggal_selesai'] ?? '');

        $validasiSaldo = $this->validasiSaldoCuti(
            $pegawaiId,
            $tanggalMulai,
            $tanggalSelesai,
            'tanggal_mulai',
            'tanggal_selesai'
        );

        if (! $validasiSaldo['sukses']) {
            return $validasiSaldo;
        }

        return parent::simpan($post, $file);
    }

    public function ubah(int $id, array $post, ?UploadedFile $file): array
    {
        $post['edit-jenis'] = 'cuti';

        $pegawaiId      = (int) session()->get('pegawai_id');
        $tanggalMulai   = (string) ($post['edit-tanggal_mulai'] ?? '');
        $tanggalSelesai = (string) ($post['edit-tanggal_selesai'] ?? '');

        $validasiSaldo = $this->validasiSaldoCuti(
            $pegawaiId,
            $tanggalMulai,
            $tanggalSelesai,
            'edit-tanggal_mulai',
            'edit-tanggal_selesai'
        );

        if (! $validasiSaldo['sukses']) {
            return $validasiSaldo;
        }

        return parent::ubah($id, $post, $file);
    }

    protected function rulesSimpan(?UploadedFile $file): array
    {
        $rules = parent::rulesSimpan($file);

        $rules['jenis']['rules'] = 'required|in_list[cuti]';
        $rules['jenis']['errors']['in_list'] = 'Jenis tidak valid';

        return $rules;
    }

    protected function rulesUbah(?UploadedFile $file): array
    {
        $rules = parent::rulesUbah($file);

        $rules['edit-jenis']['rules'] = 'required|in_list[cuti]';
        $rules['edit-jenis']['errors']['in_list'] = 'Jenis tidak valid';

        return $rules;
    }

    protected function validasiSaldoCuti(
        int $pegawaiId,
        string $tanggalMulai,
        string $tanggalSelesai,
        string $fieldMulai,
        string $fieldSelesai
    ): array {
        if ($pegawaiId <= 0 || $tanggalMulai === '' || $tanggalSelesai === '') {
            return $this->hasilSukses();
        }

        $tahun = (int) date('Y', strtotime($tanggalMulai));
        $saldo = $this->saldoCutiModel->getByPegawaiTahun($pegawaiId, $tahun);

        if ($saldo === null) {
            return $this->hasilGagal([
                $fieldMulai => 'Saldo cuti tahun ' . $tahun . ' belum tersedia. Hubungi admin.',
            ]);
        }

        $jumlahHari = $this->hitungJumlahHari($tanggalMulai, $tanggalSelesai);

        if ($jumlahHari > (int) $saldo->sisa) {
            return $this->hasilGagal([
                $fieldMulai   => 'Saldo cuti tidak mencukupi. Sisa: ' . $saldo->sisa . ' hari',
                $fieldSelesai => 'Jumlah pengajuan cuti: ' . $jumlahHari . ' hari',
            ]);
        }

        return $this->hasilSukses();
    }
}
