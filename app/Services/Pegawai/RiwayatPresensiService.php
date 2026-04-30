<?php

namespace App\Services\Pegawai;

use App\Models\PresensiModel;
use App\Services\BaseService;

class RiwayatPresensiService extends BaseService
{
    protected PresensiModel $presensiModel;

    public function __construct()
    {
        parent::__construct();

        $this->presensiModel = new PresensiModel();
    }

    public function kalender(?string $start, ?string $end): array
    {
        return $this->eksekusi(function () use ($start, $end) {
            $pegawaiId = $this->intAtauNull(session()->get('pegawai_id'));

            if ($pegawaiId === null) {
                return [];
            }

            $start = $start ?: date('Y-m-01');
            $end   = $end ?: date('Y-m-t');

            $items = $this->presensiModel->getRiwayatKalenderPegawai($pegawaiId, $start, $end);

            $events = [];

            foreach ($items as $item) {
                $warna = $this->warnaEvent($item->hasil_presensi ?? null);

                $events[] = [
                    'id'     => $item->id,
                    'title'  => $this->judulEvent($item->hasil_presensi ?? null),
                    'start'  => $item->tanggal,
                    'allDay' => true,
                    'extendedProps' => [
                        'tanggal'             => $item->tanggal,
                        'hasil_presensi'      => $item->hasil_presensi,
                        'status_datang'       => $item->status_datang,
                        'status_pulang'       => $item->status_pulang,
                        'menit_telat'         => $item->menit_telat,
                        'menit_pulang_cepat'  => $item->menit_pulang_cepat,
                        'jam_datang'          => $item->jam_datang,
                        'jam_pulang'          => $item->jam_pulang,
                        'sumber_presensi'     => $item->sumber_presensi,
                        'catatan_admin'       => $item->catatan_admin,
                        'is_manual'           => $item->is_manual,
                        'shift_id'            => $item->shift_id,
                        'nama_shift'          => $item->nama_shift,
                        'jam_masuk'           => $item->jam_masuk,
                        'jam_pulang_shift'    => $item->jam_pulang_shift,
                        'warna'               => $warna,
                    ],
                ];
            }

            return $events;
        });
    }

    private function judulEvent(?string $hasil): string
    {
        return match ($hasil) {
            'hadir' => 'Hadir',
            'alpa'  => 'Alpa',
            'izin'  => 'Izin',
            'sakit' => 'Sakit',
            'libur' => 'Libur',
            default => 'Belum Sinkron',
        };
    }

    private function warnaEvent(?string $hasil): string
    {
        return match ($hasil) {
            'hadir' => '#198754',
            'alpa'  => '#dc3545',
            'izin'  => '#0dcaf0',
            'sakit' => '#0d6efd',
            'libur' => '#6c757d',
            default => '#adb5bd',
        };
    }
}
