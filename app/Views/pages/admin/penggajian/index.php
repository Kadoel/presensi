<?= $this->extend('theme/admin/body'); ?>

<?= $this->section('content'); ?>
<?php $bulan = date('Y-m'); ?>

<div id="block-ringkasan-penggajian" class="mb-4">
    <div class="row">
        <?php
        $cards = [
            ['id' => 'total-data', 'label' => 'Total Data', 'icon' => 'fa-users', 'class' => 'text-primary'],
            ['id' => 'total-draft', 'label' => 'Draft', 'icon' => 'fa-file-lines', 'class' => 'text-warning'],
            ['id' => 'total-final', 'label' => 'Final', 'icon' => 'fa-circle-check', 'class' => 'text-success'],
            ['id' => 'total-gaji-bersih', 'label' => 'Total Gaji Bersih', 'icon' => 'fa-money-bill-wave', 'class' => 'text-success'],
        ];
        ?>

        <?php foreach ($cards as $card): ?>
            <div class="col-12 col-xl-3">
                <a class="block block-rounded block-link-shadow text-end" href="javascript:void(0)">
                    <div class="block-content block-content-full d-flex justify-content-between align-items-center">
                        <div><i class="fa <?= esc($card['icon']); ?> fa-2x <?= esc($card['class']); ?> opacity-50"></i></div>
                        <div class="text-end">
                            <div class="fs-3 fw-semibold <?= esc($card['class']); ?>" id="ringkasan-<?= esc($card['id']); ?>">0</div>
                            <div class="fs-sm fw-semibold text-uppercase text-muted"><?= esc($card['label']); ?></div>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div id="block-penggajian" class="block block-themed block-rounded">
    <div class="block-header">
        <h3 class="block-title text-white"><i class="fa fa-money-bill-wave"></i> <b>DATA PENGGAJIAN</b></h3>
    </div>

    <div class="block-content">
        <div class="row mb-4 justify-content-center align-items-center">
            <div class="col-auto" style="min-width: 250px;">
                <div class="input-group">
                    <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                    <input type="text" class="form-control" id="filter-bulan" value="<?= $bulan; ?>" autocomplete="off">
                </div>
            </div>
            <div class="col-auto">
                <div class="d-flex gap-2">
                    <button id="btn-generate" class="btn btn-primary text-white"><i class="fa fa-gears"></i> Generate</button>
                    <button id="btn-finalkan" class="btn btn-success text-white d-none"><i class="fa fa-lock"></i> Finalkan</button>
                    <button type="button" class="btn btn-success d-none" id="btn-export">
                        <i class="fa fa-file-excel"></i> Export
                    </button>
                    <button type="button" class="btn btn-primary d-none" id="btn-bulk-slip">
                        <i class="fa fa-file-zipper"></i> Bulk Slip
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="block-content block-content-full overflow-x-auto">
        <table id="penggajian-tabel" class="table table-vcenter table-hover nowrap w-100">
            <thead>
                <tr>
                    <th class="text-center"><b>#</b></th>
                    <th><b>ID</b></th>
                    <th><b>KODE</b></th>
                    <th><b>NAMA PEGAWAI</b></th>
                    <th><b>JABATAN</b></th>
                    <th class="text-center"><b>HADIR</b></th>
                    <th class="text-center"><b>IZIN</b></th>
                    <th class="text-center"><b>SAKIT</b></th>
                    <th class="text-center"><b>CUTI</b></th>
                    <th class="text-center"><b>ALPA</b></th>
                    <th class="text-end"><b>GAJI POKOK</b></th>
                    <th class="text-end"><b>TUNJANGAN</b></th>
                    <th class="text-end"><b>POTONGAN</b></th>
                    <th class="text-end"><b>GAJI BERSIH</b></th>
                    <th class="text-center"><b>STATUS</b></th>
                    <th class="text-center"><b>AKSI</b></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<div class="modal" id="modal-detail" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div id="block-content-detail" class="block block-themed block-rounded shadow-none mb-0">
                <div class="block-header">
                    <h3 class="block-title text-white">
                        <i class="fa fa-circle-info"></i>
                        <b>DETAIL PENGGAJIAN</b>
                        <span id="detail-status-header" class="badge bg-warning text-white ms-2">Draft</span>
                    </h3>
                </div>
                <div class="block-content">
                    <div class="row g-3">
                        <?php
                        $items = [
                            ['detail-kode_pegawai', 'Kode Pegawai'],
                            ['detail-nama_pegawai', 'Nama Pegawai'],
                            ['detail-nama_jabatan', 'Jabatan'],
                            ['detail-bulan', 'Bulan'],
                            ['detail-total_hadir', 'Hadir'],
                            ['detail-total_izin', 'Izin'],
                            ['detail-total_sakit', 'Sakit'],
                            ['detail-total_libur', 'Libur'],
                            ['detail-total_cuti', 'Cuti'],
                            ['detail-total_alpa', 'Alpa'],
                            ['detail-total_menit_telat', 'Menit Telat'],
                            ['detail-total_menit_pulang_cepat', 'Menit Pulang Cepat'],
                            ['detail-gaji_pokok', 'Gaji Pokok'],
                            ['detail-tunjangan', 'Tunjangan'],
                            ['detail-gaji_kotor', 'Gaji Kotor'],
                            ['detail-potongan_telat', 'Potongan Telat'],
                            ['detail-potongan_pulang_cepat', 'Potongan Pulang Cepat'],
                            ['detail-potongan_alpa', 'Potongan Alpa'],
                            ['detail-total_potongan', 'Total Potongan'],
                            ['detail-gaji_bersih', 'Gaji Bersih'],
                        ];
                        ?>
                        <?php foreach ($items as $item): ?>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold"><?= esc($item[1]); ?></label>
                                <div class="form-control bg-body-light" id="<?= esc($item[0]); ?>">-</div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="block-content block-content-full block-content-sm text-end border-top">
                    <button type="button" class="btn btn-danger" id="tutup-modal-detail"><i class="fa fa-times"></i> Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection('content'); ?>