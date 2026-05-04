<?php

namespace App\Services\Pegawai;

use App\Models\JadwalKerjaModel;
use App\Services\BaseService;

class JadwalService extends BaseService
{
    protected JadwalKerjaModel $jadwalKerjaModel;

    public function __construct()
    {
        parent::__construct();

        $this->jadwalKerjaModel = new JadwalKerjaModel();
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

            $items = $this->jadwalKerjaModel->getJadwalKalenderPegawai($pegawaiId, $start, $end);

            $events = [];

            foreach ($items as $item) {
                $warna = $this->warnaEvent($item);

                $events[] = [
                    'id'              => $item->id,
                    'title'           => $this->judulEvent($item),
                    'start'           => $item->tanggal,
                    'allDay'          => true,
                    'className'       => [
                        'event-jadwal',
                        $this->classStatus($item->status_hari),
                        'shift-' . (int) ($item->shift_id ?? 0),
                    ],
                    'backgroundColor' => $warna,
                    'borderColor'     => $warna,
                    'textColor'       => $this->textColorEvent($item),
                    'extendedProps'   => [
                        'tanggal'     => $item->tanggal,
                        'status_hari' => $item->status_hari,
                        'sumber_data' => $item->sumber_data,
                        'shift_id'    => $item->shift_id,
                        'nama_shift'  => $item->nama_shift,
                        'jam_masuk'   => $item->jam_masuk,
                        'jam_pulang'  => $item->jam_pulang,
                        'catatan'     => $item->catatan,
                        'warna'       => $warna,
                        'text_color'  => $this->textColorEvent($item),
                    ],
                ];
            }

            return $events;
        });
    }

    private function judulEvent(object $item): string
    {
        return match ($item->status_hari) {
            'kerja' => $item->nama_shift ?: 'Kerja',
            'libur' => 'Libur',
            'izin'  => 'Izin',
            'sakit' => 'Sakit',
            'cuti' => 'Cuti',
            default => '-',
        };
    }

    private function warnaEvent(object $item): string
    {
        if (($item->status_hari ?? '') !== 'kerja') {
            return match ($item->status_hari) {
                'libur' => '#dc3545',
                'cuti' => '#fd21d1',
                'izin'  => '#ffc107',
                'sakit' => '#0dcaf0',
                default => '#6c757d',
            };
        }

        $warnaShift = [
            1 => '#198754',
            2 => '#0d6efd',
            3 => '#6f42c1',
            4 => '#fd7e14',
            5 => '#20c997',
            6 => '#6610f2',
            7 => '#d63384',
            8 => '#0dcaf0',
        ];

        return $warnaShift[(int) ($item->shift_id ?? 0)] ?? '#198754';
    }

    private function textColorEvent(object $item): string
    {
        if (in_array($item->status_hari ?? '', ['izin', 'sakit'], true)) {
            return '#000000';
        }

        return '#ffffff';
    }

    private function classStatus(?string $status): string
    {
        return match ($status) {
            'kerja' => 'event-kerja',
            'libur' => 'event-libur',
            'izin'  => 'event-izin',
            'sakit' => 'event-sakit',
            default => 'event-default',
        };
    }
}
