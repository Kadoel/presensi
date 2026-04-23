<?= $this->extend('theme/body'); ?>
<?= $this->section('content'); ?>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="row">
            <div class="col-md-12">
                <div class="block block-themed block-rounded">
                    <div class="block-header">
                        <h3 class="block-title text-white"><i class="fa fa-gear"></i> <b>PENGATURAN</b></h3>
                    </div>

                    <form id="form-pengaturan" method="POST" enctype="multipart/form-data" autocomplete="off">
                        <div id="block-konten-tambah" class="block-content block-content-full">
                            <input type="hidden"
                                class="form-control"
                                id="logo-src"
                                name="logo-src"
                                value="/assets/media/photos/<?= esc($pengaturan->logo ?? 'default.png'); ?>">

                            <div class="row mb-4">
                                <label class="col-md-2 col-form-label" for="nama_usaha">Nama Usaha</label>
                                <div class="col-md-10">
                                    <input type="text"
                                        class="form-control"
                                        id="nama_usaha"
                                        name="nama_usaha"
                                        value="<?= esc($pengaturan->nama_usaha ?? ''); ?>">
                                    <div id="error-nama_usaha" class="invalid-feedback animated fadeIn"></div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <label class="col-md-2 col-form-label" for="alamat">Alamat</label>
                                <div class="col-md-10">
                                    <input type="text"
                                        class="form-control"
                                        id="alamat"
                                        name="alamat"
                                        value="<?= esc($pengaturan->alamat ?? ''); ?>">
                                    <div id="error-alamat" class="invalid-feedback animated fadeIn"></div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <label class="col-md-2 col-form-label" for="telepon">No. HP / Telepon</label>
                                <div class="col-md-10">
                                    <input type="text"
                                        class="form-control"
                                        id="telepon"
                                        name="telepon"
                                        value="<?= esc($pengaturan->telepon ?? ''); ?>"
                                        onkeypress="return onlyNumberKey(event)"
                                        maxlength="20">
                                    <div id="error-telepon" class="invalid-feedback animated fadeIn"></div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <label class="col-md-2 col-form-label" for="email">Email</label>
                                <div class="col-md-10">
                                    <input type="text"
                                        class="form-control"
                                        id="email"
                                        name="email"
                                        value="<?= esc($pengaturan->email ?? ''); ?>">
                                    <div id="error-email" class="invalid-feedback animated fadeIn"></div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <label class="col-md-2 col-form-label" for="logo">Logo</label>
                                <div class="col-md-10">
                                    <div id="preview-wrapper" style="position:relative; width:120px; max-width:400px; display:inline-block;">
                                        <img id="preview"
                                            src="/assets/media/photos/<?= esc($pengaturan->logo ?? 'default.png'); ?>"
                                            alt="Preview Image"
                                            style="width:120px; height:120px; max-width:100%; border:1px solid #ccc; padding:5px; box-sizing:border-box; display:block; border-radius:6px; object-fit:contain;">

                                        <button type="button"
                                            id="btn-reset"
                                            style="position:absolute; top:8px; right:8px; background:rgba(255,0,0,0.85); color:#fff; border:none; border-radius:50%; width:28px; height:28px; cursor:pointer; font-size:16px; line-height:26px; display:none;">×</button>
                                    </div>

                                    <input type="file"
                                        accept="image/*"
                                        class="form-control"
                                        id="logo"
                                        name="logo">
                                    <div id="error-logo" class="invalid-feedback animated fadeIn"></div>
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
        </div>
    </div>
</div>

<?= $this->endSection('content'); ?>