<?php

namespace App\Services\Admin;

use App\Models\ShiftModel;
use App\Services\BaseService;

class ShiftService extends BaseService
{
    protected ShiftModel $shiftModel;

    public function __construct()
    {
        parent::__construct();
        $this->shiftModel = new ShiftModel();
    }

    public function dataTabel()
    {
        return $this->shiftModel->selectData();
    }

    public function simpan(array $post): array
    {
        return $this->eksekusi(function () use ($post) {
            $rules = [
                'nama_shift' => [
                    'label'  => 'Nama Shift',
                    'rules'  => 'required|max_length[100]|regex_match[/^[a-zA-Z\s]+$/]|is_unique[shift.nama_shift]',
                    'errors' => [
                        'required'   => '{field} harus diisi',
                        'max_length' => '{field} maksimal 100 karakter',
                        'regex_match' => '{field} hanya boleh diisi huruf, dan spasi',
                        'is_unique'  => '{field} sudah terdaftar',
                    ]
                ],
                'jam_masuk' => [
                    'label'  => 'Jam Masuk',
                    'rules'  => 'required|regex_match[/^(2[0-3]|[01]?[0-9]):[0-5][0-9](:[0-5][0-9])?$/]',
                    'errors' => [
                        'required'    => '{field} harus diisi',
                        'regex_match' => '{field} harus berformat HH:MM atau HH:MM:SS',
                    ]
                ],
                'batas_mulai_datang' => [
                    'label'  => 'Batas Mulai Datang',
                    'rules'  => 'required|regex_match[/^(2[0-3]|[01]?[0-9]):[0-5][0-9](:[0-5][0-9])?$/]',
                    'errors' => [
                        'required'    => '{field} harus diisi',
                        'regex_match' => '{field} harus berformat HH:MM atau HH:MM:SS',
                    ]
                ],
                'batas_akhir_datang' => [
                    'label'  => 'Batas Akhir Datang',
                    'rules'  => 'required|regex_match[/^(2[0-3]|[01]?[0-9]):[0-5][0-9](:[0-5][0-9])?$/]',
                    'errors' => [
                        'required'    => '{field} harus diisi',
                        'regex_match' => '{field} harus berformat HH:MM atau HH:MM:SS',
                    ]
                ],
                'jam_pulang' => [
                    'label'  => 'Jam Pulang',
                    'rules'  => 'required|regex_match[/^(2[0-3]|[01]?[0-9]):[0-5][0-9](:[0-5][0-9])?$/]',
                    'errors' => [
                        'required'    => '{field} harus diisi',
                        'regex_match' => '{field} harus berformat HH:MM atau HH:MM:SS',
                    ]
                ],
                'batas_mulai_pulang' => [
                    'label'  => 'Batas Mulai Pulang',
                    'rules'  => 'required|regex_match[/^(2[0-3]|[01]?[0-9]):[0-5][0-9](:[0-5][0-9])?$/]',
                    'errors' => [
                        'required'    => '{field} harus diisi',
                        'regex_match' => '{field} harus berformat HH:MM atau HH:MM:SS',
                    ]
                ],
                'batas_akhir_pulang' => [
                    'label'  => 'Batas Akhir Pulang',
                    'rules'  => 'required|regex_match[/^(2[0-3]|[01]?[0-9]):[0-5][0-9](:[0-5][0-9])?$/]',
                    'errors' => [
                        'required'    => '{field} harus diisi',
                        'regex_match' => '{field} harus berformat HH:MM atau HH:MM:SS',
                    ]
                ],
                'toleransi_telat_menit' => [
                    'label'  => 'Toleransi Telat',
                    'rules'  => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[60]',
                    'errors' => [
                        'required'              => '{field} harus diisi',
                        'integer'               => '{field} harus berupa angka',
                        'greater_than_equal_to' => '{field} minimal 0',
                        'less_than_equal_to'    => '{field} maksimal 60 menit',
                    ]
                ],
                'keterangan' => [
                    'label'  => 'Keterangan',
                    'rules'  => 'permit_empty|max_length[255]',
                    'errors' => [
                        'max_length' => '{field} maksimal 255 karakter',
                    ]
                ],
                'is_active' => [
                    'label'  => 'Status Aktif',
                    'rules'  => 'required|in_list[0,1]',
                    'errors' => [
                        'required' => '{field} harus diisi',
                        'in_list'  => '{field} tidak valid',
                    ]
                ],
            ];

            $validasi = $this->validasi($rules, $post);
            if (! $validasi['sukses']) {
                return $validasi;
            }

            $namaShift        = $this->stringWajib($post['nama_shift'] ?? '');
            $kodeShift        = url_title($namaShift, '-', true);
            $jamMasuk         = $this->formatJam($this->stringWajib($post['jam_masuk'] ?? ''));
            $mulaiDatang      = $this->formatJam($this->stringWajib($post['batas_mulai_datang'] ?? ''));
            $akhirDatang      = $this->formatJam($this->stringWajib($post['batas_akhir_datang'] ?? ''));
            $jamPulang        = $this->formatJam($this->stringWajib($post['jam_pulang'] ?? ''));
            $mulaiPulang      = $this->formatJam($this->stringWajib($post['batas_mulai_pulang'] ?? ''));
            $akhirPulang      = $this->formatJam($this->stringWajib($post['batas_akhir_pulang'] ?? ''));
            $toleransiTelat   = $this->intVal($post['toleransi_telat_menit'] ?? 0);
            $keterangan       = $this->stringAtauNull($post['keterangan'] ?? '');
            $isActive         = $this->intVal($post['is_active'] ?? 1, 1);

            $errorKode = $this->validasiKodeShift($kodeShift, null, 'nama_shift');
            if ($errorKode !== null) {
                return $this->hasilGagal($errorKode);
            }

            $errorWaktu = $this->validasiWaktuShift(
                $jamMasuk,
                $mulaiDatang,
                $akhirDatang,
                $jamPulang,
                $mulaiPulang,
                $akhirPulang,
                false
            );
            if ($errorWaktu !== null) {
                return $this->hasilGagal($errorWaktu);
            }

            $simpan = $this->shiftModel->save([
                'kode_shift'            => $kodeShift,
                'nama_shift'            => $namaShift,
                'jam_masuk'             => $jamMasuk,
                'batas_mulai_datang'    => $mulaiDatang,
                'batas_akhir_datang'    => $akhirDatang,
                'jam_pulang'            => $jamPulang,
                'batas_mulai_pulang'    => $mulaiPulang,
                'batas_akhir_pulang'    => $akhirPulang,
                'toleransi_telat_menit' => $toleransiTelat,
                'keterangan'            => $keterangan,
                'is_active'             => $isActive,
            ]);

            if (! $simpan) {
                return $this->hasilGagal([
                    'general' => 'Data shift gagal disimpan'
                ]);
            }

            $this->catatAudit(
                'create',
                'shift',
                (int) $this->shiftModel->getInsertID(),
                'Menambahkan data shift: ' . $namaShift
            );

            return $this->hasilSukses('Data Shift Berhasil Ditambahkan');
        });
    }

    public function ubah(int $id, array $post): array
    {
        return $this->eksekusi(function () use ($id, $post) {
            $rules = [
                'edit-nama_shift' => [
                    'label'  => 'Nama Shift',
                    'rules'  => "required|max_length[100]|regex_match[/^[a-zA-Z\s]+$/]|is_unique[shift.nama_shift,id,{$id}]",
                    'errors' => [
                        'required'   => '{field} harus diisi',
                        'max_length' => '{field} maksimal 100 karakter',
                        'regex_match' => '{field} hanya boleh diisi huruf, dan spasi',
                        'is_unique'  => '{field} sudah terdaftar',
                    ]
                ],
                'edit-jam_masuk' => [
                    'label'  => 'Jam Masuk',
                    'rules'  => 'required|regex_match[/^(2[0-3]|[01]?[0-9]):[0-5][0-9](:[0-5][0-9])?$/]',
                    'errors' => [
                        'required'    => '{field} harus diisi',
                        'regex_match' => '{field} harus berformat HH:MM atau HH:MM:SS',
                    ]
                ],
                'edit-batas_mulai_datang' => [
                    'label'  => 'Batas Mulai Datang',
                    'rules'  => 'required|regex_match[/^(2[0-3]|[01]?[0-9]):[0-5][0-9](:[0-5][0-9])?$/]',
                    'errors' => [
                        'required'    => '{field} harus diisi',
                        'regex_match' => '{field} harus berformat HH:MM atau HH:MM:SS',
                    ]
                ],
                'edit-batas_akhir_datang' => [
                    'label'  => 'Batas Akhir Datang',
                    'rules'  => 'required|regex_match[/^(2[0-3]|[01]?[0-9]):[0-5][0-9](:[0-5][0-9])?$/]',
                    'errors' => [
                        'required'    => '{field} harus diisi',
                        'regex_match' => '{field} harus berformat HH:MM atau HH:MM:SS',
                    ]
                ],
                'edit-jam_pulang' => [
                    'label'  => 'Jam Pulang',
                    'rules'  => 'required|regex_match[/^(2[0-3]|[01]?[0-9]):[0-5][0-9](:[0-5][0-9])?$/]',
                    'errors' => [
                        'required'    => '{field} harus diisi',
                        'regex_match' => '{field} harus berformat HH:MM atau HH:MM:SS',
                    ]
                ],
                'edit-batas_mulai_pulang' => [
                    'label'  => 'Batas Mulai Pulang',
                    'rules'  => 'required|regex_match[/^(2[0-3]|[01]?[0-9]):[0-5][0-9](:[0-5][0-9])?$/]',
                    'errors' => [
                        'required'    => '{field} harus diisi',
                        'regex_match' => '{field} harus berformat HH:MM atau HH:MM:SS',
                    ]
                ],
                'edit-batas_akhir_pulang' => [
                    'label'  => 'Batas Akhir Pulang',
                    'rules'  => 'required|regex_match[/^(2[0-3]|[01]?[0-9]):[0-5][0-9](:[0-5][0-9])?$/]',
                    'errors' => [
                        'required'    => '{field} harus diisi',
                        'regex_match' => '{field} harus berformat HH:MM atau HH:MM:SS',
                    ]
                ],
                'edit-toleransi_telat_menit' => [
                    'label'  => 'Toleransi Telat',
                    'rules'  => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[60]',
                    'errors' => [
                        'required'              => '{field} harus diisi',
                        'integer'               => '{field} harus berupa angka',
                        'greater_than_equal_to' => '{field} minimal 0',
                        'less_than_equal_to'    => '{field} maksimal 60 menit',
                    ]
                ],
                'edit-keterangan' => [
                    'label'  => 'Keterangan',
                    'rules'  => 'permit_empty|max_length[255]',
                    'errors' => [
                        'max_length' => '{field} maksimal 255 karakter',
                    ]
                ],
                'edit-is_active' => [
                    'label'  => 'Status Aktif',
                    'rules'  => 'required|in_list[0,1]',
                    'errors' => [
                        'required' => '{field} harus diisi',
                        'in_list'  => '{field} tidak valid',
                    ]
                ],
            ];

            $validasi = $this->validasi($rules, $post);
            if (! $validasi['sukses']) {
                return $validasi;
            }

            $validasiNonAktifShift = $this->validasiNonAktifShift($id, $post);
            if (! $validasiNonAktifShift['sukses']) {
                return $validasiNonAktifShift;
            }

            $idData         = (int) ($post['edit-id'] ?? $id);
            $namaShift      = $this->stringWajib($post['edit-nama_shift'] ?? '');
            $kodeShift      = url_title($namaShift, '-', true);
            $jamMasuk       = $this->formatJam($this->stringWajib($post['edit-jam_masuk'] ?? ''));
            $mulaiDatang    = $this->formatJam($this->stringWajib($post['edit-batas_mulai_datang'] ?? ''));
            $akhirDatang    = $this->formatJam($this->stringWajib($post['edit-batas_akhir_datang'] ?? ''));
            $jamPulang      = $this->formatJam($this->stringWajib($post['edit-jam_pulang'] ?? ''));
            $mulaiPulang    = $this->formatJam($this->stringWajib($post['edit-batas_mulai_pulang'] ?? ''));
            $akhirPulang    = $this->formatJam($this->stringWajib($post['edit-batas_akhir_pulang'] ?? ''));
            $toleransiTelat = $this->intVal($post['edit-toleransi_telat_menit'] ?? 0);
            $keterangan     = $this->stringAtauNull($post['edit-keterangan'] ?? '');
            $isActive       = $this->intVal($post['edit-is_active'] ?? 1, 1);

            $errorKode = $this->validasiKodeShift($kodeShift, $idData, 'edit-nama_shift');
            if ($errorKode !== null) {
                return $this->hasilGagal($errorKode);
            }

            $errorWaktu = $this->validasiWaktuShift(
                $jamMasuk,
                $mulaiDatang,
                $akhirDatang,
                $jamPulang,
                $mulaiPulang,
                $akhirPulang,
                true
            );
            if ($errorWaktu !== null) {
                return $this->hasilGagal($errorWaktu);
            }

            $simpan = $this->shiftModel->save([
                'id'                    => $idData,
                'kode_shift'            => $kodeShift,
                'nama_shift'            => $namaShift,
                'jam_masuk'             => $jamMasuk,
                'batas_mulai_datang'    => $mulaiDatang,
                'batas_akhir_datang'    => $akhirDatang,
                'jam_pulang'            => $jamPulang,
                'batas_mulai_pulang'    => $mulaiPulang,
                'batas_akhir_pulang'    => $akhirPulang,
                'toleransi_telat_menit' => $toleransiTelat,
                'keterangan'            => $keterangan,
                'is_active'             => $isActive,
            ]);

            if (! $simpan) {
                return $this->hasilGagal([
                    'general' => 'Data shift gagal diubah'
                ]);
            }

            $this->catatAudit(
                'update',
                'shift',
                $idData,
                'Mengubah data shift: ' . $namaShift
            );

            return $this->hasilSukses('Data Shift Berhasil Diubah');
        });
    }

    public function ambil(int $id): array
    {
        return $this->eksekusi(function () use ($id) {
            $shift = $this->shiftModel->getShiftById($id);

            if ($shift === null) {
                return $this->hasilTidakDitemukan('Data Shift Tidak Ditemukan');
            }

            return $this->hasilData([
                'shift' => $shift
            ]);
        });
    }

    public function hapus(int $id): array
    {
        return $this->eksekusi(function () use ($id) {
            $shift = $this->shiftModel->getShiftById($id);

            if ($shift === null) {
                return $this->hasilTidakDitemukan('Data Shift Tidak Ada Di Database');
            }

            $jumlahDipakai = $this->shiftModel->jumlahJadwalYangMemakai($id);

            if ($jumlahDipakai > 0) {
                return $this->hasilGagal([], 'Data Shift tidak dapat dihapus karena masih dipakai oleh ' . $jumlahDipakai . ' jadwal kerja');
            }

            $hapus = $this->shiftModel->delete($id);

            if (! $hapus) {
                return $this->hasilGagal([], 'Data Shift Gagal Dihapus');
            }

            $this->catatAudit(
                'delete',
                'shift',
                $id,
                'Menghapus data shift: ' . (string) $shift->nama_shift
            );

            return $this->hasilSukses('Data Shift Berhasil Dihapus');
        });
    }

    protected function validasiKodeShift(string $kodeShift, ?int $excludeId = null, string $errorField = 'nama_shift'): ?array
    {
        $builder = $this->shiftModel->where('kode_shift', $kodeShift);

        if ($excludeId !== null) {
            $builder->where('id !=', $excludeId);
        }

        $cek = $builder->first();

        if ($cek !== null) {
            return [
                $errorField => 'Nama Shift menghasilkan kode shift yang sudah dipakai',
            ];
        }

        return null;
    }

    protected function validasiWaktuShift(
        string $jamMasuk,
        string $batasMulaiDatang,
        string $batasAkhirDatang,
        string $jamPulang,
        string $batasMulaiPulang,
        string $batasAkhirPulang,
        bool $edit = false
    ): ?array {
        $fieldJamMasuk        = $edit ? 'edit-jam_masuk' : 'jam_masuk';
        $fieldAkhirDatang     = $edit ? 'edit-batas_akhir_datang' : 'batas_akhir_datang';
        $fieldJamPulang       = $edit ? 'edit-jam_pulang' : 'jam_pulang';
        $fieldMulaiPulang     = $edit ? 'edit-batas_mulai_pulang' : 'batas_mulai_pulang';
        $fieldAkhirPulang     = $edit ? 'edit-batas_akhir_pulang' : 'batas_akhir_pulang';

        $tsJamMasuk        = strtotime($jamMasuk);
        $tsMulaiDatang     = strtotime($batasMulaiDatang);
        $tsAkhirDatang     = strtotime($batasAkhirDatang);
        $tsJamPulang       = strtotime($jamPulang);
        $tsMulaiPulang     = strtotime($batasMulaiPulang);
        $tsAkhirPulang     = strtotime($batasAkhirPulang);

        if (
            $tsJamMasuk === false ||
            $tsMulaiDatang === false ||
            $tsAkhirDatang === false ||
            $tsJamPulang === false ||
            $tsMulaiPulang === false ||
            $tsAkhirPulang === false
        ) {
            return ['general' => 'Format waktu tidak valid'];
        }

        // 1. Range datang
        if ($tsMulaiDatang > $tsAkhirDatang) {
            return [
                $fieldAkhirDatang => 'Batas akhir datang harus lebih besar dari batas mulai datang',
            ];
        }

        // 2. Jam masuk dalam range datang
        if ($tsJamMasuk < $tsMulaiDatang) {
            return [
                $fieldJamMasuk => 'Jam masuk terlalu awal dari batas mulai datang',
            ];
        }

        if ($tsJamMasuk > $tsAkhirDatang) {
            return [
                $fieldJamMasuk => 'Jam masuk melebihi batas akhir datang',
            ];
        }

        // 3. Range pulang
        if ($tsMulaiPulang > $tsAkhirPulang) {
            return [
                $fieldAkhirPulang => 'Batas akhir pulang harus lebih besar dari batas mulai pulang',
            ];
        }

        // 4. Jam pulang dalam range pulang
        if ($tsJamPulang < $tsMulaiPulang) {
            return [
                $fieldJamPulang => 'Jam pulang terlalu awal dari batas mulai pulang',
            ];
        }

        if ($tsJamPulang > $tsAkhirPulang) {
            return [
                $fieldAkhirPulang => 'Batas akhir pulang harus lebih besar dari jam pulang',
            ];
        }

        // 5. Relasi masuk vs pulang
        if ($tsJamMasuk >= $tsJamPulang) {
            return [
                $fieldJamPulang => 'Jam pulang harus lebih besar dari jam masuk',
            ];
        }

        // 6. Transisi datang ke pulang
        if ($tsAkhirDatang >= $tsMulaiPulang) {
            return [
                $fieldMulaiPulang => 'Batas mulai pulang harus setelah batas akhir datang',
            ];
        }

        return null;
    }

    protected function validasiNonAktifShift(int $id, array $post): array
    {
        $shift = $this->shiftModel->getShiftById($id);

        if ($shift === null) {
            return $this->hasilTidakDitemukan('Data Shift Tidak Ada Di Database');
        }

        $statusLama = (int) ($shift->is_active ?? 0);
        $statusBaru = $this->intVal($post['edit-is_active'] ?? 1, 1);

        // hanya validasi jika dari aktif -> nonaktif
        if ($statusLama === 1 && $statusBaru === 0) {
            $jumlahDipakai = $this->shiftModel->jumlahJadwalYangMemakai($id);

            if ($jumlahDipakai > 0) {
                return $this->hasilGagal([
                    'edit-is_active' => 'Data Shift tidak dapat dinonaktifkan karena masih dipakai oleh ' . $jumlahDipakai . ' jadwal kerja'
                ]);
            }
        }

        return $this->hasilSukses();
    }
}
