<?php
$totalSegment_sidebar = current_url(true)->getTotalSegments();
$segment_sidebar['2'] = current_url(true)->getSegment(2);
$segment_sidebar['2'] = str_replace('-', '_', $segment_sidebar['2']);
$segment_sidebar['3'] = $totalSegment_sidebar > 2 ? current_url(true)->getSegment(3) : '';
?>

<nav id="sidebar">
    <!-- Sidebar Content -->
    <div class="sidebar-content">
        <!-- Side Header -->
        <div class="content-header justify-content-lg-center bg-black-10">
            <!-- Logo -->
            <div>
                <span class="smini-visible fw-bold tracking-wide fs-lg">
                    p<span class="text-primary">b</span>
                </span>
                <a class="link-fx fw-bold tracking-wide mx-auto" href="<?= base_url('pegawai') ?>">
                    <span class="smini-hidden">
                        <i class="fa fa-fire text-primary"></i>
                        <span class="fs-4 text-dual">Presensi</span><span class="fs-4 text-primary">BUPDA</span>
                    </span>
                </a>
            </div>
            <!-- END Logo -->

            <!-- Options -->
            <div>
                <!-- Close Sidebar, Visible only on mobile screens -->
                <!-- Layout API, functionality initialized in Template._uiApiLayout() -->
                <button type="button" class="btn btn-sm btn-alt-danger d-lg-none" data-toggle="layout" data-action="sidebar_close">
                    <i class="fa fa-fw fa-times"></i>
                </button>
                <!-- END Close Sidebar -->
            </div>
            <!-- END Options -->
        </div>
        <!-- END Side Header -->

        <!-- Sidebar Scrolling -->
        <div class="js-sidebar-scroll">
            <!-- Side Main Navigation -->
            <div class="content-side content-side-full">
                <!--
                            Mobile navigation, desktop navigation can be found in #page-header

                            If you would like to use the same navigation in both mobiles and desktops, you can use exactly the same markup inside sidebar and header navigation ul lists
                            -->
                <ul class="nav-main">
                    <li class="nav-main-item">
                        <a class="nav-main-link <?= $totalSegment_sidebar == 1 ? 'active' : ''; ?>" href="<?= base_url('pegawai') ?>">
                            <i class="nav-main-link-icon fa fa-house-user"></i>
                            <span class="nav-main-link-name">Beranda</span>
                        </a>
                    </li>
                    <li class="nav-main-item <?= $segment_sidebar['2'] == 'jadwal' || $segment_sidebar['2'] == 'riwayat' ? 'open' : ''; ?>">
                        <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true" aria-expanded="true" href="#">
                            <i class="nav-main-link-icon fa fa-puzzle-piece"></i>
                            <span class="nav-main-link-name">Data Saya</span>
                        </a>
                        <ul class="nav-main-submenu">
                            <li class="nav-main-item">
                                <a class="nav-main-link <?= $segment_sidebar['2'] == 'jadwal' ? 'active' : ''; ?>" href="<?= base_url('pegawai/jadwal'); ?>">
                                    <span class="nav-main-link-name">Jadwal Kerja</span>
                                </a>
                            </li>
                            <li class="nav-main-item">
                                <a class="nav-main-link <?= $segment_sidebar['2'] == 'riwayat' ? 'active' : ''; ?>" href="<?= base_url('pegawai/riwayat'); ?>">
                                    <span class="nav-main-link-name">Riwayat Presensi</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-main-item <?= $segment_sidebar['2'] == 'izin' || $segment_sidebar['2'] == 'tukar' ? 'open' : ''; ?>">
                        <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true" aria-expanded="true" href="#">
                            <i class="nav-main-link-icon fa fa-puzzle-piece"></i>
                            <span class="nav-main-link-name">Pengajuan</span>
                        </a>
                        <ul class="nav-main-submenu">
                            <li class="nav-main-item">
                                <a class="nav-main-link <?= $segment_sidebar['2'] == 'izin' ? 'active' : ''; ?>" href="<?= base_url('pegawai/izin'); ?>">
                                    <span class="nav-main-link-name">Izin / Sakit</span>
                                </a>
                            </li>
                            <li class="nav-main-item">
                                <a class="nav-main-link <?= $segment_sidebar['2'] == 'cuti' ? 'active' : ''; ?>" href="<?= base_url('pegawai/cuti'); ?>">
                                    <span class="nav-main-link-name">Cuti</span>
                                </a>
                            </li>
                            <li class="nav-main-item">
                                <a class="nav-main-link <?= $segment_sidebar['2'] == 'tukar' ? 'active' : ''; ?>" href="<?= base_url('pegawai/tukar'); ?>">
                                    <span class="nav-main-link-name">Tukar Jadwal</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-main-item">
                        <a class="nav-main-link <?= $segment_sidebar['2'] == 'profil' ? 'active' : ''; ?>" href="<?= base_url('pegawai/profil') ?>">
                            <i class="nav-main-link-icon fa fa-house-user"></i>
                            <span class="nav-main-link-name">Profil</span>
                        </a>
                    </li>
                    <li class="nav-main-item">
                        <a class="nav-main-link" href="<?= base_url('pegawai/logout'); ?>">
                            <i class="nav-main-link-icon fa fa-fw fa-sign-out-alt"></i>
                            <span class="nav-main-link-name">Log Out</span>
                        </a>
                    </li>
                </ul>
            </div>
            <!-- END Side Main Navigation -->
        </div>
        <!-- END Sidebar Scrolling -->
    </div>
    <!-- Sidebar Content -->
</nav>
<!-- END Sidebar -->