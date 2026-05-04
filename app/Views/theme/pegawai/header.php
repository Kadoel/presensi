<?php
$totalSegment_header = current_url(true)->getTotalSegments();
$segment_header['2'] = current_url(true)->getSegment(2);
$segment_header['2'] = str_replace('-', '_', $segment_header['2']);
$segment_header['3'] = $totalSegment_header > 2 ? current_url(true)->getSegment(3) : '';
?>

<header id="page-header">
    <!-- Header Content -->
    <div class="content-header">
        <!-- Left Section -->
        <div class="space-x-1">
            <!-- Logo -->
            <a class="link-fx fw-bold" href="<?= base_url('pegawai') ?>">
                <i class="fa fa-fire text-primary"></i>
                <span class="fs-4 text-dual">Presensi</span><span class="fs-4 text-primary">BUPDA</span>
            </a>
            <!-- END Logo -->
        </div>
        <!-- END Left Section -->

        <!-- Middle Section -->
        <div class="d-none d-lg-block">
            <!-- Header Navigation -->
            <!-- Desktop Navigation, mobile navigation can be found in #sidebar -->
            <ul class="nav-main nav-main-horizontal nav-main-hover">
                <li class="nav-main-item">
                    <a class="nav-main-link <?= $totalSegment_header == 1 ? 'active' : ''; ?>" href="<?= base_url('pegawai') ?>">
                        <i class="nav-main-link-icon fa fa-house-user"></i>
                        <span class="nav-main-link-name">Beranda</span>
                    </a>
                </li>
                <li class="nav-main-item">
                    <a class="nav-main-link nav-main-link-submenu <?= $segment_header['2'] == 'jadwal' || $segment_header['2'] == 'riwayat' ? 'active' : ''; ?>" data-toggle="submenu" aria-haspopup="true" aria-expanded="true" href="#">
                        <i class="nav-main-link-icon fa fa-puzzle-piece"></i>
                        <span class="nav-main-link-name">Data Saya</span>
                    </a>
                    <ul class="nav-main-submenu">
                        <li class="nav-main-item">
                            <a class="nav-main-link <?= $segment_header['2'] == 'jadwal' ? 'active' : ''; ?>" href="<?= base_url('pegawai/jadwal'); ?>">
                                <span class="nav-main-link-name">Jadwal Kerja</span>
                            </a>
                        </li>
                        <li class="nav-main-item">
                            <a class="nav-main-link <?= $segment_header['2'] == 'riwayat' ? 'active' : ''; ?>" href="<?= base_url('pegawai/riwayat'); ?>">
                                <span class="nav-main-link-name">Riwayat Presensi</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-main-item">
                    <a class="nav-main-link nav-main-link-submenu <?= $segment_header['2'] == 'izin' || $segment_header['2'] == 'tukar' ? 'active' : ''; ?>" data-toggle="submenu" aria-haspopup="true" aria-expanded="true" href="#">
                        <i class="nav-main-link-icon fa fa-puzzle-piece"></i>
                        <span class="nav-main-link-name">Pengajuan</span>
                    </a>
                    <ul class="nav-main-submenu">
                        <li class="nav-main-item">
                            <a class="nav-main-link <?= $segment_header['2'] == 'izin' ? 'active' : ''; ?>" href="<?= base_url('pegawai/izin'); ?>">
                                <span class="nav-main-link-name">Izin / Sakit</span>
                            </a>
                        </li>
                        <li class="nav-main-item">
                            <a class="nav-main-link <?= $segment_header['2'] == 'cuti' ? 'active' : ''; ?>" href="<?= base_url('pegawai/cuti'); ?>">
                                <span class="nav-main-link-name">Cuti</span>
                            </a>
                        </li>
                        <li class="nav-main-item">
                            <a class="nav-main-link <?= $segment_header['2'] == 'tukar' ? 'active' : ''; ?>" href="<?= base_url('pegawai/tukar'); ?>">
                                <span class="nav-main-link-name">Tukar Jadwal</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-main-item">
                    <a class="nav-main-link <?= $segment_header['2'] == 'profil' ? 'active' : ''; ?>" href="<?= base_url('pegawai/profil') ?>">
                        <i class="nav-main-link-icon fa fa-house-user"></i>
                        <span class="nav-main-link-name">Profil</span>
                    </a>
                </li>
            </ul>
            <!-- END Header Navigation -->
        </div>
        <!-- END Middle Section -->

        <!-- Right Section -->
        <div class="space-x-1">
            <a type="button" class="btn btn-sm btn-danger" href="<?= base_url('pegawai/logout'); ?>">
                <i class="nav-main-link-icon fa fa-fw fa-sign-out-alt"></i>
                <span class="nav-main-link-name">Log Out</span>
            </a>
            <!-- Toggle Sidebar -->
            <!-- Layout API, functionality initialized in Template._uiApiLayout() -->
            <button type="button" class="btn btn-sm btn-alt-secondary d-lg-none" data-toggle="layout" data-action="sidebar_toggle">
                <i class="fa fa-fw fa-bars"></i>
            </button>
            <!-- END Toggle Sidebar -->
        </div>
        <!-- END Right Section -->
    </div>
    <!-- END Header Content -->

    <!-- Header Loader -->
    <!-- Please check out the Activity page under Elements category to see examples of showing/hiding it -->
    <div id="page-header-loader" class="overlay-header bg-primary">
        <div class="content-header">
            <div class="w-100 text-center">
                <i class="far fa-sun fa-spin text-white"></i>
            </div>
        </div>
    </div>
    <!-- END Header Loader -->
</header>