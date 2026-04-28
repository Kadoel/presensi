<?= $this->extend('theme/admin/body'); ?>
<?= $this->section('content'); ?>

<?php $tanggal = date('Y-m-d'); ?>

<div class="content">
    <div id="block-ringkasan-beranda" class="mb-4">
        <div class="mb-2">
            <h5 class="mb-1">
                <i class="fa fa-users text-primary me-1"></i>
                <b>DATA UTAMA</b>
            </h5>
            <small class="text-muted">Informasi pegawai dan pengajuan</small>
        </div>
        <div class="row">
            <?php
            $kpi = [
                ['id' => 'total-pegawai', 'label' => 'Total Pegawai', 'icon' => 'fa-users', 'class' => 'text-primary'],
                ['id' => 'izin-pending', 'label' => 'Izin Pending', 'icon' => 'fa-file-circle-question', 'class' => 'text-warning'],
                ['id' => 'tukar-jadwal-pending', 'label' => 'Tukar Jadwal Pending', 'icon' => 'fa-right-left', 'class' => 'text-warning'],
            ];
            ?>

            <?php foreach ($kpi as $card): ?>
                <div class="col-6 col-xl-4">
                    <a class="block block-rounded block-link-shadow text-end" href="javascript:void(0)">
                        <div class="block-content block-content-full d-sm-flex justify-content-between align-items-center">
                            <div class="d-none d-sm-block">
                                <i class="fa <?= esc($card['icon']); ?> fa-2x <?= esc($card['class']); ?> opacity-50"></i>
                            </div>
                            <div>
                                <div class="fs-3 fw-semibold <?= esc($card['class']); ?>" id="<?= esc($card['id']); ?>">
                                    0
                                </div>
                                <div class="fs-sm fw-semibold text-uppercase text-muted"><?= esc($card['label']); ?></div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="block-ringkasan-beranda" class="mb-4">
            <div class="mb-2">
                <h5 class="mb-1">
                    <i class="fa fa-calendar-week text-primary me-1"></i>
                    <b>JADWAL HARI INI</b>
                </h5>
                <small class="text-muted">Informasi jadwal pegawai tanggal <?= tanggal_indonesia(date('Y-m-d')); ?></small>
            </div>
            <div class="row">
                <?php
                $jadwalHariIni = [
                    ['id' => 'jadwal-kerja', 'label' => 'Jadwal Kerja', 'icon' => 'fa-briefcase', 'class' => 'text-success'],
                    ['id' => 'jadwal-izin', 'label' => 'Izin', 'icon' => 'fa-file-signature', 'class' => 'text-info'],
                    ['id' => 'jadwal-sakit', 'label' => 'Sakit', 'icon' => 'fa-notes-medical', 'class' => 'text-primary'],
                    ['id' => 'jadwal-libur', 'label' => 'Libur', 'icon' => 'fa-umbrella-beach', 'class' => 'text-secondary'],
                    ['id' => 'total-jadwal', 'label' => 'Total Jadwal', 'icon' => 'fa-calendar-days', 'class' => 'text-dark'],
                ];
                ?>

                <?php foreach ($jadwalHariIni as $card): ?>
                    <div class="col-6 col-xl">
                        <a class="block block-rounded block-link-shadow text-end" href="javascript:void(0)">
                            <div class="block-content block-content-full d-sm-flex justify-content-between align-items-center">
                                <div class="d-none d-sm-block">
                                    <i class="fa <?= esc($card['icon']); ?> fa-2x <?= esc($card['class']); ?> opacity-50"></i>
                                </div>
                                <div>
                                    <div class="fs-3 fw-semibold <?= esc($card['class']); ?>" id="<?= esc($card['id']); ?>">
                                        0
                                    </div>
                                    <div class="fs-sm fw-semibold text-uppercase text-muted"><?= esc($card['label']); ?></div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mb-2">
                <h5 class="mb-1">
                    <i class="fa fa-calendar-check text-primary me-1"></i>
                    <b>PRESENSI HARI INI</b>
                </h5>
                <small class="text-muted">Presensi pegawai tanggal <?= tanggal_indonesia(date('Y-m-d')); ?></small>
            </div>
            <div class="row">
                <?php
                $presensiHariIni = [
                    ['id' => 'total-presensi', 'label' => 'Total Presensi', 'icon' => 'fa-clipboard-check', 'class' => 'text-success'],
                    ['id' => 'tepat-datang', 'label' => 'Datang Tepat', 'icon' => 'fa-user-check', 'class' => 'text-success'],
                    ['id' => 'telat-datang', 'label' => 'Datang Telat', 'icon' => 'fa-clock', 'class' => 'text-warning'],
                    ['id' => 'tepat-pulang', 'label' => 'Pulang Tepat', 'icon' => 'fa-arrow-right-from-bracket', 'class' => 'text-success'],
                    ['id' => 'pulang-cepat', 'label' => 'Pulang Cepat', 'icon' => 'fa-person-walking-arrow-right', 'class' => 'text-warning'],
                ];
                ?>

                <?php foreach ($presensiHariIni as $card): ?>
                    <div class="col-6 col-xl">
                        <a class="block block-rounded block-link-shadow text-end" href="javascript:void(0)">
                            <div class="block-content block-content-full d-sm-flex justify-content-between align-items-center">
                                <div class="d-none d-sm-block">
                                    <i class="fa <?= esc($card['icon']); ?> fa-2x <?= esc($card['class']); ?> opacity-50"></i>
                                </div>
                                <div>
                                    <div class="fs-3 fw-semibold <?= esc($card['class']); ?>" id="<?= esc($card['id']); ?>">
                                        0
                                    </div>
                                    <div class="fs-sm fw-semibold text-uppercase text-muted"><?= esc($card['label']); ?></div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mb-2">
                <h5 class="mb-1">
                    <i class="fa fa-arrows-rotate text-primary me-1"></i>
                    <b>SINKRON HARI INI</b>
                </h5>
                <small class="text-muted">Sinkron presensi pegawai tanggal <?= tanggal_indonesia(date('Y-m-d')); ?></small>
            </div>
            <div class="row">
                <?php
                $sinkron = [
                    ['id' => 'belum-sinkron', 'label' => 'Belum Sinkron', 'icon' => 'fa-clock-rotate-left', 'class' => 'text-danger'],
                    ['id' => 'hadir', 'label' => 'Hadir', 'icon' => 'fa-user-check', 'class' => 'text-success'],
                    ['id' => 'izin', 'label' => 'Izin', 'icon' => 'fa-file-signature', 'class' => 'text-info'],
                    ['id' => 'sakit', 'label' => 'Sakit', 'icon' => 'fa-notes-medical', 'class' => 'text-primary'],
                    ['id' => 'libur', 'label' => 'Libur', 'icon' => 'fa-umbrella-beach', 'class' => 'text-secondary'],
                    ['id' => 'alpa', 'label' => 'Alpa', 'icon' => 'fa-user-xmark', 'class' => 'text-danger'],
                ];
                ?>

                <?php foreach ($sinkron as $card): ?>
                    <div class="col-6 col-xl-4">
                        <a class="block block-rounded block-link-shadow text-end" href="javascript:void(0)">
                            <div class="block-content block-content-full d-sm-flex justify-content-between align-items-center">
                                <div class="d-none d-sm-block">
                                    <i class="fa <?= esc($card['icon']); ?> fa-2x <?= esc($card['class']); ?> opacity-50"></i>
                                </div>
                                <div>
                                    <div class="fs-3 fw-semibold <?= esc($card['class']); ?>" id="<?= esc($card['id']); ?>">
                                        0
                                    </div>
                                    <div class="fs-sm fw-semibold text-uppercase text-muted"><?= esc($card['label']); ?></div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div id="block-progress-presensi" class="block block-rounded">
                    <div class="block-content block-content-full">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-semibold"><i class="fa fa-chart-simple me-1"></i> Jadwal vs Presensi</span>
                            <span id="progress-presensi-label" class="fw-semibold">0%</span>
                        </div>
                        <div class="progress push" style="height: 10px;">
                            <div id="progress-presensi-bar" class="progress-bar bg-success" role="progressbar" style="width: 0%;"></div>
                        </div>
                        <div id="progress-presensi-text" class="fs-sm text-muted">0 presensi dari 0 jadwal</div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div id="block-progress-sinkron" class="block block-rounded">
                    <div class="block-content block-content-full">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-semibold"><i class="fa fa-rotate me-1"></i> Status Sinkron</span>
                            <span id="progress-sinkron-label" class="fw-semibold">0%</span>
                        </div>
                        <div class="progress push" style="height: 10px;">
                            <div id="progress-sinkron-bar" class="progress-bar bg-primary" role="progressbar" style="width: 0%;"></div>
                        </div>
                        <div id="progress-sinkron-text" class="fs-sm text-muted">0 selesai, 0 belum sinkron</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-6">
                <div id="block-grafik-hasil-presensi" class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">
                            <i class="fa fa-chart-pie me-1"></i> Grafik Hasil Presensi Hari Ini
                        </h3>
                    </div>
                    <div class="block-content block-content-full">
                        <canvas id="chart-hasil-presensi" height="140"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div id="block-grafik-status-presensi" class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">
                            <i class="fa fa-chart-column me-1"></i> Grafik Status Datang & Pulang
                        </h3>
                    </div>
                    <div class="block-content block-content-full">
                        <canvas id="chart-status-presensi" height="140"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-8">
                <div id="block-grafik-mingguan" class="block block-rounded shadow-sm">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">
                            <i class="fa fa-chart-line text-primary me-1"></i>
                            <b>GRAFIK MINGGUAN</b>
                            <small class="text-muted d-block">Jadwal vs presensi 7 hari terakhir</small>
                        </h3>
                    </div>
                    <div class="block-content block-content-full">
                        <div class="chart-box chart-box-lg">
                            <canvas id="chart-mingguan"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div id="block-grafik-bulanan" class="block block-rounded shadow-sm">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">
                            <i class="fa fa-chart-pie text-success me-1"></i>
                            <b>GRAFIK BULANAN</b>
                            <small class="text-muted d-block">Komposisi hasil presensi bulan ini</small>
                        </h3>
                    </div>
                    <div class="block-content block-content-full">
                        <div class="chart-box chart-box-md">
                            <canvas id="chart-bulanan"></canvas>
                        </div>
                    </div>
                </div>
            </div>
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
                                    <th>Hasil Presensi</th>
                                </tr>
                            </thead>
                            <tbody id="presensi-hari-ini-body">
                                <tr>
                                    <td colspan="9" class="text-center text-muted">Memuat data...</td>
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
                        <div id="aktivitas-terbaru-wrapper" style="height:390px; overflow:hidden; position:relative;">
                            <div id="aktivitas-terbaru-list">
                                <div class="text-center text-muted py-4">Memuat data...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?= $this->endSection('content'); ?>