<?php

namespace App\Services;

use App\Models\HariLiburModel;
use App\Models\JadwalKerjaModel;
use App\Models\PegawaiModel;
use App\Models\ShiftModel;

class JadwalKerjaService extends BaseService
{
    protected JadwalKerjaModel $jadwalKerjaModel;
    protected PegawaiModel $pegawaiModel;
    protected ShiftModel $shiftModel;
    protected HariLiburModel $hariLiburModel;

    public function __construct()
    {
        parent::__construct();
        $this->jadwalKerjaModel = new JadwalKerjaModel();
        $this->pegawaiModel     = new PegawaiModel();
        $this->shiftModel       = new ShiftModel();
        $this->hariLiburModel   = new HariLiburModel();
    }

    public function dataTabel()
    {
        return $this->jadwalKerjaModel->selectData();
    }

    public function getPegawaiDropdown(): array
    {
        return $this->eksekusi(function () {
            $pegawai = $this->pegawaiModel->db->table('pegawai')
                ->select('pegawai.id, pegawai.kode_pegawai, pegawai.nama_pegawai')
                ->where('pegawai.is_active', 1)
                ->orderBy('pegawai.nama_pegawai', 'ASC')
                ->get()
                ->getResult();

            return $this->hasilData([
                'pegawai' => $pegawai
            ]);
        });
    }

    public function getShiftDropdown(): array
    {
        return $this->eksekusi(function () {
            $shift = $this->shiftModel->db->table('shift')
                ->select('shift.id, shift.kode_shift, shift.nama_shift')
                ->where('shift.is_active', 1)
                ->orderBy('shift.nama_shift', 'ASC')
                ->get()
                ->getResult();

            return $this->hasilData([
                'shift' => $shift
            ]);
        });
    }

    public function simpan(array $post): array
    {
        return $this->transaksi(function () use ($post) {
            $post['pegawai_id_validasi'] = '1';

            $rules = [
                'pegawai_id_validasi' => [
                    'label' => 'Pegawai',
                    'rules' => [
                        static function ($value, array $data, ?string &$error): bool {
                            if (
                                ! isset($data['pegawai_id']) ||
                                ! is_array($data['pegawai_id']) ||
                                count($data['pegawai_id']) < 1
                            ) {
                                $error = 'Pegawai harus dipilih';
                                return false;
                            }

                            foreach ($data['pegawai_id'] as $pegawaiId) {
                                if (! ctype_digit((string) $pegawaiId)) {
                                    $error = 'Data pegawai tidak valid';
                                    return false;
                                }
                            }

                            return true;
                        }
                    ],
                    'errors' => []
                ],
                'tanggal' => [
                    'label' => 'Tanggal',
                    'rules' => [
                        static function ($value, array $data, ?string &$error): bool {
                            $tanggalList = array_values(array_filter(array_map('trim', explode(',', (string) $value))));

                            if (count($tanggalList) < 1) {
                                $error = 'Tanggal harus dipilih';
                                return false;
                            }

                            foreach ($tanggalList as $tanggal) {
                                if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
                                    $error = 'Format tanggal tidak valid';
                                    return false;
                                }
                            }

                            return true;
                        }
                    ],
                    'errors' => []
                ],
                'status_hari' => [
                    'label'  => 'Status Hari',
                    'rules'  => 'required|in_list[kerja,libur]',
                    'errors' => [
                        'required' => '{field} harus dipilih',
                        'in_list'  => '{field} tidak valid',
                    ]
                ],
                'shift_id' => [
                    'label'  => 'Shift',
                    'rules'  => 'permit_empty|integer',
                    'errors' => [
                        'integer' => '{field} tidak valid',
                    ]
                ],
                'catatan' => [
                    'label'  => 'Catatan',
                    'rules'  => 'permit_empty|max_length[255]',
                    'errors' => [
                        'max_length' => '{field} maksimal 255 karakter',
                    ]
                ],
            ];

            $validasi = $this->validasi($rules, $post);

            if (! $validasi['sukses']) {
                if (isset($validasi['errors']['pegawai_id_validasi'])) {
                    $validasi['errors']['pegawai_id'] = $validasi['errors']['pegawai_id_validasi'];
                    unset($validasi['errors']['pegawai_id_validasi']);
                }

                return $validasi;
            }

            $pegawaiIdsRaw = $post['pegawai_id'] ?? [];
            $tanggalRaw    = $this->stringWajib($post['tanggal'] ?? '');
            $statusHari    = $this->stringWajib($post['status_hari'] ?? '');
            $shiftId       = $this->intAtauNull($post['shift_id'] ?? null);
            $catatan       = $this->stringAtauNull($post['catatan'] ?? '');
            $createdBy     = $this->intAtauNull(session()->get('user_id'));

            $pegawaiIds = array_values(array_unique(array_map('intval', $pegawaiIdsRaw)));
            $tanggalList = array_values(array_unique(array_filter(array_map('trim', explode(',', $tanggalRaw)))));

            $validasiStatus = $this->validasiStatusDanShift(
                $statusHari,
                $shiftId,
                'status_hari',
                'shift_id'
            );

            if (! $validasiStatus['sukses']) {
                return $validasiStatus;
            }

            if ($statusHari === 'kerja') {
                $validasiShift = $this->validasiShiftAktif($shiftId, 'shift_id');

                if (! $validasiShift['sukses']) {
                    return $validasiShift;
                }
            } else {
                $shiftId = null;
            }

            foreach ($pegawaiIds as $pegawaiId) {
                $validasiPegawai = $this->validasiPegawaiAktif($pegawaiId, 'pegawai_id');

                if (! $validasiPegawai['sukses']) {
                    return $validasiPegawai;
                }
            }

            $duplikat = [];

            foreach ($pegawaiIds as $pegawaiId) {
                foreach ($tanggalList as $tanggal) {
                    $bentrok = $this->jadwalKerjaModel->jumlahBentrokJadwal($pegawaiId, $tanggal);

                    if ($bentrok > 0) {
                        $duplikat[] = $tanggal;
                    }
                }
            }

            if (! empty($duplikat)) {
                $duplikat = array_values(array_unique($duplikat));

                return $this->hasilGagal([
                    'tanggal' => 'Sebagian tanggal sudah memiliki jadwal kerja: ' . implode(', ', $duplikat)
                ]);
            }

            foreach ($pegawaiIds as $pegawaiId) {
                foreach ($tanggalList as $tanggal) {
                    $simpan = $this->jadwalKerjaModel->insert([
                        'pegawai_id'             => $pegawaiId,
                        'tanggal'                => $tanggal,
                        'shift_id'               => $shiftId,
                        'status_hari'            => $statusHari,
                        'sumber_data'            => 'manual',
                        'pengajuan_izin_id'      => null,
                        'shift_id_sebelumnya'    => null,
                        'status_hari_sebelumnya' => null,
                        'catatan_sebelumnya'     => null,
                        'sumber_data_sebelumnya' => null,
                        'catatan'                => $catatan,
                        'created_by'             => $createdBy,
                    ]);

                    if (! $simpan) {
                        return $this->hasilGagal([
                            'general' => 'Data jadwal kerja gagal disimpan'
                        ]);
                    }
                }
            }

            $this->catatAudit(
                'create',
                'jadwal_kerja',
                null,
                'Menambahkan jadwal kerja untuk '
                    . count($pegawaiIds) . ' pegawai dengan ID (' . implode(', ', $pegawaiIds) . ') '
                    . 'pada ' . count($tanggalList) . ' tanggal (' . implode(', ', $tanggalList) . ') '
                    . 'dengan status hari ' . $statusHari
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

            return $this->hasilSukses('Data Jadwal Kerja Berhasil Ditambahkan', [
                'warning_hari_libur' => ! empty($hariLiburTerdeteksi),
                'hari_libur'         => $hariLiburTerdeteksi,
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
                    'edit-tanggal' => 'Jadwal pegawai pada tanggal tersebut sudah ada'
                ]);
            }

            $simpan = $this->jadwalKerjaModel->save([
                'id'                       => $id,
                'pegawai_id'               => $pegawaiId,
                'tanggal'                  => $tanggal,
                'shift_id'                 => $shiftId,
                'status_hari'              => $statusHari,
                'catatan'                  => $catatan,
            ]);

            if (! $simpan) {
                return $this->hasilGagal([
                    'general' => 'Data jadwal kerja gagal diubah'
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
            ]);
        });
    }

    public function ambil(int $id): array
    {
        return $this->eksekusi(function () use ($id) {
            $jadwal = $this->jadwalKerjaModel->getJadwalById($id);

            if ($jadwal === null) {
                return $this->hasilTidakDitemukan('Data Jadwal Kerja Tidak Ditemukan');
            }

            $hariLibur = $this->hariLiburModel->where('tanggal', $jadwal->tanggal)->first();

            return $this->hasilData([
                'jadwal'     => $jadwal,
                'hari_libur' => $hariLibur,
            ]);
        });
    }

    public function hapus(int $id): array
    {
        return $this->eksekusi(function () use ($id) {
            $jadwal = $this->jadwalKerjaModel->getJadwalById($id);

            if ($jadwal === null) {
                return $this->hasilTidakDitemukan('Data Jadwal Kerja Tidak Ada Di Database');
            }

            if (($jadwal->sumber_data ?? 'manual') !== 'manual') {
                return $this->hasilGagal([], 'Data jadwal hasil override sistem tidak dapat diubah manual');
            }

            $hapus = $this->jadwalKerjaModel->delete($id);

            if (! $hapus) {
                return $this->hasilGagal([], 'Data Jadwal Kerja Gagal Dihapus');
            }

            $this->catatAudit(
                'delete',
                'jadwal_kerja',
                $id,
                'Menghapus data jadwal kerja pegawai ID ' . (string) $jadwal->pegawai_id . ' pada tanggal ' . (string) $jadwal->tanggal
            );

            return $this->hasilSukses('Data Jadwal Kerja Berhasil Dihapus');
        });
    }

    protected function rulesSimpan(): array
    {
        return [
            'pegawai_id' => [
                'label'  => 'Pegawai',
                'rules'  => 'required|integer',
                'errors' => [
                    'required' => '{field} harus dipilih',
                    'integer'  => '{field} tidak valid',
                ]
            ],
            'tanggal' => [
                'label'  => 'Tanggal',
                'rules'  => 'required|valid_date[Y-m-d]',
                'errors' => [
                    'required'   => '{field} harus diisi',
                    'valid_date' => '{field} tidak valid',
                ]
            ],
            'shift_id' => [
                'label'  => 'Shift',
                'rules'  => 'permit_empty|integer',
                'errors' => [
                    'integer' => '{field} tidak valid',
                ]
            ],
            'status_hari' => [
                'label'  => 'Status Hari',
                'rules'  => 'required|in_list[kerja,libur]',
                'errors' => [
                    'required' => '{field} harus dipilih',
                    'in_list'  => '{field} tidak valid',
                ]
            ],
            'catatan' => [
                'label'  => 'Catatan',
                'rules'  => 'permit_empty|max_length[255]',
                'errors' => [
                    'max_length' => '{field} maksimal 255 karakter',
                ]
            ],
        ];
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
                ]
            ],
            'edit-tanggal' => [
                'label'  => 'Tanggal',
                'rules'  => 'required|valid_date[Y-m-d]',
                'errors' => [
                    'required'   => '{field} harus diisi',
                    'valid_date' => '{field} tidak valid',
                ]
            ],
            'edit-shift_id' => [
                'label'  => 'Shift',
                'rules'  => 'permit_empty|integer',
                'errors' => [
                    'integer' => '{field} tidak valid',
                ]
            ],
            'edit-status_hari' => [
                'label'  => 'Status Hari',
                'rules'  => 'required|in_list[kerja,libur]',
                'errors' => [
                    'required' => '{field} harus dipilih',
                    'in_list'  => '{field} tidak valid',
                ]
            ],
            'edit-catatan' => [
                'label'  => 'Catatan',
                'rules'  => 'permit_empty|max_length[255]',
                'errors' => [
                    'max_length' => '{field} maksimal 255 karakter',
                ]
            ],
        ];
    }

    protected function validasiPegawaiAktif(?int $pegawaiId, string $field): array
    {
        if ($pegawaiId === null) {
            return $this->hasilGagal([
                $field => 'Pegawai harus dipilih'
            ]);
        }

        $pegawai = $this->pegawaiModel->getPegawaiById($pegawaiId);

        if ($pegawai === null) {
            return $this->hasilGagal([
                $field => 'Data pegawai tidak ditemukan'
            ]);
        }

        if ((int) ($pegawai->is_active ?? 0) !== 1) {
            return $this->hasilGagal([
                $field => 'Pegawai tidak aktif'
            ]);
        }

        return $this->hasilSukses();
    }

    protected function validasiShiftAktif(?int $shiftId, string $field): array
    {
        if ($shiftId === null) {
            return $this->hasilGagal([
                $field => 'Shift harus dipilih'
            ]);
        }

        $shift = $this->shiftModel->getShiftById($shiftId);

        if ($shift === null) {
            return $this->hasilGagal([
                $field => 'Data shift tidak ditemukan'
            ]);
        }

        if ((int) ($shift->is_active ?? 0) !== 1) {
            return $this->hasilGagal([
                $field => 'Shift tidak aktif'
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
        if ($statusHari === 'kerja' && $shiftId === null) {
            return $this->hasilGagal([
                $fieldShift => 'Shift wajib dipilih jika status hari kerja'
            ]);
        }

        if ($statusHari === 'libur' && $shiftId !== null) {
            return $this->hasilGagal([
                $fieldShift => 'Shift harus dikosongkan jika status hari libur'
            ]);
        }

        return $this->hasilSukses();
    }

    protected function getInfoHariLiburPadaTanggal(string $tanggal): ?object
    {
        return $this->hariLiburModel->where('tanggal', $tanggal)->first();
    }
}
