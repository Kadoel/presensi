<?= $this->extend('theme/admin/body'); ?>

<?= $this->section('content'); ?>
<?php $tanggal = date('Y-m-d'); ?>

<div id="sticky-belum-sinkron" class="alert alert-warning d-none shadow-sm"
    style="position: sticky; top: 0; z-index: 1020; border-radius: 0;">

    <div class="container-fluid d-flex align-items-start justify-content-between">

        <div class="d-flex">
            <div class="me-2">
                <i class="fa fa-triangle-exclamation"></i>
            </div>

            <div>
                <b>Presensi Belum Sinkron</b>
                <div class="mt-1">
                    <ul id="list-belum-sinkron" class="mb-0 ps-3"></ul>
                </div>
            </div>
        </div>

        <button class="btn-close ms-2" onclick="$('#sticky-belum-sinkron').addClass('d-none')"></button>
    </div>
</div>
<div id="block-ringkasan" class="mb-4">
    <div class="row">
        <?php
        $ringkasanUtamaCards = [
            ['id' => 'total-jadwal', 'key' => 'total_jadwal', 'label' => 'Total Jadwal', 'icon' => 'fa-calendar-days', 'class' => 'text-primary'],
            ['id' => 'total-presensi', 'key' => 'total_presensi', 'label' => 'Total Record Presensi', 'icon' => 'fa-clipboard-check', 'class' => 'text-success'],
            ['id' => 'belum-sinkron', 'key' => 'belum_sinkron', 'label' => 'Belum Sinkron', 'icon' => 'fa-clock-rotate-left', 'class' => 'text-warning'],
        ];
        ?>

        <?php foreach ($ringkasanUtamaCards as $card): ?>
            <div class="col-12 col-xl-4">
                <a class="block block-rounded block-link-shadow text-end" href="javascript:void(0)">
                    <div class="block-content block-content-full d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fa <?= esc($card['icon']); ?> fa-2x <?= esc($card['class']); ?> opacity-50"></i>
                        </div>
                        <div class="text-end">
                            <div class="fs-3 fw-semibold <?= esc($card['class']); ?>" id="ringkasan-<?= esc($card['id']); ?>">
                                0
                            </div>
                            <div class="fs-sm fw-semibold text-uppercase text-muted"><?= esc($card['label']); ?></div>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row">
        <?php
        $hasilPresensiCards = [
            ['id' => 'hadir', 'key' => 'hadir', 'label' => 'Hadir', 'icon' => 'fa-user-check', 'class' => 'text-success'],
            ['id' => 'alpa', 'key' => 'alpa', 'label' => 'Alpa', 'icon' => 'fa-user-xmark', 'class' => 'text-danger'],
            ['id' => 'izin', 'key' => 'izin', 'label' => 'Izin', 'icon' => 'fa-file-signature', 'class' => 'text-info'],
            ['id' => 'sakit', 'key' => 'sakit', 'label' => 'Sakit', 'icon' => 'fa-notes-medical', 'class' => 'text-primary'],
            ['id' => 'libur', 'key' => 'libur', 'label' => 'Libur', 'icon' => 'fa-umbrella-beach', 'class' => 'text-warning'],
        ];
        ?>

        <?php foreach ($hasilPresensiCards as $card): ?>
            <div class="col-12 col-xl">
                <a class="block block-rounded block-link-shadow text-end" href="javascript:void(0)">
                    <div class="block-content block-content-full d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fa <?= esc($card['icon']); ?> fa-2x <?= esc($card['class']); ?> opacity-50"></i>
                        </div>
                        <div class="text-end">
                            <div class="fs-3 fw-semibold <?= esc($card['class']); ?>" id="ringkasan-<?= esc($card['id']); ?>">
                                0
                            </div>
                            <div class="fs-sm fw-semibold text-uppercase text-muted"><?= esc($card['label']); ?></div>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row">
        <?php
        $detailScanCards = [
            ['id' => 'tepat-waktu-datang', 'key' => 'tepat_waktu_datang', 'label' => 'Datang Tepat Waktu', 'icon' => 'fa-person-circle-check', 'class' => 'text-success'],
            ['id' => 'telat', 'key' => 'telat', 'label' => 'Datang Telat', 'icon' => 'fa-clock', 'class' => 'text-warning'],
            ['id' => 'tepat-waktu-pulang', 'key' => 'tepat_waktu_pulang', 'label' => 'Pulang Tepat Waktu', 'icon' => 'fa-right-from-bracket', 'class' => 'text-success'],
            ['id' => 'pulang-cepat', 'key' => 'pulang_cepat', 'label' => 'Pulang Cepat', 'icon' => 'fa-person-walking-arrow-right', 'class' => 'text-warning'],
        ];
        ?>

        <?php foreach ($detailScanCards as $card): ?>
            <div class="col-12 col-xl-3">
                <a class="block block-rounded block-link-shadow text-end" href="javascript:void(0)">
                    <div class="block-content block-content-full d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fa <?= esc($card['icon']); ?> fa-2x <?= esc($card['class']); ?> opacity-50"></i>
                        </div>
                        <div class="text-end">
                            <div class="fs-3 fw-semibold <?= esc($card['class']); ?>" id="ringkasan-<?= esc($card['id']); ?>">
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

<div id="block-presensi" class="block block-themed block-rounded">
    <div class="block-header">
        <h3 class="block-title text-white"><i class="fa fa-calendar-check"></i> <b>DATA PRESENSI</b></h3>
    </div>

    <div class="block-content">
        <div class="row mb-4 justify-content-center align-items-center">
            <div class="col-auto" style="min-width: 250px;">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fa fa-calendar"></i>
                    </span>
                    <input type="text" class="form-control" id="filter-tanggal"
                        value="<?= $tanggal; ?>">
                </div>
            </div>

            <div class="col-auto">
                <div class="d-flex gap-2">
                    <button id="btn-lupa-presensi" class="btn btn-primary text-white">
                        <i class="fa fa-plus"></i> Lupa Presensi
                    </button>

                    <button id="btn-sinkron-presensi" class="btn btn-danger">
                        <i class="fa fa-arrows-rotate"></i> Sinkron
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="block-content block-content-full overflow-x-auto">
        <table id="presensi-tabel" class="table table-vcenter table-hover nowrap w-100">
            <thead>
                <tr>
                    <th class="text-center"><b>#</b></th>
                    <th><b>ID</b></th>
                    <th><b>TANGGAL</b></th>
                    <th><b>KODE</b></th>
                    <th><b>NAMA PEGAWAI</b></th>
                    <th><b>SHIFT</b></th>
                    <th><b>JAM DATANG</b></th>
                    <th><b>STATUS DATANG</b></th>
                    <th><b>JAM PULANG</b></th>
                    <th><b>STATUS PULANG</b></th>
                    <th><b>HASIL PRESENSI</b></th>
                    <th><b>SUMBER</b></th>
                    <th class="text-center"><b>AKSI</b></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<div class="block block-themed block-rounded mt-4">
    <div class="block-header">
        <h3 class="block-title text-white">
            <i class="fa fa-chart-column"></i> <b>REKAP PRESENSI BULANAN</b>
        </h3>
    </div>

    <div class="block-content">
        <div class="row mb-4 justify-content-center">
            <div class="col-auto" style="min-width: 250px;">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fa fa-calendar"></i>
                    </span>
                    <input type="month" id="filter-bulan" class="form-control" value="<?= date('Y-m'); ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="block-content block-content-full overflow-x-auto">
        <table id="rekap-tabel" class="table table-vcenter table-hover nowrap w-100">
            <thead>
                <tr>
                    <th class="text-center"><b>#</b></th>
                    <th><b>NAMA PEGAWAI</b></th>
                    <th class="text-center"><b>HADIR</b></th>
                    <th class="text-center"><b>IZIN</b></th>
                    <th class="text-center"><b>SAKIT</b></th>
                    <th class="text-center"><b>LIBUR</b></th>
                    <th class="text-center"><b>ALPA</b></th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<div class="modal" id="modal-lupa-presensi" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div id="block-content-lupa" class="block block-themed block-rounded shadow-none mb-0">
                <div class="block-header">
                    <h3 class="block-title text-white"><i class="fa fa-plus"></i> <b>LUPA PRESENSI</b></h3>
                </div>
                <form id="form-lupa-presensi" autocomplete="off">
                    <div class="block-content">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label" for="pegawai_id">Pegawai <span class="text-danger">*</span></label>
                                <select class="js-select2 form-select" id="pegawai_id" name="pegawai_id" style="width:100%" data-placeholder="-- Pilih Pegawai --">
                                    <option></option>
                                    <?php if (! empty($pegawais ?? [])): ?>
                                        <?php foreach ($pegawais as $pegawai): ?>
                                            <option value="<?= esc($pegawai->id); ?>"><?= esc($pegawai->nama_pegawai); ?></option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="" disabled>Data pegawai aktif tidak tersedia</option>
                                    <?php endif; ?>
                                </select>
                                <div id="error-pegawai_id" class="invalid-feedback animated fadeIn"></div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label" for="tanggal">Tanggal <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="tanggal" name="tanggal" value="<?= $tanggal; ?>">
                                <div id="error-tanggal" class="invalid-feedback animated fadeIn"></div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label" for="jam_datang">Jam Datang <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="jam_datang" name="jam_datang">
                                <div id="error-jam_datang" class="invalid-feedback animated fadeIn"></div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label" for="jam_pulang">Jam Pulang</label>
                                <input type="time" class="form-control" id="jam_pulang" name="jam_pulang">
                                <div id="error-jam_pulang" class="invalid-feedback animated fadeIn"></div>
                            </div>
                            <div class="col-md-12 mb-4">
                                <label class="form-label" for="catatan_admin">Catatan Admin <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="catatan_admin" name="catatan_admin" rows="3" placeholder="Contoh: Pegawai lupa scan karena kios bermasalah"></textarea>
                                <div id="error-catatan_admin" class="invalid-feedback animated fadeIn"></div>
                            </div>
                        </div>
                    </div>
                    <div class="block-content block-content-full block-content-sm text-end border-top">
                        <button type="button" class="btn btn-danger" id="tutup-modal-lupa"><i class="fa fa-times"></i> Batal</button>
                        <button type="submit" id="simpan-lupa" class="btn btn-primary text-white"><i class="far fa-paper-plane"></i> Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="modal-edit-lupa" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div id="block-content-edit-lupa" class="block block-themed block-rounded shadow-none mb-0">
                <div class="block-header">
                    <h3 class="block-title text-white"><i class="fa fa-edit"></i> <b>EDIT LUPA PRESENSI</b></h3>
                </div>
                <form id="form-edit-lupa" autocomplete="off">
                    <div class="block-content">
                        <input type="hidden" id="edit-id" name="edit-id">
                        <div class="row">
                            <div class="col-md-6 mb-4"><label class="form-label">Pegawai</label><input type="text" class="form-control" id="edit-pegawai" readonly></div>
                            <div class="col-md-6 mb-4"><label class="form-label">Tanggal</label><input type="text" class="form-control" id="edit-tanggal" readonly></div>
                            <div class="col-md-6 mb-4"><label class="form-label" for="edit-jam_datang">Jam Datang <span class="text-danger">*</span></label><input type="time" class="form-control" id="edit-jam_datang" name="edit-jam_datang">
                                <div id="error-edit-jam_datang" class="invalid-feedback animated fadeIn"></div>
                            </div>
                            <div class="col-md-6 mb-4"><label class="form-label" for="edit-jam_pulang">Jam Pulang</label><input type="time" class="form-control" id="edit-jam_pulang" name="edit-jam_pulang">
                                <div id="error-edit-jam_pulang" class="invalid-feedback animated fadeIn"></div>
                            </div>
                            <div class="col-md-12 mb-4"><label class="form-label" for="edit-catatan_admin">Catatan Admin <span class="text-danger">*</span></label><textarea class="form-control" id="edit-catatan_admin" name="edit-catatan_admin" rows="3"></textarea>
                                <div id="error-edit-catatan_admin" class="invalid-feedback animated fadeIn"></div>
                            </div>
                        </div>
                    </div>
                    <div class="block-content block-content-full block-content-sm text-end border-top">
                        <button type="button" class="btn btn-danger" id="tutup-modal-edit-lupa"><i class="fa fa-times"></i> Batal</button>
                        <button type="submit" id="update-lupa" class="btn btn-primary text-white"><i class="far fa-paper-plane"></i> Ubah</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="modal-detail" tabindex="-1" role="dialog" aria-labelledby="modal-detail" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div id="block-content-detail" class="block block-themed block-rounded shadow-none mb-0">
                <div class="block-header">
                    <h3 class="block-title text-white"><i class="fa fa-circle-info"></i> <b>DETAIL PRESENSI</b></h3>
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
                            <label class="form-label fw-semibold">Hasil Presensi</label>
                            <div id="detail-hasil_presensi" class="form-control bg-body-light">-</div>
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
                            <div id="detail-sumber_presensi" class="form-control bg-body-light">-</div>
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