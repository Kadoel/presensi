<?= $this->extend('theme/body'); ?>

<?= $this->section('content'); ?>

<div class="row">
    <div class="col-md-4">
        <div id="block-konten-tambah" class="block block-themed block-rounded">
            <div class="block-header">
                <h3 class="block-title text-white"><i class="fa fa-users"></i> <b>TAMBAH USER</b></h3>
            </div>
            <form id="form_tambah_user" autocomplete="off">
                <div class="block-content block-content-full">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-4">
                                <label class="form-label" for="role">Role <span class="text-danger">*</span></label>
                                <select class="js-select2 form-select" id="role" name="role" style="width: 100%;" data-placeholder="-- Pilih Role --">
                                    <option></option>
                                    <option value="admin">Admin</option>
                                    <option value="pegawai">Pegawai</option>
                                </select>
                                <div id="error-role" class="invalid-feedback animated fadeIn"></div>
                            </div>

                            <div class="mb-4" id="wrap-pegawai_id" style="display: none;">
                                <label class="form-label" for="pegawai_id">Pegawai <span class="text-danger">*</span></label>
                                <select class="js-select2 form-select" id="pegawai_id" name="pegawai_id" style="width: 100%;" data-placeholder="-- Pilih Pegawai --">
                                    <option></option>
                                    <?php foreach ($pegawai as $item) : ?>
                                        <option value="<?= $item->id; ?>">
                                            <?= esc($item->kode_pegawai . ' - ' . $item->nama_pegawai); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div id="error-pegawai_id" class="invalid-feedback animated fadeIn"></div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label" for="username">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="username" name="username" autocomplete="off">
                                <div id="error-username" class="invalid-feedback animated fadeIn"></div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label" for="password">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password" name="password" autocomplete="off">
                                <div id="error-password" class="invalid-feedback animated fadeIn"></div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label" for="is_active">Status <span class="text-danger">*</span></label>
                                <select class="js-select2 form-select" id="is_active" name="is_active" style="width: 100%;" data-placeholder="-- Pilih Status --">
                                    <option></option>
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
                        <h3 class="block-title text-white"><i class="fa fa-file-lines"></i> <b>DATA USERS</b></h3>
                    </div>
                    <div class="block-content block-content-full overflow-x-auto">
                        <table id="users-tabel" class="table table-vcenter js-dataTable-responsive table-hover nowrap">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 8%;"><b>#</b></th>
                                    <th class="text-center"><b>ID</b></th>
                                    <th class="text-center"><b>USERNAME</b></th>
                                    <th class="text-center"><b>PEGAWAI</b></th>
                                    <th class="text-center"><b>ROLE</b></th>
                                    <th class="text-center"><b>STATUS</b></th>
                                    <th class="text-center"><b>LAST LOGIN</b></th>
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

<div class="modal" id="modal-ubah" tabindex="-1" role="dialog" aria-labelledby="modal-ubah" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div id="block-content-ubah" class="block block-themed block-rounded shadow-none mb-0">
                <div class="block-header">
                    <h3 class="block-title text-white"><i class="fa fa-pen-to-square"></i> <b>UBAH USER</b></h3>
                </div>
                <form id="form_edit_user" autocomplete="off">
                    <div class="block-content">
                        <input type="hidden" class="form-control" id="edit-id" name="edit-id">

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-4">
                                    <label class="form-label" for="edit-role">Role <span class="text-danger">*</span></label>
                                    <select class="js-select2 form-select" id="edit-role" name="edit-role" style="width: 100%;" data-placeholder="-- Pilih Role --">
                                        <option></option>
                                        <option value="admin">Admin</option>
                                        <option value="pegawai">Pegawai</option>
                                    </select>
                                    <div id="error-edit-role" class="invalid-feedback animated fadeIn"></div>
                                </div>

                                <div class="mb-4" id="wrap-edit-pegawai_id" style="display: none;">
                                    <label class="form-label" for="edit-pegawai_id">Pegawai <span class="text-danger">*</span></label>
                                    <select class="js-select2 form-select" id="edit-pegawai_id" name="edit-pegawai_id" style="width: 100%;" data-placeholder="-- Pilih Pegawai --">
                                        <option></option>
                                    </select>
                                    <div id="error-edit-pegawai_id" class="invalid-feedback animated fadeIn"></div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label" for="edit-username">Username <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit-username" name="edit-username" autocomplete="off">
                                    <div id="error-edit-username" class="invalid-feedback animated fadeIn"></div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label" for="edit-password">Password Baru</label>
                                    <input type="password" class="form-control" id="edit-password" name="edit-password" autocomplete="off">
                                    <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
                                    <div id="error-edit-password" class="invalid-feedback animated fadeIn"></div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label" for="edit-is_active">Status <span class="text-danger">*</span></label>
                                    <select class="js-select2 form-select" id="edit-is_active" name="edit-is_active" style="width: 100%;" data-placeholder="-- Pilih Status --">
                                        <option></option>
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

<?= $this->endSection('content'); ?>