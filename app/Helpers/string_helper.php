<?php
if (!function_exists('nama_belakang')) {
    function nama_belakang($nama)
    {
        $pecah = explode(',', $nama ?? '');
        return (strpos(trim($pecah[0]), ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $pecah[0]);
    }
}

if (!function_exists('hapus_spasi_lebih')) {
    function hapus_spasi_lebih($text)
    {

        return htmlspecialchars(trim(preg_replace('/\s+/', ' ', $text ?? '')));
    }
}

if (!function_exists('hapus_spasi_lebih_textarea1')) {
    function hapus_spasi_lebih_textarea1($text)
    {
        return hapus_spasi_lebih(str_replace('&nbsp', '', $text ?? ''));
    }
}

if (!function_exists('hapus_spasi_lebih_textarea')) {
    function hapus_spasi_lebih_textarea($text)
    {
        return huruf_besar_awal(hapus_spasi_lebih(hapus_spasi_lebih_textarea1(str_replace('?', '', $text ?? ''))));
    }
}

if (!function_exists('huruf_besar_awal')) {
    function huruf_besar_awal($text)
    {
        return ucwords(strtolower(hapus_spasi_lebih($text ?? '')));
    }
}

if (!function_exists('huruf_besar')) {
    function huruf_besar($text)
    {
        return strtoupper(hapus_spasi_lebih($text ?? ''));
    }
}

if (!function_exists('huruf_kecil')) {
    function huruf_kecil($text)
    {
        return strtolower(hapus_spasi_lebih($text ?? ''));
    }
}

if (!function_exists('samarkan_identitas')) {
    function samarkan_identitas($string)
    {
        $text = substr($string ?? '', 0, 11);
        return $text . '*****';
    }
}

if (!function_exists('extract_rentang_bulan')) {
    function extract_rentang_bulan($string)
    {
        $bulan = explode('~', str_replace(' ', '', $string ?? ''));
        $awal = $bulan[0];
        $akhir = count($bulan) == 1 ? $bulan[0] : $bulan[1];
        $judul = $awal == $akhir ? tanggal_indonesia($awal, 'bulan-tahun') : tanggal_indonesia($awal, 'bulan-tahun') . ' s/d ' . tanggal_indonesia($akhir, 'bulan-tahun');
        return (object)[
            'awal'      => $awal,
            'akhir'     => $akhir,
            'judul'    => $judul
        ];
    }
}

if (!function_exists('null_ke_nol')) {
    function null_ke_nol($string)
    {
        $value = $string == null ? 0 : (int)$string;
        return $value;
    }
}

if (!function_exists('nama_kelas')) {
    function nama_kelas(string $tingkat, string $proli, string $nomor): string
    {
        return huruf_besar($tingkat) . ' ' . huruf_besar($proli) . ($nomor == '-' ? '' : ' ' . $nomor);
    }
}

if (!function_exists('nama_kelas_concat')) {
    function nama_kelas_concat(string $string): string
    {
        $nama = explode(' ',  $string ?? '');
        $tingkat = $nama[0];
        $proli = $nama[1];
        $nomor = ($nama[2] == '-' ? '' : ' ' . $nama[2]);
        return huruf_besar($tingkat) . ' ' . huruf_besar($proli) . $nomor;
    }
}

if (!function_exists('tahun_ajaran')) {
    function tahun_ajaran(string $string): string
    {
        $tahun_ajaran = explode('-', str_replace(' ', '', $string ?? ''));
        return $tahun_ajaran[0];
    }
}
