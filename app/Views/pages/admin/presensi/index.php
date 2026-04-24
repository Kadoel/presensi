<?= $this->extend('theme/body'); ?>

<?= $this->section('content'); ?>
<?php $tanggal = date('Y-m-d'); ?>

<div class="row">
    <div class="col-md-12">
        <!-- KPI DASAR -->
        <div id="block-ringkasan" class="mb-4">
            <div class="row">
                <div class="col-6 col-xl-4">
                    <a class="block block-rounded block-link-shadow text-end" href="javascript:void(0)">
                        <div class="block-content block-content-full d-sm-flex justify-content-between align-items-center">
                            <div class="d-none d-sm-block">
                                <i class="fa fa-calendar-days fa-2x opacity-25"></i>
                            </div>
                            <div>
                                <div class="fs-3 fw-semibold" id="ringkasan-total-jadwal"><?= esc($ringkasan['total_jadwal'] ?? 0); ?></div>
                                <div class="fs-sm fw-semibold text-uppercase text-muted">Total Jadwal</div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-6 col-xl-4">
                    <a class="block block-rounded block-link-shadow text-end" href="javascript:void(0)">
                        <div class="block-content block-content-full d-sm-flex justify-content-between align-items-center">
                            <div class="d-none d-sm-block">
                                <i class="fa fa-clipboard-check fa-2x opacity-25"></i>
                            </div>
                            <div>
                                <div class="fs-3 fw-semibold" id="ringkasan-total-presensi"><?= esc($ringkasan['total_presensi'] ?? 0); ?></div>
                                <div class="fs-sm fw-semibold text-uppercase text-muted">Total Presensi</div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-6 col-xl-4">
                    <a class="block block-rounded block-link-shadow text-end" href="javascript:void(0)">
                        <div class="block-content block-content-full d-sm-flex justify-content-between align-items-center">
                            <div class="d-none d-sm-block">
                                <i class="fa fa-hourglass-half fa-2x opacity-25"></i>
                            </div>
                            <div>
                                <div class="fs-3 fw-semibold" id="ringkasan-belum-presensi"><?= esc($ringkasan['belum_presensi'] ?? 0); ?></div>
                                <div class="fs-sm fw-semibold text-uppercase text-muted">Belum Presensi</div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- STATUS DATANG -->
            <div class="row">
                <div class="col-6 col-xl-2">
                    <a class="block block-rounded block-link-shadow text-end" href="javascript:void(0)">
                        <div class="block-content block-content-full d-sm-flex justify-content-between align-items-center">
                            <div class="d-none d-sm-block">
                                <i class="fa fa-user-check fa-2x opacity-25"></i>
                            </div>
                            <div>
                                <div class="fs-3 fw-semibold text-success" id="ringkasan-hadir"><?= esc($ringkasan['hadir'] ?? 0); ?></div>
                                <div class="fs-sm fw-semibold text-uppercase text-muted">Hadir</div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-6 col-xl-2">
                    <a class="block block-rounded block-link-shadow text-end" href="javascript:void(0)">
                        <div class="block-content block-content-full d-sm-flex justify-content-between align-items-center">
                            <div class="d-none d-sm-block">
                                <i class="fa fa-clock fa-2x opacity-25"></i>
                            </div>
                            <div>
                                <div class="fs-3 fw-semibold text-warning" id="ringkasan-telat"><?= esc($ringkasan['telat'] ?? 0); ?></div>
                                <div class="fs-sm fw-semibold text-uppercase text-muted">Telat</div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-6 col-xl-2">
                    <a class="block block-rounded block-link-shadow text-end" href="javascript:void(0)">
                        <div class="block-content block-content-full d-sm-flex justify-content-between align-items-center">
                            <div class="d-none d-sm-block">
                                <i class="fa fa-user-xmark fa-2x opacity-25"></i>
                            </div>
                            <div>
                                <div class="fs-3 fw-semibold text-danger" id="ringkasan-alpa"><?= esc($ringkasan['alpa'] ?? 0); ?></div>
                                <div class="fs-sm fw-semibold text-uppercase text-muted">Alpa</div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-6 col-xl-2">
                    <a class="block block-rounded block-link-shadow text-end" href="javascript:void(0)">
                        <div class="block-content block-content-full d-sm-flex justify-content-between align-items-center">
                            <div class="d-none d-sm-block">
                                <i class="fa fa-file-signature fa-2x opacity-25"></i>
                            </div>
                            <div>
                                <div class="fs-3 fw-semibold text-info" id="ringkasan-izin"><?= esc($ringkasan['izin'] ?? 0); ?></div>
                                <div class="fs-sm fw-semibold text-uppercase text-muted">Izin</div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-6 col-xl-2">
                    <a class="block block-rounded block-link-shadow text-end" href="javascript:void(0)">
                        <div class="block-content block-content-full d-sm-flex justify-content-between align-items-center">
                            <div class="d-none d-sm-block">
                                <i class="fa fa-notes-medical fa-2x opacity-25"></i>
                            </div>
                            <div>
                                <div class="fs-3 fw-semibold text-primary" id="ringkasan-sakit"><?= esc($ringkasan['sakit'] ?? 0); ?></div>
                                <div class="fs-sm fw-semibold text-uppercase text-muted">Sakit</div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-6 col-xl-2">
                    <a class="block block-rounded block-link-shadow text-end" href="javascript:void(0)">
                        <div class="block-content block-content-full d-sm-flex justify-content-between align-items-center">
                            <div class="d-none d-sm-block">
                                <i class="fa fa-umbrella-beach fa-2x opacity-25"></i>
                            </div>
                            <div>
                                <div class="fs-3 fw-semibold text-secondary" id="ringkasan-libur"><?= esc($ringkasan['libur'] ?? 0); ?></div>
                                <div class="fs-sm fw-semibold text-uppercase text-muted">Libur</div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- STATUS PULANG -->
            <div class="row">
                <div class="col-6 col-xl-4">
                    <a class="block block-rounded block-link-shadow text-end" href="javascript:void(0)">
                        <div class="block-content block-content-full d-sm-flex justify-content-between align-items-center">
                            <div class="d-none d-sm-block">
                                <i class="fa fa-door-open fa-2x opacity-25"></i>
                            </div>
                            <div>
                                <div class="fs-3 fw-semibold text-secondary" id="ringkasan-belum-pulang"><?= esc($ringkasan['belum_pulang'] ?? 0); ?></div>
                                <div class="fs-sm fw-semibold text-uppercase text-muted">Belum Pulang</div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-6 col-xl-4">
                    <a class="block block-rounded block-link-shadow text-end" href="javascript:void(0)">
                        <div class="block-content block-content-full d-sm-flex justify-content-between align-items-center">
                            <div class="d-none d-sm-block">
                                <i class="fa fa-right-from-bracket fa-2x opacity-25"></i>
                            </div>
                            <div>
                                <div class="fs-3 fw-semibold text-success" id="ringkasan-pulang"><?= esc($ringkasan['pulang'] ?? 0); ?></div>
                                <div class="fs-sm fw-semibold text-uppercase text-muted">Pulang</div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-6 col-xl-4">
                    <a class="block block-rounded block-link-shadow text-end" href="javascript:void(0)">
                        <div class="block-content block-content-full d-sm-flex justify-content-between align-items-center">
                            <div class="d-none d-sm-block">
                                <i class="fa fa-person-walking-arrow-right fa-2x opacity-25"></i>
                            </div>
                            <div>
                                <div class="fs-3 fw-semibold text-warning" id="ringkasan-pulang-cepat"><?= esc($ringkasan['pulang_cepat'] ?? 0); ?></div>
                                <div class="fs-sm fw-semibold text-uppercase text-muted">Pulang Cepat</div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <div id="block-presensi" class="block block-themed block-rounded">
            <div class="block-header">
                <h3 class="block-title text-white">
                    <i class="fa fa-calendar-check"></i> <b>DATA PRESENSI</b>
                </h3>
            </div>

            <div class="block-content">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label" for="filter-tanggal">Tanggal</label>
                        <input type="text"
                            class="js-flatpickr form-control"
                            id="filter-tanggal"
                            value="<?= $tanggal; ?>"
                            autocomplete="off">
                    </div>

                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" id="btn-sinkron-presensi" class="btn btn-danger">
                            <i class="fa fa-arrows-rotate"></i> Sinkron Presensi
                        </button>
                    </div>
                </div>
            </div>

            <div class="block-content block-content-full overflow-x-auto">
                <table id="presensi-tabel" class="table table-vcenter js-dataTable-responsive table-hover nowrap w-100">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 8%;"><b>#</b></th>
                            <th><b>ID</b></th>
                            <th><b>TANGGAL</b></th>
                            <th><b>KODE</b></th>
                            <th><b>NAMA PEGAWAI</b></th>
                            <th><b>SHIFT</b></th>
                            <th><b>JAM DATANG</b></th>
                            <th><b>STATUS DATANG</b></th>
                            <th><b>JAM PULANG</b></th>
                            <th><b>STATUS PULANG</b></th>
                            <th><b>SUMBER</b></th>
                            <th class="text-center" style="width: 8%;"><b>AKSI</b></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="modal-detail" tabindex="-1" role="dialog" aria-labelledby="modal-detail" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div id="block-content-detail" class="block block-themed block-rounded shadow-none mb-0">
                <div class="block-header">
                    <h3 class="block-title text-white">
                        <i class="fa fa-circle-info"></i> <b>DETAIL PRESENSI</b>
                    </h3>
                </div>

                <div class="block-content">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kode Pegawai</label>
                            <div id="detail-kode_pegawai" class="form-control bg-body-light">-</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Pegawai</label>
                            <div id="detail-nama_pegawai" class="form-control bg-body-light">-</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jenis Kelamin</label>
                            <div id="detail-jenis_kelamin" class="form-control bg-body-light">-</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">No. HP</label>
                            <div id="detail-no_hp" class="form-control bg-body-light">-</div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Alamat</label>
                            <div id="detail-alamat" class="form-control bg-body-light">-</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanggal</label>
                            <div id="detail-tanggal" class="form-control bg-body-light">-</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Shift</label>
                            <div id="detail-shift" class="form-control bg-body-light">-</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jam Datang</label>
                            <div id="detail-jam_datang" class="form-control bg-body-light">-</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status Datang</label>
                            <div id="detail-status_datang" class="form-control bg-body-light">-</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jam Pulang</label>
                            <div id="detail-jam_pulang" class="form-control bg-body-light">-</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status Pulang</label>
                            <div id="detail-status_pulang" class="form-control bg-body-light">-</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Menit Telat</label>
                            <div id="detail-menit_telat" class="form-control bg-body-light">-</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Menit Pulang Cepat</label>
                            <div id="detail-menit_pulang_cepat" class="form-control bg-body-light">-</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Sumber Input</label>
                            <div id="detail-is_manual" class="form-control bg-body-light">-</div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Catatan Admin</label>
                            <div id="detail-catatan_admin" class="form-control bg-body-light">-</div>
                        </div>
                    </div>
                </div>

                <div class="block-content block-content-full block-content-sm text-end border-top">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                        <i class="fa fa-times"></i> Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>