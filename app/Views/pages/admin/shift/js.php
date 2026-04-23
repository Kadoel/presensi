<script src="/assets/plugins/select2/js/select2.full.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        let csrfToken = '<?= csrf_token(); ?>';
        let csrfHash = '<?= csrf_hash(); ?>';

        <?= loadingoverlay_fa(); ?>
        <?= notifikasi(); ?>

        <?php if (session()->getFlashdata('sukses')) : ?>
            notifikasi('success', 'right', '<?= session()->getFlashdata('sukses'); ?>');
        <?php elseif (session()->getFlashdata('gagal')) : ?>
            notifikasi('danger', 'right', '<?= session()->getFlashdata("gagal"); ?>');
        <?php endif; ?>

        <?= select2('is_active'); ?>
        <?= select2_modal('edit-is_active', 'modal-ubah'); ?>

        $('#toleransi_telat_menit').on('input', function() {
            let val = parseInt(this.value) || 0;

            if (val > 60) {
                this.value = 60;
            }

            if (val < 0) {
                this.value = 0;
            }
        });

        let data_shift = $('#shift-tabel').DataTable({
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
                url: '<?= base_url("admin/shift"); ?>',
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
                    } // 404
                }
            },
            columns: [{
                    data: '#'
                },
                {
                    data: 'id'
                },
                {
                    data: 'action'
                },
                {
                    data: 'nama_shift'
                },
                {
                    data: 'jam_masuk'
                },
                {
                    data: 'batas_mulai_datang'
                },
                {
                    data: 'batas_akhir_datang'
                },
                {
                    data: 'toleransi_telat_menit'
                },
                {
                    data: 'jam_pulang'
                },
                {
                    data: 'batas_mulai_pulang'
                },
                {
                    data: 'batas_akhir_pulang'
                },
                {
                    data: 'keterangan'
                },
                {
                    data: 'is_active'
                }
            ],
            order: [
                [1, 'asc'],
            ],
            columnDefs: [{
                    targets: [1],
                    visible: false
                }, {
                    targets: [0, 1, 2],
                    orderable: false
                },
                {
                    targets: [0, 1, 2],
                    searchable: false
                },
                {
                    'targets': [0, 4, 5, 6, 7, 8, 9, 10, 12],
                    'className': 'text-center',
                    'width': '8%'
                },
                {
                    targets: [2],
                    className: 'dt-body-center',
                    'width': '8%'
                }
            ]
        });

        //----------- TAMBAH DATA -----------------------------

        function clear_errors_tambah() {
            const fields = [
                'nama_shift',
                'jam_masuk',
                'batas_mulai_datang',
                'batas_akhir_datang',
                'toleransi_telat_menit',
                'jam_pulang',
                'batas_mulai_pulang',
                'batas_akhir_pulang',
                'keterangan',
                'is_active'
            ];

            fields.forEach(function(field) {
                $('#' + field).removeClass('is-invalid');
                $('#error-' + field).html('').hide();
            });
        }

        $('#form_tambah_shift').on('submit', function(e) {
            $("#block-konten-tambah").LoadingOverlay("show");
            clear_errors_tambah();
            e.preventDefault();
            var fd = new FormData(this);
            fd.append([csrfToken], csrfHash);

            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/shift/simpan') ?>',
                dataType: 'JSON',
                cache: false,
                contentType: false,
                processData: false,
                data: fd,
                success: function(result) {
                    $("#block-konten-tambah").LoadingOverlay("hide");
                    console.log(result);
                    if (result['sukses']) {
                        $('#nama_shift').val('');
                        $('#jam_masuk').val('');
                        $('#batas_mulai_datang').val('');
                        $('#batas_akhir_datang').val('');
                        $('#toleransi_telat_menit').val('');
                        $('#jam_pulang').val('');
                        $('#batas_mulai_pulang').val('');
                        $('#batas_akhir_pulang').val('');
                        $('#keterangan').val('');
                        $('#is_active').val('');
                        $('#is_active').trigger('change');

                        clear_errors_tambah();
                        notifikasi('success', 'right', result['pesan']);
                        data_shift.ajax.reload();

                    } else {
                        KadoelAjax.handleError(result);
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    if (xhr.status == 403) {
                        notifikasi('info', 'right', 'Token Kadaluarsa, Silahkan Reload Halaman Terlebih Dahulu');
                    } else {
                        console.log(xhr, code)
                    } // 404
                }
            });
        });
        //--------------- END TAMBAH DATA -----------------

        //--------------- EDIT DATA -----------------------
        function setJamFlatpickr(selector, value) {
            const el = document.querySelector(selector);
            const jam = value ? value.toString().slice(0, 5) : '';

            if (!el) return;

            if (el._flatpickr) {
                el._flatpickr.setDate(jam, true, 'H:i');
            } else {
                $(selector).val(jam);
            }
        }

        function clear_errors_edit() {
            const fields = [
                'edit-nama_shift',
                'edit-jam_masuk',
                'edit-batas_mulai_datang',
                'edit-batas_akhir_datang',
                'edit-toleransi_telat_menit',
                'edit-jam_pulang',
                'edit-batas_mulai_pulang',
                'edit-batas_akhir_pulang',
                'edit-keterangan',
                'edit-is_active'
            ];

            fields.forEach(function(field) {
                $('#' + field).removeClass('is-invalid');
                $('#error-' + field).html('').hide();
            });
        }

        function setJamFlatpickr(selector, value) {
            const el = document.querySelector(selector);
            const jam = value ? value.toString().slice(0, 5) : '';

            if (!el) return;

            if (el._flatpickr) {
                el._flatpickr.setDate(jam, true, 'H:i');
            } else {
                $(selector).val(jam);
            }
        }

        $('#shift-tabel').on('click', '#act-edit', function() {
            let id = $(this).data('id');
            $("#block-content-ubah").LoadingOverlay("show");
            clear_errors_edit();
            $('#modal-ubah').removeClass('fade');

            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/shift/edit') ?>',
                dataType: 'JSON',
                data: {
                    [csrfToken]: csrfHash,
                    id: id
                },
                success: function(result) {
                    $("#block-content-ubah").LoadingOverlay("hide");
                    if (result['sukses']) {
                        const shift = result['shift'] || {};

                        $('#edit-id').val(id);
                        $('#edit-nama_shift').val(shift.nama_shift);
                        setJamFlatpickr('#edit-jam_masuk', shift.jam_masuk);
                        setJamFlatpickr('#edit-batas_mulai_datang', shift.batas_mulai_datang);
                        setJamFlatpickr('#edit-batas_akhir_datang', shift.batas_akhir_datang);
                        $('#edit-toleransi_telat_menit').val(shift.toleransi_telat_menit);
                        setJamFlatpickr('#edit-jam_pulang', shift.jam_pulang);
                        setJamFlatpickr('#edit-batas_mulai_pulang', shift.batas_mulai_pulang);
                        setJamFlatpickr('#edit-batas_akhir_pulang', shift.batas_akhir_pulang);
                        $('#edit-keterangan').val(shift.keterangan);
                        $('#edit-is_active').val(shift.is_active).trigger('change');

                        jQuery('#modal-ubah').modal('show');
                    } else {
                        KadoelAjax.handleError(result);
                    }
                },
                error: function(xhr, status, error) {
                    if (xhr.status == 403) {
                        notifikasi('info', 'right', 'Token Kadaluarsa, Silahkan Reload Halaman Terlebih Dahulu');
                    } else {
                        console.log(xhr.status + ': ' + xhr.statusText)
                    } // 404
                }
            });
        });

        function tutup_modal() {
            $('#edit-id').val('');
            $('#edit-nama_shift').val('');
            $('#edit-jam_masuk').val('');
            $('#edit-batas_mulai_datang').val('');
            $('#edit-batas_akhir_datang').val('');
            $('#edit-toleransi_telat_menit').val('');
            $('#edit-jam_pulang').val('');
            $('#edit-batas_mulai_pulang').val('');
            $('#edit-batas_akhir_pulang').val('');
            $('#edit-keterangan').val('');
            $('#edit-is_active').val('');
            $('#edit-is_active').trigger('change');

            jQuery('#modal-ubah').modal('hide');
        }

        $('#modal-ubah').on('click', '#tutup-modal', function() {
            $('#modal-ubah').addClass('fade');
            tutup_modal();
        });

        $('#form_edit_shift').on('submit', function(e) {
            $('#update-data').prop('disabled', true);
            const id = $('#edit-id').val();
            clear_errors_edit();
            $("#block-content-ubah").LoadingOverlay("show");
            e.preventDefault();
            var fd = new FormData(this);
            fd.append([csrfToken], csrfHash);
            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/shift/update') ?>' + '/' + id,
                dataType: 'JSON',
                cache: false,
                contentType: false,
                processData: false,
                data: fd,
                success: function(result) {
                    console.log(result);
                    $("#block-content-ubah").LoadingOverlay("hide");
                    if (result['sukses']) {
                        tutup_modal();
                        clear_errors_edit();
                        notifikasi('success', 'right', result['pesan']);
                        data_shift.ajax.reload();

                    } else {
                        KadoelAjax.handleError(result);
                    }

                    $('#update-data').prop('disabled', false);
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    if (xhr.status == 403) {
                        notifikasi('info', 'right', 'Token Kadaluarsa, Silahkan Reload Halaman Terlebih Dahulu');
                    } else {
                        console.log(xhr.status + ': ' + xhr.statusText)
                    } // 404
                }
            });
        });
        //------------------ END EDIT DATA -------------------------------

        //------------------ DELETE DATA ---------------------------
        $('#shift-tabel').on('click', '#act-delete', function() {
            let id = $(this).data('id');
            let nama = $(this).data('nama');

            Swal.fire({
                title: 'PRESENSI',
                html: 'Hapus Shift ' + nama + '?',
                showClass: {
                    popup: 'animate__animated animate__zoomIn'
                },
                hideClass: {
                    popup: 'animate__animated animate__zoomOut'
                },
                imageUrl: '<?= base_url('assets/media/favicons/apple-touch-icon-180x180.png') ?>',
                imageWidth: 128,
                imageHeight: 128,
                imageAlt: 'ANTRIAN',
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
                        url: '<?= base_url('admin/shift/delete') ?>',
                        dataType: 'JSON',
                        data: {
                            [csrfToken]: csrfHash,
                            id: id,
                        },
                        success: function(result) {
                            $("#block-tabel").LoadingOverlay("hide");
                            if (result['sukses']) {
                                notifikasi('success', 'right', 'Data Shift ' + nama + ' Berhasil Dihapus');
                                data_shift.ajax.reload();
                            } else {
                                KadoelAjax.handleError(result);
                            }
                        },
                        error: function(xhr, status, error) {
                            if (xhr.status == 403) {
                                notifikasi('info', 'right', 'Silahkan Reload Halaman Terlebih Dahulu, Kemudian Ulangi Hapus');
                            } else {
                                console.log(xhr.status + ': ' + xhr.statusText)
                            } // 404
                        }
                    });
                }
            })
        });
        //-------------------- END DELETE DATA -----------------------------------
    });
</script>
<script>
    Codebase.helpersOnLoad(['jq-select2', 'js-flatpickr']);
</script>