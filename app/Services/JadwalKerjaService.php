<?php

namespace App\Services;

use App\Models\HariLiburModel;
use App\Models\JadwalKerjaModel;
use App\Models\PegawaiModel;
use App\Models\PresensiModel;
use App\Models\ShiftModel;

class JadwalKerjaService extends BaseService
{
    protected JadwalKerjaModel $jadwalKerjaModel;
    protected PegawaiModel $pegawaiModel;
    protected ShiftModel $shiftModel;
    protected HariLiburModel $hariLiburModel;
    protected PresensiModel $presensiModel;

    public function __construct()
    {
        parent::__construct();

        $this->jadwalKerjaModel = new JadwalKerjaModel();
        $this->pegawaiModel     = new PegawaiModel();
        $this->shiftModel       = new ShiftModel();
        $this->hariLiburModel   = new HariLiburModel();
        $this->presensiModel    = new PresensiModel();
    }

    public function dataTabel(?string $bulan = null)
    {
        return $this->jadwalKerjaModel->selectData($bulan);
    }

    public function getPegawaiDropdown(): array
    {
        return $this->eksekusi(function () {
            $pegawai = $this->pegawaiModel->getPegawaiDropdown();

            return $this->hasilData([
                'pegawai' => $pegawai,
            ]);
        });
    }

    public function getShiftDropdown(): array
    {
        return $this->eksekusi(function () {
            $shift = $this->shiftModel->getShiftDropdown();

            return $this->hasilData([
                'shift' => $shift,
            ]);
        });
    }

    /**
     * Generate jadwal kerja multiple tanggal.
     * Payload:
     * - tanggal: "YYYY-MM-DD,YYYY-MM-DD"
     * - shift_pegawai[shift_id][]: pegawai_id
     * - libur_pegawai[]: pegawai_id
     * - catatan: optional
     *
     * Rule utama:
     * - semua pegawai aktif wajib dipilih tepat satu section
     * - section boleh kosong
     */
    public function simpan(array $post): array
    {
        return $this->transaksi(function () use ($post) {
            $hariIni   = date('Y-m-d');
            $createdBy = $this->intAtauNull(session()->get('user_id'));
            $catatan   = $this->stringAtauNull($post['catatan'] ?? '');

            $tanggalList = $this->parseTanggalList($post['tanggal'] ?? '');

            if (empty($tanggalList)) {
                return $this->hasilGagal([
                    'tanggal' => 'Tanggal harus dipilih',
                ]);
            }

            foreach ($tanggalList as $tanggal) {
                if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
                    return $this->hasilGagal([
                        'tanggal' => 'Format tanggal tidak valid',
                    ]);
                }

                if ($tanggal < $hariIni) {
                    return $this->hasilGagal([
                        'tanggal' => 'Tidak diizinkan membuat jadwal sebelum hari ini',
                    ]);
                }
            }

            $items = $this->bangunItemGenerate($post['shift_pegawai'] ?? [], $post['libur_pegawai'] ?? []);

            $validasiCoverage = $this->validasiCoveragePegawaiAktif($items);
            if (! $validasiCoverage['sukses']) {
                return $validasiCoverage;
            }

            $validasiShift = $this->validasiSemuaShiftGenerate($items);
            if (! $validasiShift['sukses']) {
                return $validasiShift;
            }

            $duplikatJadwal = $this->cariDuplikatJadwalGenerate($items, $tanggalList);
            if (! empty($duplikatJadwal)) {
                return $this->hasilGagal([
                    'tanggal' => 'Sebagian pegawai sudah memiliki jadwal: ' . implode(', ', array_slice($duplikatJadwal, 0, 10)),
                ]);
            }

            $rows = [];
            $now  = date('Y-m-d H:i:s');

            foreach ($tanggalList as $tanggal) {
                foreach ($items as $item) {
                    $rows[] = [
                        'pegawai_id'             => (int) $item['pegawai_id'],
                        'tanggal'                => $tanggal,
                        'shift_id'               => $item['shift_id'],
                        'status_hari'            => $item['status_hari'],
                        'sumber_data'            => 'manual',
                        'pengajuan_izin_id'      => null,
                        'hari_libur_id'          => null,
                        'shift_id_sebelumnya'    => null,
                        'status_hari_sebelumnya' => null,
                        'catatan_sebelumnya'     => null,
                        'sumber_data_sebelumnya' => null,
                        'catatan'                => $catatan,
                        'created_by'             => $createdBy,
                        'created_at'             => $now,
                        'updated_at'             => $now,
                    ];
                }
            }

            if (! $this->jadwalKerjaModel->insertBatch($rows)) {
                return $this->hasilGagal([
                    'general' => 'Data jadwal kerja gagal disimpan',
                ]);
            }

            $this->catatAudit(
                'create',
                'jadwal_kerja',
                null,
                'Generate jadwal kerja untuk ' . count($tanggalList) . ' tanggal dan ' . count($items) . ' pegawai aktif'
            );

            $hariLiburTerdeteksi = [];
            foreach ($tanggalList as $tanggal) {
                $hariLibur = $this->getInfoHariLiburPadaTanggal($tanggal);

                if ($hariLibur !== null) {
                    $hariLiburTerdeteksi[] = [
                        'tanggal'    => $tanggal,
                        'nama_libur' => $hariLibur->nama_libur,
                    ];
                }
            }

            return $this->hasilSukses('Jadwal kerja berhasil digenerate', [
                'jumlah_tanggal'      => count($tanggalList),
                'jumlah_pegawai'      => count($items),
                'jumlah_data'         => count($rows),
                'warning_hari_libur'  => ! empty($hariLiburTerdeteksi),
                'hari_libur'          => $hariLiburTerdeteksi,
                'status_hari_kerja'   => true,
            ]);
        });
    }

    public function ubah(int $id, array $post): array
    {
        return $this->eksekusi(function () use ($id, $post) {
            $jadwal = $this->jadwalKerjaModel->getJadwalById($id);

            if ($jadwal === null) {
                return $this->hasilTidakDitemukan('Data Jadwal Kerja Tidak Ditemukan');
            }

            if (($jadwal->sumber_data ?? 'manual') !== 'manual') {
                return $this->hasilGagal([], 'Data jadwal hasil override sistem tidak dapat diubah manual');
            }

            if ($jadwal->tanggal < date('Y-m-d')) {
                return $this->hasilGagal([
                    'edit-tanggal' => 'Tidak diizinkan mengubah jadwal sebelum hari ini',
                ]);
            }

            $rules = $this->rulesUbah();

            $validasi = $this->validasi($rules, $post);
            if (! $validasi['sukses']) {
                return $validasi;
            }

            $pegawaiId   = $this->intAtauNull($post['edit-pegawai_id'] ?? null);
            $tanggal     = $this->stringWajib($post['edit-tanggal'] ?? '');
            $statusHari  = $this->stringWajib($post['edit-status_hari'] ?? '');
            $shiftId     = $this->intAtauNull($post['edit-shift_id'] ?? null);
            $catatan     = $this->stringAtauNull($post['edit-catatan'] ?? '');

            $validasiPegawai = $this->validasiPegawaiAktif($pegawaiId, 'edit-pegawai_id');
            if (! $validasiPegawai['sukses']) {
                return $validasiPegawai;
            }

            $validasiStatus = $this->validasiStatusDanShift($statusHari, $shiftId, 'edit-status_hari', 'edit-shift_id');
            if (! $validasiStatus['sukses']) {
                return $validasiStatus;
            }

            if ($statusHari === 'kerja') {
                $validasiShift = $this->validasiShiftAktif($shiftId, 'edit-shift_id');
                if (! $validasiShift['sukses']) {
                    return $validasiShift;
                }
            } else {
                $shiftId = null;
            }

            $bentrok = $this->jadwalKerjaModel->jumlahBentrokJadwal($pegawaiId, $tanggal, $id);
            if ($bentrok > 0) {
                return $this->hasilGagal([
                    'edit-tanggal' => 'Jadwal pegawai pada tanggal tersebut sudah ada',
                ]);
            }

            $simpan = $this->jadwalKerjaModel->save([
                'id'          => $id,
                'pegawai_id'  => $pegawaiId,
                'tanggal'     => $tanggal,
                'shift_id'    => $shiftId,
                'status_hari' => $statusHari,
                'catatan'     => $catatan,
            ]);

            if (! $simpan) {
                return $this->hasilGagal([
                    'general' => 'Data jadwal kerja gagal diubah',
                ]);
            }

            $this->catatAudit(
                'update',
                'jadwal_kerja',
                $id,
                'Mengubah data jadwal kerja pegawai ID ' . $pegawaiId . ' pada tanggal ' . $tanggal
            );

            $hariLiburTerdeteksi = [];
            $hariLibur = $this->getInfoHariLiburPadaTanggal($tanggal);

            if ($hariLibur !== null) {
                $hariLiburTerdeteksi[] = [
                    'tanggal'    => $tanggal,
                    'nama_libur' => $hariLibur->nama_libur,
                ];
            }

            return $this->hasilSukses('Data Jadwal Kerja Berhasil Diubah', [
                'warning_hari_libur' => ! empty($hariLiburTerdeteksi),
                'hari_libur'         => $hariLiburTerdeteksi,
                'status_hari_kerja'  => $statusHari === 'kerja',
            ]);
        });
    }

    public function ambil(int $id): array
    {
        return $this->eksekusi(function () use ($id) {
            $hariIni = date('Y-m-d');
            $jadwal = $this->jadwalKerjaModel->getJadwalById($id);

            if ($jadwal === null) {
                return $this->hasilTidakDitemukan('Data Jadwal Kerja Tidak Ditemukan');
            }

            if ($jadwal->tanggal < $hariIni) {
                return $this->hasilGagal([], 'Tidak diizinkan mengubah jadwal sebelum hari ini');
            }

            if ($this->presensiModel->sudahAdaPresensi($jadwal->pegawai_id, $jadwal->tanggal)) {
                return $this->hasilGagal([], 'Tidak diizinkan mengubah jadwal yang sudah presensi');
            }

            $hariLibur = $this->hariLiburModel->where('tanggal', $jadwal->tanggal)->first();

            return $this->hasilData([
                'jadwal'     => $jadwal,
                'hari_libur' => $hariLibur,
            ]);
        });
    }

    public function copyJadwalPegawai(array $post): array
    {
        return $this->transaksi(function () use ($post) {
            $pegawaiSumberId = $this->intAtauNull($post['copy-pegawai_sumber_id'] ?? null);
            $pegawaiTujuanId = $this->intAtauNull($post['copy-pegawai_tujuan_id'] ?? null);
            $tanggalMulai    = $this->stringWajib($post['copy-tanggal_mulai'] ?? '');
            $tanggalSelesai  = $this->stringWajib($post['copy-tanggal_selesai'] ?? '');
            $catatan         = $this->stringAtauNull($post['copy-catatan'] ?? '');
            $createdBy       = $this->intAtauNull(session()->get('user_id'));
            $hariIni         = date('Y-m-d');

            if ($pegawaiSumberId === null) {
                return $this->hasilGagal([
                    'copy-pegawai_sumber_id' => 'Pegawai sumber harus dipilih'
                ]);
            }

            if ($pegawaiTujuanId === null) {
                return $this->hasilGagal([
                    'copy-pegawai_tujuan_id' => 'Pegawai tujuan harus dipilih'
                ]);
            }

            if ($pegawaiSumberId === $pegawaiTujuanId) {
                return $this->hasilGagal([
                    'copy-pegawai_tujuan_id' => 'Pegawai tujuan tidak boleh sama dengan pegawai sumber'
                ]);
            }

            if ($tanggalMulai === '' || $tanggalSelesai === '') {
                return $this->hasilGagal([
                    'copy-tanggal_mulai'   => 'Tanggal mulai harus diisi',
                    'copy-tanggal_selesai' => 'Tanggal selesai harus diisi',
                ]);
            }

            if ($tanggalMulai < $hariIni) {
                return $this->hasilGagal([
                    'copy-tanggal_mulai' => 'Tanggal mulai tidak boleh sebelum hari ini'
                ]);
            }

            if ($tanggalMulai > $tanggalSelesai) {
                return $this->hasilGagal([
                    'copy-tanggal_mulai'   => 'Tanggal mulai tidak boleh melebihi tanggal selesai',
                    'copy-tanggal_selesai' => 'Tanggal selesai tidak boleh lebih kecil dari tanggal mulai',
                ]);
            }

            $validasiSumber = $this->validasiPegawaiAktif($pegawaiSumberId, 'copy-pegawai_sumber_id');
            if (! $validasiSumber['sukses']) {
                return $validasiSumber;
            }

            $validasiTujuan = $this->validasiPegawaiAktif($pegawaiTujuanId, 'copy-pegawai_tujuan_id');
            if (! $validasiTujuan['sukses']) {
                return $validasiTujuan;
            }

            $jadwalSumber = $this->jadwalKerjaModel->getJadwalPegawaiDalamRentang(
                $pegawaiSumberId,
                $tanggalMulai,
                $tanggalSelesai
            );

            if (empty($jadwalSumber)) {
                return $this->hasilGagal([
                    'copy-tanggal_mulai'   => 'Pegawai sumber belum memiliki jadwal pada rentang tanggal tersebut',
                    'copy-tanggal_selesai' => 'Pegawai sumber belum memiliki jadwal pada rentang tanggal tersebut',
                ]);
            }

            $validasiLengkap = $this->validasiJadwalSumberLengkap(
                $jadwalSumber,
                $tanggalMulai,
                $tanggalSelesai
            );

            if (! $validasiLengkap['sukses']) {
                return $validasiLengkap;
            }

            $duplikatTujuan = [];

            foreach ($jadwalSumber as $jadwal) {
                $tanggal = (string) $jadwal->tanggal;

                if ($this->jadwalKerjaModel->jumlahBentrokJadwal($pegawaiTujuanId, $tanggal) > 0) {
                    $duplikatTujuan[] = tanggal_indonesia($tanggal);
                }
            }

            if (! empty($duplikatTujuan)) {
                return $this->hasilGagal([
                    'copy-tanggal_mulai'   => 'Pegawai tujuan sudah memiliki jadwal pada tanggal: ' . implode(', ', $duplikatTujuan),
                    'copy-tanggal_selesai' => 'Pegawai tujuan sudah memiliki jadwal pada tanggal: ' . implode(', ', $duplikatTujuan),
                ]);
            }

            $validasiSudahAdaSinkron = $this->validasiSudahSinkronTanggal($jadwalSumber);
            if (! $validasiSudahAdaSinkron['sukses']) {
                return $validasiSudahAdaSinkron;
            }

            $rows = [];

            foreach ($jadwalSumber as $jadwal) {
                $rows[] = [
                    'pegawai_id'             => $pegawaiTujuanId,
                    'tanggal'                => $jadwal->tanggal,
                    'shift_id'               => $jadwal->shift_id,
                    'status_hari'            => $jadwal->status_hari,
                    'sumber_data'            => 'manual',
                    'pengajuan_izin_id'      => null,
                    'hari_libur_id'          => null,
                    'shift_id_sebelumnya'    => null,
                    'status_hari_sebelumnya' => null,
                    'catatan_sebelumnya'     => null,
                    'sumber_data_sebelumnya' => null,
                    'catatan'                => $catatan ?: $jadwal->catatan,
                    'created_by'             => $createdBy,
                    'created_at'             => date('Y-m-d H:i:s'),
                    'updated_at'             => date('Y-m-d H:i:s'),
                ];
            }

            if (! $this->jadwalKerjaModel->insertBatch($rows)) {
                return $this->hasilGagal([
                    'general' => 'Copy jadwal pegawai gagal disimpan'
                ]);
            }

            $this->catatAudit(
                'copy',
                'jadwal_kerja',
                null,
                'Copy jadwal dari pegawai ID ' . $pegawaiSumberId .
                    ' ke pegawai ID ' . $pegawaiTujuanId .
                    ' tanggal ' . $tanggalMulai . ' s.d. ' . $tanggalSelesai
            );

            return $this->hasilSukses('Copy jadwal pegawai berhasil', [
                'jumlah_data' => count($rows),
            ]);
        });
    }

    public function simpanIndividu(array $post): array
    {
        return $this->transaksi(function () use ($post) {
            $pegawaiId  = $this->intAtauNull($post['individu-pegawai_id'] ?? null);
            $tanggalRaw = $this->stringWajib($post['individu-tanggal'] ?? '');
            $statusHari = $this->stringWajib($post['individu-status_hari'] ?? '');
            $shiftId    = $this->intAtauNull($post['individu-shift_id'] ?? null);
            $catatan    = $this->stringAtauNull($post['individu-catatan'] ?? '');
            $createdBy  = $this->intAtauNull(session()->get('user_id'));
            $hariIni    = date('Y-m-d');

            $tanggalList = array_values(array_unique(array_filter(array_map('trim', explode(',', $tanggalRaw)))));

            if ($pegawaiId === null) {
                return $this->hasilGagal([
                    'individu-pegawai_id' => 'Pegawai harus dipilih'
                ]);
            }

            if (empty($tanggalList)) {
                return $this->hasilGagal([
                    'individu-tanggal' => 'Tanggal harus dipilih'
                ]);
            }

            if (! in_array($statusHari, ['kerja', 'libur'], true)) {
                return $this->hasilGagal([
                    'individu-status_hari' => 'Status hari tidak valid'
                ]);
            }

            if ($statusHari === 'kerja' && $shiftId === null) {
                return $this->hasilGagal([
                    'individu-shift_id' => 'Shift wajib dipilih jika status kerja'
                ]);
            }

            if ($statusHari === 'libur') {
                $shiftId = null;
            }

            $validasiPegawai = $this->validasiPegawaiAktif($pegawaiId, 'individu-pegawai_id');

            if (! $validasiPegawai['sukses']) {
                return $validasiPegawai;
            }

            if ($statusHari === 'kerja') {
                $validasiShift = $this->validasiShiftAktif($shiftId, 'individu-shift_id');

                if (! $validasiShift['sukses']) {
                    return $validasiShift;
                }
            }

            $tanggalTidakValid = [];
            $tanggalLampau = [];
            $tanggalBelumAdaJadwalLain = [];
            $tanggalBentrok = [];
            $tanggalSudahSinkron = [];

            foreach ($tanggalList as $tanggal) {
                if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
                    $tanggalTidakValid[] = $tanggal;
                    continue;
                }

                if ($tanggal < $hariIni) {
                    $tanggalLampau[] = tanggal_indonesia($tanggal);
                    continue;
                }

                if ($this->presensiModel->countSudahSinkronByTanggal($tanggal) > 0) {
                    $tanggalSudahSinkron[] = tanggal_indonesia($tanggal);
                    continue;
                }

                if ($this->jadwalKerjaModel->jumlahJadwalSelainPegawaiPadaTanggal($pegawaiId, $tanggal) < 1) {
                    $tanggalBelumAdaJadwalLain[] = tanggal_indonesia($tanggal);
                    continue;
                }

                if ($this->jadwalKerjaModel->jumlahBentrokJadwal($pegawaiId, $tanggal) > 0) {
                    $tanggalBentrok[] = tanggal_indonesia($tanggal);
                }
            }

            if (! empty($tanggalTidakValid)) {
                return $this->hasilGagal([
                    'individu-tanggal' => 'Format tanggal tidak valid: ' . implode(', ', $tanggalTidakValid)
                ]);
            }

            if (! empty($tanggalLampau)) {
                return $this->hasilGagal([
                    'individu-tanggal' => 'Tidak boleh membuat jadwal untuk tanggal lampau: ' . implode(', ', $tanggalLampau)
                ]);
            }

            if (! empty($tanggalSudahSinkron)) {
                return $this->hasilGagal([
                    'individu-tanggal' => 'Tidak bisa menambahkan jadwal karena tanggal sudah disinkron: ' . implode(', ', $tanggalSudahSinkron)
                ]);
            }

            if (! empty($tanggalBelumAdaJadwalLain)) {
                return $this->hasilGagal([
                    'individu-tanggal' => 'Tidak bisa menambahkan jadwal individu karena belum ada jadwal pegawai lain pada tanggal: ' . implode(', ', $tanggalBelumAdaJadwalLain)
                ]);
            }

            if (! empty($tanggalBentrok)) {
                return $this->hasilGagal([
                    'individu-tanggal' => 'Pegawai sudah memiliki jadwal pada tanggal: ' . implode(', ', $tanggalBentrok)
                ]);
            }

            $rows = [];

            foreach ($tanggalList as $tanggal) {
                $rows[] = [
                    'pegawai_id'             => $pegawaiId,
                    'tanggal'                => $tanggal,
                    'shift_id'               => $shiftId,
                    'status_hari'            => $statusHari,
                    'sumber_data'            => 'manual',
                    'pengajuan_izin_id'      => null,
                    'hari_libur_id'          => null,
                    'shift_id_sebelumnya'    => null,
                    'status_hari_sebelumnya' => null,
                    'catatan_sebelumnya'     => null,
                    'sumber_data_sebelumnya' => null,
                    'catatan'                => $catatan,
                    'created_by'             => $createdBy,
                    'created_at'             => date('Y-m-d H:i:s'),
                    'updated_at'             => date('Y-m-d H:i:s'),
                ];
            }

            if (! $this->jadwalKerjaModel->insertBatch($rows)) {
                return $this->hasilGagal([
                    'general' => 'Jadwal individu gagal disimpan'
                ]);
            }

            $this->catatAudit(
                'create individu',
                'jadwal_kerja',
                null,
                'Menambahkan jadwal individu pegawai ID ' . $pegawaiId .
                    ' pada tanggal ' . implode(', ', $tanggalList) .
                    ' dengan status ' . $statusHari
            );

            return $this->hasilSukses('Jadwal individu berhasil ditambahkan', [
                'jumlah_data' => count($rows),
            ]);
        });
    }

    public function kalender(string $start, string $end): array
    {
        return $this->eksekusi(function () use ($start, $end) {
            $rows = $this->jadwalKerjaModel->getKalenderRingkasan($start, $end);

            $totalPegawaiAktif = (int) $this->pegawaiModel
                ->where('is_active', 1)
                ->countAllResults();

            $events = [];

            foreach ($rows as $row) {
                $totalJadwal = (int) $row->total_jadwal;
                $totalKerja  = (int) $row->total_kerja;
                $totalLibur  = (int) $row->total_libur;
                $totalIzin   = (int) $row->total_izin;
                $totalSakit  = (int) $row->total_sakit;

                $bermasalah = $totalJadwal !== $totalPegawaiAktif;

                $classNames = ['fc-jadwal-event'];

                if ($bermasalah) {
                    $classNames[] = 'fc-jadwal-bermasalah';
                } elseif ($totalLibur === $totalJadwal) {
                    $classNames[] = 'fc-jadwal-libur';
                } elseif ($totalKerja === $totalJadwal) {
                    $classNames[] = 'fc-jadwal-kerja';
                } else {
                    $classNames[] = 'fc-jadwal-campuran';
                }

                $events[] = [
                    'title'      => $totalJadwal . ' Jadwal',
                    'start'      => $row->tanggal,
                    'allDay'     => true,
                    'classNames' => $classNames,
                    'extendedProps' => [
                        'tanggal'             => $row->tanggal,
                        'total_jadwal'        => $totalJadwal,
                        'total_pegawai_aktif' => $totalPegawaiAktif,
                        'total_kerja'         => $totalKerja,
                        'total_libur'         => $totalLibur,
                        'total_izin'          => $totalIzin,
                        'total_sakit'         => $totalSakit,
                        'bermasalah'          => $bermasalah,
                        'kurang_jadwal'       => max($totalPegawaiAktif - $totalJadwal, 0),
                        'lebih_jadwal'        => max($totalJadwal - $totalPegawaiAktif, 0),
                    ],
                ];
            }

            return $events;
        });
    }

    public function detailTanggal(string $tanggal): array
    {
        return $this->eksekusi(function () use ($tanggal) {
            return $this->hasilData([
                'tanggal' => $tanggal,
                'items' => $this->jadwalKerjaModel->getDetailByTanggal($tanggal),
            ]);
        });
    }

    protected function parseTanggalList($value): array
    {
        return array_values(array_unique(array_filter(array_map('trim', explode(',', (string) $value)))));
    }

    protected function bangunItemGenerate(array $shiftPegawai, array $liburPegawai): array
    {
        $items = [];

        foreach ($shiftPegawai as $shiftId => $pegawaiIds) {
            $shiftId = (int) $shiftId;

            if ($shiftId <= 0) {
                continue;
            }

            foreach ((array) $pegawaiIds as $pegawaiId) {
                $pegawaiId = (int) $pegawaiId;

                if ($pegawaiId <= 0) {
                    continue;
                }

                $items[] = [
                    'pegawai_id'  => $pegawaiId,
                    'shift_id'    => $shiftId,
                    'status_hari' => 'kerja',
                ];
            }
        }

        foreach ((array) $liburPegawai as $pegawaiId) {
            $pegawaiId = (int) $pegawaiId;

            if ($pegawaiId <= 0) {
                continue;
            }

            $items[] = [
                'pegawai_id'  => $pegawaiId,
                'shift_id'    => null,
                'status_hari' => 'libur',
            ];
        }

        return $items;
    }

    protected function validasiCoveragePegawaiAktif(array $items): array
    {
        $pegawaiAktif = $this->pegawaiModel
            ->select('id, kode_pegawai, nama_pegawai')
            ->where('is_active', 1)
            ->orderBy('nama_pegawai', 'ASC')
            ->findAll();

        if (empty($pegawaiAktif)) {
            return $this->hasilGagal([
                'pegawai' => 'Tidak ada pegawai aktif untuk dijadwalkan',
            ]);
        }

        $pegawaiAktifIds = array_map(static fn($row) => (int) $row->id, $pegawaiAktif);
        $pegawaiTerpilih = array_map('intval', array_column($items, 'pegawai_id'));

        $duplikatPegawai = array_values(array_unique(array_diff_assoc($pegawaiTerpilih, array_unique($pegawaiTerpilih))));

        if (! empty($duplikatPegawai)) {
            return $this->hasilGagal([
                'pegawai' => 'Pegawai tidak boleh dipilih lebih dari satu section',
            ]);
        }

        $pegawaiTidakAktifDipilih = array_values(array_diff($pegawaiTerpilih, $pegawaiAktifIds));
        if (! empty($pegawaiTidakAktifDipilih)) {
            return $this->hasilGagal([
                'pegawai' => 'Terdapat pegawai tidak aktif atau tidak valid yang dipilih',
            ]);
        }

        $pegawaiBelumDipilih = array_values(array_diff($pegawaiAktifIds, $pegawaiTerpilih));
        if (! empty($pegawaiBelumDipilih)) {
            $namaBelumDipilih = [];

            foreach ($pegawaiAktif as $pegawai) {
                if (in_array((int) $pegawai->id, $pegawaiBelumDipilih, true)) {
                    $namaBelumDipilih[] = '<li>' . $pegawai->nama_pegawai . '</li>';
                }
            }

            return $this->hasilGagal([
                'pegawai' => '<strong>Semua pegawai aktif harus dijadwalkan. Belum dipilih: </strong><br /><ul>' . implode('', $namaBelumDipilih) . '</ul>',
            ]);
        }

        return $this->hasilSukses();
    }

    protected function validasiSemuaShiftGenerate(array $items): array
    {
        $shiftIds = [];

        foreach ($items as $item) {
            if (($item['status_hari'] ?? '') === 'kerja') {
                $shiftIds[] = (int) $item['shift_id'];
            }
        }

        $shiftIds = array_values(array_unique($shiftIds));

        foreach ($shiftIds as $shiftId) {
            $validasiShift = $this->validasiShiftAktif($shiftId, 'shift_pegawai');

            if (! $validasiShift['sukses']) {
                return $validasiShift;
            }
        }

        return $this->hasilSukses();
    }

    protected function cariDuplikatJadwalGenerate(array $items, array $tanggalList): array
    {
        $duplikat = [];

        foreach ($items as $item) {
            foreach ($tanggalList as $tanggal) {
                $bentrok = $this->jadwalKerjaModel->jumlahBentrokJadwal((int) $item['pegawai_id'], $tanggal);

                if ($bentrok > 0) {
                    $duplikat[] = $tanggal . ' - Pegawai ID ' . $item['pegawai_id'];
                }
            }
        }

        return array_values(array_unique($duplikat));
    }

    protected function rulesUbah(): array
    {
        return [
            'edit-pegawai_id' => [
                'label'  => 'Pegawai',
                'rules'  => 'required|integer',
                'errors' => [
                    'required' => '{field} harus dipilih',
                    'integer'  => '{field} tidak valid',
                ],
            ],
            'edit-tanggal' => [
                'label'  => 'Tanggal',
                'rules'  => 'required|valid_date[Y-m-d]',
                'errors' => [
                    'required'   => '{field} harus diisi',
                    'valid_date' => '{field} tidak valid',
                ],
            ],
            'edit-shift_id' => [
                'label'  => 'Shift',
                'rules'  => 'permit_empty|integer',
                'errors' => [
                    'integer' => '{field} tidak valid',
                ],
            ],
            'edit-status_hari' => [
                'label'  => 'Status Hari',
                'rules'  => 'required|in_list[kerja,libur]',
                'errors' => [
                    'required' => '{field} harus dipilih',
                    'in_list'  => '{field} tidak valid',
                ],
            ],
            'edit-catatan' => [
                'label'  => 'Catatan',
                'rules'  => 'permit_empty|max_length[255]',
                'errors' => [
                    'max_length' => '{field} maksimal 255 karakter',
                ],
            ],
        ];
    }

    protected function validasiPegawaiAktif(?int $pegawaiId, string $field): array
    {
        if ($pegawaiId === null) {
            return $this->hasilGagal([
                $field => 'Pegawai harus dipilih',
            ]);
        }

        $pegawai = $this->pegawaiModel->getPegawaiById($pegawaiId);

        if ($pegawai === null) {
            return $this->hasilGagal([
                $field => 'Data pegawai tidak ditemukan',
            ]);
        }

        if ((int) ($pegawai->is_active ?? 0) !== 1) {
            return $this->hasilGagal([
                $field => 'Pegawai tidak aktif',
            ]);
        }

        return $this->hasilSukses();
    }

    protected function validasiShiftAktif(?int $shiftId, string $field): array
    {
        if ($shiftId === null) {
            return $this->hasilGagal([
                $field => 'Shift harus dipilih',
            ]);
        }

        $shift = $this->shiftModel->getShiftById($shiftId);

        if ($shift === null) {
            return $this->hasilGagal([
                $field => 'Data shift tidak ditemukan',
            ]);
        }

        if ((int) ($shift->is_active ?? 0) !== 1) {
            return $this->hasilGagal([
                $field => 'Shift tidak aktif',
            ]);
        }

        return $this->hasilSukses();
    }

    protected function validasiStatusDanShift(
        string $statusHari,
        ?int $shiftId,
        string $fieldStatus,
        string $fieldShift
    ): array {
        if (! in_array($statusHari, ['kerja', 'libur'], true)) {
            return $this->hasilGagal([
                $fieldStatus => 'Status hari tidak valid',
            ]);
        }

        if ($statusHari === 'kerja' && $shiftId === null) {
            return $this->hasilGagal([
                $fieldShift => 'Shift wajib dipilih jika status hari kerja',
            ]);
        }

        if ($statusHari === 'libur' && $shiftId !== null) {
            return $this->hasilGagal([
                $fieldShift => 'Shift harus dikosongkan jika status hari libur',
            ]);
        }

        return $this->hasilSukses();
    }

    protected function getInfoHariLiburPadaTanggal(string $tanggal): ?object
    {
        return $this->hariLiburModel->where('tanggal', $tanggal)->first();
    }

    protected function validasiJadwalSumberLengkap(
        array $jadwalSumber,
        string $tanggalMulai,
        string $tanggalSelesai
    ): array {
        $tanggalAda = [];

        foreach ($jadwalSumber as $jadwal) {
            $tanggalAda[] = (string) $jadwal->tanggal;
        }

        $tanggalKosong = [];
        $tanggal = $tanggalMulai;

        while ($tanggal <= $tanggalSelesai) {
            if (! in_array($tanggal, $tanggalAda, true)) {
                $tanggalKosong[] = tanggal_indonesia($tanggal);
            }

            $tanggal = date('Y-m-d', strtotime($tanggal . ' +1 day'));
        }

        if (! empty($tanggalKosong)) {
            return $this->hasilGagal([
                'copy-tanggal_mulai'   => 'Jadwal sumber belum lengkap. Tanggal belum terjadwal: ' . implode(', ', $tanggalKosong),
                'copy-tanggal_selesai' => 'Jadwal sumber belum lengkap. Tanggal belum terjadwal: ' . implode(', ', $tanggalKosong),
            ]);
        }

        return $this->hasilSukses();
    }

    protected function validasiSudahSinkronTanggal(array $jadwalSumber)
    {
        $tanggalSudahSinkron = [];

        foreach ($jadwalSumber as $jadwal) {
            $tanggal = (string) $jadwal->tanggal;

            if ($this->presensiModel->countSudahSinkronByTanggal($tanggal) > 0) {
                $tanggalSudahSinkron[] = tanggal_indonesia($tanggal);
            }
        }

        if (! empty($tanggalSudahSinkron)) {
            return $this->hasilGagal([
                'copy-tanggal_mulai'   => 'Tidak bisa copy jadwal karena tanggal sudah disinkron: ' . implode(', ', $tanggalSudahSinkron),
                'copy-tanggal_selesai' => 'Tidak bisa copy jadwal karena tanggal sudah disinkron: ' . implode(', ', $tanggalSudahSinkron),
            ]);
        }

        return $this->hasilSukses();
    }
}
