<script src="/assets/plugins/select2/js/select2.full.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        let csrfToken = '<?= csrf_token(); ?>';
        let csrfHash = '<?= csrf_hash(); ?>';

        <?= loadingoverlay_fa(); ?>
        <?= notifikasi(); ?>

        <?= select2('pegawai_id'); ?>
        <?= select2('status_hari'); ?>
        <?= select2('shift_id'); ?>

        <?= select2_modal('edit-pegawai_id', 'modal-ubah'); ?>
        <?= select2_modal('edit-status_hari', 'modal-ubah'); ?>
        <?= select2_modal('edit-shift_id', 'modal-ubah'); ?>

        function toggleShiftTambah() {
            let status = $('#status_hari').val();

            if (status === 'kerja') {
                $('#wrap-shift_id').show();
            } else {
                $('#wrap-shift_id').hide();
                $('#shift_id').val('').trigger('change');
            }
        }

        function toggleShiftEdit() {
            let status = $('#edit-status_hari').val();

            if (status === 'kerja') {
                $('#wrap-edit-shift_id').show();
            } else {
                $('#wrap-edit-shift_id').hide();
                $('#edit-shift_id').val('').trigger('change');
            }
        }

        $('#status_hari').on('change', function() {
            toggleShiftTambah();
        });

        $('#edit-status_hari').on('change', function() {
            toggleShiftEdit();
        });

        let data_jadwal = $('#jadwal-kerja-tabel').DataTable({
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
                url: '<?= base_url("admin/jadwal"); ?>',
                method: 'POST',
                data: {
                    [csrfToken]: csrfHash
                },
                async: true,
                error: function(xhr, error, code) {
                    if (xhr.status == 403) {
                        notifikasi('info', 'right', 'Token Kadaluarsa, Silahkan Reload Halaman Terlebih Dahulu');
                    } else {
                        console.log(xhr, code)
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
                    data: 'bulan_jadwal'
                },
                {
                    data: 'nama_pegawai'
                },
                {
                    data: 'tanggal'
                },
                {
                    data: 'status_hari'
                },
                {
                    data: 'nama_shift'
                },
                {
                    data: 'sumber_data'
                },
                {
                    data: 'catatan'
                },
                {
                    data: 'action'
                }
            ],
            order: [
                [2, 'asc'],
                [3, 'asc'],
                [4, 'asc']
            ],
            rowGroup: {
                dataSrc: ['bulan_jadwal', 'nama_pegawai']
            },
            columnDefs: [{
                    targets: [1, 2, 3],
                    visible: false
                },
                {
                    targets: [0, 1, 2, 3, 4, -1],
                    orderable: false
                },
                {
                    targets: '_all',
                    searchable: false
                },
                {
                    targets: [0, 2, 5, 6, 7, 9],
                    className: 'text-center'
                }
            ]
        });

        function clear_errors_tambah() {
            const fields = [
                'pegawai_id',
                'tanggal',
                'status_hari',
                'shift_id',
                'catatan'
            ];

            fields.forEach(function(field) {
                $('#' + field).removeClass('is-invalid');
                $('#error-' + field).html('').hide();
            });
        }

        function clear_errors_edit() {
            const fields = [
                'edit-pegawai_id',
                'edit-tanggal',
                'edit-status_hari',
                'edit-shift_id',
                'edit-catatan'
            ];

            fields.forEach(function(field) {
                $('#' + field).removeClass('is-invalid');
                $('#error-' + field).html('').hide();
            });
        }

        $('#form_tambah_jadwal').on('submit', function(e) {
            $("#block-konten-tambah").LoadingOverlay("show");
            clear_errors_tambah();
            e.preventDefault();

            let fd = new FormData(this);

            fd.append([csrfToken], csrfHash);

            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/jadwal/simpan') ?>',
                dataType: 'JSON',
                cache: false,
                contentType: false,
                processData: false,
                data: fd,
                success: function(result) {
                    console.log(result);
                    $("#block-konten-tambah").LoadingOverlay("hide");

                    if (result['sukses']) {
                        $('#pegawai_id').val([]).trigger('change');
                        $('#tanggal').val('');
                        $('#status_hari').val('').trigger('change');
                        $('#shift_id').val('').trigger('change');
                        $('#catatan').val('');

                        toggleShiftTambah();
                        clear_errors_tambah();
                        notifikasi('success', 'right', result['pesan']);
                        data_jadwal.ajax.reload();

                        if (result['warning_hari_libur'] && result['hari_libur'] && result['hari_libur'].length) {
                            let infoLibur = result['hari_libur'].map(function(item) {
                                return `<li><b>${item.tanggal}</b> - ${item.nama_libur}</li>`;
                            }).join('');

                            Swal.fire({
                                title: 'Info Hari Libur Global',
                                html: `
                                        <p>Jadwal berhasil disimpan, tetapi tanggal berikut merupakan <b>hari libur global</b>:</p>
                                        <ul style="text-align:left;">${infoLibur}</ul>
                                        <p>Silakan buka <b>menu Hari Libur</b> untuk menetapkan apakah pegawai tetap <b>kerja</b> atau <b>libur</b>. pada tanggal di atas</p>
                                    `,
                                icon: 'warning',
                                confirmButtonText: 'Mengerti'
                            });
                        }
                    } else {
                        KadoelAjax.handleError(result);
                    }
                }
            });
        });

        function setFlatpickrTanggal(selector, value) {
            const el = document.querySelector(selector);

            if (!el) return;

            if (el._flatpickr) {
                if (value) {
                    el._flatpickr.setDate(value, true, 'Y-m-d');
                } else {
                    el._flatpickr.clear();
                }
            } else {
                $(selector).val(value || '');
            }
        }

        $('#jadwal-kerja-tabel').on('click', '#act-edit', function() {
            let id = $(this).data('id');
            $("#block-content-ubah").LoadingOverlay("show");
            clear_errors_edit();
            $('#modal-ubah').removeClass('fade');

            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/jadwal/edit') ?>',
                dataType: 'JSON',
                data: {
                    [csrfToken]: csrfHash,
                    id: id
                },
                success: function(result) {
                    $("#block-content-ubah").LoadingOverlay("hide");

                    if (result['sukses']) {
                        const jadwal = result['jadwal'] || {};
                        const hariLibur = result['hari_libur'] || null;

                        $('#edit-id').val(jadwal.id);
                        $('#edit-pegawai_id').val(jadwal.pegawai_id).trigger('change');
                        $('#edit-status_hari').val(jadwal.status_hari).trigger('change');
                        $('#edit-shift_id').val(jadwal.shift_id).trigger('change');
                        $('#edit-catatan').val(jadwal.catatan);
                        setFlatpickrTanggal('#edit-tanggal', jadwal.tanggal ?? '');

                        toggleShiftEdit();

                        if (hariLibur) {
                            notifikasi('info', 'right', 'Info: tanggal ini terdaftar sebagai hari libur: ' + hariLibur.nama_libur);
                        }

                        jQuery('#modal-ubah').modal('show');
                    } else {
                        KadoelAjax.handleError(result);
                    }
                },
                error: function(xhr, status, error) {
                    $("#block-content-ubah").LoadingOverlay("hide");
                    if (xhr.status == 403) {
                        notifikasi('info', 'right', 'Token Kadaluarsa, Silahkan Reload Halaman Terlebih Dahulu');
                    } else {
                        console.log(xhr.status + ': ' + xhr.statusText)
                    }
                }
            });
        });

        function tutup_modal() {
            $('#edit-id').val('');
            $('#edit-pegawai_id').val('').trigger('change');
            $('#edit-tanggal').val('');
            $('#edit-status_hari').val('').trigger('change');
            $('#edit-shift_id').val('').trigger('change');
            $('#edit-catatan').val('');

            toggleShiftEdit();
            jQuery('#modal-ubah').modal('hide');
        }

        $('#modal-ubah').on('click', '#tutup-modal', function() {
            $('#modal-ubah').addClass('fade');
            tutup_modal();
        });

        $('#form_edit_jadwal').on('submit', function(e) {
            $('#update-data').prop('disabled', true);
            const id = $('#edit-id').val();
            clear_errors_edit();
            $("#block-content-ubah").LoadingOverlay("show");
            e.preventDefault();

            let fd = new FormData(this);
            fd.append([csrfToken], csrfHash);

            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/jadwal/update') ?>/' + id,
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
                        data_jadwal.ajax.reload();

                        if (result['warning_hari_libur'] && result['hari_libur'] && result['hari_libur'].length) {
                            let infoLibur = result['hari_libur'].map(function(item) {
                                return `<li><b>${item.tanggal}</b> - ${item.nama_libur}</li>`;
                            }).join('');

                            Swal.fire({
                                title: 'Info Hari Libur Global',
                                html: `
                                        <p>Jadwal berhasil diubah, tetapi tanggal berikut merupakan <b>hari libur global</b>:</p>
                                        <ul style="text-align:left;">${infoLibur}</ul>
                                        <p>Silakan buka <b>menu Hari Libur</b> untuk menetapkan apakah pegawai tetap <b>kerja</b> atau <b>libur</b> pada tanggal di atas.</p>
                                    `,
                                icon: 'warning',
                                confirmButtonText: 'Mengerti'
                            });
                        }
                    } else {
                        KadoelAjax.handleError(result);
                    }

                    $('#update-data').prop('disabled', false);
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    $("#block-content-ubah").LoadingOverlay("hide");
                    $('#update-data').prop('disabled', false);

                    if (xhr.status == 403) {
                        notifikasi('info', 'right', 'Token Kadaluarsa, Silahkan Reload Halaman Terlebih Dahulu');
                    } else {
                        console.log(xhr.status + ': ' + xhr.statusText)
                    }
                }
            });
        });

        $('#jadwal-kerja-tabel').on('click', '#act-delete', function() {
            let id = $(this).data('id');
            let nama = $(this).data('nama');
            let tanggal = $(this).data('tanggal');

            Swal.fire({
                title: 'PRESENSI',
                html: 'Hapus Jadwal Kerja <b>' + nama + '</b> pada tanggal <b>' + tanggal + '</b>?',
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
                        url: '<?= base_url('admin/jadwal/delete') ?>',
                        dataType: 'JSON',
                        data: {
                            [csrfToken]: csrfHash,
                            id: id,
                        },
                        success: function(result) {
                            $("#block-tabel").LoadingOverlay("hide");
                            if (result['sukses']) {
                                notifikasi('success', 'right', result['pesan']);
                                data_jadwal.ajax.reload();
                            } else {
                                KadoelAjax.handleError(result);
                            }
                        },
                        error: function(xhr, status, error) {
                            $("#block-tabel").LoadingOverlay("hide");
                            if (xhr.status == 403) {
                                notifikasi('info', 'right', 'Silahkan Reload Halaman Terlebih Dahulu, Kemudian Ulangi Hapus');
                            } else {
                                console.log(xhr.status + ': ' + xhr.statusText)
                            }
                        }
                    });
                }
            })
        });

        toggleShiftTambah();
        toggleShiftEdit();
    });
</script>
<script>
    Codebase.helpersOnLoad(['jq-select2', 'js-flatpickr']);
</script>