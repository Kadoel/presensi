<?php
$totalSegment = current_url(true)->getTotalSegments();
$segment['2'] = current_url(true)->getSegment(2);
$segment['3'] = $totalSegment > 2 ? current_url(true)->getSegment(3) : '';
?>

<li class="nav-main-heading">DATA MASTER</li>
<li class="nav-main-item">
    <a class="nav-main-link <?= $segment['2'] == 'pengaturan' ? 'active' : ''; ?>" href="<?= base_url('admin/pengaturan'); ?>">
        <i class="nav-main-link-icon fa fa-gear"></i>
        <span class="nav-main-link-name">Pengaturan</span>
    </a>
</li>
<li class="nav-main-item">
    <a class="nav-main-link <?= $segment['2'] == 'jabatan' ? 'active' : ''; ?>" href="<?= base_url('admin/jabatan'); ?>">
        <i class="nav-main-link-icon fa fa-arrows-turn-right"></i>
        <span class="nav-main-link-name">Jabatan</span>
    </a>
</li>
<li class="nav-main-item">
    <a class="nav-main-link <?= $segment['2'] == 'pegawai' ? 'active' : ''; ?>" href="<?= base_url('admin/pegawai'); ?>">
        <i class="nav-main-link-icon fa fa-user-group"></i>
        <span class="nav-main-link-name">Pegawai</span>
    </a>
</li>
<li class="nav-main-item">
    <a class="nav-main-link <?= $segment['2'] == 'shift' ? 'active' : ''; ?>" href="<?= base_url('admin/shift'); ?>">
        <i class="nav-main-link-icon fa fa-display"></i>
        <span class="nav-main-link-name">Shift</span>
    </a>
</li>
<li class="nav-main-item">
    <a class="nav-main-link <?= $segment['2'] == 'pengguna' ? 'active' : ''; ?>" href="<?= base_url('admin/pengguna'); ?>">
        <i class="nav-main-link-icon fab fa-gripfire"></i>
        <span class="nav-main-link-name">Pengguna</span>
    </a>
</li>
<li class="nav-main-item">
    <a class="nav-main-link <?= $segment['2'] == 'libur' ? 'active' : ''; ?>" href="<?= base_url('admin/libur'); ?>">
        <i class="nav-main-link-icon fa fa-display"></i>
        <span class="nav-main-link-name">Hari Libur</span>
    </a>
</li>

<li class="nav-main-item">
    <a class="nav-main-link <?= $segment['2'] == 'izin' ? 'active' : ''; ?>" href="<?= base_url('admin/izin'); ?>">
        <i class="nav-main-link-icon fa fa-gear"></i>
        <span class="nav-main-link-name">Izin & Sakit</span>
    </a>
</li>

<li class="nav-main-item">
    <a class="nav-main-link <?= $segment['2'] == 'presensi' ? 'active' : ''; ?>" href="<?= base_url('admin/presensi'); ?>">
        <i class="nav-main-link-icon fa fa-gear"></i>
        <span class="nav-main-link-name">Presensi</span>
    </a>
</li>

<li class="nav-main-item">
    <a class="nav-main-link <?= $segment['2'] == 'jadwal' ? 'active' : ''; ?>" href="<?= base_url('admin/jadwal'); ?>">
        <i class="nav-main-link-icon fa fa-display"></i>
        <span class="nav-main-link-name">Jadwal</span>
    </a>
</li>

<li class="nav-main-item">
    <a class="nav-main-link <?= $segment['2'] == 'tukar' ? 'active' : ''; ?>" href="<?= base_url('admin/tukar'); ?>">
        <i class="nav-main-link-icon fa fa-display"></i>
        <span class="nav-main-link-name">Tukar Jadwal</span>
    </a>
</li>

<li class="nav-main-item">
    <a class="nav-main-link <?= $segment['2'] == 'log' ? 'active' : ''; ?>" href="<?= base_url('admin/log'); ?>">
        <i class="nav-main-link-icon fa fa-display"></i>
        <span class="nav-main-link-name">Audit Logs</span>
    </a>
</li>

<!-- <li class="nav-main-item <?= (($segment['2'] == 'krama') || ($segment['2'] == 'krama' && ($segment['3'] == 'aktif' || $segment['3'] == 'nonaktif' || $segment['3'] == 'import'))) ? 'open' : ' ' ?>">
    <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true" aria-expanded="false" href="#">
        <i class="nav-main-link-icon fa fa-people-group"></i>
        <span class="nav-main-link-name">Krama</span>
    </a>
    <ul class="nav-main-submenu">
        <li class="nav-main-item">
            <a href="<?= base_url('kelihan/krama/import'); ?>" class="nav-main-link <?= ($totalSegment == 3 && $segment['3'] == 'import') ? 'active' : ' ' ?>"><span class="nav-main-link-name">Import</span></a>
        </li>
        <li class="nav-main-item">
            <a href="<?= base_url('kelihan/krama/aktif'); ?>" class="nav-main-link <?= ($totalSegment == 3 && $segment['3'] == 'aktif') ? 'active' : ' ' ?>"><span class="nav-main-link-name">Aktif</span></a>
        </li>
        <li class="nav-main-item">
            <a href="<?= base_url('kelihan/krama/nonaktif'); ?>" class="nav-main-link <?= ($totalSegment == 3 && $segment['3'] == 'nonaktif') ? 'active' : ' ' ?>"><span class="nav-main-link-name">Non Aktif</span></a>
        </li>
    </ul>
</li> -->