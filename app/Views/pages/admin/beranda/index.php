<?= $this->extend('theme/body'); ?>
<?= $this->section('content'); ?>

<?php $tanggal = date('Y-m-d'); ?>

<div class="content">
    <div class="row">
        <?php
        $cards = [
            ['id' => 'total-pegawai', 'label' => 'Total Pegawai', 'icon' => 'fa-users', 'class' => 'text-primary'],
            ['id' => 'hadir-hari-ini', 'label' => 'Hadir Hari Ini', 'icon' => 'fa-user-check', 'class' => 'text-success'],
            ['id' => 'terlambat-hari-ini', 'label' => 'Terlambat', 'icon' => 'fa-clock', 'class' => 'text-warning'],
            ['id' => 'izin-sakit-hari-ini', 'label' => 'Izin / Sakit', 'icon' => 'fa-notes-medical', 'class' => 'text-info'],
            ['id' => 'alpa-hari-ini', 'label' => 'Alpa Hari Ini', 'icon' => 'fa-user-xmark', 'class' => 'text-danger'],
            ['id' => 'izin-pending', 'label' => 'Izin Pending', 'icon' => 'fa-file-signature', 'class' => 'text-secondary'],
            ['id' => 'tukar-jadwal-pending', 'label' => 'Tukar Jadwal Pending', 'icon' => 'fa-right-left', 'class' => 'text-secondary'],
        ];
        ?>

        <?php foreach ($cards as $card): ?>
            <div class="col-6 col-xl-3">
                <a class="block block-rounded block-link-shadow text-end" href="javascript:void(0)">
                    <div class="block-content block-content-full d-sm-flex justify-content-between align-items-center">
                        <div class="d-none d-sm-block">
                            <i class="fa <?= esc($card['icon']); ?> fa-2x opacity-25"></i>
                        </div>
                        <div>
                            <div class="fs-3 fw-semibold <?= esc($card['class']); ?>" id="<?= esc($card['id']); ?>">0</div>
                            <div class="fs-sm fw-semibold text-uppercase text-muted"><?= esc($card['label']); ?></div>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row">
        <div class="col-xl-8">
            <div id="block-presensi-hari-ini" class="block block-themed block-rounded">
                <div class="block-header">
                    <h3 class="block-title text-white">
                        <i class="fa fa-calendar-check"></i> <b>PRESENSI HARI INI</b>
                    </h3>
                    <div class="block-options">
                        <span class="badge bg-light text-dark"><?= esc($tanggal); ?></span>
                    </div>
                </div>

                <div class="block-content block-content-full overflow-x-auto">
                    <table class="table table-vcenter table-hover nowrap w-100">
                        <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th>Kode</th>
                                <th>Nama Pegawai</th>
                                <th>Shift</th>
                                <th>Jam Datang</th>
                                <th>Status Datang</th>
                                <th>Jam Pulang</th>
                                <th>Status Pulang</th>
                            </tr>
                        </thead>
                        <tbody id="presensi-hari-ini-body">
                            <tr>
                                <td colspan="8" class="text-center text-muted">Memuat data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div id="block-aktivitas-terbaru" class="block block-themed block-rounded">
                <div class="block-header">
                    <h3 class="block-title text-white">
                        <i class="fa fa-clock-rotate-left"></i> <b>AKTIVITAS TERBARU</b>
                    </h3>
                </div>

                <div class="block-content">
                    <div id="aktivitas-terbaru-list">
                        <div class="text-center text-muted py-4">Memuat data...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection('content'); ?>