<?php
if (!function_exists('konversi_ke_detik')) {
    function konversi_ke_detik($waktu): int
    {
        $arr = explode(':', $waktu);
        if (count($arr) === 3) {
            return ($arr[0] * 3600) + ($arr[1] * 60) + $arr[2];
        }
        return ($arr[0] * 3600) + ($arr[1] * 60);
    }
}

if (!function_exists('konversi_ke_waktu')) {
    function konversi_ke_waktu($detik)
    {
        return gmdate("H:i", $detik);
    }
}

if (!function_exists('tanggal_indonesia')) {
    function tanggal_indonesia($date, $param = false)
    {
        if ($date == NULL) {
            return NULL;
        } else {
            $bulan = array(
                "Januari",
                "Februari",
                "Maret",
                "April",
                "Mei",
                "Juni",
                "Juli",
                "Agustus",
                "September",
                "Oktober",
                "November",
                "Desember"
            );
            $year        = substr($date, 0, 4); // memisahkan format tahun menggunakan substring
            $month       = substr($date, 5, 2); // memisahkan format bulan menggunakan substring
            if (strlen($date) > 7) {
                $currentdate = substr($date, 8, 2); // memisahkan format tanggal menggunakan substring
            }

            if ($param == false) {
                $result = $currentdate . " " . $bulan[(int) $month - 1] . " " . $year;
            } else {
                if ($param == 'bulan') {
                    $result = $bulan[(int) $month - 1];
                } else if ($param == 'bulan-tahun') {
                    $result = $bulan[(int) $month - 1] . " " . $year;
                } else {
                    $result = $currentdate . " " . $bulan[(int) $month - 1] . " " . $year;
                }
            }
            return $result;
        }
    }
}

if (!function_exists('tanggal_indonesia_singkat')) {
    function tanggal_indonesia_singkat($date, $param = false)
    {
        if ($date == NULL) {
            return NULL;
        } else {
            $bulan = array(
                "Jan",
                "Feb",
                "Mar",
                "Apr",
                "Mei",
                "Jun",
                "Jul",
                "Agus",
                "Sep",
                "Okt",
                "Nov",
                "Des"
            );
            $year        = substr($date, 0, 4); // memisahkan format tahun menggunakan substring
            $month       = substr($date, 5, 2); // memisahkan format bulan menggunakan substring
            if (strlen($date) > 7) {
                $currentdate = substr($date, 8, 2); // memisahkan format tanggal menggunakan substring
            }

            if ($param == false) {
                $result = $currentdate . " " . $bulan[(int) $month - 1] . " " . $year;
            } else {
                if ($param == 'bulan') {
                    $result = $bulan[(int) $month - 1];
                } else if ($param == 'bulan-tahun') {
                    $result = $bulan[(int) $month - 1] . " " . $year;
                } else {
                    $result = $currentdate . " " . $bulan[(int) $month - 1] . " " . $year;
                }
            }
            return $result;
        }
    }
}

if (!function_exists('tanggal_indonesia_jam')) {
    function tanggal_indonesia_jam($date)
    {
        if ($date == NULL) {
            return NULL;
        } else {
            $pecah = explode(" ", $date);
            $tanggal = $pecah[0];
            $jam = $pecah[1];
            $bulan = array(
                "Jan",
                "Feb",
                "Mar",
                "Apr",
                "Mei",
                "Jun",
                "Jul",
                "Agus",
                "Sep",
                "Okt",
                "Nov",
                "Des"
            );
            $year        = substr($tanggal, 0, 4); // memisahkan format tahun menggunakan substring
            $month       = substr($tanggal, 5, 2); // memisahkan format bulan menggunakan substring
            if (strlen($tanggal) > 7) {
                $currentdate = substr($date, 8, 2); // memisahkan format tanggal menggunakan substring
            }

            $result = $currentdate . " " . $bulan[(int) $month - 1] . " " . $year . " " . $jam;
            return $result;
        }
    }
}

if (!function_exists('tanggal_indonesia_reverse')) {
    function tanggal_indonesia_reverse($date, $param = false)
    {
        $months = array(
            "01" => "Januari",
            "02" => "Februari",
            "03" => "Maret",
            "04" => "April",
            "05" => "Mei",
            "06" => "Juni",
            "07" => "Juli",
            "08" => "Agustus",
            "09" => "September",
            "10" => "Oktober",
            "11" => "November",
            "12" => "Desember"
        );

        $conv = explode(" ", $date);
        $tanggal = $conv[0];
        $tahun = $conv[2];
        $bulan = array_search($conv[1], $months);

        if ($param == false) {
            $result = $tahun . '-' . $bulan . '-' . $tanggal;
        } else {
            if ($param == 'bulan') {
                $result = $bulan;
            } else if ($param == 'bulan-tahun') {
                $result = $tahun . '-' . $bulan;
            } else {
                $result = $tahun . '-' . $bulan . '-' . $tanggal;
            }
        }
        return $result;
    }
}

if (!function_exists('tanggal_with_zero')) {
    function tanggal_with_zero()
    {
        $d = array(
            '01' => 1,
            '02' => 2,
            '03' => 3,
            '04' => 4,
            '05' => 5,
            '06' => 6,
            '07' => 7,
            '08' => 8,
            '09' => 9,
            '10' => 10,
            '11' => 11,
            '12' => 12,
            '13' => 13,
            '14' => 14,
            '15' => 15,
            '16' => 16,
            '17' => 17,
            '18' => 18,
            '19' => 19,
            '20' => 20,
            '21' => 21,
            '22' => 22,
            '23' => 23,
            '24' => 24,
            '25' => 25,
            '26' => 26,
            '27' => 27,
            '28' => 28,
            '29' => 29,
            '30' => 30,
            '31' => 31
        );

        return $d;
    }
}

if (!function_exists('show_tanggal')) {
    function show_tanggal($bulan)
    {
        $tanggal = array();
        $batasAkhir = cal_days_in_month(CAL_GREGORIAN, (int)explode("-", $bulan)[1], (int)explode("-", $bulan)[0]);
        $d = [
            '00',
            '01',
            '02',
            '03',
            '04',
            '05',
            '06',
            '07',
            '08',
            '09',
            '10',
            '11',
            '12',
            '13',
            '14',
            '15',
            '16',
            '17',
            '18',
            '19',
            '20',
            '21',
            '22',
            '23',
            '24',
            '25',
            '26',
            '27',
            '28',
            '29',
            '30',
            '31'
        ];

        for ($index = 1; $index <= $batasAkhir; $index++) {
            array_push($tanggal, $d[$index]);
        }

        return $tanggal;
    }
}

if (!function_exists('bulan_angka')) {
    function bulan_angka()
    {
        $bulan = array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');
        return $bulan;
    }
}

if (!function_exists('bulan_singkat')) {
    function bulan_singkat($bln)
    {
        $month = array(
            "Jan",
            "Feb",
            "Mar",
            "Apr",
            "Mei",
            "Jun",
            "Jul",
            "Agu",
            "Sep",
            "Okt",
            "Nov",
            "Des"
        );

        return $month[(int) $bln - 1];
    }
}

if (!function_exists('show_five_years')) {
    function show_five_years()
    {
        $tahun1 = strval(date('Y') - 4);
        $tahun2 = strval(date('Y') - 3);
        $tahun3 = strval(date('Y') - 2);
        $tahun4 = strval(date('Y') - 1);
        $tahun5 = date('Y');
        $tahun = array($tahun1, $tahun2, $tahun3, $tahun4, $tahun5);
        return $tahun;
    }
}

if (!function_exists('show_date_filter')) {
    function show_date_filter($awal, $akhir)
    {
        $date1 = date_create($awal);
        $date2 = date_create($akhir);
        $diff = date_diff($date2, $date1)->format("%a");
        $dateAwal = date_format($date1, "Y-m-d");
        $tgl = array();
        for ($i = 0; $i <= $diff; $i++) {
            $dt = date('Y-m-d', strtotime($dateAwal . ' + ' . $i . ' day'));
            array_push($tgl, $dt);
        }
        return $tgl;
    }

    if (!function_exists('rentang_hari')) {
        function rentang_hari($awal, $akhir)
        {
            $date1 = date_create($awal);
            $date2 = date_create($akhir);
            $diff = date_diff($date2, $date1)->format("%a");
            return $diff;
        }
    }

    if (!function_exists('list_bulan')) {
        function list_bulan($awal, $akhir)
        {
            $start_date = date("Y-m", strtotime($awal));
            $end_date   = date("Y-m", strtotime($akhir));
            $start = strtotime($start_date);
            $end = strtotime($end_date);

            $month = $start;
            $months[] = date('Y-m', $start);
            while ($month < $end) {
                $month = strtotime("+1 month", $month);
                $months[] = date('Y-m', $month);
            }

            foreach ($months as $mon) {
                $mon_arr = explode("-", $mon);
                $y = $mon_arr[0];
                $m = $mon_arr[1];
                $start_dates_arr[] = date("Y-m", strtotime($m . '/01/' . $y . ' 00:00:00'));
            }

            return $start_dates_arr;
        }
    }

    if (!function_exists('ambil_jam')) {
        function ambil_jam(?string $datetime, string $format = 'H:i:s'): ?string
        {
            if (empty($datetime)) {
                return null;
            }

            try {
                return (new DateTime($datetime))->format($format);
            } catch (\Throwable $e) {
                return null;
            }
        }
    }
}
