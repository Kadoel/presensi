<?php

namespace App\Services;

use App\Models\JadwalKerjaModel;
use App\Models\PegawaiModel;
use App\Models\PresensiModel;
use App\Models\ShiftModel;
use App\Models\TukarJadwalModel;

class TukarJadwalService extends BaseService
{
    protected TukarJadwalModel $tukarJadwalModel;
    protected JadwalKerjaModel $jadwalKerjaModel;
    protected PegawaiModel $pegawaiModel;
    protected ShiftModel $shiftModel;
    protected PresensiModel $presensiModel;

    public const STATUS_PENDING   = 'pending';
    public const STATUS_APPROVED  = 'approved';
    public const STATUS_REJECTED  = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    public const TIPE_PENGAJUAN_PEGAWAI = 'pegawai';
    public const TIPE_PENGAJUAN_ADMIN   = 'admin';

    public const SWAP_SIMPLE = 'simple';
    public const SWAP_PAIRED = 'paired';

    public function __construct()
    {
        parent::__construct();

        $this->tukarJadwalModel = new TukarJadwalModel();
        $this->jadwalKerjaModel = new JadwalKerjaModel();
        $this->pegawaiModel     = new PegawaiModel();
        $this->shiftModel       = new ShiftModel();
        $this->presensiModel    = new PresensiModel();
    }

    public function dataTabel()
    {
        return $this->tukarJadwalModel->selectData();
    }

    public function ambil(int $id): array
    {
        return $this->eksekusi(function () use ($id) {
            $data = $this->tukarJadwalModel->getTukarJadwalById($id);

            if ($data === null) {
                return $this->hasilTidakDitemukan('Data Tukar Jadwal Tidak Ditemukan');
            }

            return $this->hasilData([
                'tukar_jadwal' => $data,
            ]);
        });
    }

    public function simpanPengajuanPegawai(array $post): array
    {
        return $this->simpanPengajuan($post, self::TIPE_PENGAJUAN_PEGAWAI, false);
    }

    public function simpanLangsungAdmin(array $post): array
    {
        return $this->simpanPengajuan($post, self::TIPE_PENGAJUAN_ADMIN, true);
    }

    public function approve(int $id, ?string $catatanApproval = null): array
    {
        return $this->transaksi(function () use ($id, $catatanApproval) {
            $data = $this->tukarJadwalModel->find($id);

            if (! is_object($data)) {
                return $this->hasilTidakDitemukan('Data Tukar Jadwal Tidak Ditemukan');
            }

            if (($data->status ?? '') !== self::STATUS_PENDING) {
                return $this->hasilGagal([], 'Hanya pengajuan dengan status pending yang dapat disetujui');
            }

            $jadwalA = $this->jadwalKerjaModel->find((int) $data->jadwal_kerja_a_id);
            $jadwalB = $this->jadwalKerjaModel->find((int) $data->jadwal_kerja_b_id);

            if (! is_object($jadwalA) || ! is_object($jadwalB)) {
                return $this->hasilGagal([], 'Slot jadwal utama sudah berubah atau tidak ditemukan');
            }

            $snapshot = $this->validasiSnapshotUtama($data, $jadwalA, $jadwalB);
            if (! $snapshot['sukses']) {
                return $snapshot;
            }

            $validasi = $this->validasiTukarSlot($jadwalA, $jadwalB);
            if (! $validasi['sukses']) {
                return $validasi;
            }

            $proses = $this->jalankanSwapDariHasilValidasi($validasi, $jadwalA, $jadwalB);
            if (! $proses['sukses']) {
                return $proses;
            }

            $approvedBy = $this->intAtauNull(session()->get('user_id'));

            $update = $this->tukarJadwalModel->update($id, [
                'status'           => self::STATUS_APPROVED,
                'catatan_approval' => $this->stringAtauNull($catatanApproval),
                'disetujui_oleh'   => $approvedBy,
                'disetujui_at'     => date('Y-m-d H:i:s'),
                'tipe_swap'        => $validasi['tipe_swap'] ?? null,
            ]);

            if (! $update) {
                return $this->hasilGagal([], 'Status approval gagal diperbarui');
            }

            $this->catatAudit(
                'approve',
                'tukar_jadwal',
                $id,
                'Menyetujui tukar jadwal pegawai ID ' . $data->pegawai_a_id
                    . ' tanggal ' . $data->tanggal_a
                    . ' dengan pegawai ID ' . $data->pegawai_b_id
                    . ' tanggal ' . $data->tanggal_b
            );

            return $this->hasilSukses('Tukar jadwal berhasil disetujui');
        });
    }

    public function reject(int $id, ?string $catatanApproval = null): array
    {
        return $this->transaksi(function () use ($id, $catatanApproval) {
            $data = $this->tukarJadwalModel->find($id);

            if (! is_object($data)) {
                return $this->hasilTidakDitemukan('Data Tukar Jadwal Tidak Ditemukan');
            }

            if (($data->status ?? '') !== self::STATUS_PENDING) {
                return $this->hasilGagal([], 'Hanya pengajuan dengan status pending yang dapat ditolak');
            }

            $rejectedBy = $this->intAtauNull(session()->get('user_id'));

            $update = $this->tukarJadwalModel->update($id, [
                'status'           => self::STATUS_REJECTED,
                'catatan_approval' => $this->stringAtauNull($catatanApproval),
                'disetujui_oleh'   => $rejectedBy,
                'disetujui_at'     => date('Y-m-d H:i:s'),
            ]);

            if (! $update) {
                return $this->hasilGagal([], 'Status reject gagal diperbarui');
            }

            $this->catatAudit(
                'reject',
                'tukar_jadwal',
                $id,
                'Menolak tukar jadwal pegawai ID ' . $data->pegawai_a_id
                    . ' tanggal ' . $data->tanggal_a
                    . ' dengan pegawai ID ' . $data->pegawai_b_id
                    . ' tanggal ' . $data->tanggal_b
            );

            return $this->hasilSukses('Tukar jadwal berhasil ditolak');
        });
    }

    protected function simpanPengajuan(array $post, string $tipePengajuan, bool $autoApprove): array
    {
        return $this->transaksi(function () use ($post, $tipePengajuan, $autoApprove) {
            $rules = [
                'pegawai_a_id' => [
                    'label'  => 'Pegawai A',
                    'rules'  => 'required|integer',
                    'errors' => [
                        'required' => '{field} harus diisi',
                        'integer'  => '{field} tidak valid',
                    ]
                ],
                'pegawai_b_id' => [
                    'label'  => 'Pegawai B',
                    'rules'  => 'required|integer',
                    'errors' => [
                        'required' => '{field} harus diisi',
                        'integer'  => '{field} tidak valid',
                    ]
                ],
                'jadwal_kerja_a_id' => [
                    'label'  => 'Slot Jadwal A',
                    'rules'  => 'required|integer',
                    'errors' => [
                        'required' => '{field} harus diisi',
                        'integer'  => '{field} tidak valid',
                    ]
                ],
                'jadwal_kerja_b_id' => [
                    'label'  => 'Slot Jadwal B',
                    'rules'  => 'required|integer',
                    'errors' => [
                        'required' => '{field} harus diisi',
                        'integer'  => '{field} tidak valid',
                    ]
                ],
                'alasan' => [
                    'label'  => 'Alasan',
                    'rules'  => 'required|regex_match[/^[a-zA-Z0-9,.\s]+$/]',
                    'errors' => [
                        'required'    => '{field} harus diisi',
                        'regex_match' => '{field} hanya boleh diisi huruf, angka, titik, koma, dan spasi',
                    ]
                ]
            ];

            $validasi = $this->validasi($rules, $post);
            if (! $validasi['sukses']) {
                return $validasi;
            }

            $jadwalAId = $this->intAtauNull($post['jadwal_kerja_a_id'] ?? null);
            $jadwalBId = $this->intAtauNull($post['jadwal_kerja_b_id'] ?? null);
            $alasan    = $this->stringAtauNull($post['alasan'] ?? '');
            $userId    = $this->intAtauNull(session()->get('user_id'));

            if ($jadwalAId === null || $jadwalBId === null) {
                return $this->hasilGagal([], 'Slot jadwal tidak valid');
            }

            if ($jadwalAId === $jadwalBId) {
                return $this->hasilGagal([], 'Slot jadwal sumber dan tujuan tidak boleh sama');
            }

            $jadwalA = $this->jadwalKerjaModel->find($jadwalAId);
            $jadwalB = $this->jadwalKerjaModel->find($jadwalBId);

            if (! is_object($jadwalA) || ! is_object($jadwalB)) {
                return $this->hasilGagal([], 'Data slot jadwal tidak ditemukan');
            }

            $validasiSwap = $this->validasiTukarSlot($jadwalA, $jadwalB);
            if (! $validasiSwap['sukses']) {
                return $validasiSwap;
            }

            $dataInsert = [
                'jadwal_kerja_a_id' => $jadwalA->id,
                'pegawai_a_id'      => $jadwalA->pegawai_id,
                'tanggal_a'         => $jadwalA->tanggal,
                'shift_a_id'        => $jadwalA->shift_id,
                'status_hari_a'     => $jadwalA->status_hari,
                'sumber_data_a'     => $jadwalA->sumber_data,

                'jadwal_kerja_b_id' => $jadwalB->id,
                'pegawai_b_id'      => $jadwalB->pegawai_id,
                'tanggal_b'         => $jadwalB->tanggal,
                'shift_b_id'        => $jadwalB->shift_id,
                'status_hari_b'     => $jadwalB->status_hari,
                'sumber_data_b'     => $jadwalB->sumber_data,

                'tipe_swap'      => $validasiSwap['tipe_swap'] ?? null,
                'alasan'         => $alasan,
                'status'         => $autoApprove ? self::STATUS_APPROVED : self::STATUS_PENDING,
                'tipe_pengajuan' => $tipePengajuan,
                'diajukan_oleh'  => $userId,
                'disetujui_oleh' => $autoApprove ? $userId : null,
                'disetujui_at'   => $autoApprove ? date('Y-m-d H:i:s') : null,
            ];

            $insert = $this->tukarJadwalModel->insert($dataInsert);

            if (! $insert) {
                return $this->hasilGagal([], 'Pengajuan tukar jadwal gagal disimpan');
            }

            $id = (int) $this->tukarJadwalModel->getInsertID();

            if ($autoApprove) {
                $proses = $this->jalankanSwapDariHasilValidasi($validasiSwap, $jadwalA, $jadwalB);
                if (! $proses['sukses']) {
                    return $proses;
                }

                $this->catatAudit(
                    'create',
                    'tukar_jadwal',
                    $id,
                    'Admin membuat dan menyetujui langsung tukar jadwal pegawai ID '
                        . $jadwalA->pegawai_id . ' tanggal ' . $jadwalA->tanggal
                        . ' dengan pegawai ID ' . $jadwalB->pegawai_id . ' tanggal ' . $jadwalB->tanggal
                );

                return $this->hasilSukses('Tukar jadwal berhasil dibuat dan langsung disetujui');
            }

            $this->catatAudit(
                'create',
                'tukar_jadwal',
                $id,
                'Mengajukan tukar jadwal pegawai ID '
                    . $jadwalA->pegawai_id . ' tanggal ' . $jadwalA->tanggal
                    . ' dengan pegawai ID ' . $jadwalB->pegawai_id . ' tanggal ' . $jadwalB->tanggal
            );

            return $this->hasilSukses('Pengajuan tukar jadwal berhasil disimpan');
        });
    }

    /**
     * @return array{
     *     sukses: bool,
     *     pesan: string,
     *     errors: array,
     *     tipe_swap?: string,
     *     jadwal_silang_a?: mixed,
     *     jadwal_silang_b?: mixed
     * }
     */
    protected function validasiTukarSlot($jadwalA, $jadwalB): array
    {
        $basic = $this->validasiDasar($jadwalA, $jadwalB);
        if (! $basic['sukses']) {
            return $basic;
        }

        if (! is_object($jadwalA) || ! is_object($jadwalB)) {
            return $this->hasilGagal([], 'Data slot jadwal tidak valid');
        }

        $tanggalA = (string) $jadwalA->tanggal;
        $tanggalB = (string) $jadwalB->tanggal;

        // swap simple tanggal sama
        if ($tanggalA === $tanggalB) {
            if ($this->tidakAdaPerubahanEfektif($jadwalA, $jadwalB)) {
                return $this->hasilGagal([], 'Tukar jadwal ditolak karena tidak ada perubahan jadwal yang efektif');
            }

            return $this->hasilSukses('', [
                'tipe_swap' => self::SWAP_SIMPLE,
            ]);
        }

        $jadwalSilangA = $this->jadwalKerjaModel
            ->getJadwalByPegawaiDanTanggal((int) $jadwalA->pegawai_id, $tanggalB);

        $jadwalSilangB = $this->jadwalKerjaModel
            ->getJadwalByPegawaiDanTanggal((int) $jadwalB->pegawai_id, $tanggalA);

        $adaSilangA = is_object($jadwalSilangA);
        $adaSilangB = is_object($jadwalSilangB);

        // simple swap beda tanggal, tanpa slot silang
        if (! $adaSilangA && ! $adaSilangB) {
            return $this->hasilSukses('', [
                'tipe_swap' => self::SWAP_SIMPLE,
            ]);
        }

        // paired swap
        if ($adaSilangA && $adaSilangB) {
            $validasiSilang = $this->validasiDasar($jadwalSilangA, $jadwalSilangB);

            if (! $validasiSilang['sukses']) {
                return $this->hasilGagal([], 'Tukar jadwal tidak dapat dilakukan karena slot silang tidak valid');
            }

            if ($this->tidakAdaPerubahanEfektif($jadwalA, $jadwalB, $jadwalSilangA, $jadwalSilangB)) {
                return $this->hasilGagal([], 'Tukar jadwal ditolak karena tidak ada perubahan jadwal yang efektif');
            }

            return $this->hasilSukses('', [
                'tipe_swap'       => self::SWAP_PAIRED,
                'jadwal_silang_a' => $jadwalSilangA,
                'jadwal_silang_b' => $jadwalSilangB,
            ]);
        }

        return $this->hasilGagal([], 'Tukar jadwal beda tanggal tidak valid karena hanya salah satu pegawai memiliki jadwal pada tanggal silang');
    }

    protected function validasiDasar($jadwalA, $jadwalB): array
    {
        if (! is_object($jadwalA) || ! is_object($jadwalB)) {
            return $this->hasilGagal([], 'Data jadwal tidak valid');
        }

        if ((int) $jadwalA->pegawai_id === (int) $jadwalB->pegawai_id) {
            return $this->hasilGagal([], 'Tidak dapat menukar jadwal dengan pegawai yang sama');
        }

        if (($jadwalA->sumber_data ?? '') !== 'manual' || ($jadwalB->sumber_data ?? '') !== 'manual') {
            return $this->hasilGagal([], 'Tukar jadwal hanya dapat dilakukan pada jadwal manual');
        }

        if (($jadwalA->status_hari ?? '') !== 'kerja' || ($jadwalB->status_hari ?? '') !== 'kerja') {
            return $this->hasilGagal([], 'Hanya jadwal kerja yang dapat ditukar');
        }

        if ($jadwalA->shift_id === null || $jadwalB->shift_id === null) {
            return $this->hasilGagal([], 'Shift pada jadwal tidak valid');
        }

        if (
            $this->sudahAdaPresensi((int) $jadwalA->pegawai_id, (string) $jadwalA->tanggal) ||
            $this->sudahAdaPresensi((int) $jadwalB->pegawai_id, (string) $jadwalB->tanggal)
        ) {
            return $this->hasilGagal([], 'Tidak dapat menukar jadwal yang sudah memiliki presensi');
        }

        return $this->hasilSukses();
    }

    protected function validasiSnapshotUtama($data, $jadwalA, $jadwalB): array
    {
        if (! is_object($data) || ! is_object($jadwalA) || ! is_object($jadwalB)) {
            return $this->hasilGagal([], 'Data snapshot jadwal tidak valid');
        }

        if (
            (int) $jadwalA->pegawai_id !== (int) $data->pegawai_a_id ||
            (string) $jadwalA->tanggal !== (string) $data->tanggal_a ||
            (int) $jadwalA->shift_id !== (int) $data->shift_a_id ||
            (string) $jadwalA->status_hari !== (string) $data->status_hari_a ||
            (string) $jadwalA->sumber_data !== (string) $data->sumber_data_a
        ) {
            return $this->hasilGagal([], 'Slot jadwal A sudah berubah sejak pengajuan dibuat');
        }

        if (
            (int) $jadwalB->pegawai_id !== (int) $data->pegawai_b_id ||
            (string) $jadwalB->tanggal !== (string) $data->tanggal_b ||
            (int) $jadwalB->shift_id !== (int) $data->shift_b_id ||
            (string) $jadwalB->status_hari !== (string) $data->status_hari_b ||
            (string) $jadwalB->sumber_data !== (string) $data->sumber_data_b
        ) {
            return $this->hasilGagal([], 'Slot jadwal B sudah berubah sejak pengajuan dibuat');
        }

        return $this->hasilSukses();
    }

    protected function prosesSwapSimple($jadwalA, $jadwalB): bool
    {
        if (! is_object($jadwalA) || ! is_object($jadwalB)) {
            return false;
        }

        $pegawaiA = (int) $jadwalA->pegawai_id;
        $pegawaiB = (int) $jadwalB->pegawai_id;

        $ok1 = $this->jadwalKerjaModel->update($jadwalA->id, [
            'pegawai_id' => $pegawaiB,
        ]);

        $ok2 = $this->jadwalKerjaModel->update($jadwalB->id, [
            'pegawai_id' => $pegawaiA,
        ]);

        return $ok1 && $ok2;
    }

    protected function prosesSwapPaired($jadwalA, $jadwalB, $jadwalSilangA, $jadwalSilangB): bool
    {
        if (
            ! is_object($jadwalA) ||
            ! is_object($jadwalB) ||
            ! is_object($jadwalSilangA) ||
            ! is_object($jadwalSilangB)
        ) {
            return false;
        }

        $pegawaiA = (int) $jadwalA->pegawai_id;
        $pegawaiB = (int) $jadwalB->pegawai_id;

        $ok1 = $this->jadwalKerjaModel->update($jadwalA->id, [
            'pegawai_id' => $pegawaiB,
        ]);

        $ok2 = $this->jadwalKerjaModel->update($jadwalSilangB->id, [
            'pegawai_id' => $pegawaiA,
        ]);

        $ok3 = $this->jadwalKerjaModel->update($jadwalB->id, [
            'pegawai_id' => $pegawaiA,
        ]);

        $ok4 = $this->jadwalKerjaModel->update($jadwalSilangA->id, [
            'pegawai_id' => $pegawaiB,
        ]);

        return $ok1 && $ok2 && $ok3 && $ok4;
    }

    protected function jalankanSwapDariHasilValidasi(array $validasi, $jadwalA, $jadwalB): array
    {
        $tipeSwap = $validasi['tipe_swap'] ?? null;

        if ($tipeSwap === self::SWAP_SIMPLE) {
            $ok = $this->prosesSwapSimple($jadwalA, $jadwalB);

            if (! $ok) {
                return $this->hasilGagal([], 'Proses tukar jadwal gagal dilakukan');
            }

            return $this->hasilSukses();
        }

        if ($tipeSwap === self::SWAP_PAIRED) {
            $jadwalSilangA = $validasi['jadwal_silang_a'] ?? null;
            $jadwalSilangB = $validasi['jadwal_silang_b'] ?? null;

            $ok = $this->prosesSwapPaired(
                $jadwalA,
                $jadwalB,
                $jadwalSilangA,
                $jadwalSilangB
            );

            if (! $ok) {
                return $this->hasilGagal([], 'Proses tukar jadwal gagal dilakukan');
            }

            return $this->hasilSukses();
        }

        return $this->hasilGagal([], 'Tipe swap tidak valid');
    }

    protected function tidakAdaPerubahanEfektif($jadwalA, $jadwalB, $jadwalSilangA = null, $jadwalSilangB = null): bool
    {
        if (! is_object($jadwalA) || ! is_object($jadwalB)) {
            return false;
        }

        // Case simple: tanggal sama dan shift sama
        if (
            (string) $jadwalA->tanggal === (string) $jadwalB->tanggal &&
            (int) $jadwalA->shift_id === (int) $jadwalB->shift_id
        ) {
            return true;
        }

        // Case paired: dua-duanya punya slot silang
        if (is_object($jadwalSilangA) && is_object($jadwalSilangB)) {
            $slotTanggalAIdentik =
                (string) $jadwalA->tanggal === (string) $jadwalSilangB->tanggal &&
                (int) $jadwalA->shift_id === (int) $jadwalSilangB->shift_id;

            $slotTanggalBIdentik =
                (string) $jadwalB->tanggal === (string) $jadwalSilangA->tanggal &&
                (int) $jadwalB->shift_id === (int) $jadwalSilangA->shift_id;

            return $slotTanggalAIdentik && $slotTanggalBIdentik;
        }

        return false;
    }

    protected function sudahAdaPresensi(int $pegawaiId, string $tanggal): bool
    {
        return $this->presensiModel->sudahAdaPresensi($pegawaiId, $tanggal);
    }

    public function getPegawaiAktif(): array
    {
        return $this->pegawaiModel->db->table('pegawai')
            ->select('id, kode_pegawai, nama_pegawai')
            ->where('is_active', 1)
            ->orderBy('nama_pegawai', 'ASC')
            ->get()
            ->getResult();
    }

    public function getSlotPegawai(int $pegawaiId): array
    {
        return $this->eksekusi(function () use ($pegawaiId) {
            if ($pegawaiId < 1) {
                return $this->hasilGagal([
                    'pegawai_id' => 'Pegawai tidak valid'
                ]);
            }

            $slots = $this->jadwalKerjaModel->db->table('jadwal_kerja')
                ->select('
                    jadwal_kerja.id,
                    jadwal_kerja.tanggal,
                    jadwal_kerja.shift_id,
                    jadwal_kerja.status_hari,
                    jadwal_kerja.sumber_data,
                    shift.nama_shift
                ')
                ->join('shift', 'shift.id = jadwal_kerja.shift_id', 'left')
                ->where('jadwal_kerja.pegawai_id', $pegawaiId)
                ->where('jadwal_kerja.sumber_data', 'manual')
                ->where('jadwal_kerja.status_hari', 'kerja')
                ->where('jadwal_kerja.shift_id IS NOT NULL')
                ->orderBy('jadwal_kerja.tanggal', 'ASC')
                ->get()
                ->getResult();

            return $this->hasilData([
                'slots' => $slots
            ]);
        });
    }
}
