<script src="/assets/plugins/select2/js/select2.full.min.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        let csrfToken = '<?= csrf_token(); ?>';
        let csrfHash = '<?= csrf_hash(); ?>';

        <?= loadingoverlay_fa(); ?>
        <?= notifikasi(); ?>

        <?= select2('jenis'); ?>
        <?= select2_modal('edit-jenis', 'modal-ubah'); ?>

        const minTanggalHariIni = '<?= date('Y-m-d'); ?>';

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
            responsive: false,
            scrollX: true,
            language: {
                url: '<?= base_url("assets/plugins/DataTablesbs5/plugins/id.json"); ?>'
            },
            ajax: {
                url: '<?= base_url("pegawai/izin"); ?>',
                method: 'POST',
                data: function(d) {
                    d[csrfToken] = csrfHash;
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
                    targets: [0, 6, 8],
                    orderable: false
                },
                {
                    targets: [0, 6, 8],
                    searchable: false
                },
                {
                    targets: [0, 2, 6, 7, 8],
                    className: 'text-center'
                }
            ]
        });

        function clear_errors_tambah() {
            const fields = ['jenis', 'tanggal_mulai', 'tanggal_selesai', 'alasan', 'lampiran'];

            fields.forEach(function(field) {
                $('#' + field).removeClass('is-invalid');
                $('#error-' + field).html('').hide();
            });
        }

        function clear_errors_edit() {
            const fields = ['edit-jenis', 'edit-tanggal_mulai', 'edit-tanggal_selesai', 'edit-alasan', 'edit-lampiran'];

            fields.forEach(function(field) {
                $('#' + field).removeClass('is-invalid');
                $('#error-' + field).html('').hide();
            });
        }

        function resetTambah() {
            $('#jenis').val('').trigger('change');
            $('#tanggal_mulai').val('');
            $('#tanggal_selesai').val('');
            $('#alasan').val('');
            $('#lampiran').val('');

            clearFlatpickr('#tanggal_mulai');
            clearFlatpickr('#tanggal_selesai');

            clear_errors_tambah();
        }

        function tutup_modal() {
            $('#edit-id').val('');
            $('#edit-jenis').val('').trigger('change');
            $('#edit-tanggal_mulai').val('');
            $('#edit-tanggal_selesai').val('');
            $('#edit-alasan').val('');
            $('#edit-lampiran').val('');

            clearFlatpickr('#edit-tanggal_mulai');
            clearFlatpickr('#edit-tanggal_selesai');

            clear_errors_edit();
            jQuery('#modal-ubah').modal('hide');
        }

        function clearFlatpickr(selector) {
            const el = document.querySelector(selector);

            if (el && el._flatpickr) {
                el._flatpickr.clear();
            }
        }

        function setFlatpickrTanggal(selector, value, options = {}) {
            const el = document.querySelector(selector);

            if (!el) return;

            if (el._flatpickr) {
                if (value) {
                    el._flatpickr.setDate(value, true, 'Y-m-d');
                } else {
                    el._flatpickr.clear();
                }

                if (options.minDate !== undefined) {
                    el._flatpickr.set('minDate', options.minDate);
                }
            } else {
                $(selector).val(value || '');
            }
        }

        $('#form_tambah_pengajuan').on('submit', function(e) {
            e.preventDefault();

            $("#block-konten-tambah").LoadingOverlay("show");
            clear_errors_tambah();

            let fd = new FormData(this);
            fd.append([csrfToken], csrfHash);

            $.ajax({
                type: 'POST',
                url: '<?= base_url('pegawai/izin/simpan') ?>',
                dataType: 'JSON',
                cache: false,
                contentType: false,
                processData: false,
                data: fd,
                success: function(result) {
                    $("#block-konten-tambah").LoadingOverlay("hide");

                    if (result.sukses) {
                        resetTambah();
                        notifikasi('success', 'right', result.pesan);
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

        $('#pengajuan-izin-tabel').on('click', '#act-edit', function() {
            let id = $(this).data('id');

            $("#block-content-ubah").LoadingOverlay("show");
            clear_errors_edit();
            $('#modal-ubah').removeClass('fade');

            $.ajax({
                type: 'POST',
                url: '<?= base_url('pegawai/izin/edit') ?>',
                dataType: 'JSON',
                data: {
                    [csrfToken]: csrfHash,
                    id: id
                },
                success: function(result) {
                    $("#block-content-ubah").LoadingOverlay("hide");

                    if (result.sukses) {
                        const item = result.pengajuan_izin || {};

                        $('#edit-id').val(item.id);
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

        $('#modal-ubah').on('click', '#tutup-modal', function() {
            $('#modal-ubah').addClass('fade');
            tutup_modal();
        });

        $('#form_edit_pengajuan').on('submit', function(e) {
            e.preventDefault();

            $('#update-data').prop('disabled', true);
            $("#block-content-ubah").LoadingOverlay("show");
            clear_errors_edit();

            const id = $('#edit-id').val();
            let fd = new FormData(this);
            fd.append([csrfToken], csrfHash);

            $.ajax({
                type: 'POST',
                url: '<?= base_url('pegawai/izin/update') ?>/' + id,
                dataType: 'JSON',
                cache: false,
                contentType: false,
                processData: false,
                data: fd,
                success: function(result) {
                    $("#block-content-ubah").LoadingOverlay("hide");

                    if (result.sukses) {
                        tutup_modal();
                        notifikasi('success', 'right', result.pesan);
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

            Swal.fire({
                title: 'PRESENSI',
                html: 'Hapus Data Pengajuan Ini?',
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
                if (!result.isConfirmed) {
                    return;
                }

                $("#block-tabel").LoadingOverlay("show");

                $.ajax({
                    type: 'POST',
                    url: '<?= base_url('pegawai/izin/delete') ?>',
                    dataType: 'JSON',
                    data: {
                        [csrfToken]: csrfHash,
                        id: id
                    },
                    success: function(result) {
                        $("#block-tabel").LoadingOverlay("hide");

                        if (result.sukses) {
                            notifikasi('success', 'right', result.pesan);
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
            });
        });

        $('#tanggal_mulai').on('change', function() {
            let mulai = $(this).val();
            setMinTanggalSelesai('#tanggal_selesai', mulai);
        });

        $('#edit-tanggal_mulai').on('change', function() {
            let mulai = $(this).val();
            setMinTanggalSelesai('#edit-tanggal_selesai', mulai);
        });

        function setMinTanggalSelesai(selector, mulai) {
            const el = document.querySelector(selector);

            if (el && el._flatpickr) {
                el._flatpickr.set('minDate', mulai || minTanggalHariIni);

                if ($(selector).val() && mulai && $(selector).val() < mulai) {
                    el._flatpickr.clear();
                }
            }
        }

        Codebase.helpersOnLoad(['jq-select2', 'js-flatpickr']);

        if (typeof flatpickr !== 'undefined') {
            const fpTanggalSelesai = flatpickr('#tanggal_selesai', {
                dateFormat: 'Y-m-d',
                minDate: minTanggalHariIni
            });

            flatpickr('#tanggal_mulai', {
                dateFormat: 'Y-m-d',
                minDate: minTanggalHariIni,
                onChange: function(selectedDates, dateStr) {
                    fpTanggalSelesai.set('minDate', dateStr || minTanggalHariIni);

                    if ($('#tanggal_selesai').val() && $('#tanggal_selesai').val() < dateStr) {
                        fpTanggalSelesai.clear();
                    }
                }
            });

            const fpEditTanggalSelesai = flatpickr('#edit-tanggal_selesai', {
                dateFormat: 'Y-m-d',
                minDate: minTanggalHariIni
            });

            flatpickr('#edit-tanggal_mulai', {
                dateFormat: 'Y-m-d',
                minDate: minTanggalHariIni,
                onChange: function(selectedDates, dateStr) {
                    fpEditTanggalSelesai.set('minDate', dateStr || minTanggalHariIni);

                    if ($('#edit-tanggal_selesai').val() && $('#edit-tanggal_selesai').val() < dateStr) {
                        fpEditTanggalSelesai.clear();
                    }
                }
            });
        }
    });
</script>