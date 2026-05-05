<?php
$totalSegment = current_url(true)->getTotalSegments();
$segment2 = $totalSegment >= 2 ? current_url(true)->getSegment(2) : '';

$menus = [
    [
        'type' => 'item',
        'label' => 'Beranda',
        'url' => 'admin',
        'icon' => 'fa fa-house-user',
        'active' => $totalSegment == 1,
    ],
    [
        'type' => 'heading',
        'label' => 'MASTER DATA',
    ],
    [
        'type' => 'item',
        'label' => 'Pengaturan',
        'url' => 'admin/pengaturan',
        'icon' => 'fa fa-cogs',
        'segment' => 'pengaturan',
    ],
    [
        'type' => 'item',
        'label' => 'Jabatan',
        'url' => 'admin/jabatan',
        'icon' => 'fa fa-sitemap',
        'segment' => 'jabatan',
    ],
    [
        'type' => 'item',
        'label' => 'Pegawai',
        'url' => 'admin/pegawai',
        'icon' => 'fa fa-users',
        'segment' => 'pegawai',
    ],
    [
        'type' => 'item',
        'label' => 'Pengguna',
        'url' => 'admin/pengguna',
        'icon' => 'fa fa-user-shield',
        'segment' => 'pengguna',
    ],
    [
        'type' => 'heading',
        'label' => 'KALENDER & SHIFT',
    ],
    [
        'type' => 'item',
        'label' => 'Shift',
        'url' => 'admin/shift',
        'icon' => 'fa fa-clock',
        'segment' => 'shift',
    ],
    [
        'type' => 'item',
        'label' => 'Jadwal Kerja',
        'url' => 'admin/jadwal',
        'icon' => 'fa fa-calendar-alt',
        'segment' => 'jadwal',
    ],
    [
        'type' => 'item',
        'label' => 'Hari Libur',
        'url' => 'admin/libur',
        'icon' => 'fa fa-calendar-xmark',
        'segment' => 'libur',
    ],
    [
        'type' => 'heading',
        'label' => 'TRANSAKSI',
    ],
    [
        'type' => 'item',
        'label' => 'Presensi',
        'url' => 'admin/presensi',
        'icon' => 'fa fa-calendar-check',
        'segment' => 'presensi',
    ],
    [
        'type' => 'item',
        'label' => 'Izin & Sakit',
        'url' => 'admin/izin-sakit',
        'icon' => 'fa fa-file-medical',
        'segment' => 'izin-sakit',
    ],
    [
        'type' => 'item',
        'label' => 'Cuti',
        'url' => 'admin/cuti',
        'icon' => 'fa fa-calendar-days',
        'segment' => 'cuti',
    ],
    [
        'type' => 'item',
        'label' => 'Saldo Cuti',
        'url' => 'admin/saldo-cuti',
        'icon' => 'fa fa-wallet',
        'segment' => 'saldo-cuti',
    ],
    [
        'type' => 'item',
        'label' => 'Tukar Jadwal',
        'url' => 'admin/tukar',
        'icon' => 'fa fa-right-left',
        'segment' => 'tukar',
    ],
    [
        'type' => 'heading',
        'label' => 'PAYROLL',
    ],
    [
        'type' => 'item',
        'label' => 'Pengaturan Gaji',
        'url' => 'admin/pengaturan-gaji',
        'icon' => 'fa fa-money-bill-wave',
        'segment' => 'pengaturan-gaji',
    ],
    [
        'type' => 'item',
        'label' => 'Penggajian',
        'url' => 'admin/penggajian',
        'icon' => 'fa fa-money-check-dollar',
        'segment' => 'penggajian',
    ],
    [
        'type' => 'heading',
        'label' => 'MONITORING',
    ],
    [
        'type' => 'item',
        'label' => 'Audit Logs',
        'url' => 'admin/log',
        'icon' => 'fa fa-clipboard-list',
        'segment' => 'log',
    ],
];
?>

<?php foreach ($menus as $menu): ?>
    <?php if (($menu['type'] ?? '') === 'heading'): ?>
        <li class="nav-main-heading"><?= esc($menu['label']); ?></li>
        <?php continue; ?>
    <?php endif; ?>

    <?php
    $isActive = $menu['active'] ?? ($segment2 === ($menu['segment'] ?? ''));
    ?>

    <li class="nav-main-item">
        <a class="nav-main-link <?= $isActive ? 'active' : ''; ?>" href="<?= base_url($menu['url']); ?>">
            <i class="nav-main-link-icon <?= esc($menu['icon']); ?>"></i>
            <span class="nav-main-link-name"><?= esc($menu['label']); ?></span>
        </a>
    </li>
<?php endforeach; ?>