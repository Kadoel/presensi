<?= $this->extend('theme/pegawai/body'); ?>
<?= $this->section('content'); ?>

<div class="content">
    <div class="row">
        <div class="col-xl-12">
            <div class="block block-rounded">
                <div class="block-content block-content-full">
                    <div class="row align-items-center g-3">

                        <div class="col-md-4">
                            <div class="d-flex align-items-center justify-content-start text-start">
                                <div class="me-3 flex-shrink-0">
                                    <img
                                        id="foto-pegawai"
                                        src="<?= session()->get('foto')
                                                    ? base_url('assets/media/pegawai/' . session()->get('foto'))
                                                    : base_url('assets/media/pegawai/default.png'); ?>"
                                        alt="Foto Pegawai"
                                        class="img-avatar img-avatar48">
                                </div>

                                <div class="min-w-0">
                                    <div class="fw-semibold text-muted">
                                        <i class="fa fa-user me-1 text-primary"></i>
                                        Selamat datang
                                    </div>

                                    <h5 class="mb-1 text-truncate">
                                        <?= esc((string) session()->get('nama_pegawai')); ?>
                                    </h5>

                                    <span class="badge bg-light text-dark border">
                                        <i class="fa fa-id-badge me-1"></i>
                                        <?= esc((string) session()->get('kode_pegawai')); ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 border-md-start">
                            <div class="text-start">
                                <div class="fw-semibold text-muted mb-1">
                                    <i class="fa fa-calendar-day me-1 text-success"></i>
                                    Jadwal Hari Ini
                                </div>

                                <div id="jadwal-kosong" class="d-none">
                                    <span class="badge bg-danger">Tidak ada jadwal</span>
                                </div>

                                <div id="jadwal-ada">
                                    <div class="fw-semibold" id="jadwal-shift">Memuat...</div>
                                    <div class="mt-1">
                                        <span class="badge bg-success mb-1">
                                            <i class="fa fa-arrow-right-to-bracket me-1"></i>
                                            <span id="jadwal-jam-masuk">-</span>
                                        </span>
                                        <span class="badge bg-danger mb-1">
                                            <i class="fa fa-arrow-right-from-bracket me-1"></i>
                                            <span id="jadwal-jam-pulang">-</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 border-md-start">
                            <div class="text-start">
                                <div class="fw-semibold text-muted mb-1">
                                    <i class="fa fa-clipboard-check me-1 text-warning"></i>
                                    Status Presensi
                                </div>

                                <div class="d-flex justify-content-center justify-content-md-between align-items-center gap-2">
                                    <div class="fw-semibold" id="status-presensi-text">Memuat...</div>
                                    <span id="status-presensi-badge" class="badge bg-secondary">-</span>
                                </div>

                                <div class="mt-2 small text-muted">
                                    <i class="fa fa-arrow-right-to-bracket me-1"></i>
                                    Datang: <span id="status-jam-datang">-</span>
                                    <span class="d-none d-md-inline mx-1">|</span>
                                    <br class="d-md-none">
                                    <i class="fa fa-arrow-right-from-bracket me-1"></i>
                                    Pulang: <span id="status-jam-pulang">-</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-2">
        <h5 class="mb-1">
            <i class="fa fa-chart-simple text-primary me-1"></i>
            <b>RINGKASAN BULAN INI</b>
        </h5>
        <small class="text-muted">Rekap hasil presensi bulan berjalan</small>
    </div>

    <div class="row">
        <div class="col-12 col-xl-4">
            <a class="block block-rounded block-link-shadow text-end" href="javascript:void(0)">
                <div class="block-content block-content-full d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fa fa-user-check fa-2x text-success opacity-50"></i>
                    </div>
                    <div class="text-end">
                        <div class="fs-3 fw-semibold text-success" id="hadir">0</div>
                        <div class="fs-sm fw-semibold text-uppercase text-muted">Hadir</div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-12 col-xl-4">
            <a class="block block-rounded block-link-shadow text-end" href="javascript:void(0)">
                <div class="block-content block-content-full d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fa fa-file-signature fa-2x text-info opacity-50"></i>
                    </div>
                    <div class="text-end">
                        <div class="fs-3 fw-semibold text-info" id="izin">0</div>
                        <div class="fs-sm fw-semibold text-uppercase text-muted">Izin</div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-12 col-xl-4">
            <a class="block block-rounded block-link-shadow text-end" href="javascript:void(0)">
                <div class="block-content block-content-full d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fa fa-notes-medical fa-2x text-primary opacity-50"></i>
                    </div>
                    <div class="text-end">
                        <div class="fs-3 fw-semibold text-primary" id="sakit">0</div>
                        <div class="fs-sm fw-semibold text-uppercase text-muted">Sakit</div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-12 col-xl-4">
            <a class="block block-rounded block-link-shadow text-end" href="javascript:void(0)">
                <div class="block-content block-content-full d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fa fa-umbrella-beach fa-2x text-secondary opacity-50"></i>
                    </div>
                    <div class="text-end">
                        <div class="fs-3 fw-semibold text-secondary" id="libur">0</div>
                        <div class="fs-sm fw-semibold text-uppercase text-muted">Libur</div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-12 col-xl-4">
            <a class="block block-rounded block-link-shadow text-end" href="javascript:void(0)">
                <div class="block-content block-content-full d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fa fa-umbrella-beach fa-2x text-secondary opacity-50"></i>
                    </div>
                    <div class="text-end">
                        <div class="fs-3 fw-semibold text-secondary" id="cuti">0</div>
                        <div class="fs-sm fw-semibold text-uppercase text-muted">Cuti</div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-12 col-xl-4">
            <a class="block block-rounded block-link-shadow text-end" href="javascript:void(0)">
                <div class="block-content block-content-full d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fa fa-user-xmark fa-2x text-danger opacity-50"></i>
                    </div>
                    <div class="text-end">
                        <div class="fs-3 fw-semibold text-danger" id="alpa">0</div>
                        <div class="fs-sm fw-semibold text-uppercase text-muted">Alpa</div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="mb-2">
        <h5 class="mb-1">
            <i class="fa fa-bolt text-primary me-1"></i>
            <b>AKSI CEPAT</b>
        </h5>
        <small class="text-muted">Menu cepat untuk kebutuhan pegawai</small>
    </div>

    <div class="row">
        <div class="col-12 col-xl">
            <a class="block block-rounded block-link-shadow text-center" href="<?= base_url('pegawai/jadwal'); ?>">
                <div class="block-content block-content-full">
                    <i class="fa fa-calendar-days fa-2x text-primary mb-2"></i>
                    <div class="fw-semibold">Jadwal Saya</div>
                    <div class="fs-sm text-muted">Lihat jadwal kerja</div>
                </div>
            </a>
        </div>

        <div class="col-12 col-xl">
            <a class="block block-rounded block-link-shadow text-center" href="<?= base_url('pegawai/riwayat'); ?>">
                <div class="block-content block-content-full">
                    <i class="fa fa-clock-rotate-left fa-2x text-success mb-2"></i>
                    <div class="fw-semibold">Riwayat Presensi</div>
                    <div class="fs-sm text-muted">Cek presensi saya</div>
                </div>
            </a>
        </div>

        <div class="col-12 col-xl">
            <a class="block block-rounded block-link-shadow text-center" href="<?= base_url('pegawai/izin'); ?>">
                <div class="block-content block-content-full">
                    <i class="fa fa-file-signature fa-2x text-info mb-2"></i>
                    <div class="fw-semibold">Izin & Sakit</div>
                    <div class="fs-sm text-muted">Ajukan Izin atau sakit</div>
                </div>
            </a>
        </div>

        <div class="col-12 col-xl">
            <a class="block block-rounded block-link-shadow text-center" href="<?= base_url('pegawai/tukar'); ?>">
                <div class="block-content block-content-full">
                    <i class="fa fa-right-left fa-2x text-warning mb-2"></i>
                    <div class="fw-semibold">Tukar Jadwal</div>
                    <div class="fs-sm text-muted">Ajukan perubahan</div>
                </div>
            </a>
        </div>

        <div class="col-12 col-xl">
            <a class="block block-rounded block-link-shadow text-center" href="<?= base_url('pegawai/cuti'); ?>">
                <div class="block-content block-content-full">
                    <i class="fa fa-calendar-days fa-2x text-primary mb-2"></i>
                    <div class="fw-semibold">Cuti</div>
                    <div class="fs-sm text-muted">Ajukan Cuti</div>
                </div>
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div id="block-riwayat-presensi" class="block block-rounded">
                <div class="block-header block-header-default">
                    <h3 class="block-title">
                        <i class="fa fa-clock-rotate-left me-1"></i> Riwayat Presensi Terbaru
                    </h3>
                </div>
                <div class="block-content block-content-full">
                    <div class="table-responsive">
                        <table class="table table-vcenter table-hover">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Shift</th>
                                    <th>Jam Datang</th>
                                    <th>Jam Pulang</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="riwayat-presensi-body">
                                <?php for ($i = 0; $i < 5; $i++): ?>
                                    <tr class="riwayat-row d-none" data-index="<?= $i; ?>">
                                        <td class="riwayat-tanggal">-</td>
                                        <td class="riwayat-shift">-</td>
                                        <td class="riwayat-jam-datang">-</td>
                                        <td class="riwayat-jam-pulang">-</td>
                                        <td>
                                            <span class="badge riwayat-status-badge bg-secondary">-</span>
                                        </td>
                                    </tr>
                                <?php endfor; ?>

                                <tr id="riwayat-kosong">
                                    <td colspan="5" class="text-center text-muted">Memuat data...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection('content'); ?>