<?= $this->extend('theme/admin/body'); ?>

<?= $this->section('content'); ?>
<?php $maxTanggal = date('Y-m-d'); ?>
<div class="row">
    <div class="col-md-4">
        <div id="block-konten-tambah" class="block block-themed block-rounded">
            <div class="block-header">
                <h3 class="block-title text-white"><i class="fa fa-pen-to-square"></i> <b>TAMBAH</b></h3>
            </div>
            <form id="form_tambah_pegawai" autocomplete="off">
                <div class="block-content block-content-full">
                    <div class="row">
                        <div class="col-md-12">
                            <input type="hidden"
                                class="form-control"
                                id="logo-src"
                                name="logo-src"
                                value="/assets/media/pegawai/<?= esc($pegawai->logo ?? 'default.png'); ?>">

                            <div class="mb-4">
                                <label class="form-label" for="nama_pegawai">Nama Pegawai <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama_pegawai" name="nama_pegawai" autocomplete="off">
                                <div id="error-nama_pegawai" class="invalid-feedback animated fadeIn"></div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label" for="tempat_lahir">Tempat Lahir <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" autocomplete="off">
                                <div id="error-tempat_lahir" class="invalid-feedback animated fadeIn"></div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label" for="jenis_kelamin">Jenis Kelamin <span class="text-danger">*</span></label>
                                <select class="js-select2 form-select" id="jenis_kelamin" name="jenis_kelamin" style="width: 100%;" data-placeholder="-- Pilih Status --">
                                    <option></option><!-- Required for data-placeholder attribute to work with Select2 plugin -->
                                    <option value="L">Laki-Laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                                <div id="error-jenis_kelamin" class="invalid-feedback animated fadeIn"></div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label" for="tanggal_lahir">Tanggal Lahir <span class="text-danger">*</span></label>
                                <input type="text" class="js-flatpickr form-control" id="tanggal_lahir" name="tanggal_lahir" placeholder="Y-m-d" max="<?= $maxTanggal; ?>" autocomplete="off">
                                <div id="error-tanggal_lahir" class="invalid-feedback animated fadeIn"></div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label" for="no_hp">No. HP <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="no_hp" name="no_hp" onkeypress="return onlyNumberKey(event)" autocomplete="off">
                                <div id="error-no_hp" class="invalid-feedback animated fadeIn"></div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label" for="alamat">Alamat <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="alamat" name="alamat" autocomplete="off">
                                <div id="error-alamat" class="invalid-feedback animated fadeIn"></div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label" for="jabatan_id">Jabatan <span class="text-danger">*</span></label>
                                <select class="js-select2 form-select" id="jabatan_id" name="jabatan_id" style="width: 100%;" data-placeholder="-- Pilih Status --">
                                    <option></option><!-- Required for data-placeholder attribute to work with Select2 plugin -->
                                    <?php foreach ($jabatans as $jabatan) : ?>
                                        <option value="<?= $jabatan->id; ?>"><?= $jabatan->nama_jabatan; ?></option>
                                    <?php endforeach ?>
                                </select>
                                <div id="error-jabatan_id" class="invalid-feedback animated fadeIn"></div>
                            </div>
                            <div class="mb-4">
                                <label class="col-md-2 col-form-label" for="foto">Foto</label>
                                <div class="col-md-12">
                                    <div id="preview-wrapper" style="position:relative; width:120px; max-width:400px; display:inline-block;">
                                        <img id="preview"
                                            src="/assets/media/pegawai/default.png"
                                            alt="Preview Image"
                                            style="width:120px; height:120px; max-width:100%; border:1px solid #ccc; padding:5px; box-sizing:border-box; display:block; border-radius:6px; object-fit:contain;">

                                        <button type="button"
                                            id="btn-reset"
                                            style="position:absolute; top:8px; right:8px; background:rgba(255,0,0,0.85); color:#fff; border:none; border-radius:50%; width:28px; height:28px; cursor:pointer; font-size:16px; line-height:26px; display:none;">×</button>
                                    </div>

                                    <input type="file"
                                        accept="image/*"
                                        class="form-control"
                                        id="foto"
                                        name="foto">
                                    <div id="error-foto" class="invalid-feedback animated fadeIn"></div>
                                </div>
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
                        <h3 class="block-title text-white"><i class="fa fa-file-lines"></i> <b>DATA JABATAN</b></h3>
                    </div>
                    <div class="block-content block-content-full overflow-x-auto">
                        <table id="pegawai-tabel" class="table table-vcenter js-dataTable-responsive table-hover nowrap">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 8%;"><b>#</b></th>
                                    <th class="text-center"><b>ID</b></th>
                                    <th class="text-center"><b>NAMA PEGAWAI</b></th>
                                    <th class="text-center"><b>TEMPAT LAHIR</b></th>
                                    <th class="text-center"><b>TANGAL LAHIR</b></th>
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
                    <h3 class="block-title text-white"><i class="fa fa-pen-to-square"></i> <b>UBAH PEGAWAI</b></h3>
                </div>
                <form id="form_edit_pegawai" autocomplete="off">
                    <div class="block-content">
                        <input type="hidden" class="form-control" id="edit-id" name="edit-id">
                        <input type="hidden" class="form-control" id="edit-foto-src" name="edit-foto-src">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-4">
                                    <label class="form-label" for="edit-nama_pegawai">Nama Pegawai <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit-nama_pegawai" name="edit-nama_pegawai" autocomplete="off">
                                    <div id="error-edit-nama_pegawai" class="invalid-feedback animated fadeIn"></div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label" for="edit-tempat_lahir">Tempat Lahir <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit-tempat_lahir" name="edit-tempat_lahir" autocomplete="off">
                                    <div id="error-edit-tempat_lahir" class="invalid-feedback animated fadeIn"></div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label" for="edit-jenis_kelamin">Jenis Kelamin <span class="text-danger">*</span></label>
                                    <select class="js-select2 form-select" id="edit-jenis_kelamin" name="edit-jenis_kelamin" style="width: 100%;" data-placeholder="-- Pilih Status --">
                                        <option></option><!-- Required for data-placeholder attribute to work with Select2 plugin -->
                                        <option value="L">Laki-Laki</option>
                                        <option value="P">Perempuan</option>
                                    </select>
                                    <div id="error-edit-jenis_kelamin" class="invalid-feedback animated fadeIn"></div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label" for="edit-tanggal_lahir">Tanggal Lahir <span class="text-danger">*</span></label>
                                    <input type="text" class="js-flatpickr form-control" id="edit-tanggal_lahir" name="edit-tanggal_lahir" placeholder="Y-m-d" max="<?= $maxTanggal; ?>" autocomplete="off">
                                    <div id="error-edit-tanggal_lahir" class="invalid-feedback animated fadeIn"></div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label" for="edit-no_hp">No. HP <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit-no_hp" name="edit-no_hp" autocomplete="off">
                                    <div id="error-edit-no_hp" class="invalid-feedback animated fadeIn"></div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label" for="edit-alamat">Alamat <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit-alamat" name="edit-alamat" autocomplete="off">
                                    <div id="error-edit-alamat" class="invalid-feedback animated fadeIn"></div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label" for="edit-jabatan_id">Jabatan <span class="text-danger">*</span></label>
                                    <select class="js-select2 form-select" id="edit-jabatan_id" name="edit-jabatan_id" style="width: 100%;" data-placeholder="-- Pilih Status --">
                                        <option></option><!-- Required for data-placeholder attribute to work with Select2 plugin -->
                                        <?php foreach ($jabatans as $jabatan) : ?>
                                            <option value="<?= $jabatan->id; ?>"><?= $jabatan->nama_jabatan; ?></option>
                                        <?php endforeach ?>
                                    </select>
                                    <div id="error-edit-jabatan_id" class="invalid-feedback animated fadeIn"></div>
                                </div>
                                <div class="mb-4">
                                    <label class="col-md-2 col-form-label" for="edit-foto">Foto</label>
                                    <div class="col-md-12">
                                        <div id="preview-wrapper" style="position:relative; width:120px; max-width:400px; display:inline-block;">
                                            <img id="edit-preview"
                                                src=""
                                                alt="Preview Image"
                                                style="width:120px; height:120px; max-width:100%; border:1px solid #ccc; padding:5px; box-sizing:border-box; display:block; border-radius:6px; object-fit:contain;">

                                            <button type="button"
                                                id="edit-btn-reset"
                                                style="position:absolute; top:8px; right:8px; background:rgba(255,0,0,0.85); color:#fff; border:none; border-radius:50%; width:28px; height:28px; cursor:pointer; font-size:16px; line-height:26px; display:none;">×</button>
                                        </div>

                                        <input type="file"
                                            accept="image/*"
                                            class="form-control"
                                            id="edit-foto"
                                            name="edit-foto">
                                        <div id="error-edit-foto" class="invalid-feedback animated fadeIn"></div>
                                    </div>
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

<!-- Modal Detail Pegawai Ultra Premium -->
<div class="modal" id="modal-detail" tabindex="-1" role="dialog" aria-labelledby="modal-detail" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden ultra-detail-modal">
            <div id="block-content-detail" class="shadow-none mb-0">

                <!-- HEADER HERO -->
                <div class="ultra-hero position-relative text-white overflow-hidden">
                    <div class="ultra-hero-pattern"></div>

                    <div class="position-relative p-4 p-md-5">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                            <div>
                                <div class="small text-uppercase fw-semibold opacity-75 mb-2 letter-space-1">
                                    PROFIL PEGAWAI
                                </div>
                                <h2 class="fw-bold mb-1" id="detail-nama_pegawai">-</h2>

                                <div class="mt-3 d-flex flex-wrap gap-2">
                                    <span class="badge rounded-pill bg-light text-dark px-3 py-2 fw-semibold shadow-sm" id="detail-kode_pegawai">
                                        -
                                    </span>
                                    <span class="badge rounded-pill px-3 py-2 fw-semibold shadow-sm" id="detail-is_active">
                                        -
                                    </span>
                                </div>
                            </div>

                            <div class="d-flex gap-2 flex-wrap">
                                <button type="button" class="btn btn-outline-light btn-sm rounded-pill px-3" data-bs-dismiss="modal">
                                    <i class="fa fa-times me-1"></i> Tutup
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BODY -->
                <div class="bg-body p-4 p-md-5">
                    <div class="row g-4 align-items-start">

                        <!-- SIDEBAR -->
                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden ultra-card">
                                <div class="card-body p-4 text-center">

                                    <div class="mb-4 position-relative d-inline-block">
                                        <div class="ultra-avatar-ring"></div>
                                        <img id="detail-foto"
                                            src="/assets/media/pegawai/default.png"
                                            alt="Foto Pegawai"
                                            class="rounded-circle border border-4 border-white shadow ultra-avatar">
                                    </div>

                                    <div class="mb-3">
                                        <span id="detail-jabatan-badge" class="badge bg-info-subtle text-info px-3 py-2 rounded-pill fw-semibold">
                                            -
                                        </span>
                                    </div>

                                    <!-- BARCODE / QR PLACEHOLDER -->
                                    <div class="ultra-qr-box mb-3">
                                        <div class="small text-muted mb-2">QR Pegawai</div>

                                        <div class="ultra-qr-placeholder mb-3">
                                            <img id="detail-qrcode"
                                                src="/assets/media/qrcode/default.png"
                                                alt="QR Pegawai"
                                                style="width: 140px; height: 140px; object-fit: contain;">
                                        </div>

                                        <!-- 🔥 ACTION BUTTONS -->
                                        <div class="d-flex flex-column gap-2">

                                            <a id="btn-download-kartu"
                                                href="#"
                                                target="_blank"
                                                class="btn btn-sm btn-info w-100">
                                                <i class="fa fa-id-card me-1"></i> Download Kartu Pegawai
                                            </a>
                                            <a id="btn-download-qr"
                                                href="#"
                                                class="btn btn-sm btn-success w-100">
                                                <i class="fa fa-qrcode me-1"></i> Download QR Code
                                            </a>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CONTENT -->
                        <div class="col-lg-8">
                            <div class="row g-4">
                                <!-- INFORMASI -->
                                <div class="col-12">
                                    <div class="card border-0 shadow-sm rounded-4 ultra-card">
                                        <div class="card-body p-4">
                                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
                                                <div>
                                                    <h4 class="fw-bold mb-1">Informasi Personal</h4>
                                                    <div class="text-muted small">Detail lengkap identitas pegawai</div>
                                                </div>
                                            </div>

                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <div class="ultra-info-card">
                                                        <div class="ultra-info-label">
                                                            <i class="fa fa-user text-primary me-1"></i> Nama Pegawai
                                                        </div>
                                                        <div class="ultra-info-value" id="detail-nama_pegawai_2">-</div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="ultra-info-card">
                                                        <div class="ultra-info-label">
                                                            <i class="fa fa-barcode text-primary me-1"></i> Kode Pegawai
                                                        </div>
                                                        <div class="ultra-info-value" id="detail-kode_pegawai_4">-</div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="ultra-info-card">
                                                        <div class="ultra-info-label">
                                                            <i class="fa fa-venus-mars text-primary me-1"></i> Jenis Kelamin
                                                        </div>
                                                        <div class="ultra-info-value" id="detail-jenis_kelamin">-</div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="ultra-info-card">
                                                        <div class="ultra-info-label">
                                                            <i class="fa fa-briefcase text-primary me-1"></i> Jabatan
                                                        </div>
                                                        <div class="ultra-info-value" id="detail-jabatan">-</div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="ultra-info-card">
                                                        <div class="ultra-info-label">
                                                            <i class="fa fa-location-dot text-primary me-1"></i> Tempat Lahir
                                                        </div>
                                                        <div class="ultra-info-value" id="detail-tempat_lahir">-</div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="ultra-info-card">
                                                        <div class="ultra-info-label">
                                                            <i class="fa fa-calendar-days text-primary me-1"></i> Tanggal Lahir
                                                        </div>
                                                        <div class="ultra-info-value" id="detail-tanggal_lahir">-</div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="ultra-info-card">
                                                        <div class="ultra-info-label">
                                                            <i class="fa fa-phone text-primary me-1"></i> No. HP
                                                        </div>
                                                        <div class="ultra-info-value" id="detail-no_hp">-</div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="ultra-info-card">
                                                        <div class="ultra-info-label">
                                                            <i class="fa fa-circle-check text-primary me-1"></i> Status
                                                        </div>
                                                        <div class="ultra-info-value" id="detail-status_text_3">-</div>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="ultra-info-card">
                                                        <div class="ultra-info-label">
                                                            <i class="fa fa-map text-primary me-1"></i> Alamat
                                                        </div>
                                                        <div class="ultra-info-value" id="detail-alamat">-</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- FOOT SUMMARY -->
                                <div class="col-12">
                                    <div class="card border-0 shadow-sm rounded-4 ultra-card">
                                        <div class="card-body p-4">
                                            <div class="row align-items-center g-3">
                                                <div class="col-md-9">
                                                    <h5 class="fw-bold mb-2">Ringkasan Pegawai</h5>
                                                    <p class="text-muted mb-0">
                                                        Pegawai dengan nama
                                                        <span class="fw-semibold text-dark" id="detail-summary-nama">-</span>
                                                        saat ini memiliki status
                                                        <span class="fw-semibold text-primary" id="detail-summary-status">-</span>
                                                        dan terdaftar dengan kode
                                                        <span class="fw-semibold text-dark" id="detail-summary-kode">-</span>.
                                                    </p>
                                                </div>
                                                <div class="col-md-3 text-md-end text-center">
                                                    <div class="ultra-summary-icon">
                                                        <i class="fa fa-user-check"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- END FOOT SUMMARY -->

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<!-- END Modal Detail Pegawai Ultra Premium -->

<?= $this->endSection('content'); ?>