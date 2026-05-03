<?= $this->extend('theme/admin/body'); ?>

<?= $this->section('content'); ?>
<div class="row">
    <div class="col-md-4">
        <div id="block-generate" class="block block-themed block-rounded">
            <div class="block-header">
                <h3 class="block-title text-white"><i class="fa fa-wallet"></i> <b>GENERATE SALDO CUTI</b></h3>
            </div>
            <form id="form_generate_saldo_cuti" autocomplete="off">
                <div class="block-content block-content-full">
                    <div class="mb-4">
                        <label class="form-label" for="tahun">Tahun <span class="text-danger">*</span></label>
                        <input type="number"
                            class="form-control"
                            id="tahun"
                            name="tahun"
                            min="<?= date('Y'); ?>"
                            value="<?= date('Y'); ?>"
                            required>
                        <div id="error-tahun" class="invalid-feedback animated fadeIn"></div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="jatah">Jumlah Saldo Cuti <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="jatah" name="jatah" value="12" min="0" max="365">
                            <span class="input-group-text">Hari</span>
                        </div>
                        <div id="error-jatah" class="invalid-feedback animated fadeIn"></div>
                    </div>

                    <div class="alert alert-info fs-sm mb-0">
                        <i class="fa fa-circle-info"></i>
                        Generate akan membuat saldo cuti untuk semua pegawai aktif pada tahun yang dipilih.
                        Jika saldo sudah ada, jatah akan diperbarui dan sisa dihitung ulang dari jatah dikurangi terpakai.
                    </div>
                </div>

                <div class="block-content block-content-full block-content-sm bg-body-light fs-sm text-end">
                    <button type="submit" class="btn btn-sm btn-primary text-white">
                        <i class="fa fa-gears"></i> Generate
                    </button>
                </div>
            </form>
        </div>

        <div id="block-ringkasan" class="block block-themed block-rounded">
            <div class="block-header">
                <h3 class="block-title text-white"><i class="fa fa-chart-simple"></i> <b>RINGKASAN</b></h3>
            </div>
            <div class="block-content block-content-full">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="fs-sm text-muted">Pegawai</div>
                        <div class="fs-3 fw-bold" id="summary-total-pegawai">0</div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="fs-sm text-muted">Jatah</div>
                        <div class="fs-3 fw-bold" id="summary-total-jatah">0</div>
                    </div>
                    <div class="col-6">
                        <div class="fs-sm text-muted">Terpakai</div>
                        <div class="fs-3 fw-bold" id="summary-total-terpakai">0</div>
                    </div>
                    <div class="col-6">
                        <div class="fs-sm text-muted">Sisa</div>
                        <div class="fs-3 fw-bold" id="summary-total-sisa">0</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div id="block-tabel" class="block block-themed block-rounded">
            <div class="block-header">
                <h3 class="block-title text-white"><i class="fa fa-table"></i> <b>DATA SALDO CUTI</b></h3>
            </div>
            <div class="block-content block-content-full">
                <div class="mb-3">
                    <label class="form-label" for="tahun_filter">Filter Tahun</label>
                    <input type="number" class="form-control" id="tahun_filter" value="<?= (int) date('Y'); ?>" min="<?= date('Y'); ?>" max="2100" style="max-width: 160px;">
                </div>

                <div class="table-responsive" style="overflow-x: auto;">
                    <table id="saldo-cuti-tabel" class="table table-vcenter table-hover nowrap" style="width:100%;">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 8%;"><b>#</b></th>
                                <th class="text-center"><b>ID</b></th>
                                <th class="text-center"><b>PEGAWAI</b></th>
                                <th class="text-center"><b>TAHUN</b></th>
                                <th class="text-center"><b>JATAH</b></th>
                                <th class="text-center"><b>TERPAKAI</b></th>
                                <th class="text-center"><b>SISA</b></th>
                                <th class="text-center"><b>STATUS PEGAWAI</b></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>