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

        let data_jabatan = $('#jabatan-tabel').DataTable({
            destroy: true,
            processing: true,
            pagingType: 'full_numbers',
            serverSide: true,
            searching: true,
            paging: true,
            info: true,
            language: {
                url: '<?= base_url("assets/plugins/DataTablesbs5/plugins/id.json"); ?>'
            },
            ajax: {
                url: '<?= base_url("admin/jabatan"); ?>',
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
                    data: 'nama_jabatan'
                },
                {
                    data: 'deskripsi'
                },
                {
                    data: 'is_active'
                },
                {
                    data: 'action'
                }
            ],
            responsive: true,
            order: [
                [1, 'asc'],
            ],
            columnDefs: [{
                    targets: [1],
                    visible: false
                }, {
                    targets: [0, -1, 1],
                    orderable: false
                },
                {
                    targets: [0, -1, 1],
                    searchable: false
                },
                {
                    'targets': [0, 4],
                    'className': 'text-center',
                    'width': '8%'
                },
                {
                    targets: [-1],
                    className: 'dt-body-center',
                    'width': '8%'
                }
            ]
        });

        //----------- TAMBAH DATA -----------------------------

        function clear_errors_tambah() {
            const fields = [
                'nama_jabatan',
                'deskripsi',
                'is_active'
            ];

            fields.forEach(function(field) {
                $('#' + field).removeClass('is-invalid');
                $('#error-' + field).html('').hide();
            });
        }

        $('#form_tambah_jabatan').on('submit', function(e) {
            $("#block-konten-tambah").LoadingOverlay("show");
            clear_errors_tambah();
            e.preventDefault();
            var fd = new FormData(this);
            fd.append([csrfToken], csrfHash);

            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/jabatan/simpan') ?>',
                dataType: 'JSON',
                cache: false,
                contentType: false,
                processData: false,
                data: fd,
                success: function(result) {
                    $("#block-konten-tambah").LoadingOverlay("hide");
                    if (result['sukses']) {
                        $('#nama_jabatan').val('');
                        $('#deskripsi').val('');
                        $('#is_active').val('');
                        $('#is_active').trigger('change');

                        clear_errors_tambah();
                        notifikasi('success', 'right', result['pesan']);
                        data_jabatan.ajax.reload();

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
        function clear_errors_edit() {
            const fields = [
                'edit-nama_jabatan',
                'edit-deskripsi',
                'edit-is_active'
            ];

            fields.forEach(function(field) {
                $('#' + field).removeClass('is-invalid');
                $('#error-' + field).html('').hide();
            });
        }

        $('#jabatan-tabel').on('click', '#act-edit', function() {
            let id = $(this).data('id');
            $("#block-content-ubah").LoadingOverlay("show");
            clear_errors_edit();
            $('#modal-ubah').removeClass('fade');

            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/jabatan/edit') ?>',
                dataType: 'JSON',
                data: {
                    [csrfToken]: csrfHash,
                    id: id
                },
                success: function(result) {
                    $("#block-content-ubah").LoadingOverlay("hide");
                    if (result['sukses']) {
                        const jabatan = result['jabatan'] || {};

                        $('#edit-id').val(id);
                        $('#edit-nama_jabatan').val(jabatan.nama_jabatan);
                        $('#edit-deskripsi').val(jabatan.deskripsi);
                        $('#edit-is_active').val(jabatan.is_active).trigger('change');

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
            $('#edit-nama_jabatan').val('');
            $('#edit-deskripsi').val('');
            $('#edit-is_active').val('');
            $('#edit-is_active').trigger('change');

            jQuery('#modal-ubah').modal('hide');
        }

        $('#modal-ubah').on('click', '#tutup-modal', function() {
            $('#modal-ubah').addClass('fade');
            tutup_modal();
        });

        $('#form_edit_jabatan').on('submit', function(e) {
            $('#update-data').prop('disabled', true);
            const id = $('#edit-id').val();
            clear_errors_edit();
            $("#block-content-ubah").LoadingOverlay("show");
            e.preventDefault();
            var fd = new FormData(this);
            fd.append([csrfToken], csrfHash);
            console.log(id);
            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/jabatan/update') ?>' + '/' + id,
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
                        data_jabatan.ajax.reload();

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
        $('#jabatan-tabel').on('click', '#act-delete', function() {
            let id = $(this).data('id');
            let nama = $(this).data('nama');

            Swal.fire({
                title: 'PRESENSI',
                html: 'Hapus Jabatan ' + nama + '?',
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
                        url: '<?= base_url('admin/jabatan/delete') ?>',
                        dataType: 'JSON',
                        data: {
                            [csrfToken]: csrfHash,
                            id: id,
                        },
                        success: function(result) {
                            $("#block-tabel").LoadingOverlay("hide");
                            if (result['sukses']) {
                                notifikasi('success', 'right', 'Data Jabatan ' + nama + ' Berhasil Dihapus');
                                data_jabatan.ajax.reload();
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
    Codebase.helpersOnLoad(['jq-select2']);
</script>