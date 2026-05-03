<?php

namespace App\Services\Admin;

use App\Models\CutiModel;
use App\Models\SaldoCutiModel;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\HTTP\Files\UploadedFile;

class CutiService extends PengajuanIzinService
{
    protected SaldoCutiModel $saldoCutiModel;

    public function __construct()
    {
        parent::__construct();

        $this->pengajuanIzinModel = new CutiModel();
        $this->saldoCutiModel     = new SaldoCutiModel();
    }

    public function dataTabel(): BaseBuilder
    {
        return $this->pengajuanIzinModel->selectData();
    }

    public function simpan(array $post, ?UploadedFile $file): array
    {
        $post['jenis'] = 'cuti';

        $validasiSaldo = $this->validasiSaldoDariPostTambah($post);

        if (! $validasiSaldo['sukses']) {
            return $validasiSaldo;
        }

        return parent::simpan($post, $file);
    }

    public function ubah(int $id, array $post, ?UploadedFile $file): array
    {
        $post['edit-jenis'] = 'cuti';

        $validasiSaldo = $this->validasiSaldoDariPostEdit($post);

        if (! $validasiSaldo['sukses']) {
            return $validasiSaldo;
        }

        return parent::ubah($id, $post, $file);
    }

    public function approve(int $id, ?int $approvedBy, ?string $catatanApproval = null): array
    {
        $pengajuan = $this->pengajuanIzinModel->getPengajuanById($id);

        if ($pengajuan === null) {
            return $this->hasilTidakDitemukan('Data Cuti Tidak Ditemukan');
        }

        $validasiSaldo = $this->validasiSaldoCuti(
            (int) $pengajuan->pegawai_id,
            (string) $pengajuan->tanggal_mulai,
            (string) $pengajuan->tanggal_selesai,
            null,
            null
        );

        if (! $validasiSaldo['sukses']) {
            return $validasiSaldo;
        }

        return parent::approve($id, $approvedBy, $catatanApproval);
    }

    protected function prosesSetelahApproveBerhasil(object $pengajuan, ?int $approvedBy = null): array
    {
        if (($pengajuan->jenis ?? '') !== 'cuti') {
            return $this->hasilSukses();
        }

        $pegawaiId  = (int) $pengajuan->pegawai_id;
        $tahun      = $this->ambilTahunCuti((string) $pengajuan->tanggal_mulai);
        $jumlahHari = $this->hitungJumlahHariCuti(
            (string) $pengajuan->tanggal_mulai,
            (string) $pengajuan->tanggal_selesai
        );

        $kurangi = $this->saldoCutiModel->kurangiSaldo($pegawaiId, $tahun, $jumlahHari);

        if (! $kurangi) {
            return $this->hasilGagal([], 'Saldo cuti gagal dikurangi');
        }

        return $this->hasilSukses();
    }

    protected function prosesSetelahCancelApproveBerhasil(object $pengajuan): array
    {
        if (($pengajuan->jenis ?? '') !== 'cuti') {
            return $this->hasilSukses();
        }

        $pegawaiId  = (int) $pengajuan->pegawai_id;
        $tahun      = $this->ambilTahunCuti((string) $pengajuan->tanggal_mulai);
        $jumlahHari = $this->hitungJumlahHariCuti(
            (string) $pengajuan->tanggal_mulai,
            (string) $pengajuan->tanggal_selesai
        );

        $kembali = $this->saldoCutiModel->tambahSaldo($pegawaiId, $tahun, $jumlahHari);

        if (! $kembali) {
            return $this->hasilGagal([], 'Saldo cuti gagal dikembalikan');
        }

        return $this->hasilSukses();
    }

    protected function validasiSaldoDariPostTambah(array $post): array
    {
        return $this->validasiSaldoCuti(
            (int) ($post['pegawai_id'] ?? 0),
            (string) ($post['tanggal_mulai'] ?? ''),
            (string) ($post['tanggal_selesai'] ?? ''),
            'tanggal_mulai',
            'tanggal_selesai'
        );
    }

    protected function validasiSaldoDariPostEdit(array $post): array
    {
        return $this->validasiSaldoCuti(
            (int) ($post['edit-pegawai_id'] ?? 0),
            (string) ($post['edit-tanggal_mulai'] ?? ''),
            (string) ($post['edit-tanggal_selesai'] ?? ''),
            'edit-tanggal_mulai',
            'edit-tanggal_selesai'
        );
    }

    protected function validasiSaldoCuti(
        int $pegawaiId,
        string $tanggalMulai,
        string $tanggalSelesai,
        ?string $fieldMulai,
        ?string $fieldSelesai
    ): array {
        if ($pegawaiId <= 0 || $tanggalMulai === '' || $tanggalSelesai === '') {
            return $this->hasilSukses();
        }

        $tahun      = $this->ambilTahunCuti($tanggalMulai);
        $saldo      = $this->saldoCutiModel->getByPegawaiTahun($pegawaiId, $tahun);
        $jumlahHari = $this->hitungJumlahHariCuti($tanggalMulai, $tanggalSelesai);

        if ($saldo === null) {
            $pesan = 'Saldo cuti tahun ' . $tahun . ' belum digenerate untuk pegawai ini';

            if ($fieldMulai !== null) {
                return $this->hasilGagal([
                    $fieldMulai => $pesan,
                ]);
            }

            return $this->hasilGagal([], $pesan);
        }

        if ($jumlahHari > (int) $saldo->sisa) {
            $pesan = 'Saldo cuti tidak mencukupi. Sisa: ' . $saldo->sisa . ' hari, pengajuan: ' . $jumlahHari . ' hari';

            if ($fieldMulai !== null && $fieldSelesai !== null) {
                return $this->hasilGagal([
                    $fieldMulai   => 'Saldo cuti tidak mencukupi. Sisa: ' . $saldo->sisa . ' hari',
                    $fieldSelesai => 'Jumlah pengajuan cuti: ' . $jumlahHari . ' hari',
                ]);
            }

            return $this->hasilGagal([], $pesan);
        }

        return $this->hasilSukses();
    }

    protected function hitungJumlahHariCuti(string $tanggalMulai, string $tanggalSelesai): int
    {
        $mulai   = new \DateTime($tanggalMulai);
        $selesai = new \DateTime($tanggalSelesai);

        return $mulai->diff($selesai)->days + 1;
    }

    protected function ambilTahunCuti(string $tanggalMulai): int
    {
        return (int) date('Y', strtotime($tanggalMulai));
    }
}
