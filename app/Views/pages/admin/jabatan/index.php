<?= $this->extend('theme/admin/body'); ?>

<?= $this->section('content'); ?>

<div class="row">
    <div class="col-md-4">
        <div id="block-konten-tambah" class="block block-themed block-rounded">
            <div class="block-header">
                <h3 class="block-title text-white"><i class="fa fa-pen-to-square"></i> <b>TAMBAH</b></h3>
            </div>
            <form id="form_tambah_jabatan" autocomplete="off">
                <div class="block-content block-content-full">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-4">
                                <label class="form-label" for="nama_jabatan">Nama Layanan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama_jabatan" name="nama_jabatan" autocomplete="off">
                                <div id="error-nama_jabatan" class="invalid-feedback animated fadeIn"></div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label" for="deskripsi">Deskripsi</label>
                                <input type="text" class="form-control" id="deskripsi" name="deskripsi" autocomplete="off">
                                <div id="error-deskripsi" class="invalid-feedback animated fadeIn"></div>
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
                    <!-- <a class="btn btn-sm btn-alt-danger">
                        <i class="fa fa-sync-alt opacity-50 me-1"></i> Batal
                    </a> -->
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
                        <h3 class="block-title text-white"><i class="fa fa-file-lines"></i> <b>DATA JABATAN</b></h3>
                    </div>
                    <div class="block-content block-content-full overflow-x-auto">
                        <table id="jabatan-tabel" class="table table-vcenter js-dataTable-responsive table-hover nowrap">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 8%;"><b>#</b></th>
                                    <th class="text-center"><b>ID</b></th>
                                    <th class="text-center"><b>NAMA JABATAN</b></th>
                                    <th class="text-center"><b>DESKRIPSI</b></th>
                                    <th class="text-center"><b>STATUS</b></th>
                                    <th class="text-center" style="width: 8%;"><b>AKSI</b></th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
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
                    <h3 class="block-title text-white"><i class="fa fa-pen-to-square"></i> <b>UBAH JABATAN</b></h3>
                </div>
                <form id="form_edit_jabatan" autocomplete="off">
                    <div class="block-content">
                        <input type="hidden" class="form-control" id="edit-id" name="edit-id">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-4">
                                    <label class="form-label" for="edit-nama_jabatan">Nama Layanan <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit-nama_jabatan" name="edit-nama_jabatan" autocomplete="off">
                                    <div id="error-edit-nama_jabatan" class="invalid-feedback animated fadeIn"></div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label" for="edit-deskripsi">Deskripsi</label>
                                    <input type="text" class="form-control" id="edit-deskripsi" name="edit-deskripsi" autocomplete="off">
                                    <div id="error-edit-deskripsi" class="invalid-feedback animated fadeIn"></div>
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