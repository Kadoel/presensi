<?= $this->extend('theme/admin/body'); ?>

<?= $this->section('content'); ?>
<?php $minTanggal = date('Y-m-01'); ?>
<div class="row">
    <div class="col-md-4">
        <div id="block-konten-tambah" class="block block-themed block-rounded">
            <div class="block-header">
                <h3 class="block-title text-white"><i class="fa fa-calendar-days"></i> <b>TAMBAH</b></h3>
            </div>
            <form id="form_tambah_hari_libur" autocomplete="off">
                <div class="block-content block-content-full">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-4">
                                <label class="form-label" for="tanggal">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" class="js-flatpickr form-control" id="tanggal" name="tanggal" placeholder="Y-m-d" min="<?= $minTanggal; ?>" autocomplete="off">
                                <div id="error-tanggal" class="invalid-feedback animated fadeIn"></div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label" for="nama_libur">Nama Hari Libur <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama_libur" name="nama_libur" autocomplete="off">
                                <div id="error-nama_libur" class="invalid-feedback animated fadeIn"></div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label" for="keterangan">Keterangan</label>
                                <textarea class="form-control" id="keterangan" name="keterangan" rows="3"></textarea>
                                <div id="error-keterangan" class="invalid-feedback animated fadeIn"></div>
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
                        <h3 class="block-title text-white"><i class="fa fa-file-lines"></i> <b>DATA HARI LIBUR</b></h3>
                    </div>
                    <div class="block-content block-content-full overflow-x-auto">
                        <table id="hari-libur-tabel" class="table table-vcenter js-dataTable-responsive table-hover nowrap">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 8%;"><b>#</b></th>
                                    <th class="text-center"><b>ID</b></th>
                                    <th class="text-center"><b>TANGGAL</b></th>
                                    <th class="text-center"><b>NAMA LIBUR</b></th>
                                    <th class="text-center"><b>KETERANGAN</b></th>
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
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div id="block-content-ubah" class="block block-themed block-rounded shadow-none mb-0">
                <div class="block-header">
                    <h3 class="block-title text-white">
                        <i class="fa fa-pen-to-square"></i> <b>UBAH HARI LIBUR</b>
                    </h3>
                </div>

                <form id="form_edit_hari_libur" autocomplete="off">
                    <div class="block-content">
                        <input type="hidden" id="edit-id" name="edit-id">

                        <div class="mb-4">
                            <label class="form-label" for="edit-tanggal">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="edit-tanggal" name="edit-tanggal" readonly>
                            <div id="error-edit-tanggal" class="invalid-feedback animated fadeIn"></div>
                            <small class="text-muted">Tanggal hari libur tidak dapat diubah. Hapus lalu buat ulang jika ingin ganti tanggal.</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="edit-nama_libur">Nama Hari Libur <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit-nama_libur" name="edit-nama_libur" autocomplete="off">
                            <div id="error-edit-nama_libur" class="invalid-feedback animated fadeIn"></div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="edit-keterangan">Keterangan</label>
                            <input type="text" class="form-control" id="edit-keterangan" name="edit-keterangan" autocomplete="off">
                            <div id="error-edit-keterangan" class="invalid-feedback animated fadeIn"></div>
                        </div>

                        <hr>

                        <div class="alert alert-info mb-3">
                            Pegawai berikut sudah dijadwalkan <b>kerja</b> pada tanggal hari libur ini.
                            Centang pegawai yang <b>tetap bekerja</b>. Yang tidak dicentang akan diubah menjadi <b>libur</b>.
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 8%;">Pilih</th>
                                        <th>Pegawai</th>
                                        <th class="text-center">Shift</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody id="edit-list-pegawai-terdampak">
                                    <tr>
                                        <td colspan="4" class="text-center">Tidak ada data</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="block-content block-content-full block-content-sm text-end border-top">
                        <button type="button" id="tutup-modal" class="btn btn-danger">
                            <i class="fa fa-times"></i> Batal
                        </button>
                        <button type="submit" id="update-data" class="btn btn-primary text-white">
                            <i class="fa fa-save"></i> Ubah
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="modal-konfirmasi-override" tabindex="-1" role="dialog" aria-labelledby="modal-konfirmasi-override" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div id="block-content-konfirmasi-override" class="block block-themed block-rounded shadow-none mb-0">
                <div class="block-header">
                    <h3 class="block-title text-white">
                        <i class="fa fa-calendar-days"></i> <b>KONFIRMASI OVERRIDE HARI LIBUR</b>
                    </h3>
                </div>

                <div class="block-content">
                    <input type="hidden" id="override-hari_libur_id">

                    <div class="alert alert-warning">
                        Hari libur sudah disimpan. Silakan centang untuk menentukan pegawai yang <b>tetap bekerja</b>.
                        Yang tidak dicentang akan diubah menjadi <b>libur</b>.
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 8%;">Pilih</th>
                                    <th>Pegawai</th>
                                    <th class="text-center">Shift</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody id="list-pegawai-terdampak"></tbody>
                        </table>
                    </div>
                </div>

                <div class="block-content block-content-full block-content-sm text-end border-top">
                    <button type="button" id="simpan-override-libur" class="btn btn-primary text-white">
                        <i class="fa fa-save"></i> Simpan Override
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection('content'); ?>