<?= $this->extend('theme/pegawai/body'); ?>

<?= $this->section('content'); ?>
<?php $minTanggal = date('Y-m-d'); ?>

<div class="row">
    <div class="col-md-4">
        <div id="block-konten-tambah" class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">
                    <i class="fa fa-file-medical"></i> <b>TAMBAH PENGAJUAN</b>
                </h3>
            </div>

            <form id="form_tambah_pengajuan" autocomplete="off" enctype="multipart/form-data">
                <div class="block-content block-content-full">
                    <div class="mb-4">
                        <label class="form-label" for="jenis">Jenis <span class="text-danger">*</span></label>
                        <select class="js-select2 form-select" id="jenis" name="jenis" style="width: 100%;" data-placeholder="-- Pilih Jenis --">
                            <option></option>
                            <option value="izin">Izin</option>
                            <option value="sakit">Sakit</option>
                        </select>
                        <div id="error-jenis" class="invalid-feedback animated fadeIn"></div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="tanggal_mulai">Tanggal Mulai <span class="text-danger">*</span></label>
                        <input type="date" class="js-flatpickr form-control" id="tanggal_mulai" name="tanggal_mulai" min="<?= $minTanggal; ?>" autocomplete="off">
                        <div id="error-tanggal_mulai" class="invalid-feedback animated fadeIn"></div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="tanggal_selesai">Tanggal Selesai <span class="text-danger">*</span></label>
                        <input type="date" class="js-flatpickr form-control" id="tanggal_selesai" name="tanggal_selesai" min="<?= $minTanggal; ?>" autocomplete="off">
                        <div id="error-tanggal_selesai" class="invalid-feedback animated fadeIn"></div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="alasan">Alasan <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="alasan" name="alasan" rows="3"></textarea>
                        <div id="error-alasan" class="invalid-feedback animated fadeIn"></div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="lampiran">Lampiran PDF</label>
                        <input type="file" class="form-control" id="lampiran" name="lampiran" accept=".pdf,application/pdf">
                        <div id="error-lampiran" class="invalid-feedback animated fadeIn"></div>
                    </div>
                </div>

                <div class="block-content block-content-full block-content-sm bg-body-light fs-sm text-end">
                    <button type="submit" class="btn btn-sm btn-primary text-white">
                        <i class="far fa-paper-plane"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-8">
        <div id="block-tabel" class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">
                    <i class="fa fa-file-lines"></i> <b>DATA PENGAJUAN SAYA</b>
                </h3>
            </div>

            <div class="block-content block-content-full">
                <div class="table-responsive" style="overflow-x: auto;">
                    <table id="pengajuan-izin-tabel" class="table table-vcenter table-hover nowrap" style="width:100%;">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 8%;"><b>#</b></th>
                                <th class="text-center"><b>ID</b></th>
                                <th class="text-center"><b>JENIS</b></th>
                                <th class="text-center"><b>MULAI</b></th>
                                <th class="text-center"><b>SELESAI</b></th>
                                <th class="text-center"><b>ALASAN</b></th>
                                <th class="text-center"><b>LAMPIRAN</b></th>
                                <th class="text-center"><b>STATUS</b></th>
                                <th class="text-center" style="width: 12%;"><b>AKSI</b></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="modal-ubah" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div id="block-content-ubah" class="block block-rounded shadow-none mb-0">
                <div class="block-header block-header-default">
                    <h3 class="block-title">
                        <i class="fa fa-pen-to-square"></i> <b>UBAH PENGAJUAN</b>
                    </h3>
                </div>

                <form id="form_edit_pengajuan" autocomplete="off" enctype="multipart/form-data">
                    <div class="block-content">
                        <input type="hidden" id="edit-id" name="edit-id">

                        <div class="mb-4">
                            <label class="form-label" for="edit-jenis">Jenis <span class="text-danger">*</span></label>
                            <select class="js-select2 form-select" id="edit-jenis" name="edit-jenis" style="width: 100%;" data-placeholder="-- Pilih Jenis --">
                                <option></option>
                                <option value="izin">Izin</option>
                                <option value="sakit">Sakit</option>
                            </select>
                            <div id="error-edit-jenis" class="invalid-feedback animated fadeIn"></div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="edit-tanggal_mulai">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" class="js-flatpickr form-control" id="edit-tanggal_mulai" name="edit-tanggal_mulai" min="<?= $minTanggal; ?>" autocomplete="off">
                            <div id="error-edit-tanggal_mulai" class="invalid-feedback animated fadeIn"></div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="edit-tanggal_selesai">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" class="js-flatpickr form-control" id="edit-tanggal_selesai" name="edit-tanggal_selesai" min="<?= $minTanggal; ?>" autocomplete="off">
                            <div id="error-edit-tanggal_selesai" class="invalid-feedback animated fadeIn"></div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="edit-alasan">Alasan <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="edit-alasan" name="edit-alasan" rows="3"></textarea>
                            <div id="error-edit-alasan" class="invalid-feedback animated fadeIn"></div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="edit-lampiran">Lampiran PDF</label>
                            <input type="file" class="form-control" id="edit-lampiran" name="edit-lampiran" accept=".pdf,application/pdf">
                            <div id="error-edit-lampiran" class="invalid-feedback animated fadeIn"></div>
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