<?php

namespace App\Services\Admin;

use App\Models\PegawaiModel;
use App\Models\SaldoCutiModel;
use App\Services\BaseService;
use CodeIgniter\Database\BaseBuilder;

class SaldoCutiService extends BaseService
{
    protected SaldoCutiModel $saldoCutiModel;
    protected PegawaiModel $pegawaiModel;

    public function __construct()
    {
        parent::__construct();
        $this->saldoCutiModel = new SaldoCutiModel();
        $this->pegawaiModel   = new PegawaiModel();
    }

    public function dataTabel(?int $tahun = null): BaseBuilder
    {
        $builder = $this->saldoCutiModel->selectData();

        if ($tahun !== null && $tahun > 0) {
            $builder->where('saldo_cuti.tahun', $tahun);
        }

        return $builder;
    }

    public function ringkasan(int $tahun): array
    {
        return $this->eksekusi(function () use ($tahun) {
            return $this->hasilData([
                'ringkasan' => $this->saldoCutiModel->getRingkasanByTahun($tahun),
            ]);
        });
    }

    public function generate(array $post): array
    {
        return $this->transaksi(function () use ($post) {
            $rules = [
                'tahun' => [
                    'label'  => 'Tahun',
                    'rules'  => 'required|integer|greater_than_equal_to[2000]|less_than_equal_to[2100]',
                    'errors' => [
                        'required'              => '{field} harus diisi',
                        'integer'               => '{field} tidak valid',
                        'greater_than_equal_to'  => '{field} minimal 2000',
                        'less_than_equal_to'     => '{field} maksimal 2100',
                    ],
                ],
                'jatah' => [
                    'label'  => 'Jumlah Saldo Cuti',
                    'rules'  => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[365]',
                    'errors' => [
                        'required'              => '{field} harus diisi',
                        'integer'               => '{field} tidak valid',
                        'greater_than_equal_to'  => '{field} minimal 0 hari',
                        'less_than_equal_to'     => '{field} maksimal 365 hari',
                    ],
                ],
            ];

            $validasi = $this->validasi($rules, $post);

            if (! $validasi['sukses']) {
                return $validasi;
            }


            $tahun = (int) ($post['tahun'] ?? date('Y'));
            $jatah = (int) ($post['jatah'] ?? 12);

            $tahunSekarang = (int) date('Y');
            if ($tahun < $tahunSekarang) {
                return $this->hasilGagal([
                    'tahun' => 'Tidak boleh memilih tahun sebelumnya'
                ]);
            }

            $pegawaiAktif = $this->pegawaiModel->db->table('pegawai')
                ->select('id, nama_pegawai')
                ->where('is_active', 1)
                ->get()
                ->getResult();

            if (empty($pegawaiAktif)) {
                return $this->hasilGagal([], 'Tidak ada pegawai aktif untuk digenerate');
            }

            $dibuat = 0;
            $diubah = 0;
            $dilewati = 0;

            foreach ($pegawaiAktif as $pegawai) {
                $pegawaiId = (int) $pegawai->id;
                $saldo = $this->saldoCutiModel->getByPegawaiTahun($pegawaiId, $tahun);

                if ($saldo === null) {
                    $insert = $this->saldoCutiModel->insert([
                        'pegawai_id' => $pegawaiId,
                        'tahun'     => $tahun,
                        'jatah'     => $jatah,
                        'terpakai'  => 0,
                        'sisa'      => $jatah,
                    ]);

                    if (! $insert) {
                        return $this->hasilGagal([], 'Gagal generate saldo cuti untuk pegawai ID ' . $pegawaiId);
                    }

                    $dibuat++;
                    continue;
                }

                $terpakai = (int) ($saldo->terpakai ?? 0);

                if ($terpakai > $jatah) {
                    $dilewati++;
                    continue;
                }

                $update = $this->saldoCutiModel->update((int) $saldo->id, [
                    'jatah' => $jatah,
                    'sisa'  => max(0, $jatah - $terpakai),
                ]);

                if (! $update) {
                    return $this->hasilGagal([], 'Gagal update saldo cuti untuk pegawai ID ' . $pegawaiId);
                }

                $diubah++;
            }

            $this->catatAudit(
                'generate',
                'saldo_cuti',
                null,
                'Generate saldo cuti tahun ' . $tahun . ' dengan jatah ' . $jatah . ' hari. Dibuat: ' . $dibuat . ', diubah: ' . $diubah . ', dilewati: ' . $dilewati
            );

            $pesan = 'Generate saldo cuti berhasil. Dibuat: ' . $dibuat . ', diubah: ' . $diubah;

            if ($dilewati > 0) {
                $pesan .= ', dilewati: ' . $dilewati . ' karena terpakai lebih besar dari jatah baru';
            }

            return $this->hasilSukses($pesan);
        });
    }
}
