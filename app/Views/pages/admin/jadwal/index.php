<?= $this->extend('theme/body'); ?>

<?= $this->section('content'); ?>

<div class="row">
    <div class="col-12">
        <div id="block-konten-tambah" class="block block-themed block-rounded">
            <div class="block-header">
                <h3 class="block-title text-white"><i class="fa fa-calendar-plus"></i> <b>GENERATE JADWAL KERJA</b></h3>
            </div>

            <form id="form_tambah_jadwal" autocomplete="off">
                <div class="block-content block-content-full">
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <label class="form-label" for="tanggal">Tanggal <span class="text-danger">*</span></label>
                            <input type="text"
                                class="js-flatpickr form-control"
                                id="tanggal"
                                name="tanggal"
                                data-mode="multiple"
                                data-date-format="Y-m-d"
                                autocomplete="off"
                                placeholder="Pilih satu atau beberapa tanggal">
                            <div id="error-tanggal" class="invalid-feedback animated fadeIn"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="catatan">Catatan</label>
                            <input type="text" class="form-control" id="catatan" name="catatan" autocomplete="off" placeholder="Opsional">
                            <div id="error-catatan" class="invalid-feedback animated fadeIn"></div>
                        </div>
                    </div>

                    <div class="alert alert-info d-flex align-items-center" role="alert">
                        <div class="flex-shrink-0 me-3">
                            <i class="fa fa-circle-info fa-2x"></i>
                        </div>
                        <div>
                            Semua pegawai aktif wajib dipilih tepat satu kali, baik pada salah satu shift maupun pada section libur.
                            Section boleh kosong selama semua pegawai aktif sudah terjadwal di section lain.
                        </div>
                    </div>

                    <div id="error-pegawai" class="alert alert-danger d-none"></div>
                    <div id="error-shift_pegawai" class="alert alert-danger d-none"></div>

                    <div class="row">
                        <?php foreach ($shift as $item): ?>
                            <div class="col-xl-6 mb-4">
                                <div class="block block-rounded border mb-0 h-100">
                                    <div class="block-header bg-body-light">
                                        <h3 class="block-title">
                                            <i class="fa fa-clock text-primary"></i>
                                            <?= esc($item->nama_shift); ?>
                                        </h3>
                                    </div>
                                    <div class="block-content block-content-full">
                                        <select class="js-select2 form-select section-pegawai"
                                            name="shift_pegawai[<?= esc($item->id); ?>][]"
                                            id="shift_pegawai_<?= esc($item->id); ?>"
                                            multiple
                                            style="width: 100%;"
                                            data-placeholder="-- Pilih Pegawai --">
                                            <?php foreach ($pegawai as $p): ?>
                                                <option value="<?= esc($p->id); ?>">
                                                    <?= esc($p->nama_pegawai); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <div class="col-xl-6 mb-4">
                            <div class="block block-rounded border mb-0 h-100">
                                <div class="block-header bg-body-light">
                                    <h3 class="block-title">
                                        <i class="fa fa-umbrella-beach text-danger"></i> Libur
                                    </h3>
                                </div>
                                <div class="block-content block-content-full">
                                    <select class="js-select2 form-select section-pegawai"
                                        name="libur_pegawai[]"
                                        id="libur_pegawai"
                                        multiple
                                        style="width: 100%;"
                                        data-placeholder="-- Pilih Pegawai Libur --">
                                        <?php foreach ($pegawai as $p): ?>
                                            <option value="<?= esc($p->id); ?>">
                                                <?= esc($p->nama_pegawai); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="block-content block-content-full block-content-sm bg-body-light fs-sm text-end">
                    <button type="button" id="reset-generate" class="btn btn-sm btn-secondary">
                        <i class="fa fa-rotate-left"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-sm btn-primary text-white">
                        <i class="far fa-paper-plane"></i> Generate Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-12">
        <div id="block-tabel" class="block block-themed block-rounded">
            <div class="block-header">
                <h3 class="block-title text-white"><i class="fa fa-file-lines"></i> <b>DATA JADWAL KERJA</b></h3>
            </div>

            <div class="block-content block-content-full">
                <div class="table-responsive" style="overflow-x: auto;">
                    <table id="jadwal-kerja-tabel" class="table table-vcenter table-hover nowrap" style="width:100%;">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 8%;"><b>#</b></th>
                                <th class="text-center"><b>ID</b></th>
                                <th class="text-center"><b>BULAN</b></th>
                                <th class="text-center"><b>PEGAWAI</b></th>
                                <th class="text-center"><b>TANGGAL</b></th>
                                <th class="text-center"><b>STATUS</b></th>
                                <th class="text-center"><b>SHIFT</b></th>
                                <th class="text-center"><b>SUMBER</b></th>
                                <th class="text-center"><b>CATATAN</b></th>
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

<div class="modal" id="modal-ubah" tabindex="-1" role="dialog" aria-labelledby="modal-ubah" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div id="block-content-ubah" class="block block-themed block-rounded shadow-none mb-0">
                <div class="block-header">
                    <h3 class="block-title text-white"><i class="fa fa-pen-to-square"></i> <b>UBAH JADWAL KERJA</b></h3>
                </div>

                <form id="form_edit_jadwal" autocomplete="off">
                    <div class="block-content">
                        <input type="hidden" class="form-control" id="edit-id" name="edit-id">

                        <div class="mb-4">
                            <label class="form-label" for="edit-pegawai_id">Pegawai <span class="text-danger">*</span></label>
                            <select class="js-select2 form-select" id="edit-pegawai_id" name="edit-pegawai_id" style="width: 100%;" data-placeholder="-- Pilih Pegawai --">
                                <option></option>
                                <?php foreach ($pegawai as $item) : ?>
                                    <option value="<?= esc($item->id); ?>">
                                        <?= esc($item->nama_pegawai); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div id="error-edit-pegawai_id" class="invalid-feedback animated fadeIn"></div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="edit-tanggal">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" class="js-flatpickr form-control" id="edit-tanggal" name="edit-tanggal" autocomplete="off">
                            <div id="error-edit-tanggal" class="invalid-feedback animated fadeIn"></div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="edit-status_hari">Status Hari <span class="text-danger">*</span></label>
                            <select class="js-select2 form-select" id="edit-status_hari" name="edit-status_hari" style="width: 100%;" data-placeholder="-- Pilih Status Hari --">
                                <option></option>
                                <option value="kerja">Kerja</option>
                                <option value="libur">Libur</option>
                            </select>
                            <div id="error-edit-status_hari" class="invalid-feedback animated fadeIn"></div>
                        </div>

                        <div class="mb-4" id="wrap-edit-shift_id">
                            <label class="form-label" for="edit-shift_id">Shift <span class="text-danger">*</span></label>
                            <select class="js-select2 form-select" id="edit-shift_id" name="edit-shift_id" style="width: 100%;" data-placeholder="-- Pilih Shift --">
                                <option></option>
                                <?php foreach ($shift as $item) : ?>
                                    <option value="<?= esc($item->id); ?>">
                                        <?= esc($item->nama_shift); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div id="error-edit-shift_id" class="invalid-feedback animated fadeIn"></div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="edit-catatan">Catatan</label>
                            <input type="text" class="form-control" id="edit-catatan" name="edit-catatan" autocomplete="off">
                            <div id="error-edit-catatan" class="invalid-feedback animated fadeIn"></div>
                        </div>
                    </div>

                    <div class="block-content block-content-full block-content-sm text-end border-top">
                        <button type="button" id="tutup-modal" class="btn btn-danger">
                            <i class="fa fa-times"></i> Batal
                        </button>
                        <button type="submit" id="update-data" class="btn btn-primary text-white">
                            <i class="far fa-paper-plane"></i> Ubah
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection('content'); ?>