<?php
$totalSegment = current_url(true)->getTotalSegments();
$segment['2'] = current_url(true)->getSegment(2);
$segment['3'] = $totalSegment > 2 ? current_url(true)->getSegment(3) : '';
?>

<li class="nav-main-heading">DATA MASTER</li>

<li class="nav-main-item">
    <a class="nav-main-link <?= $segment['2'] == 'pengaturan' ? 'active' : ''; ?>" href="<?= base_url('admin/pengaturan'); ?>">
        <i class="nav-main-link-icon fa fa-cogs"></i>
        <span class="nav-main-link-name">Pengaturan</span>
    </a>
</li>

<li class="nav-main-item">
    <a class="nav-main-link <?= $segment['2'] == 'jabatan' ? 'active' : ''; ?>" href="<?= base_url('admin/jabatan'); ?>">
        <i class="nav-main-link-icon fa fa-sitemap"></i>
        <span class="nav-main-link-name">Jabatan</span>
    </a>
</li>

<li class="nav-main-item">
    <a class="nav-main-link <?= $segment['2'] == 'shift' ? 'active' : ''; ?>" href="<?= base_url('admin/shift'); ?>">
        <i class="nav-main-link-icon fa fa-clock"></i>
        <span class="nav-main-link-name">Shift</span>
    </a>
</li>

<li class="nav-main-item">
    <a class="nav-main-link <?= $segment['2'] == 'pegawai' ? 'active' : ''; ?>" href="<?= base_url('admin/pegawai'); ?>">
        <i class="nav-main-link-icon fa fa-users"></i>
        <span class="nav-main-link-name">Pegawai</span>
    </a>
</li>

<li class="nav-main-item">
    <a class="nav-main-link <?= $segment['2'] == 'pengguna' ? 'active' : ''; ?>" href="<?= base_url('admin/pengguna'); ?>">
        <i class="nav-main-link-icon fa fa-user-shield"></i>
        <span class="nav-main-link-name">Pengguna</span>
    </a>
</li>

<li class="nav-main-item">
    <a class="nav-main-link <?= $segment['2'] == 'jadwal' ? 'active' : ''; ?>" href="<?= base_url('admin/jadwal'); ?>">
        <i class="nav-main-link-icon fa fa-calendar-alt"></i>
        <span class="nav-main-link-name">Jadwal Kerja</span>
    </a>
</li>

<li class="nav-main-item">
    <a class="nav-main-link <?= $segment['2'] == 'libur' ? 'active' : ''; ?>" href="<?= base_url('admin/libur'); ?>">
        <i class="nav-main-link-icon fa fa-calendar-xmark"></i>
        <span class="nav-main-link-name">Hari Libur</span>
    </a>
</li>

<li class="nav-main-item">
    <a class="nav-main-link <?= $segment['2'] == 'izin-sakit' ? 'active' : ''; ?>" href="<?= base_url('admin/izin-sakit'); ?>">
        <i class="nav-main-link-icon fa fa-file-medical"></i>
        <span class="nav-main-link-name">Izin & Sakit</span>
    </a>
</li>

<li class="nav-main-item">
    <a class="nav-main-link <?= $segment['2'] == 'cuti' ? 'active' : ''; ?>" href="<?= base_url('admin/cuti'); ?>">
        <i class="nav-main-link-icon fa fa-calendar-days"></i>
        <span class="nav-main-link-name">Cuti</span>
    </a>
</li>



<li class="nav-main-item">
    <a class="nav-main-link <?= $segment['2'] == 'tukar' ? 'active' : ''; ?>" href="<?= base_url('admin/tukar'); ?>">
        <i class="nav-main-link-icon fa fa-right-left"></i>
        <span class="nav-main-link-name">Tukar Jadwal</span>
    </a>
</li>

<li class="nav-main-item">
    <a class="nav-main-link <?= $segment['2'] == 'presensi' ? 'active' : ''; ?>" href="<?= base_url('admin/presensi'); ?>">
        <i class="nav-main-link-icon fa fa-calendar-check"></i>
        <span class="nav-main-link-name">Presensi</span>
    </a>
</li>

<li class="nav-main-item">
    <a class="nav-main-link <?= $segment['2'] == 'log' ? 'active' : ''; ?>" href="<?= base_url('admin/log'); ?>">
        <i class="nav-main-link-icon fa fa-clipboard-list"></i>
        <span class="nav-main-link-name">Audit Logs</span>
    </a>
</li>