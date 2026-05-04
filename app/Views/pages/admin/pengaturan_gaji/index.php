<?= $this->extend('theme/admin/body'); ?>

<?= $this->section('content'); ?>
<div class="row">
    <div class="col-md-4">
        <div id="block-konten-tambah" class="block block-themed block-rounded">
            <div class="block-header">
                <h3 class="block-title text-white"><i class="fa fa-money-bill-wave"></i> <b>TAMBAH PENGATURAN GAJI</b></h3>
            </div>

            <form id="form_tambah_pengaturan_gaji" autocomplete="off">
                <div class="block-content block-content-full">
                    <div class="mb-4">
                        <label class="form-label" for="jabatan_id">Jabatan <span class="text-danger">*</span></label>
                        <select class="js-select2 form-select" id="jabatan_id" name="jabatan_id" style="width:100%" data-placeholder="-- Pilih Jabatan --">
                            <option></option>
                            <?php foreach ($jabatan as $item): ?>
                                <option value="<?= esc($item->id); ?>"><?= esc($item->nama_jabatan); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div id="error-jabatan_id" class="invalid-feedback animated fadeIn"></div>
                    </div>

                    <?php
                    $fields = [
                        'gaji_pokok' => 'Gaji Pokok',
                        'tunjangan' => 'Tunjangan',
                        'potongan_telat_per_menit' => 'Potongan Telat Per Menit',
                        'potongan_pulang_cepat_per_menit' => 'Potongan Pulang Cepat Per Menit',
                        'potongan_alpa_per_hari' => 'Potongan Alpa Per Hari',
                    ];
                    ?>

                    <?php foreach ($fields as $name => $label): ?>
                        <div class="mb-4">
                            <label class="form-label" for="<?= esc($name); ?>"><?= esc($label); ?> <span class="text-danger">*</span></label>
                            <input type="text" class="form-control rupiah" id="<?= esc($name); ?>" name="<?= esc($name); ?>" value="0">
                            <div id="error-<?= esc($name); ?>" class="invalid-feedback animated fadeIn"></div>
                        </div>
                    <?php endforeach; ?>

                    <div class="mb-4">
                        <label class="form-label" for="is_active">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="is_active" name="is_active">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                        <div id="error-is_active" class="invalid-feedback animated fadeIn"></div>
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
        <div id="block-tabel" class="block block-themed block-rounded">
            <div class="block-header">
                <h3 class="block-title text-white"><i class="fa fa-list"></i> <b>DATA PENGATURAN GAJI</b></h3>
            </div>

            <div class="block-content block-content-full">
                <div class="table-responsive" style="overflow-x:auto;">
                    <table id="pengaturan-gaji-tabel" class="table table-vcenter table-hover nowrap" style="width:100%;">
                        <thead>
                            <tr>
                                <th class="text-center"><b>#</b></th>
                                <th><b>ID</b></th>
                                <th><b>JABATAN</b></th>
                                <th><b>GAJI POKOK</b></th>
                                <th><b>TUNJANGAN</b></th>
                                <th><b>TELAT/MENIT</b></th>
                                <th><b>PULANG CEPAT/MENIT</b></th>
                                <th><b>ALPA/HARI</b></th>
                                <th class="text-center"><b>STATUS</b></th>
                                <th class="text-center"><b>AKSI</b></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="modal-ubah" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div id="block-content-ubah" class="block block-themed block-rounded shadow-none mb-0">
                <div class="block-header">
                    <h3 class="block-title text-white"><i class="fa fa-edit"></i> <b>UBAH PENGATURAN GAJI</b></h3>
                </div>

                <form id="form_edit_pengaturan_gaji" autocomplete="off">
                    <div class="block-content">
                        <input type="hidden" id="edit-id" name="edit-id">

                        <div class="mb-4">
                            <label class="form-label" for="edit-jabatan_id">Jabatan <span class="text-danger">*</span></label>
                            <select class="js-select2 form-select" id="edit-jabatan_id" name="edit-jabatan_id" style="width:100%" data-placeholder="-- Pilih Jabatan --">
                                <option></option>
                                <?php foreach ($jabatan as $item): ?>
                                    <option value="<?= esc($item->id); ?>"><?= esc($item->nama_jabatan); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div id="error-edit-jabatan_id" class="invalid-feedback animated fadeIn"></div>
                        </div>

                        <?php foreach ($fields as $name => $label): ?>
                            <div class="mb-4">
                                <label class="form-label" for="edit-<?= esc($name); ?>"><?= esc($label); ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rupiah" id="edit-<?= esc($name); ?>" name="edit-<?= esc($name); ?>">
                                <div id="error-edit-<?= esc($name); ?>" class="invalid-feedback animated fadeIn"></div>
                            </div>
                        <?php endforeach; ?>

                        <div class="mb-4">
                            <label class="form-label" for="edit-is_active">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit-is_active" name="edit-is_active">
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                            <div id="error-edit-is_active" class="invalid-feedback animated fadeIn"></div>
                        </div>
                    </div>

                    <div class="block-content block-content-full block-content-sm text-end border-top">
                        <button type="button" class="btn btn-danger" id="tutup-modal"><i class="fa fa-times"></i> Batal</button>
                        <button type="submit" class="btn btn-primary text-white" id="update-data"><i class="far fa-paper-plane"></i> Ubah</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>
