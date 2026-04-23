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

        <?= select2('role'); ?>
        <?= select2('pegawai_id'); ?>
        <?= select2('is_active'); ?>

        <?= select2_modal('edit-role', 'modal-ubah'); ?>
        <?= select2_modal('edit-pegawai_id', 'modal-ubah'); ?>
        <?= select2_modal('edit-is_active', 'modal-ubah'); ?>

        function togglePegawaiTambah() {
            let role = $('#role').val();

            if (role === 'pegawai') {
                $('#wrap-pegawai_id').show();
            } else {
                $('#wrap-pegawai_id').hide();
                $('#pegawai_id').val('').trigger('change');
            }
        }

        let selectedEditPegawaiId = '';

        function togglePegawaiEdit() {
            let role = $('#edit-role').val();

            if (role === 'pegawai') {
                $('#wrap-edit-pegawai_id').show();

                if (selectedEditPegawaiId) {
                    $('#edit-pegawai_id').val(selectedEditPegawaiId).trigger('change');
                }
            } else {
                selectedEditPegawaiId = $('#edit-pegawai_id').val();
                $('#wrap-edit-pegawai_id').hide();
                $('#edit-pegawai_id').val('').trigger('change');
            }
        }

        $('#role').on('change', function() {
            togglePegawaiTambah();
        });

        $('#edit-role').on('change', function() {
            togglePegawaiEdit();
        });

        let data_users = $('#users-tabel').DataTable({
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
                url: '<?= base_url("admin/pengguna"); ?>',
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
                    data: 'username'
                },
                {
                    data: 'pegawai_id'
                },
                {
                    data: 'role'
                },
                {
                    data: 'is_active'
                },
                {
                    data: 'last_login_at'
                },
                {
                    data: 'action'
                }
            ],
            responsive: true,
            order: [
                [4, 'asc'],
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
                    targets: [0, 4, 5],
                    className: 'text-center'
                },
                {
                    targets: [7],
                    className: 'dt-body-center',
                    width: '8%'
                }
            ]
        });

        function clear_errors_tambah() {
            const fields = [
                'role',
                'pegawai_id',
                'username',
                'password',
                'is_active'
            ];

            fields.forEach(function(field) {
                $('#' + field).removeClass('is-invalid');
                $('#error-' + field).html('').hide();
            });
        }

        function clear_errors_edit() {
            const fields = [
                'edit-role',
                'edit-pegawai_id',
                'edit-username',
                'edit-password',
                'edit-is_active'
            ];

            fields.forEach(function(field) {
                $('#' + field).removeClass('is-invalid');
                $('#error-' + field).html('').hide();
            });
        }

        $('#form_tambah_user').on('submit', function(e) {
            $("#block-konten-tambah").LoadingOverlay("show");
            clear_errors_tambah();
            e.preventDefault();

            let fd = new FormData(this);
            fd.append([csrfToken], csrfHash);

            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/pengguna/simpan') ?>',
                dataType: 'JSON',
                cache: false,
                contentType: false,
                processData: false,
                data: fd,
                success: function(result) {
                    $("#block-konten-tambah").LoadingOverlay("hide");

                    if (result['sukses']) {
                        $('#role').val('').trigger('change');
                        $('#pegawai_id').val('').trigger('change');
                        $('#username').val('');
                        $('#password').val('');
                        $('#is_active').val('').trigger('change');

                        togglePegawaiTambah();
                        clear_errors_tambah();
                        notifikasi('success', 'right', result['pesan']);
                        data_users.ajax.reload();
                    } else {
                        KadoelAjax.handleError(result);
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    $("#block-konten-tambah").LoadingOverlay("hide");

                    if (xhr.status == 403) {
                        notifikasi('info', 'right', 'Token Kadaluarsa, Silahkan Reload Halaman Terlebih Dahulu');
                    } else {
                        console.log(xhr.status + ': ' + xhr.statusText);
                    }
                }
            });
        });

        function tutup_modal() {
            selectedEditPegawaiId = '';

            $('#edit-id').val('');
            $('#edit-role').val('').trigger('change');
            $('#edit-pegawai_id').html('<option></option>').val('').trigger('change');
            $('#edit-username').val('');
            $('#edit-password').val('');
            $('#edit-is_active').val('').trigger('change');

            togglePegawaiEdit();
            jQuery('#modal-ubah').modal('hide');
        }

        $('#users-tabel').on('click', '#act-edit', function() {
            let id = $(this).data('id');

            $("#block-content-ubah").LoadingOverlay("show");
            clear_errors_edit();
            $('#modal-ubah').removeClass('fade');

            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/pengguna/edit') ?>',
                dataType: 'JSON',
                data: {
                    [csrfToken]: csrfHash,
                    id: id
                },
                success: function(result) {
                    if (!result['sukses']) {
                        $("#block-content-ubah").LoadingOverlay("hide");
                        notifikasi('danger', 'right', result['pesan']);
                        return;
                    }

                    const user = result['user'] || {};

                    $.ajax({
                        type: 'GET',
                        url: '<?= base_url('admin/pengguna/dropdown-pegawai-edit') ?>/' + id,
                        dataType: 'JSON',
                        success: function(dropdownResult) {
                            $("#block-content-ubah").LoadingOverlay("hide");

                            if (!dropdownResult['sukses']) {
                                notifikasi('danger', 'right', dropdownResult['pesan'] || 'Gagal memuat dropdown pegawai');
                                return;
                            }

                            let options = '<option></option>';
                            let dataPegawai = dropdownResult['pegawai'] || [];

                            dataPegawai.forEach(function(item) {
                                options += `<option value="${item.id}">${item.kode_pegawai} - ${item.nama_pegawai}</option>`;
                            });

                            $('#edit-pegawai_id').html(options);

                            $('#edit-id').val(user.id);
                            $('#edit-role').val(user.role).trigger('change');
                            $('#edit-username').val(user.username);
                            $('#edit-password').val('');
                            $('#edit-is_active').val(user.is_active).trigger('change');

                            selectedEditPegawaiId = user.pegawai_id ?? '';

                            if (user.pegawai_id !== null) {
                                $('#edit-pegawai_id').val(user.pegawai_id).trigger('change');
                            } else {
                                $('#edit-pegawai_id').val('').trigger('change');
                            }

                            togglePegawaiEdit();
                            jQuery('#modal-ubah').modal('show');
                        },
                        error: function(xhr, status, error) {
                            $("#block-content-ubah").LoadingOverlay("hide");

                            if (xhr.status == 403) {
                                notifikasi('info', 'right', 'Token Kadaluarsa, Silahkan Reload Halaman Terlebih Dahulu');
                            } else {
                                console.log(xhr.status + ': ' + xhr.statusText);
                            }
                        }
                    });
                },
                error: function(xhr, status, error) {
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

        $('#form_edit_user').on('submit', function(e) {
            $('#update-data').prop('disabled', true);
            const id = $('#edit-id').val();

            clear_errors_edit();
            $("#block-content-ubah").LoadingOverlay("show");
            e.preventDefault();

            let fd = new FormData(this);
            fd.append([csrfToken], csrfHash);

            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/pengguna/update') ?>/' + id,
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
                        data_users.ajax.reload();
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
                        console.log(xhr.status + ': ' + xhr.statusText);
                    }
                }
            });
        });

        $('#users-tabel').on('click', '#act-delete', function() {
            let id = $(this).data('id');
            let nama = $(this).data('nama');

            Swal.fire({
                title: 'PRESENSI',
                html: 'Hapus User ' + nama + '?',
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
                        url: '<?= base_url('admin/pengguna/delete') ?>',
                        dataType: 'JSON',
                        data: {
                            [csrfToken]: csrfHash,
                            id: id,
                        },
                        success: function(result) {
                            $("#block-tabel").LoadingOverlay("hide");

                            if (result['sukses']) {
                                notifikasi('success', 'right', 'Data User ' + nama + ' Berhasil Dihapus');
                                data_users.ajax.reload();
                            } else {
                                KadoelAjax.handleError(result);
                            }
                        },
                        error: function(xhr, status, error) {
                            $("#block-tabel").LoadingOverlay("hide");

                            if (xhr.status == 403) {
                                notifikasi('info', 'right', 'Silahkan Reload Halaman Terlebih Dahulu, Kemudian Ulangi Hapus');
                            } else {
                                console.log(xhr.status + ': ' + xhr.statusText);
                            }
                        }
                    });
                }
            })
        });

        togglePegawaiTambah();
        togglePegawaiEdit();
    });
</script>

<script>
    Codebase.helpersOnLoad(['jq-select2']);
</script>