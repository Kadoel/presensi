<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->set404Override(function () {
    return view('errors/error_404');
});

$routes->GET('/tes', 'Tes::index', ['filter' => 'role:admin']);
$routes->GET('/', 'Login::index');
$routes->POST('/auth', 'Login::auth');

//HALAMAN PRESENSI
$routes->group('presensi', static function ($routes) {
    $routes->get('/', 'KiosPresensiController::index');
    $routes->post('preview', 'KiosPresensiController::preview');
    $routes->post('submit', 'KiosPresensiController::submit');
});

/* --------- ROLE ADMIN -------------*/
$routes->group('admin', ['filter' => 'role:admin'], function ($routes) {
    $routes->GET("/", "Admin\Beranda::index");
    $routes->get('summary', 'Admin\Beranda::summary');
    $routes->get('presensi-hari-ini', 'Admin\Beranda::presensiHariIni');
    $routes->get('aktivitas-terbaru', 'Admin\Beranda::aktivitasTerbaru');
    $routes->GET("logout", "Admin\Beranda::logout");

    //Pengaturan
    $routes->group("pengaturan", static function ($routes) {
        $routes->GET('/', "Admin\Pengaturan::index");
        $routes->POST('simpan', "Admin\Pengaturan::simpan");
    });

    //Jabatan
    $routes->group("jabatan", static function ($routes) {
        $routes->match(["GET", "POST"], "/", "Admin\Jabatan::index");
        $routes->POST('simpan', "Admin\Jabatan::simpan");
        $routes->POST('edit', "Admin\Jabatan::edit");
        $routes->POST('delete', "Admin\Jabatan::delete");
        $routes->POST('update/(:num)', "Admin\Jabatan::update/$1");
    });

    //Shift
    $routes->group("shift", static function ($routes) {
        $routes->match(["GET", "POST"], "/", "Admin\Shift::index");
        $routes->POST('simpan', "Admin\Shift::simpan");
        $routes->POST('edit', "Admin\Shift::edit");
        $routes->POST('delete', "Admin\Shift::delete");
        $routes->POST('update/(:num)', "Admin\Shift::update/$1");
    });

    //Pegawai
    $routes->group("pegawai", static function ($routes) {
        $routes->match(["GET", "POST"], "/", "Admin\Pegawai::index");
        $routes->POST('simpan', "Admin\Pegawai::simpan");
        $routes->POST('edit', "Admin\Pegawai::edit");
        $routes->POST('delete', "Admin\Pegawai::delete");
        $routes->GET('kartu/(:num)', 'Admin\Pegawai::kartu/$1');
        $routes->GET('download/(:num)', 'Admin\Pegawai::downloadQRCode/$1');
        $routes->POST('update/(:num)', "Admin\Pegawai::update/$1");
    });

    //Pengguna
    $routes->group("pengguna", static function ($routes) {
        $routes->match(['GET', 'POST'], '/', 'Admin\Users::index');
        $routes->post('simpan', 'Admin\Users::simpan');
        $routes->post('edit', 'Admin\Users::edit'); // ambil data untuk modal edit
        $routes->post('delete', 'Admin\Users::hapus');
        $routes->post('update/(:num)', 'Admin\Users::ubah/$1');
        // dropdown pegawai
        $routes->get('dropdown-pegawai', 'Admin\Users::dropdownPegawai');
        $routes->get('dropdown-pegawai-edit/(:num)', 'Admin\Users::dropdownPegawaiEdit/$1');
    });

    //Hari Libur
    $routes->group("libur", static function ($routes) {
        $routes->match(['GET', 'POST'], '/', 'Admin\HariLibur::index');
        $routes->post('simpan', 'Admin\HariLibur::simpan');
        $routes->post('edit', 'Admin\HariLibur::edit');
        $routes->post('delete', 'Admin\HariLibur::hapus');
        $routes->post('konfirmasi-override', 'Admin\HariLibur::konfirmasiOverride');
        $routes->post('update/(:num)', 'Admin\HariLibur::update/$1');
    });

    //Pengajuan Izin & Sakit
    $routes->group("izin", static function ($routes) {
        $routes->match(['GET', 'POST'], '/', 'Admin\PengajuanIzin::index');
        $routes->post('simpan', 'Admin\PengajuanIzin::simpan');
        $routes->post('edit', 'Admin\PengajuanIzin::edit');
        $routes->post('delete', 'Admin\PengajuanIzin::hapus');
        $routes->post('approve', 'Admin\PengajuanIzin::approve');
        $routes->post('reject', 'Admin\PengajuanIzin::reject');
        $routes->post('cancel-approve', 'Admin\PengajuanIzin::cancelApprove');
        $routes->post('update/(:num)', 'Admin\PengajuanIzin::ubah/$1');
    });

    //Jadwal Kerja
    $routes->group('jadwal', static function ($routes) {
        $routes->match(['GET', 'POST'], '/', 'Admin\JadwalKerja::index');
        $routes->post('simpan', 'Admin\JadwalKerja::simpan');
        $routes->post('edit', 'Admin\JadwalKerja::edit');
        $routes->post('update/(:num)', 'Admin\JadwalKerja::update/$1');
    });

    //Audit Logs
    $routes->group('log', static function ($routes) {
        $routes->match(['GET', 'POST'], '/', 'Admin\AuditLog::index');
        $routes->POST('detail', 'Admin\AuditLog::detail');
    });

    //Tukar Jadwal
    $routes->group('tukar', static function ($routes) {
        $routes->match(['GET', 'POST'], '/', 'Admin\TukarJadwal::index');
        $routes->post('detail', 'Admin\TukarJadwal::detail');
        $routes->post('simpan-langsung', 'Admin\TukarJadwal::simpanLangsung');
        $routes->post('approve', 'Admin\TukarJadwal::approve');
        $routes->post('reject', 'Admin\TukarJadwal::reject');
        $routes->post('slot-pegawai', 'Admin\TukarJadwal::getSlotPegawai');
    });

    //Presensi
    $routes->group('presensi', static function ($routes) {
        $routes->match(['GET', 'POST'], '/', 'Admin\Presensi::index');
        $routes->post('detail', 'Admin\Presensi::detail');
        $routes->post('sinkron', 'Admin\Presensi::sinkron');
        $routes->post('generate-alpa', 'Admin\Presensi::generateAlpa'); // alias lama
        $routes->post('ringkasan', 'Admin\Presensi::ringkasan');
        $routes->post('lupa/simpan', 'Admin\Presensi::simpanLupa');
        $routes->post('lupa/update/(:num)', 'Admin\Presensi::updateLupa/$1');
        $routes->post('lupa/delete', 'Admin\Presensi::deleteLupa');
    });
});

/* --------- ROLE PEGAWAI -------------*/
$routes->group('pegawai', ['filter' => 'role:pegawai'], function ($routes) {
    $routes->GET("/", "Pegawai\Beranda::index");
    $routes->GET("logout", "Pegawai\Beranda::logout");
});
