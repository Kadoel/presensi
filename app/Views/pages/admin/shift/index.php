<?= $this->extend('theme/admin/body'); ?>

<?= $this->section('content'); ?>

<div class="row">
    <div class="col-md-4">
        <div id="block-konten-tambah" class="block block-themed block-rounded">
            <div class="block-header">
                <h3 class="block-title text-white"><i class="fa fa-pen-to-square"></i> <b>TAMBAH</b></h3>
            </div>
            <form id="form_tambah_shift" autocomplete="off">
                <div class="block-content block-content-full">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-4">
                                <label class="form-label" for="nama_shift">Nama Shift <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama_shift" name="nama_shift" autocomplete="off">
                                <div id="error-nama_shift" class="invalid-feedback animated fadeIn"></div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label" for="jam_masuk">Jam Masuk <span class="text-danger">*</span></label>
                                <input type="text"
                                    id="jam_masuk"
                                    name="jam_masuk"
                                    class="js-flatpickr form-control"
                                    data-enable-time="true"
                                    data-no-calendar="true"
                                    data-date-format="H:i"
                                    data-time_24hr="true"
                                    autocomplete="off">
                                <div id="error-jam_masuk" class="invalid-feedback animated fadeIn"></div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label" for="batas_mulai_datang">Batas Mulai Datang <span class="text-danger">*</span></label>
                                <input type="text"
                                    id="batas_mulai_datang"
                                    name="batas_mulai_datang"
                                    class="js-flatpickr form-control"
                                    data-enable-time="true"
                                    data-no-calendar="true"
                                    data-date-format="H:i"
                                    data-time_24hr="true"
                                    autocomplete="off">
                                <div id="error-batas_mulai_datang" class="invalid-feedback animated fadeIn"></div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label" for="batas_akhir_datang">Batas Akhir Datang <span class="text-danger">*</span></label>
                                <input type="text"
                                    id="batas_akhir_datang"
                                    name="batas_akhir_datang"
                                    class="js-flatpickr form-control"
                                    data-enable-time="true"
                                    data-no-calendar="true"
                                    data-date-format="H:i"
                                    data-time_24hr="true"
                                    autocomplete="off">
                                <div id="error-batas_akhir_datang" class="invalid-feedback animated fadeIn"></div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label" for="toleransi_telat_menit">Toleransi Telat <small><code>(Menit)</code></small> <span class="text-danger">*</span></label>
                                <input type="number"
                                    class="form-control"
                                    id="toleransi_telat_menit"
                                    name="toleransi_telat_menit"
                                    min="0"
                                    max="60"
                                    step="1"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                <div id="error-toleransi_telat_menit" class="invalid-feedback animated fadeIn"></div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label" for="jam_pulang">Jam Pulang <span class="text-danger">*</span></label>
                                <input type="text"
                                    id="jam_pulang"
                                    name="jam_pulang"
                                    class="js-flatpickr form-control"
                                    data-enable-time="true"
                                    data-no-calendar="true"
                                    data-date-format="H:i"
                                    data-time_24hr="true"
                                    autocomplete="off">
                                <div id="error-jam_pulang" class="invalid-feedback animated fadeIn"></div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label" for="batas_mulai_pulang">Batas Mulai Pulang <span class="text-danger">*</span></label>
                                <input type="text"
                                    id="batas_mulai_pulang"
                                    name="batas_mulai_pulang"
                                    class="js-flatpickr form-control"
                                    data-enable-time="true"
                                    data-no-calendar="true"
                                    data-date-format="H:i"
                                    data-time_24hr="true"
                                    autocomplete="off">
                                <div id="error-batas_mulai_pulang" class="invalid-feedback animated fadeIn"></div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label" for="batas_akhir_pulang">Batas Akhir Pulang <span class="text-danger">*</span></label>
                                <input type="text"
                                    id="batas_akhir_pulang"
                                    name="batas_akhir_pulang"
                                    class="js-flatpickr form-control"
                                    data-enable-time="true"
                                    data-no-calendar="true"
                                    data-date-format="H:i"
                                    data-time_24hr="true"
                                    autocomplete="off">
                                <div id="error-batas_akhir_pulang" class="invalid-feedback animated fadeIn"></div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label" for="keterangan">Keterangan</label>
                                <input type="text" class="form-control" id="keterangan" name="keterangan" autocomplete="off">
                                <div id="error-keterangan" class="invalid-feedback animated fadeIn"></div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label" for="is_active">Status <span class="text-danger">*</span></label>
                                <select class="js-select2 form-select" id="is_active" name="is_active" style="width: 100%;" data-placeholder="-- Pilih Status --">
                                    <option></option><!-- Required for data-placeholder attribute to work with Select2 plugin -->
                                    <option value="1">Aktif</option>
                                    <option value="0">Tidak Aktif</option>
                                </select>
                                <div id="error-is_active" class="invalid-feedback animated fadeIn"></div>
                            </div>
                        </div>
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
        <div class="row">
            <div class="col-md-12">
                <div id="block-tabel" class="block block-themed block-rounded">
                    <div class="block-header">
                        <h3 class="block-title text-white"><i class="fa fa-file-lines"></i> <b>DATA SHIFT</b></h3>
                    </div>
                    <div class="block-content block-content-full">
                        <div class="table-responsive" style="overflow-x: auto;">
                            <table id="shift-tabel" class="table table-vcenter table-hover nowrap" style="width:100%;">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 8%;"><b>#</b></th>
                                        <th class="text-center"><b>ID</b></th>
                                        <th class="text-center" style="width: 8%;"><b>AKSI</b></th>
                                        <th class="text-center"><b>NAMA SHIFT</b></th>
                                        <th class="text-center"><b>JAM MASUK</b></th>
                                        <th class="text-center"><b>BATAS MULAI DATANG</b></th>
                                        <th class="text-center"><b>BATAS AKHIR DATANG</b></th>
                                        <th class="text-center"><b>TOLERANSI TELAT</b></th>
                                        <th class="text-center"><b>JAM PULANG</b></th>
                                        <th class="text-center"><b>BATAS MULAI PULANG</b></th>
                                        <th class="text-center"><b>BATAS AKHIR PULANG</b></th>
                                        <th class="text-center"><b>KETERANGAN</b></th>
                                        <th class="text-center"><b>STATUS</b></th>
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
</div>

<!-- Fade In Modal -->
<div class="modal" id="modal-ubah" tabindex="-1" role="dialog" aria-labelledby="modal-ubah" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div id="block-content-ubah" class="block block-themed block-rounded shadow-none mb-0">
                <div class="block-header">
                    <h3 class="block-title text-white"><i class="fa fa-pen-to-square"></i> <b>UBAH SHIFT</b></h3>
                </div>
                <form id="form_edit_shift" autocomplete="off">
                    <div class="block-content">
                        <input type="hidden" class="form-control" id="edit-id" name="edit-id">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-4">
                                    <label class="form-label" for="edit-nama_shift">Nama Shift <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit-nama_shift" name="edit-nama_shift" autocomplete="off">
                                    <div id="error-edit-nama_shift" class="invalid-feedback animated fadeIn"></div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label" for="edit-jam_masuk">Jam Masuk <span class="text-danger">*</span></label>
                                    <input type="text"
                                        id="edit-jam_masuk"
                                        name="edit-jam_masuk"
                                        class="js-flatpickr form-control"
                                        data-enable-time="true"
                                        data-no-calendar="true"
                                        data-date-format="H:i"
                                        data-time_24hr="true"
                                        autocomplete="off">
                                    <div id="error-edit-jam_masuk" class="invalid-feedback animated fadeIn"></div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label" for="edit-batas_mulai_datang">Batas Mulai Datang <span class="text-danger">*</span></label>
                                    <input type="text"
                                        id="edit-batas_mulai_datang"
                                        name="edit-batas_mulai_datang"
                                        class="js-flatpickr form-control"
                                        data-enable-time="true"
                                        data-no-calendar="true"
                                        data-date-format="H:i"
                                        data-time_24hr="true"
                                        autocomplete="off">
                                    <div id="error-edit-batas_mulai_datang" class="invalid-feedback animated fadeIn"></div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label" for="edit-batas_akhir_datang">Batas Akhir Datang <span class="text-danger">*</span></label>
                                    <input type="text"
                                        id="edit-batas_akhir_datang"
                                        name="edit-batas_akhir_datang"
                                        class="js-flatpickr form-control"
                                        data-enable-time="true"
                                        data-no-calendar="true"
                                        data-date-format="H:i"
                                        data-time_24hr="true"
                                        autocomplete="off">
                                    <div id="error-edit-batas_akhir_datang" class="invalid-feedback animated fadeIn"></div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label" for="edit-toleransi_telat_menit">Toleransi Telat <small><code>(Menit)</code></small> <span class="text-danger">*</span></label>
                                    <input type="number"
                                        class="form-control"
                                        id="edit-toleransi_telat_menit"
                                        name="edit-toleransi_telat_menit"
                                        min="0"
                                        max="60"
                                        step="1"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                    <div id="error-edit-toleransi_telat_menit" class="invalid-feedback animated fadeIn"></div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label" for="edit-jam_pulang">Jam Pulang <span class="text-danger">*</span></label>
                                    <input type="text"
                                        id="edit-jam_pulang"
                                        name="edit-jam_pulang"
                                        class="js-flatpickr form-control"
                                        data-enable-time="true"
                                        data-no-calendar="true"
                                        data-date-format="H:i"
                                        data-time_24hr="true"
                                        autocomplete="off">
                                    <div id="error-edit-jam_pulang" class="invalid-feedback animated fadeIn"></div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label" for="edit-batas_mulai_pulang">Batas Mulai Pulang <span class="text-danger">*</span></label>
                                    <input type="text"
                                        id="edit-batas_mulai_pulang"
                                        name="edit-batas_mulai_pulang"
                                        class="js-flatpickr form-control"
                                        data-enable-time="true"
                                        data-no-calendar="true"
                                        data-date-format="H:i"
                                        data-time_24hr="true"
                                        autocomplete="off">
                                    <div id="error-edit-batas_mulai_pulang" class="invalid-feedback animated fadeIn"></div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label" for="edit-batas_akhir_pulang">Batas Akhir Pulang <span class="text-danger">*</span></label>
                                    <input type="text"
                                        id="edit-batas_akhir_pulang"
                                        name="edit-batas_akhir_pulang"
                                        class="js-flatpickr form-control"
                                        data-enable-time="true"
                                        data-no-calendar="true"
                                        data-date-format="H:i"
                                        data-time_24hr="true"
                                        autocomplete="off">
                                    <div id="error-edit-batas_akhir_pulang" class="invalid-feedback animated fadeIn"></div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label" for="edit-keterangan">Keterangan</label>
                                    <input type="text" class="form-control" id="edit-keterangan" name="edit-keterangan" autocomplete="off">
                                    <div id="error-edit-keterangan" class="invalid-feedback animated fadeIn"></div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label" for="edit-is_active">Status <span class="text-danger">*</span></label>
                                    <select class="js-select2 form-select" id="edit-is_active" name="edit-is_active" style="width: 100%;" data-placeholder="-- Pilih Status --">
                                        <option></option><!-- Required for data-placeholder attribute to work with Select2 plugin -->
                                        <option value="1">Aktif</option>
                                        <option value="0">Tidak Aktif</option>
                                    </select>
                                    <div id="error-edit-is_active" class="invalid-feedback animated fadeIn"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="block-content block-content-full block-content-sm text-end border-top">
                        <button type="button" id="tutup-modal" class="btn btn-danger">
                            <i class="fa fa-sync-alt opacity-50 me-1"></i> Batal
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
<!-- END Fade In Modal -->

<?= $this->endSection('content'); ?>