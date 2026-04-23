<?= $this->extend('theme/body'); ?>

<?= $this->section('content'); ?>
<div class="row">
    <div class="col-md-12">
        <div id="block-timeline" class="block block-themed block-rounded">
            <div class="block-header">
                <h3 class="block-title text-white">
                    <i class="fa fa-timeline"></i> <b>AKTIVITAS TERBARU</b>
                </h3>
            </div>
            <div class="block-content">
                <?php if (! empty($timelineLogs)) : ?>
                    <ul class="timeline timeline-modern pull-t">
                        <?php foreach ($timelineLogs as $item) : ?>
                            <li class="timeline-event">
                                <div class="timeline-event-time">
                                    <?= esc($item->created_at_human); ?>
                                </div>

                                <i class="timeline-event-icon <?= esc($item->icon); ?> <?= esc($item->bg_class); ?>"></i>

                                <div class="timeline-event-block">
                                    <p class="fw-semibold mb-1">
                                        <?= esc(strtoupper($item->action)); ?>
                                        <?php if (! empty($item->username)) : ?>
                                            - <?= esc($item->username); ?>
                                        <?php else : ?>
                                            - System
                                        <?php endif; ?>
                                    </p>

                                    <p class="mb-1">
                                        <?= esc($item->description ?? '-'); ?>
                                    </p>

                                    <p class="fs-sm text-muted mb-0">
                                        <?= tanggal_indonesia_jam(date('Y-m-d H:i:s', strtotime($item->created_at))); ?>
                                        | Tabel: <?= esc($item->table_name ?? '-'); ?>
                                        <?php if (! empty($item->row_id)) : ?>
                                            | Row ID: <?= (int) $item->row_id; ?>
                                        <?php endif; ?>
                                        <?php if (! empty($item->ip_address)) : ?>
                                            | IP: <?= esc($item->ip_address); ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <div class="text-center text-muted py-4">
                        Belum ada data aktivitas.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div id="block-tabel" class="block block-themed block-rounded">
            <div class="block-header">
                <h3 class="block-title text-white">
                    <i class="fa fa-clock-rotate-left"></i> <b>RIWAYAT AKTIVITAS USER</b>
                </h3>
            </div>
            <div class="block-content block-content-full overflow-x-auto">
                <div class="row">
                    <div class="row align-items-end">
                        <div class="col-md-2">
                            <div class="mb-4">
                                <label class="form-label" for="filter_user_id">User</label>
                                <select class="js-select2 form-select" id="filter_user_id" data-placeholder="-- Semua User --">
                                    <option></option>
                                    <?php foreach ($filterUsers as $item) : ?>
                                        <option value="<?= esc($item->user_id); ?>">
                                            <?= esc($item->username ?? ('User ID ' . $item->user_id)); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="mb-4">
                                <label class="form-label" for="filter_action">Action</label>
                                <select class="js-select2 form-select" id="filter_action" data-placeholder="-- Semua Action --">
                                    <option></option>
                                    <?php foreach ($filterActions as $item) : ?>
                                        <option value="<?= esc($item->action); ?>">
                                            <?= esc(strtoupper($item->action)); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="mb-4">
                                <label class="form-label" for="filter_table_name">Tabel</label>
                                <select class="js-select2 form-select" id="filter_table_name" data-placeholder="-- Semua Tabel --">
                                    <option></option>
                                    <?php foreach ($filterTables as $item) : ?>
                                        <option value="<?= esc($item->table_name); ?>">
                                            <?= esc($item->table_name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-4">
                                <label class="form-label" for="filter_tanggal_range">Rentang Tanggal</label>
                                <input type="text" class="js-flatpickr form-control" id="filter_tanggal_range" placeholder="Pilih rentang tanggal" autocomplete="off">
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="mb-4">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="button" id="btn-reset-filter" class="btn btn-danger">
                                        <i class="fa fa-rotate-left"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <table id="audit-log-tabel" class="table table-vcenter js-dataTable-responsive table-hover nowrap">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 8%;"><b>#</b></th>
                                    <th class="text-center"><b>ID</b></th>
                                    <th class="text-center"><b>USER</b></th>
                                    <th class="text-center"><b>AKSI</b></th>
                                    <th class="text-center"><b>TABEL</b></th>
                                    <th class="text-center"><b>ROW ID</b></th>
                                    <th class="text-center"><b>DESKRIPSI</b></th>
                                    <th class="text-center"><b>IP ADDRESS</b></th>
                                    <th class="text-center"><b>WAKTU</b></th>
                                    <th class="text-center" style="width: 8%;"><b>AKSI</b></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
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
                        <i class="fa fa-circle-info"></i> <b>DETAIL AUDIT LOG</b>
                    </h3>
                </div>
                <div class="block-content">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <tr>
                                <th width="25%">ID</th>
                                <td id="detail-id"></td>
                            </tr>
                            <tr>
                                <th>User</th>
                                <td id="detail-user"></td>
                            </tr>
                            <tr>
                                <th>Aksi</th>
                                <td id="detail-action"></td>
                            </tr>
                            <tr>
                                <th>Tabel</th>
                                <td id="detail-table_name"></td>
                            </tr>
                            <tr>
                                <th>Row ID</th>
                                <td id="detail-row_id"></td>
                            </tr>
                            <tr>
                                <th>Deskripsi</th>
                                <td id="detail-description"></td>
                            </tr>
                            <tr>
                                <th>IP Address</th>
                                <td id="detail-ip_address"></td>
                            </tr>
                            <tr>
                                <th>User Agent</th>
                                <td id="detail-user_agent" style="word-break: break-word;"></td>
                            </tr>
                            <tr>
                                <th>Created At</th>
                                <td id="detail-created_at"></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="block-content block-content-full block-content-sm text-end border-top">
                    <button type="button" id="tutup-modal" class="btn btn-danger">
                        <i class="fa fa-times opacity-50 me-1"></i> Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection('content'); ?>