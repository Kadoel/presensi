<?= $this->extend('theme/pegawai/body'); ?>
<?= $this->section('content'); ?>

<div class="row">

    <!-- FORM -->
    <div class="col-md-4">
        <div id="block-konten-tambah" class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">
                    <i class="fa fa-right-left me-1"></i> Ajukan Tukar Jadwal
                </h3>
            </div>

            <form id="form_tambah_swap" autocomplete="off">
                <div class="block-content block-content-full">

                    <!-- SLOT SAYA -->
                    <div class="mb-4">
                        <label class="form-label">Slot Jadwal Saya <span class="text-danger">*</span></label>
                        <select id="jadwal_kerja_a_id" class="js-select2 form-select" style="width:100%;" data-placeholder="-- Pilih Slot Jadwal Saya --">
                            <option></option>
                        </select>
                        <div id="error-jadwal_kerja_a_id" class="invalid-feedback"></div>
                    </div>

                    <hr>

                    <!-- PEGAWAI TUJUAN -->
                    <div class="mb-4">
                        <label class="form-label">Pegawai Tujuan <span class="text-danger">*</span></label>
                        <select id="pegawai_b_id" class="js-select2 form-select" style="width:100%;" data-placeholder="-- Pilih Pegawai Tujuan --">
                            <option></option>
                            <?php foreach ($pegawai as $p): ?>
                                <option value="<?= $p->id ?>">
                                    <?= esc($p->nama_pegawai) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="error-pegawai_b_id" class="invalid-feedback"></div>
                    </div>

                    <!-- SLOT TUJUAN -->
                    <div class="mb-4">
                        <label class="form-label">Slot Jadwal Tujuan <span class="text-danger">*</span></label>
                        <select id="jadwal_kerja_b_id" class="js-select2 form-select" style="width:100%;" data-placeholder="-- Pilih Slot Jadwal Tujuan --">
                            <option></option>
                        </select>
                        <div id="error-jadwal_kerja_b_id" class="invalid-feedback"></div>
                    </div>

                    <!-- ALASAN -->
                    <div class="mb-4">
                        <label class="form-label">Alasan <span class="text-danger">*</span></label>
                        <textarea id="alasan" class="form-control" rows="3"></textarea>
                        <div id="error-alasan" class="invalid-feedback"></div>
                    </div>

                </div>

                <div class="block-content block-content-full block-content-sm bg-body-light text-end">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa fa-paper-plane me-1"></i> Ajukan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- TABEL -->
    <div class="col-md-8">
        <div id="block-tabel" class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">
                    <i class="fa fa-table me-1"></i> Riwayat Tukar Jadwal
                </h3>
            </div>

            <div class="block-content block-content-full">
                <div class="table-responsive">
                    <table id="swap-tabel" class="table table-vcenter table-hover nowrap" style="width:100%;">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 5%;">#</th>
                                <th class="text-center">ID</th>
                                <th class="text-center">PEGAWAI A</th>
                                <th class="text-center">TGL A</th>
                                <th class="text-center">SHIFT A</th>
                                <th class="text-center">PEGAWAI B</th>
                                <th class="text-center">TGL B</th>
                                <th class="text-center">SHIFT B</th>
                                <th class="text-center">TIPE</th>
                                <th class="text-center">PENGAJUAN</th>
                                <th class="text-center">STATUS</th>
                                <th class="text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- MODAL DETAIL -->
<div class="modal" id="modal-detail" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div id="block-content-detail" class="block block-rounded shadow-none mb-0">

                <div class="block-header block-header-default">
                    <h3 class="block-title">
                        <i class="fa fa-circle-info me-1"></i> Detail Tukar Jadwal
                    </h3>
                </div>

                <div class="block-content">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <th>ID</th>
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
                            <th>Catatan</th>
                            <td id="detail-catatan-approval"></td>
                        </tr>
                    </table>
                </div>

                <div class="block-content block-content-full text-end border-top">
                    <button type="button" id="tutup-modal" class="btn btn-secondary">
                        Tutup
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>