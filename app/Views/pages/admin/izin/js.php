<script src="/assets/plugins/select2/js/select2.full.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        let csrfToken = '<?= csrf_token(); ?>';
        let csrfHash = '<?= csrf_hash(); ?>';

        <?= loadingoverlay_fa(); ?>
        <?= notifikasi(); ?>

        <?= select2('pegawai_id'); ?>
        <?= select2('jenis'); ?>
        <?= select2_modal('edit-pegawai_id', 'modal-ubah'); ?>
        <?= select2_modal('edit-jenis', 'modal-ubah'); ?>

        function initTooltips() {
            $('[data-bs-toggle="tooltip"]').tooltip();
        }

        $('#pengajuan-izin-tabel').on('draw.dt', function() {
            initTooltips();
        });

        let data_pengajuan = $('#pengajuan-izin-tabel').DataTable({
            destroy: true,
            processing: true,
            pagingType: 'full_numbers',
            serverSide: true,
            searching: true,
            paging: true,
            info: true,
            responsive: false, // ❗ WAJIB false
            scrollX: true, // ❗ INI KUNCI
            language: {
                url: '<?= base_url("assets/plugins/DataTablesbs5/plugins/id.json"); ?>'
            },
            ajax: {
                url: '<?= base_url("admin/izin"); ?>',
                method: 'POST',
                data: {
                    [csrfToken]: csrfHash
                },
                async: true,
                error: function(xhr, error, code) {
                    if (xhr.status == 403) {
                        notifikasi('info', 'right', 'Token Kadaluarsa, Silahkan Reload Halaman Terlebih Dahulu');
                    } else {
                        console.log(xhr, code);
                    }
                }
            },
            columns: [{
                    data: '#'
                },
                {
                    data: 'id'
                },
                {
                    data: 'pegawai_id'
                },
                {
                    data: 'jenis'
                },
                {
                    data: 'tanggal_mulai'
                },
                {
                    data: 'tanggal_selesai'
                },
                {
                    data: 'alasan'
                },
                {
                    data: 'lampiran_btn'
                },
                {
                    data: 'status'
                },
                {
                    data: 'action'
                }
            ],
            order: [
                [1, 'desc']
            ],
            columnDefs: [{
                    targets: [1],
                    visible: false
                },
                {
                    targets: [0, 7],
                    orderable: false
                },
                {
                    targets: [0, 7],
                    searchable: false
                },
                {
                    targets: [0, 3, 7, 9],
                    className: 'text-center'
                }
            ]
        });

        function clear_errors_tambah() {
            const fields = ['pegawai_id', 'jenis', 'tanggal_mulai', 'tanggal_selesai', 'alasan', 'lampiran'];
            fields.forEach(function(field) {
                $('#' + field).removeClass('is-invalid');
                $('#error-' + field).html('').hide();
            });
        }

        function clear_errors_edit() {
            const fields = ['edit-pegawai_id', 'edit-jenis', 'edit-tanggal_mulai', 'edit-tanggal_selesai', 'edit-alasan', 'edit-lampiran'];
            fields.forEach(function(field) {
                $('#' + field).removeClass('is-invalid');
                $('#error-' + field).html('').hide();
            });
        }

        $('#form_tambah_pengajuan').on('submit', function(e) {
            $("#block-konten-tambah").LoadingOverlay("show");
            clear_errors_tambah();
            e.preventDefault();

            let fd = new FormData(this);
            fd.append([csrfToken], csrfHash);

            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/izin/simpan') ?>',
                dataType: 'JSON',
                cache: false,
                contentType: false,
                processData: false,
                data: fd,
                success: function(result) {
                    $("#block-konten-tambah").LoadingOverlay("hide");
                    if (result['sukses']) {
                        $('#pegawai_id').val('').trigger('change');
                        $('#jenis').val('').trigger('change');
                        $('#tanggal_mulai').val('');
                        $('#tanggal_selesai').val('');
                        $('#alasan').val('');
                        $('#lampiran').val('');
                        clear_errors_tambah();
                        notifikasi('success', 'right', result['pesan']);
                        data_pengajuan.ajax.reload();
                    } else {
                        KadoelAjax.handleError(result);
                    }
                },
                error: function(xhr) {
                    $("#block-konten-tambah").LoadingOverlay("hide");
                    if (xhr.status == 403) {
                        notifikasi('info', 'right', 'Token Kadaluarsa, Silahkan Reload Halaman Terlebih Dahulu');
                    } else {
                        console.log(xhr.status + ': ' + xhr.statusText);
                    }
                }
            });
        });

        function setFlatpickrTanggal(selector, value, options = {}) {
            const el = document.querySelector(selector);

            if (!el) return;

            if (el._flatpickr) {
                if (value) {
                    el._flatpickr.setDate(value, true, 'Y-m-d');
                } else {
                    el._flatpickr.clear();
                }

                // 👉 tambahan kecil di sini
                if (options.minDate !== undefined) {
                    el._flatpickr.set('minDate', options.minDate);
                }
            } else {
                $(selector).val(value || '');
            }
        }

        $('#edit-tanggal_mulai').on('change', function() {
            let mulai = $(this).val();

            const el = document.querySelector('#edit-tanggal_selesai');

            if (el && el._flatpickr) {
                el._flatpickr.set('minDate', mulai || minTanggalHariIni);

                if ($('#edit-tanggal_selesai').val() < mulai) {
                    el._flatpickr.clear();
                }
            }
        });

        $('#pengajuan-izin-tabel').on('click', '#act-edit', function() {
            let id = $(this).data('id');
            $("#block-content-ubah").LoadingOverlay("show");
            clear_errors_edit();
            $('#modal-ubah').removeClass('fade');

            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/izin/edit') ?>',
                dataType: 'JSON',
                data: {
                    [csrfToken]: csrfHash,
                    id: id
                },
                success: function(result) {
                    $("#block-content-ubah").LoadingOverlay("hide");
                    if (result['sukses']) {
                        const item = result['pengajuan_izin'] || {};

                        $('#edit-id').val(item.id);
                        $('#edit-pegawai_id').val(item.pegawai_id).trigger('change');
                        $('#edit-jenis').val(item.jenis).trigger('change');
                        $('#edit-alasan').val(item.alasan);
                        $('#edit-lampiran').val('');

                        setFlatpickrTanggal('#edit-tanggal_mulai', item.tanggal_mulai ?? '');

                        setFlatpickrTanggal('#edit-tanggal_selesai', item.tanggal_selesai ?? '', {
                            minDate: item.tanggal_mulai ?? minTanggalHariIni
                        });

                        jQuery('#modal-ubah').modal('show');
                    } else {
                        KadoelAjax.handleError(result);
                    }
                },
                error: function(xhr) {
                    $("#block-content-ubah").LoadingOverlay("hide");
                    if (xhr.status == 403) {
                        notifikasi('info', 'right', 'Token Kadaluarsa, Silahkan Reload Halaman Terlebih Dahulu');
                    } else {
                        console.log(xhr.status + ': ' + xhr.statusText);
                    }
                }
            });
        });

        function tutup_modal() {
            $('#edit-id').val('');
            $('#edit-pegawai_id').val('').trigger('change');
            $('#edit-jenis').val('').trigger('change');
            $('#edit-tanggal_mulai').val('');
            $('#edit-tanggal_selesai').val('');
            $('#edit-alasan').val('');
            $('#edit-lampiran').val('');
            jQuery('#modal-ubah').modal('hide');
        }

        $('#modal-ubah').on('click', '#tutup-modal', function() {
            $('#modal-ubah').addClass('fade');
            tutup_modal();
        });

        $('#form_edit_pengajuan').on('submit', function(e) {
            $('#update-data').prop('disabled', true);
            const id = $('#edit-id').val();
            clear_errors_edit();
            $("#block-content-ubah").LoadingOverlay("show");
            e.preventDefault();

            let fd = new FormData(this);
            fd.append([csrfToken], csrfHash);

            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/izin/update') ?>/' + id,
                dataType: 'JSON',
                cache: false,
                contentType: false,
                processData: false,
                data: fd,
                success: function(result) {
                    $("#block-content-ubah").LoadingOverlay("hide");
                    if (result['sukses']) {
                        tutup_modal();
                        clear_errors_edit();
                        notifikasi('success', 'right', result['pesan']);
                        data_pengajuan.ajax.reload();
                    } else {
                        KadoelAjax.handleError(result);
                    }

                    $('#update-data').prop('disabled', false);
                },
                error: function(xhr) {
                    $("#block-content-ubah").LoadingOverlay("hide");
                    $('#update-data').prop('disabled', false);

                    if (xhr.status == 403) {
                        notifikasi('info', 'right', 'Token Kadaluarsa, Silahkan Reload Halaman Terlebih Dahulu');
                    } else {
                        console.log(xhr.status + ': ' + xhr.statusText);
                    }
                }
            });
        });

        $('#pengajuan-izin-tabel').on('click', '#act-delete', function() {
            let id = $(this).data('id');
            let nama = $(this).data('nama');

            Swal.fire({
                title: 'PRESENSI',
                html: 'Hapus Data Pengajuan Milik ' + nama + '?',
                showClass: {
                    popup: 'animate__animated animate__zoomIn'
                },
                hideClass: {
                    popup: 'animate__animated animate__zoomOut'
                },
                imageUrl: '<?= base_url('assets/media/favicons/apple-touch-icon-180x180.png') ?>',
                imageWidth: 128,
                imageHeight: 128,
                imageAlt: 'PRESENSI',
                showCancelButton: true,
                confirmButtonColor: '#65A30D',
                cancelButtonColor: '#d33',
                confirmButtonText: '<i class="fa fa-trash-can"></i> Hapus',
                cancelButtonText: '<i class="fas fa-times"></i> Batal',
                allowEscapeKey: false,
                allowOutsideClick: false,
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#block-tabel").LoadingOverlay("show");

                    $.ajax({
                        type: 'POST',
                        url: '<?= base_url('admin/izin/delete') ?>',
                        dataType: 'JSON',
                        data: {
                            [csrfToken]: csrfHash,
                            id: id
                        },
                        success: function(result) {
                            $("#block-tabel").LoadingOverlay("hide");
                            if (result['sukses']) {
                                notifikasi('success', 'right', result['pesan']);
                                data_pengajuan.ajax.reload();
                            } else {
                                KadoelAjax.handleError(result);
                            }
                        },
                        error: function(xhr) {
                            $("#block-tabel").LoadingOverlay("hide");
                            if (xhr.status == 403) {
                                notifikasi('info', 'right', 'Silahkan Reload Halaman Terlebih Dahulu, Kemudian Ulangi Hapus');
                            } else {
                                console.log(xhr.status + ': ' + xhr.statusText);
                            }
                        }
                    });
                }
            });
        });

        $('#pengajuan-izin-tabel').on('click', '#act-approve', function() {
            let id = $(this).data('id');

            Swal.fire({
                title: 'Setujui Pengajuan?',
                input: 'text',
                inputLabel: 'Catatan Approval (opsional)',
                inputPlaceholder: 'Masukkan catatan approval',
                showCancelButton: true,
                confirmButtonText: 'Setujui',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#block-tabel").LoadingOverlay("show");

                    $.ajax({
                        type: 'POST',
                        url: '<?= base_url('admin/izin/approve') ?>',
                        dataType: 'JSON',
                        data: {
                            [csrfToken]: csrfHash,
                            id: id,
                            catatan_approval: result.value
                        },
                        success: function(result) {
                            $("#block-tabel").LoadingOverlay("hide");
                            if (result['sukses']) {
                                notifikasi('success', 'right', result['pesan']);
                                data_pengajuan.ajax.reload();
                            } else {
                                KadoelAjax.handleError(result);
                            }
                        },
                        error: function(xhr) {
                            $("#block-tabel").LoadingOverlay("hide");
                            console.log(xhr.status + ': ' + xhr.statusText);
                        }
                    });
                }
            });
        });

        $('#pengajuan-izin-tabel').on('click', '#act-reject', function() {
            let id = $(this).data('id');

            Swal.fire({
                title: 'Tolak Pengajuan?',
                input: 'text',
                inputLabel: 'Catatan Penolakan (opsional)',
                inputPlaceholder: 'Masukkan catatan penolakan',
                showCancelButton: true,
                confirmButtonText: 'Tolak',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#block-tabel").LoadingOverlay("show");

                    $.ajax({
                        type: 'POST',
                        url: '<?= base_url('admin/izin/reject') ?>',
                        dataType: 'JSON',
                        data: {
                            [csrfToken]: csrfHash,
                            id: id,
                            catatan_approval: result.value
                        },
                        success: function(result) {
                            $("#block-tabel").LoadingOverlay("hide");
                            if (result['sukses']) {
                                notifikasi('success', 'right', result['pesan']);
                                data_pengajuan.ajax.reload();
                            } else {
                                KadoelAjax.handleError(result);
                            }
                        },
                        error: function(xhr) {
                            $("#block-tabel").LoadingOverlay("hide");
                            console.log(xhr.status + ': ' + xhr.statusText);
                        }
                    });
                }
            });
        });

        $('#pengajuan-izin-tabel').on('click', '#act-cancel-approve', function() {
            let id = $(this).data('id');

            Swal.fire({
                title: 'Batalkan Persetujuan?',
                text: "Status akan kembali menjadi pending",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Batalkan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#block-tabel").LoadingOverlay("show");

                    $.ajax({
                        type: 'POST',
                        url: '<?= base_url('admin/izin/cancel-approve') ?>',
                        dataType: 'JSON',
                        data: {
                            [csrfToken]: csrfHash,
                            id: id
                        },
                        success: function(result) {
                            $("#block-tabel").LoadingOverlay("hide");

                            if (result['sukses']) {
                                notifikasi('success', 'right', result['pesan']);
                                data_pengajuan.ajax.reload();
                            } else {
                                KadoelAjax.handleError(result);
                            }
                        },
                        error: function(xhr) {
                            $("#block-tabel").LoadingOverlay("hide");
                            console.log(xhr.status + ': ' + xhr.statusText);
                        }
                    });
                }
            });
        });
    });
</script>
<script>
    Codebase.helpersOnLoad(['jq-select2', 'js-flatpickr']);

    Codebase.helpersOnLoad(['jq-select2', 'js-flatpickr']);

    const minTanggalHariIni = '<?= date('Y-m-d'); ?>';

    document.addEventListener('DOMContentLoaded', function() {
        if (typeof flatpickr !== 'undefined') {
            const fpTanggalMulai = flatpickr('#tanggal_mulai', {
                dateFormat: 'Y-m-d',
                minDate: minTanggalHariIni,
                onChange: function(selectedDates, dateStr) {
                    if (fpTanggalSelesai) {
                        fpTanggalSelesai.set('minDate', dateStr || minTanggalHariIni);

                        if ($('#tanggal_selesai').val() && $('#tanggal_selesai').val() < dateStr) {
                            fpTanggalSelesai.clear();
                        }
                    }
                }
            });

            const fpTanggalSelesai = flatpickr('#tanggal_selesai', {
                dateFormat: 'Y-m-d',
                minDate: minTanggalHariIni
            });

            const fpEditTanggalMulai = flatpickr('#edit-tanggal_mulai', {
                dateFormat: 'Y-m-d',
                minDate: minTanggalHariIni,
                onChange: function(selectedDates, dateStr) {
                    if (fpEditTanggalSelesai) {
                        fpEditTanggalSelesai.set('minDate', dateStr || minTanggalHariIni);

                        if ($('#edit-tanggal_selesai').val() && $('#edit-tanggal_selesai').val() < dateStr) {
                            fpEditTanggalSelesai.clear();
                        }
                    }
                }
            });

            const fpEditTanggalSelesai = flatpickr('#edit-tanggal_selesai', {
                dateFormat: 'Y-m-d',
                minDate: minTanggalHariIni
            });

            $('#tanggal_mulai').on('change', function() {
                let mulai = $(this).val();
                if (fpTanggalSelesai) {
                    fpTanggalSelesai.set('minDate', mulai || minTanggalHariIni);

                    if ($('#tanggal_selesai').val() && $('#tanggal_selesai').val() < mulai) {
                        fpTanggalSelesai.clear();
                    }
                }
            });

            $('#edit-tanggal_mulai').on('change', function() {
                let mulai = $(this).val();
                if (fpEditTanggalSelesai) {
                    fpEditTanggalSelesai.set('minDate', mulai || minTanggalHariIni);

                    if ($('#edit-tanggal_selesai').val() && $('#edit-tanggal_selesai').val() < mulai) {
                        fpEditTanggalSelesai.clear();
                    }
                }
            });

            $('#pengajuan-izin-tabel').on('click', '#act-edit', function() {
                setTimeout(function() {
                    let mulai = $('#edit-tanggal_mulai').val();
                    if (fpEditTanggalSelesai) {
                        fpEditTanggalSelesai.set('minDate', mulai || minTanggalHariIni);
                    }
                }, 200);
            });
        }
    });
</script>