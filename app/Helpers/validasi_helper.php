<?php

if (!function_exists('validate_nik')) {
    function validate_nik($text): bool
    {
        // $text = (!empty($text)) ? $text : "10";
        if (preg_match("/^\d{16}$/", $text ?? '')) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('validate_nip')) {
    function validate_nip($text): bool
    {
        // $text = (!empty($text)) ? $text : "10";
        if (hapus_spasi_lebih($text) == '') {
            return true;
        } else {
            if (preg_match("/^\d{18}$/", $text ?? '')) {
                return true;
            } else {
                return false;
            }
        }
    }
}

if (!function_exists('validate_nuptk')) {
    function validate_nuptk($text): bool
    {
        // $text = (!empty($text)) ? $text : "10";
        if (hapus_spasi_lebih($text) == '') {
            return true;
        } else {
            if (preg_match("/^\d{16}$/", $text ?? '')) {
                return true;
            } else {
                return false;
            }
        }
    }
}

if (!function_exists('validate_nisn')) {
    function validate_nisn($text): bool
    {
        // $text = (!empty($text)) ? $text : "10";

        if (preg_match("/^\d{10}$/", $text ?? '')) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('validate_kelas')) {
    function validate_kelas($text, array $kelas): bool
    {
        // $text = (!empty($text)) ? $text : "10";

        if (preg_match("/^[a-zA-Z]{3,7}[1|2|3|4|5|6]?$/", $text ?? '') && ambil_kelas_import($text ?? '', $kelas) != "") {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('validate_alamat')) {
    function validate_alamat($text): bool
    {
        // $text = (!empty($text)) ? $text : "@#/";
        if (preg_match("/^[a-zA-Z0-9\/,.\s]+$/", $text ?? '')) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('validasi_dusun')) {
    function validasi_dusun($text): bool
    {
        // $text = (!empty($text)) ? $text : "@#/";
        if (hapus_spasi_lebih($text) == '') {
            return true;
        } else {
            if (preg_match("/^[a-zA-Z0-9,.\s]+$/", $text ?? '')) {
                return true;
            } else {
                return false;
            }
        }
    }
}

if (!function_exists('validate_nama')) {
    function validate_nama($text): bool
    {
        // $text = (!empty($text)) ? $text : "1";
        if (preg_match("/^[a-zA-Z.\s]+$/", $text ?? '')) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('validate_email')) {
    function validate_email($text): bool
    {
        // $text = (!empty($text)) ? $text : "1";
        if (filter_var($text, FILTER_VALIDATE_EMAIL)) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('validate_tanggal')) {
    function validate_tanggal($text): bool
    {
        // $text = (!empty($text)) ? $text : "10";
        //Y-m-d
        if (preg_match("/^\d{4}[\-](0?[1-9]|1[012])[\-](0?[1-9]|[12][0-9]|3[01])$/", $text ?? '')) {
            return true;
        } else {
            return false;
        }
    }
}


if (!function_exists('validate_status')) {
    function validate_status($text): bool
    {
        // $text = (!empty($text)) ? $text : "10";
        if (preg_match("/^[1|2|3|4]$/", $text ?? '')) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('validate_jk')) {
    function validate_jk($text): bool
    {
        // $text = (!empty($text)) ? $text : "10";
        if (preg_match("/^[1|2]$/", $text ?? '')) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('validate_agama')) {
    function validate_agama($text): bool
    {
        // $text = (!empty($text)) ? $text : "10";
        /*
            1. Hindu
            2. Islam
            3. Kristen
            4. Katolik
            5. Buddha
            6. Kong Hu Chu
        */
        if (preg_match("/^[1|2|3|4|5|6]$/", $text ?? '')) {
            return true;
        } else {
            return false;
        }
    }
}


if (!function_exists('validate_hp')) {
    function validate_hp($text): bool
    {
        // $text = (!empty($text)) ? $text : "10";
        if (preg_match("/^(0)8[1-9][0-9]{8,9}$/", $text ?? '')) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('validate_kode_mapel')) {
    function validate_kode_mapel($text): bool
    {
        // $text = (!empty($text)) ? $text : "10";
        if (preg_match("/^[a-zA-Z]{3,6}$/", $text ?? '')) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('validate_mapel')) {
    function validate_mapel($text): bool
    {
        // $text = (!empty($text)) ? $text : "1";
        if (preg_match("/^[a-zA-Z\s]+$/", $text ?? '')) {
            return true;
        } else {
            return false;
        }
    }
}


if (!function_exists('validate_lokasi')) {
    function validate_lokasi($text): bool
    {
        // $text = (!empty($text)) ? $text : "10";
        if (preg_match("/^(\-?\d+(\.\d+)?),\s*(\-?\d+(\.\d+)?)$/", $text ?? '')) {
            return true;
        } else {
            return false;
        }
    }
}

// if (!function_exists('validate_allow_presensi')) {
//     function validate_allow_presensi($idStaf, $shift, $sesi)
//     {
//         $presensi = new PresensiModel();
//         $presensiExist = $presensi->getPresensiToDay($idStaf);
//         if ($presensiExist == null && $sesi == 'pulang') {
//             $result = [
//                 "sukses" => false,
//                 "pesan" => "Anda Belum Melakukan Presensi Datang"
//             ];
//         } else if ($presensiExist != null && $sesi == 'datang') {
//             $result = [
//                 "sukses" => false,
//                 "pesan" => "Anda Sudah Melakukan Presensi Datang"
//             ];
//         } else {
//             $pengaturanModel = new PengaturanModel();
//             $pengaturan = $pengaturanModel->getPengaturan();

//             if ($shift == 'pagi' && $sesi == 'datang') {
//                 $batasAwal = $pengaturan->shift_pagi_datang_awal;
//                 $batasAkhir = $pengaturan->shift_pagi_datang_akhir;
//             }
//             if ($shift == 'pagi' && $sesi == 'pulang') {
//                 $batasAwal = $pengaturan->shift_pagi_pulang_awal;
//                 $batasAkhir = $pengaturan->shift_pagi_pulang_akhir;
//             }
//             if ($shift == 'siang' && $sesi == 'datang') {
//                 $batasAwal = $pengaturan->shift_siang_datang_awal;
//                 $batasAkhir = $pengaturan->shift_siang_datang_akhir;
//             }
//             if ($shift == 'siang' && $sesi == 'pulang') {
//                 $batasAwal = $pengaturan->shift_siang_pulang_awal;
//                 $batasAkhir = $pengaturan->shift_siang_pulang_akhir;
//             }

//             $timeNow = new DateTime();
//             $var = strtotime($timeNow->format('H:i'));
//             if ($var >= strtotime($batasAwal) && $var <= strtotime($batasAkhir)) {
//                 $result = [
//                     "sukses" => true
//                 ];
//             } else {
//                 $result = [
//                     "sukses" => false,
//                     "pesan" => "Saat ini pukul <b>" . $timeNow->format('H:i') . "</b><br /><br />Presensi <b>" . huruf_besar_awal($sesi) . "</b> Pada Shift <b>" . huruf_besar_awal($shift) . "</b> Dimulai Pukul <br /><b>" . $batasAwal . "</b> s/d <b>" . $batasAkhir . "</b>"
//                 ];
//             }
//         }
//         return (object)$result;
//     }
// }

/**
 * menghitung Jarak Diantara 2 Posisi (Latitude, Longitude)
 * the Haversine formula.
 * @param float $lokasiUsaha (Latitude, Longitude) Dari Lokasi Tujuan
 * @param float $lokasiSaatIni (Latitude, Longitude) Dari Lokasi Saat Ini
 * @return float Jarak antar point dalam satuan Meter
 */

if (!function_exists('validate_jarak')) {
    function validate_jarak($lokasiUsaha, $lokasiSaatIni): float
    {
        $lokasiUsahaLatLong = explode(",", $lokasiUsaha);
        $lokasiSaatIniLatLong = explode(",", $lokasiSaatIni);
        $lokasiUsahaLat = hapus_spasi_lebih($lokasiUsahaLatLong[0]);
        $lokasiUsahaLong = hapus_spasi_lebih($lokasiUsahaLatLong[1]);
        $lokasiSaatIniLat = hapus_spasi_lebih($lokasiSaatIniLatLong[0]);
        $lokasiSaatIniLong = hapus_spasi_lebih($lokasiSaatIniLatLong[1]);

        $latFrom = deg2rad($lokasiSaatIniLat);
        $lonFrom = deg2rad($lokasiSaatIniLong);
        $latTo = deg2rad($lokasiUsahaLat);
        $lonTo = deg2rad($lokasiUsahaLong);
        $earthRadius = 6371000;

        $lonDelta = $lonTo - $lonFrom;
        $a = pow(cos($latTo) * sin($lonDelta), 2) + pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
        $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);

        $angle = atan2(sqrt($a), $b);
        //Satuan Meter
        return round(($angle * $earthRadius), 2);
    }
}

// if (!function_exists('validasi_saldo')) {
//     function validasi_saldo($mode, $rekening, $totalAkhir, $totalAwal = false)
//     {
//         $valid = false;
//         $bulan = extract_rentang_bulan(date('Y-m'));
//         $saldoAkhir = rekap_cashflow($bulan->awal, $bulan->akhir, $rekening)->akhir;
//         if ($mode == 'tambah') {
//             $valid = $saldoAkhir < $totalAkhir ? false : true;
//         }
//         if ($mode == 'ubah') {
//             $saldoAkhir += $totalAwal;
//             $valid = $saldoAkhir < $totalAkhir ? false : true;
//         }
//         if ($mode == 'ubah-rekening') {
//             $valid = ($saldoAkhir - $totalAwal) < $totalAkhir ? false : true;
//         }

//         return $valid;
//     }
// }

// if (!function_exists('validate_jabatan')) {
//     function validate_jabatan($text)
//     {
//         $regex = new JabatanModel();
//         if (preg_match("/^[" . $regex->getJabatanArray('regex') . "]$/", $text ?? '')) {
//             return true;
//         } else {
//             return false;
//         }
//     }
// }
