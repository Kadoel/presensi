<?= $this->extend('theme/pegawai/body'); ?>
<?= $this->section('content'); ?>

<style>
    .ultra-detail-page .ultra-hero {
        background: linear-gradient(135deg, #2563eb 0%, #14b8a6 100%);
        border-radius: 1.25rem 1.25rem 0 0;
    }

    .ultra-detail-page .ultra-hero-pattern {
        position: absolute;
        inset: 0;
        background-image: radial-gradient(rgba(255, 255, 255, .25) 1px, transparent 1px);
        background-size: 22px 22px;
        opacity: .35;
    }

    .ultra-card {
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .ultra-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 1rem 2.5rem rgba(15, 23, 42, .08) !important;
    }

    .ultra-avatar {
        width: 148px;
        height: 148px;
        object-fit: cover;
    }

    .ultra-avatar-ring {
        position: absolute;
        inset: -8px;
        border-radius: 999px;
        background: linear-gradient(135deg, rgba(37, 99, 235, .22), rgba(20, 184, 166, .22));
    }

    .ultra-qr-box {
        background: linear-gradient(180deg, #f8fafc, #ffffff);
        border: 1px solid rgba(15, 23, 42, .08);
        border-radius: 1rem;
        padding: 1rem;
    }

    .ultra-qr-placeholder {
        background: #fff;
        border: 1px dashed rgba(15, 23, 42, .15);
        border-radius: 1rem;
        padding: .75rem;
        display: inline-block;
    }

    .ultra-info-card {
        background: #fff;
        border: 1px solid rgba(15, 23, 42, .08);
        border-radius: 1rem;
        padding: 1rem;
        height: 100%;
    }

    .ultra-info-label {
        color: #64748b;
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .035em;
        margin-bottom: .35rem;
    }

    .ultra-info-value {
        color: #0f172a;
        font-weight: 700;
        word-break: break-word;
    }

    .ultra-summary-icon {
        width: 72px;
        height: 72px;
        border-radius: 999px;
        background: rgba(37, 99, 235, .1);
        color: #2563eb;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
    }

    .letter-space-1 {
        letter-spacing: .12em;
    }

    #preview-wrapper {
        position: relative;
        width: 120px;
        max-width: 400px;
        display: inline-block;
    }

    #preview {
        width: 120px;
        height: 120px;
        max-width: 100%;
        border: 1px solid #ccc;
        padding: 5px;
        box-sizing: border-box;
        display: block;
        border-radius: 6px;
        object-fit: contain;
    }

    #btn-reset {
        position: absolute;
        top: 8px;
        right: 8px;
        background: rgba(255, 0, 0, 0.85);
        color: #fff;
        border: none;
        border-radius: 50%;
        width: 28px;
        height: 28px;
        cursor: pointer;
        font-size: 16px;
        line-height: 26px;
        display: none;
    }
</style>

<div class="content ultra-detail-page">
    <div id="block-content-detail" class="block block-rounded shadow-sm overflow-hidden">
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
                            <span class="badge rounded-pill bg-light text-dark px-3 py-2 fw-semibold shadow-sm" id="detail-kode_pegawai">-</span>
                            <span class="badge rounded-pill px-3 py-2 fw-semibold shadow-sm bg-secondary" id="detail-is_active">-</span>
                        </div>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-light btn-sm rounded-pill px-3" id="btn-edit-profil">
                            <i class="fa fa-pen-to-square me-1"></i> Edit Profil
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-body p-4 p-md-5">
            <div class="row g-4 align-items-start">
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden ultra-card">
                        <div class="card-body p-4 text-center">
                            <div class="mb-4 position-relative d-inline-block">
                                <div class="ultra-avatar-ring"></div>
                                <img id="detail-foto" src="/assets/media/pegawai/default.png" alt="Foto Pegawai" class="rounded-circle border border-4 border-white shadow ultra-avatar">
                            </div>

                            <div class="mb-3">
                                <span id="detail-jabatan-badge" class="badge bg-info-subtle text-info px-3 py-2 rounded-pill fw-semibold">-</span>
                            </div>

                            <div class="ultra-qr-box mb-3">
                                <div class="small text-muted mb-2">QR Pegawai</div>
                                <div class="ultra-qr-placeholder mb-3">
                                    <img id="detail-qrcode" src="/assets/media/qrcode/default.png" alt="QR Pegawai" style="width: 140px; height: 140px; object-fit: contain;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="row g-4">
                        <div class="col-12">
                            <div class="card border-0 shadow-sm rounded-4 ultra-card">
                                <div class="card-body p-4">
                                    <div class="mb-4">
                                        <h4 class="fw-bold mb-1">Informasi Personal</h4>
                                        <div class="text-muted small">Detail lengkap identitas pegawai</div>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="ultra-info-card">
                                                <div class="ultra-info-label"><i class="fa fa-user text-primary me-1"></i> Nama Pegawai</div>
                                                <div class="ultra-info-value" id="detail-nama_pegawai_2">-</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="ultra-info-card">
                                                <div class="ultra-info-label"><i class="fa fa-barcode text-primary me-1"></i> Kode Pegawai</div>
                                                <div class="ultra-info-value" id="detail-kode_pegawai_4">-</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="ultra-info-card">
                                                <div class="ultra-info-label"><i class="fa fa-venus-mars text-primary me-1"></i> Jenis Kelamin</div>
                                                <div class="ultra-info-value" id="detail-jenis_kelamin">-</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="ultra-info-card">
                                                <div class="ultra-info-label"><i class="fa fa-briefcase text-primary me-1"></i> Jabatan</div>
                                                <div class="ultra-info-value" id="detail-jabatan">-</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="ultra-info-card">
                                                <div class="ultra-info-label"><i class="fa fa-location-dot text-primary me-1"></i> Tempat Lahir</div>
                                                <div class="ultra-info-value" id="detail-tempat_lahir">-</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="ultra-info-card">
                                                <div class="ultra-info-label"><i class="fa fa-calendar-days text-primary me-1"></i> Tanggal Lahir</div>
                                                <div class="ultra-info-value" id="detail-tanggal_lahir">-</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="ultra-info-card">
                                                <div class="ultra-info-label"><i class="fa fa-phone text-primary me-1"></i> No. HP</div>
                                                <div class="ultra-info-value" id="detail-no_hp">-</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="ultra-info-card">
                                                <div class="ultra-info-label"><i class="fa fa-circle-check text-primary me-1"></i> Status</div>
                                                <div class="ultra-info-value" id="detail-status_text_3">-</div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="ultra-info-card">
                                                <div class="ultra-info-label"><i class="fa fa-map text-primary me-1"></i> Alamat</div>
                                                <div class="ultra-info-value" id="detail-alamat">-</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

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
                                            <div class="ultra-summary-icon"><i class="fa fa-user-check"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
                    <h3 class="block-title"><i class="fa fa-pen-to-square me-1"></i> Ubah Profil</h3>
                </div>

                <form id="form_edit_profil" autocomplete="off" enctype="multipart/form-data">
                    <div class="block-content">
                        <input type="hidden" id="foto-src" name="foto-src" value="default.png">

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
                            <label class="col-md-2 col-form-label" for="foto">Foto</label>
                            <div class="col-md-12">
                                <div id="preview-wrapper">
                                    <img id="preview" src="/assets/media/pegawai/default.png" alt="Preview Image">
                                    <button type="button" id="btn-reset">×</button>
                                </div>

                                <input type="file" accept="image/*" class="form-control" id="foto" name="foto">
                                <div id="error-foto" class="invalid-feedback animated fadeIn"></div>
                            </div>
                        </div>
                    </div>

                    <div class="block-content block-content-full block-content-sm text-end border-top">
                        <button type="button" id="tutup-modal" class="btn btn-danger"><i class="fa fa-times me-1"></i> Batal</button>
                        <button type="submit" id="update-data" class="btn btn-primary text-white"><i class="far fa-paper-plane me-1"></i> Ubah</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection('content'); ?>