<?= $this->extend('theme/admin/body'); ?>
<?= $this->section('content'); ?>

<div class="row">
    <div class="col-md-4">
        <div id="block-konten-tambah" class="block block-themed block-rounded">
            <div class="block-header">
                <h3 class="block-title text-white">
                    <i class="fa fa-right-left"></i> <b>TUKAR JADWAL LANGSUNG</b>
                </h3>
            </div>

            <form id="form_tambah_swap" autocomplete="off">
                <div class="block-content block-content-full">
                    <div class="mb-4">
                        <label class="form-label" for="pegawai_a_id">Pegawai A <span class="text-danger">*</span></label>
                        <select class="js-select2 form-select" id="pegawai_a_id" name="pegawai_a_id" style="width: 100%;" data-placeholder="-- Pilih Pegawai A --">
                            <option></option>
                            <?php foreach ($pegawai as $item) : ?>
                                <option value="<?= $item->id; ?>">
                                    <?= esc($item->nama_pegawai); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="error-pegawai_a_id" class="invalid-feedback animated fadeIn"></div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="jadwal_kerja_a_id">Slot Jadwal A <span class="text-danger">*</span></label>
                        <select class="js-select2 form-select" id="jadwal_kerja_a_id" name="jadwal_kerja_a_id" style="width: 100%;" data-placeholder="-- Pilih Slot Jadwal A --">
                            <option></option>
                        </select>
                        <div id="error-jadwal_kerja_a_id" class="invalid-feedback animated fadeIn"></div>
                    </div>

                    <hr>

                    <div class="mb-4">
                        <label class="form-label" for="pegawai_b_id">Pegawai B <span class="text-danger">*</span></label>
                        <select class="js-select2 form-select" id="pegawai_b_id" name="pegawai_b_id" style="width: 100%;" data-placeholder="-- Pilih Pegawai B --">
                            <option></option>
                            <?php foreach ($pegawai as $item) : ?>
                                <option value="<?= $item->id; ?>">
                                    <?= esc($item->nama_pegawai); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="error-pegawai_b_id" class="invalid-feedback animated fadeIn"></div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="jadwal_kerja_b_id">Slot Jadwal B <span class="text-danger">*</span></label>
                        <select class="js-select2 form-select" id="jadwal_kerja_b_id" name="jadwal_kerja_b_id" style="width: 100%;" data-placeholder="-- Pilih Slot Jadwal B --">
                            <option></option>
                        </select>
                        <div id="error-jadwal_kerja_b_id" class="invalid-feedback animated fadeIn"></div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="alasan">Alasan</label>
                        <textarea class="form-control" id="alasan" name="alasan" rows="3"></textarea>
                        <div id="error-alasan" class="invalid-feedback animated fadeIn"></div>
                    </div>
                </div>

                <div class="block-content block-content-full block-content-sm bg-body-light fs-sm text-end">
                    <button type="submit" class="btn btn-sm btn-primary text-white">
                        <i class="fa fa-paper-plane"></i> Simpan Langsung
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-8">
        <div id="block-tabel" class="block block-themed block-rounded">
            <div class="block-header">
                <h3 class="block-title text-white">
                    <i class="fa fa-table"></i> <b>DATA SWAP JADWAL</b>
                </h3>
            </div>
            <div class="block-content block-content-full">
                <div class="table-responsive" style="overflow-x: auto;">
                    <table id="swap-tabel" class="table table-vcenter table-hover nowrap" style="width:100%;">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 8%;"><b>#</b></th>
                                <th class="text-center"><b>ID</b></th>
                                <th class="text-center"><b>PEGAWAI A</b></th>
                                <th class="text-center"><b>TGL A</b></th>
                                <th class="text-center"><b>SHIFT A</b></th>
                                <th class="text-center"><b>PEGAWAI B</b></th>
                                <th class="text-center"><b>TGL B</b></th>
                                <th class="text-center"><b>SHIFT B</b></th>
                                <th class="text-center"><b>TIPE</b></th>
                                <th class="text-center"><b>PENGAJUAN</b></th>
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

<div class="modal" id="modal-detail" tabindex="-1" role="dialog" aria-labelledby="modal-detail" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div id="block-content-detail" class="block block-themed block-rounded shadow-none mb-0">
                <div class="block-header">
                    <h3 class="block-title text-white">
                        <i class="fa fa-circle-info"></i> <b>DETAIL SWAP JADWAL</b>
                    </h3>
                </div>
                <div class="block-content">
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th width="25%">ID</th>
                            <td id="detail-id"></td>
                        </tr>
                        <tr>
                            <th>Pegawai A</th>
                            <td id="detail-pegawai-a"></td>
                        </tr>
                        <tr>
                            <th>Tanggal A</th>
                            <td id="detail-tanggal-a"></td>
                        </tr>
                        <tr>
                            <th>Pegawai B</th>
                            <td id="detail-pegawai-b"></td>
                        </tr>
                        <tr>
                            <th>Tanggal B</th>
                            <td id="detail-tanggal-b"></td>
                        </tr>
                        <tr>
                            <th>Tipe Swap</th>
                            <td id="detail-tipe-swap"></td>
                        </tr>
                        <tr>
                            <th>Tipe Pengajuan</th>
                            <td id="detail-tipe-pengajuan"></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td id="detail-status"></td>
                        </tr>
                        <tr>
                            <th>Alasan</th>
                            <td id="detail-alasan"></td>
                        </tr>
                        <tr>
                            <th>Diajukan Oleh</th>
                            <td id="detail-diajukan-oleh"></td>
                        </tr>
                        <tr>
                            <th>Disetujui Oleh</th>
                            <td id="detail-disetujui-oleh"></td>
                        </tr>
                        <tr>
                            <th>Disetujui At</th>
                            <td id="detail-disetujui-at"></td>
                        </tr>
                        <tr>
                            <th>Catatan Approval</th>
                            <td id="detail-catatan-approval"></td>
                        </tr>
                    </table>
                </div>
                <div class="block-content block-content-full block-content-sm text-end border-top">
                    <button type="button" id="tutup-modal" class="btn btn-danger">
                        <i class="fa fa-times opacity-50 me-1"></i> Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection('content'); ?>