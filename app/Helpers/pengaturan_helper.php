<?php

use App\Models\BackupdbModel;
use App\Models\KelasModel;

if (!function_exists('warna_tema')) {
    function warna_tema($key)
    {
        $warna = array(
            'standar' => '<link rel="stylesheet" id="css-theme" href="/assets/css/tema/standar.min.css">',
            'elegance' => '<link rel="stylesheet" id="css-theme" href="/assets/css/tema/elegance.min.css">',
            'pulse' => '<link rel="stylesheet" id="css-theme" href="/assets/css/tema/pulse.min.css">',
            'corporate' => '<link rel="stylesheet" id="css-theme" href="/assets/css/tema/corporate.min.css">',
            'flat' => '<link rel="stylesheet" id="css-theme" href="/assets/css/tema/flat.min.css">',
            'earth' => '<link rel="stylesheet" id="css-theme" href="/assets/css/tema/earth.min.css">'
        );

        if (array_key_exists($key, $warna)) {
            return $warna[$key];
        } else {
            return '';
        }
    }
}

if (!function_exists('data_warna_tema')) {
    function data_warna_tema()
    {
        $warna = [
            [
                'value'   => 'standar',
                'text' => 'Standar'
            ],
            [
                'value'   => 'elegance',
                'text' => 'Elegance'
            ],
            [
                'value'   => 'pulse',
                'text' => 'Pulse'
            ],
            [
                'value'   => 'corporate',
                'text' => 'Corporate'
            ],
            [
                'value'   => 'flat',
                'text' => 'Flat'
            ],
            [
                'value'   => 'earth',
                'text' => 'Earth'
            ]
        ];

        return $warna;
    }
}

if (!function_exists('data_warna_block')) {
    function data_warna_block()
    {
        $warna = [
            [
                'value'   => 'bg-default',
                'text' => 'Standar'
            ],
            [
                'value'   => 'bg-gd-default',
                'text' => 'Gradient Standar'
            ],
            [
                'value'   => 'bg-elegance',
                'text' => 'Elegance'
            ],
            [
                'value'   => 'bg-gd-elegance',
                'text' => 'Gradient Elegance'
            ],
            [
                'value'   => 'bg-pulse',
                'text' => 'Pulse'
            ],
            [
                'value'   => 'bg-gd-pulse',
                'text' => 'Gradient Pulse'
            ],
            [
                'value'   => 'bg-flat',
                'text' => 'Flat'
            ],
            [
                'value'   => 'bg-gd-flat',
                'text' => 'Gradient Flat'
            ],
            [
                'value'   => 'bg-corporate',
                'text' => 'Corporate'
            ],
            [
                'value'   => 'bg-gd-corporate',
                'text' => 'Gradient Corporate'
            ],
            [
                'value'   => 'bg-earth',
                'text' => 'Earth'
            ],
            [
                'value'   => 'bg-gd-earth',
                'text' => 'Gradient Earth'
            ],
            [
                'value'   => 'bg-success',
                'text' => 'Success'
            ],
            [
                'value'   => 'bg-info',
                'text' => 'Info'
            ],
            [
                'value'   => 'bg-warning',
                'text' => 'Warning'
            ],
            [
                'value'   => 'bg-danger',
                'text' => 'Danger'
            ],
            [
                'value'   => 'bg-gd-sun',
                'text' => 'Gradient Sun'
            ],
            [
                'value'   => 'bg-gd-lake',
                'text' => 'Gradient Lake'
            ],
            [
                'value'   => 'bg-gd-leaf',
                'text' => 'Gradient Leaf'
            ],
            [
                'value'   => 'bg-gd-sea',
                'text' => 'Gradient Sea'
            ],
            [
                'value'   => 'bg-gd-emerald',
                'text' => 'Gradient Emerald'
            ],
            [
                'value'   => 'bg-gd-aqua',
                'text' => 'Gradient Aqua'
            ],
            [
                'value'   => 'bg-gd-cherry',
                'text' => 'Gradient Cherry'
            ],
            [
                'value'   => 'bg-gd-dusk',
                'text' => 'Gradient Dusk'
            ],
        ];

        return $warna;
    }
}

if (!function_exists('data_mode_layout')) {
    function data_mode_layout()
    {
        $warna = [
            [
                'value'   => 'boxed',
                'text' => 'Boxed'
            ],
            [
                'value'   => 'narrow',
                'text' => 'Narrow'
            ],
        ];

        return $warna;
    }
}

if (!function_exists('mode_layout')) {
    function mode_layout($key)
    {
        $mode = array(
            'boxed' => 'main-content-boxed',
            'narrow' => 'main-content-narrow',
        );

        if (array_key_exists($key, $mode)) {
            return $mode[$key];
        } else {
            return '';
        }
    }
}

if (!function_exists('data_mode_sidebar')) {
    function data_mode_sidebar()
    {
        $warna = [
            [
                'value'   => 'mini-gelap',
                'text' => 'Mini Gelap'
            ],
            [
                'value'   => 'mini-terang',
                'text' => 'Mini Terang'
            ],
            [
                'value'   => 'full-gelap',
                'text' => 'Full Gelap'
            ],
            [
                'value'   => 'full-terang',
                'text' => 'Full Terang'
            ]
        ];

        return $warna;
    }
}

if (!function_exists('mode_sidebar')) {
    function mode_sidebar($key)
    {
        $mode = array(
            'mini-gelap' => 'sidebar-mini sidebar-inverse',
            'mini-terang' => 'sidebar-mini sidebar-light',
            'full-gelap' => 'sidebar-full sidebar-inverse',
            'full-terang' => 'sidebar-full sidebar-light',
        );

        if (array_key_exists($key, $mode)) {
            return $mode[$key];
        } else {
            return '';
        }
    }
}

if (!function_exists('data_mode_header')) {
    function data_mode_header()
    {
        $warna = [
            [
                'value'   => 'fixed-gelap',
                'text' => 'Fixed Gelap'
            ],
            [
                'value'   => 'fixed-terang',
                'text' => 'Fixed Terang'
            ],
            [
                'value'   => 'static-gelap',
                'text' => 'Static Gelap'
            ],
            [
                'value'   => 'static-terang',
                'text' => 'Static Terang'
            ]
        ];

        return $warna;
    }
}

if (!function_exists('mode_header')) {
    function mode_header($key)
    {
        $mode = array(
            'fixed-gelap' => 'page-header-fixed page-header-inverse page-header-solid',
            'fixed-terang' => 'page-header-fixed page-header-light page-header-solid',
            'static-gelap' => 'page-header-static page-header-inverse page-header-glass',
            'static-terang' => 'page-header-static page-header-light page-header-glass',
        );

        if (array_key_exists($key, $mode)) {
            return $mode[$key];
        } else {
            return '';
        }
    }
}

if (!function_exists('data_shift')) {
    function data_shift()
    {
        $shift = [
            [
                'value' => 'pagi',
                'text'  => 'Pagi'
            ],
            [
                'value' => 'siang',
                'text'  => 'Siang'
            ]
        ];

        return $shift;
    }
}

if (!function_exists('data_status_anggota_motor')) {
    function data_status_anggota_motor()
    {
        $status = [
            [
                'value' => '1',
                'text'  => 'Krama Aktif'
            ],
            [
                'value' => '2',
                'text'  => 'Krama Nyada'
            ],
            [
                'value' => '3',
                'text'  => 'Pemuda'
            ],
            [
                'value' => '4',
                'text'  => 'Pendatang'
            ]
        ];

        return $status;
    }
}

if (!function_exists('data_role')) {
    function data_role()
    {
        $role = [
            [
                'value' => '1',
                'text'  => 'Administrator'
            ],
            [
                'value' => '3',
                'text'  => 'Koordinator Motor'
            ],
            [
                'value' => '4',
                'text'  => 'Koordinator Mobil'
            ],
            [
                'value' => '5',
                'text'  => 'Pengawas'
            ]
        ];

        return $role;
    }
}

if (!function_exists('display_role')) {
    function display_role($key, $param = false)
    {
        $role = array(
            '0' => [
                'badge'     => '<span class="badge badge-secondary">Staf</span>',
                'header'    => 'Staf'
            ],
            '1' => [
                'badge'     => '<span class="badge badge-primary">Administrator</span>',
                'header'    => 'Administrator'
            ],
            '2' => [
                'badge'     => '<span class="badge badge-info">Administrasi Office</span>',
                'header'    => 'Administrasi Office'
            ],
            '3' => [
                'badge'     => '<span class="badge badge-warning">Koordinator Motor</span>',
                'header'    => 'Koordinator Motor'
            ],
            '4' => [
                'badge'     => '<span class="badge badge-success">Koordinator Mobil</span>',
                'header'    => 'Koordinator Mobil'
            ],
            '5' => [
                'badge'     => '<span class="badge badge-primary">Pengawas</span>',
                'header'    => 'Pengawas'
            ],
        );

        if ($param != 'header' || $param == false) {
            if (array_key_exists($key, $role)) {
                return $role[$key]['badge'];
            } else {
                return '<span class="badge badge-danger">Tidak Diketahui</span>';
            }
        }

        if ($param == true && $param == 'header') {
            if (array_key_exists($key, $role)) {
                return $role[$key]['header'];
            } else {
                return 'Tidak Diketahui';
            }
        }
    }
}

if (!function_exists('data_jabatan_pengguna')) {
    function data_jabatan_pengguna()
    {
        $role = [
            [
                'value' => '1',
                'text'  => 'IT BUPDA'
            ],
            [
                'value' => '2',
                'text'  => 'Ketua BUPDA'
            ],
            [
                'value' => '3',
                'text'  => 'Bendahara BUPDA'
            ],
            [
                'value' => '4',
                'text'  => 'Bendesa Adat'
            ],
            [
                'value' => '5',
                'text'  => 'Koordinator'
            ]
        ];

        return $role;
    }
}

if (!function_exists('display_jabatan_pengguna')) {
    function display_jabatan_pengguna($key)
    {
        $jabatan = array(
            '1' => '<span class="badge badge-primary">IT BUPDA</span>',
            '2' => '<span class="badge badge-primary">Ketua BUPDA</span>',
            '3' => '<span class="badge badge-primary">Bendahara BUPDA</span>',
            '4' => '<span class="badge badge-success">Bendesa Adat</span>',
            '5' => '<span class="badge badge-info">Koordinator</span>',
        );

        if (array_key_exists($key, $jabatan)) {
            return $jabatan[$key];
        } else {
            return '';
        }
    }
}

if (!function_exists('data_divisi')) {
    function data_divisi()
    {
        $role = [
            [
                'value' => '1',
                'text'  => 'E-Parkir'
            ],
            [
                'value' => '2',
                'text'  => 'Usaha'
            ]
        ];

        return $role;
    }
}

if (!function_exists('display_divisi')) {
    function display_divisi($key, $param = false)
    {
        $divisi = array(
            '1' => [
                'badge' => '<span class="badge badge-info">E-Parkir</span>',
                'header' => 'E-Parkir'
            ],
            '2' => [
                'badge' => '<span class="badge badge-info">Usaha</span>',
                'header' => 'Usaha'
            ],
        );

        if ($param != 'header' || $param == false) {
            if (array_key_exists($key, $divisi)) {
                return $divisi[$key]['badge'];
            } else {
                return '<span class="badge badge-danger">Tidak Diketahui</span>';
            }
        }

        if ($param == true && $param == 'header') {
            if (array_key_exists($key, $divisi)) {
                return $divisi[$key]['header'];
            } else {
                return 'Tidak Diketahui';
            }
        }
    }
}

if (!function_exists('data_komunitas')) {
    function data_komunitas()
    {
        $komunitas = [
            [
                'value' => '1',
                'text'  => 'Ojek'
            ],
            [
                'value' => '2',
                'text'  => 'Trans Up'
            ],
            [
                'value' => '3',
                'text'  => 'Pick Up'
            ],
            [
                'value' => '4',
                'text'  => 'Sewa Motor'
            ]
        ];

        return $komunitas;
    }
}

if (!function_exists('display_komunitas')) {
    function display_komunitas($key)
    {
        $komunitas = array(
            '1' => 'Ojek',
            '2' => 'Trans Up',
            '3' => 'Pick Up',
            '4' => 'Sewa Motor'
        );

        if (array_key_exists($key, $komunitas)) {
            return $komunitas[$key];
        } else {
            return '';
        }
    }
}

if (!function_exists('data_rekening')) {
    function data_rekening($mutasi = FALSE)
    {
        $rekening = [
            [
                'value' => '1',
                'text'  => 'Laci Office'
            ],
            [
                'value' => '2',
                'text'  => 'Rek. LPD'
            ]
        ];

        if ($mutasi == 'mutasi') {
            $rekening[] = [
                'value' => '3',
                'text'  => 'Rek. BUPDA'
            ];
        }

        return $rekening;
    }
}

if (!function_exists('display_rekening')) {
    function display_rekening($key)
    {
        $rekening = array(
            '1' => 'Laci Office',
            '2' => 'Rek. LPD',
            '3' => 'Rek. BUPDA'
        );

        if (array_key_exists($key, $rekening)) {
            return $rekening[$key];
        } else {
            return 'Unknown';
        }
    }
}

if (!function_exists('kode_presensi')) {
    function kode_presensi($key)
    {
        $kode = array(
            '4e4365af92f2a09c2e02ca3911abf7c7db3cbdf41b686e7604d38736a8ab57368341bd462bbd3a0c65b346447e95a3e504514afe9131e710214592eb9aaf1af8' => 'datang',
            '5302372fae7708fd41bf741b92098dea350f0ba81a4d0084e590c37c3d153c36f9cfdb32af4150186dc768bae779c33513ebcd943cdd20e8fe45d15e4b7412ac' => 'pulang',
            '154db8fddff14f30f2b8a2168275841c915568714faab42d31c177967c43d91e2f7a20ea22e957e3546998ac7cbdea847e5c87e1cd2c250f049a761b4e6ef132'  => 'bupda1433-kadoel'
        );

        if (array_key_exists($key, $kode)) {
            return $kode[$key];
        } else {
            return '';
        }
    }
}

//HELPER FOR PENDAPATAN PARKIR
if (!function_exists('tarif_parkir')) {
    function tarif_parkir($key)
    {
        $tarif = array(
            'menginap'  => 5000,
            'mobil'     => 5000,
            'motor'     => 2000
        );

        if (array_key_exists($key, $tarif)) {
            return $tarif[$key];
        } else {
            return 0;
        }
    }
}

//HELPER FOR SATUAN BARANG
if (!function_exists('data_satuan_barang')) {
    function data_satuan_barang()
    {
        $satuan = [
            [
                'value' => '1',
                'text'  => 'Buah'
            ],
            [
                'value' => '2',
                'text'  => 'Bal'
            ],
            [
                'value' => '3',
                'text'  => 'Kotak'
            ],
            [
                'value' => '4',
                'text'  => 'Unit'
            ],
            [
                'value' => '5',
                'text'  => 'Galon'
            ],
            [
                'value' => '6',
                'text'  => 'Gulung'
            ],
            [
                'value' => '7',
                'text'  => 'Roll'
            ],
            [
                'value' => '8',
                'text'  => 'Lusin'
            ],
            [
                'value' => '9',
                'text'  => 'Gros'
            ],
            [
                'value' => '10',
                'text'  => 'Rim'
            ],
            [
                'value' => '11',
                'text'  => 'Slop'
            ],
            [
                'value' => '12',
                'text'  => 'Pack'
            ],
            [
                'value' => '13',
                'text'  => 'Paket'
            ],
            [
                'value' => '14',
                'text'  => 'Botol'
            ],
            [
                'value' => '15',
                'text'  => 'Saset'
            ],
        ];

        return $satuan;
    }
}

if (!function_exists('satuan_barang')) {
    function satuan_barang($key)
    {
        $satuan = array(
            '1' => 'Buah',
            '2' => 'Bal',
            '3' => 'Kotak',
            '4' => 'Unit',

            '5' => 'Galon',
            '6' => 'Gulung',
            '7' => 'Roll',
            '8' => 'Lusin',

            '9' => 'Gros',
            '10' => 'Rim',
            '11' => 'Slop',
            '12' => 'Pack',

            '13' => 'Paket',
            '14' => 'Botol',
            '15' => 'Saset',
        );

        if (array_key_exists($key, $satuan)) {
            return $satuan[$key];
        } else {
            return 'Buah';
        }
    }
}

if (!function_exists('data_jenis_sewa')) {
    function data_jenis_sewa()
    {
        $jenis = [
            [
                'value' => '1',
                'text'  => 'Tour'
            ],
            [
                'value' => '2',
                'text'  => 'Drop'
            ],
            [
                'value' => '3',
                'text'  => 'Jemput'
            ]
        ];

        return $jenis;
    }
}

if (!function_exists('jenis_sewa')) {
    function jenis_sewa($key)
    {
        $jenis = array(
            '1'  => 'Tour',
            '2'  => 'Drop',
            '3'  => 'Jemput'
        );

        if (array_key_exists($key, $jenis)) {
            return $jenis[$key];
        } else {
            return 'Tidak Diketahui';
        }
    }
}

//HELPER FOR PENDAPATAN PARKIR
if (!function_exists('iuran_mobil')) {
    function iuran_mobil($param)
    {
        $key = huruf_kecil($param);
        $iuran = array(
            'tour'      => 20000,
            'drop'      => 10000,
            'jemput'    => 5000,
            '1'         => 20000,
            '2'         => 10000,
            '3'         => 5000
        );

        if (array_key_exists($key, $iuran)) {
            return $iuran[$key];
        } else {
            return 0;
        }
    }
}

if (!function_exists('data_member_eparkir')) {
    function data_member_eparkir()
    {
        $member = [
            [
                'value' => '1',
                'text'  => 'Flat Id'
            ],
            [
                'value' => '2',
                'text'  => 'Member Id'
            ]
        ];

        return $member;
    }
}

if (!function_exists('member_eparkir')) {
    function member_eparkir($key)
    {
        $member = array(
            '1' => 'Flat Id',
            '2' => 'Member Id'
        );

        if (array_key_exists($key, $member)) {
            return $member[$key];
        } else {
            return 'Unknown';
        }
    }
}

if (!function_exists('data_kendaraan_eparkir')) {
    function data_kendaraan_eparkir()
    {
        $kendaraan = [
            [
                'value' => '1',
                'text'  => 'Global'
            ],
            [
                'value' => '2',
                'text'  => 'Motor'
            ],
            [
                'value' => '3',
                'text'  => 'Mobil'
            ]
        ];

        return $kendaraan;
    }
}

if (!function_exists('kendaraan_eparkir')) {
    function kendaraan_eparkir($key)
    {
        $kendaraan = array(
            '1' => 'Global',
            '2' => 'Motor',
            '3' => 'Mobil',
        );

        if (array_key_exists($key, $kendaraan)) {
            return $kendaraan[$key];
        } else {
            return 'Unknown';
        }
    }
}

if (!function_exists('tarif_member')) {
    function tarif_member($kategori, $kendaraan)
    {
        /*
            $kategori dengan nilai 0 = unknown, 1 = Flat Id, 2 = Member Id
            $Kendaraan dengan nilai 0 = unknown, 1 = Global. 2 = Motor, 3 = Mobil
        */

        $harga = 0;
        if ($kategori == 0 && $kendaraan == 0) {
            $harga = 0;
        } else {
            if ($kategori == 1) {
                if ($kendaraan == 1) {
                    $harga = 100000;
                } else {
                    $harga = 0;
                }
            } else if ($kategori == 2) {
                if ($kendaraan == 2) {
                    $harga = 150000;
                } else if ($kendaraan == 3) {
                    $harga = 200000;
                } else {
                    $harga = 0;
                }
            } else {
                $harga = 0;
            }
        }
        return (int)$harga;
    }
}

if (!function_exists('data_jenis_usaha')) {
    function data_jenis_usaha()
    {
        $usaha = [
            [
                'value' => '1',
                'text'  => 'Ojek'
            ],
            [
                'value' => '2',
                'text'  => 'Sewa Motor'
            ],
            [
                'value' => '3',
                'text'  => 'Driver'
            ],
            [
                'value' => '4',
                'text'  => 'Pick-Up'
            ],
            [
                'value' => '5',
                'text'  => 'Umum'
            ]
        ];

        return $usaha;
    }
}

if (!function_exists('jenis_usaha')) {
    function jenis_usaha($key)
    {
        $usaha = array(
            '1' => 'Ojek',
            '2' => 'Sewa Motor',
            '3' => 'Driver',
            '4' => 'Pick-Up',
            '5' => 'Umum'
        );

        if (array_key_exists($key, $usaha)) {
            return $usaha[$key];
        } else {
            return 'Umum';
        }
    }
}

if (!function_exists('tarif_topup')) {
    function tarif_topup($kendaraan)
    {
        $harga = 0;
        if ($kendaraan == 2) {
            $harga = 100000;
        } else if ($kendaraan == 3) {
            $harga = 150000;
        } else {
            $harga = 0;
        }
        return $harga;
    }
}

if (!function_exists('data_jenis_mutasi')) {
    function data_jenis_mutasi()
    {
        $role = [
            [
                'value' => '1',
                'text'  => 'Setoran ke LPD'
            ],
            [
                'value' => '2',
                'text'  => 'Modal Tambahan'
            ],
            [
                'value' => '3',
                'text'  => 'Lainnya'
            ]
        ];

        return $role;
    }
}


/**
 * Function that groups an array of associative arrays by some key.
 * 
 * @param {String} $key Property to sort by.
 * @param {Array} $data Array that stores multiple associative arrays.
 */
function array_groupby($key, $data)
{
    $result = array();

    foreach ($data as $val) {
        if (array_key_exists($key, $val)) {
            $result[$val[$key]][] = $val;
        } else {
            $result[""][] = $val;
        }
    }

    return $result;
}

if (!function_exists('data_agama')) {
    function data_agama($key)
    {
        $agama = array(
            '1' => 'Hindu',
            '2' => 'Islam',
            '3' => 'Kristen',
            '4' => 'Katolik',
            '5' => 'Buddha',
            '6' => 'Kong Hu Chu',
        );

        if (array_key_exists($key, $agama)) {
            return $agama[$key];
        } else {
            return 'Unknown';
        }
    }
}
