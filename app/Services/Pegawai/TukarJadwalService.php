<?php

namespace App\Services\Pegawai;

use App\Models\JadwalKerjaModel;
use App\Models\PegawaiModel;
use App\Models\TukarJadwalModel;
use App\Services\Admin\TukarJadwalService as AdminTukarJadwalService;
use CodeIgniter\Database\BaseBuilder;

class TukarJadwalService extends AdminTukarJadwalService
{
    protected TukarJadwalModel $tukarJadwalModelPegawai;
    protected JadwalKerjaModel $jadwalKerjaModelPegawai;
    protected PegawaiModel $pegawaiModelPegawai;

    public function __construct()
    {
        parent::__construct();

        $this->tukarJadwalModelPegawai = new TukarJadwalModel();
        $this->jadwalKerjaModelPegawai = new JadwalKerjaModel();
        $this->pegawaiModelPegawai     = new PegawaiModel();
    }

    public function dataTabel(): BaseBuilder
    {
        $pegawaiId = $this->intAtauNull(session()->get('pegawai_id'));

        return $this->tukarJadwalModelPegawai
            ->selectDataPegawai((int) $pegawaiId);
    }

    public function getPegawaiTujuan(): array
    {
        $pegawaiId = $this->intAtauNull(session()->get('pegawai_id'));

        return $this->pegawaiModelPegawai
            ->select('id, kode_pegawai, nama_pegawai')
            ->where('is_active', 1)
            ->where('id !=', (int) $pegawaiId)
            ->orderBy('nama_pegawai', 'ASC')
            ->findAll();
    }

    public function getSlotSaya(): array
    {
        return $this->eksekusi(function () {
            $pegawaiId = $this->intAtauNull(session()->get('pegawai_id'));

            if ($pegawaiId === null) {
                return $this->hasilGagal([], 'Data pegawai tidak ditemukan pada session');
            }

            return $this->hasilData([
                'slots' => $this->jadwalKerjaModelPegawai->getSlotTukarJadwalPegawai($pegawaiId),
            ]);
        });
    }

    public function getSlotPegawai(int $pegawaiId): array
    {
        return $this->eksekusi(function () use ($pegawaiId) {
            $pegawaiLoginId = $this->intAtauNull(session()->get('pegawai_id'));

            if ($pegawaiLoginId === null) {
                return $this->hasilGagal([], 'Data pegawai tidak ditemukan pada session');
            }

            if ($pegawaiId === (int) $pegawaiLoginId) {
                return $this->hasilGagal([], 'Pegawai tujuan tidak boleh sama dengan pegawai login');
            }

            $pegawai = $this->pegawaiModelPegawai->getPegawaiById($pegawaiId);

            if ($pegawai === null || (int) ($pegawai->is_active ?? 0) !== 1) {
                return $this->hasilGagal([], 'Pegawai tujuan tidak valid atau tidak aktif');
            }

            return $this->hasilData([
                'slots' => $this->jadwalKerjaModelPegawai->getSlotTukarJadwalPegawai($pegawaiId),
            ]);
        });
    }

    public function simpan(array $post): array
    {
        $pegawaiId = $this->intAtauNull(session()->get('pegawai_id'));

        if ($pegawaiId === null) {
            return $this->hasilGagal([], 'Data pegawai tidak ditemukan pada session');
        }

        $post['pegawai_a_id'] = $pegawaiId;

        $jadwalAId = $this->intAtauNull($post['jadwal_kerja_a_id'] ?? null);

        if ($jadwalAId === null) {
            return $this->hasilGagal([
                'jadwal_kerja_a_id' => 'Slot jadwal saya harus dipilih',
            ]);
        }

        $jadwalA = $this->jadwalKerjaModelPegawai->find($jadwalAId);

        if (! is_object($jadwalA) || (int) $jadwalA->pegawai_id !== (int) $pegawaiId) {
            return $this->hasilGagal([
                'jadwal_kerja_a_id' => 'Slot jadwal saya tidak valid',
            ]);
        }

        return $this->simpanPengajuanPegawai($post);
    }

    public function ambil(int $id): array
    {
        return $this->eksekusi(function () use ($id) {
            $pegawaiId = $this->intAtauNull(session()->get('pegawai_id'));
            $data = $this->tukarJadwalModelPegawai->getTukarJadwalById($id);

            if ($data === null) {
                return $this->hasilTidakDitemukan('Data Tukar Jadwal Tidak Ditemukan');
            }

            $terlibat = (int) $data->pegawai_a_id === (int) $pegawaiId
                || (int) $data->pegawai_b_id === (int) $pegawaiId;

            if (! $terlibat) {
                return $this->hasilTidakDitemukan('Data Tukar Jadwal Tidak Ditemukan');
            }

            return $this->hasilData([
                'tukar_jadwal' => $data,
            ]);
        });
    }
}
