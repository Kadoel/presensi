<?php
if (! function_exists('slipRupiah')) {
    function slipRupiah($value): string
    {
        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }
}

if (! function_exists('slipBulanIndonesia')) {
    function slipBulanIndonesia(?string $bulan): string
    {
        $bulanIndo = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        if (! preg_match('/^(\d{4})-(\d{2})$/', $bulan, $match)) {
            return $bulan ?: '-';
        }

        return $bulanIndo[(int) $match[2]] . ' ' . $match[1];
    }
}
if (! function_exists('slipAngka')) {
    function slipAngka($value): int
    {
        return (int) ($value ?? 0);
    }
}

if (! function_exists('slipMenit')) {
    function slipMenit($value): string
    {
        $value = (int) ($value ?? 0);

        return $value > 0 ? $value . ' menit' : '-';
    }
}
