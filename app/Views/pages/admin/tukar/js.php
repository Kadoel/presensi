<script src="/assets/plugins/select2/js/select2.full.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        let csrfToken = '<?= csrf_token(); ?>';
        let csrfHash = '<?= csrf_hash(); ?>';

        <?= loadingoverlay_fa(); ?>
        <?= notifikasi(); ?>

        <?= select2('pegawai_a_id'); ?>
        <?= select2('pegawai_b_id'); ?>
        <?= select2('jadwal_kerja_a_id'); ?>
        <?= select2('jadwal_kerja_b_id'); ?>

        function clearErrorsTambah() {
            const fields = ['jadwal_kerja_a_id', 'jadwal_kerja_b_id', 'alasan'];
            fields.forEach(function(field) {
                $('#' + field).removeClass('is-invalid');
                $('#error-' + field).html('').hide();
            });
        }

        function resetFormTambah() {
            $('#pegawai_a_id').val('').trigger('change');
            $('#pegawai_b_id').val('').trigger('change');
            $('#jadwal_kerja_a_id').html('<option></option>').val('').trigger('change');
            $('#jadwal_kerja_b_id').html('<option></option>').val('').trigger('change');
            $('#alasan').val('');
            clearErrorsTambah();
        }

        function loadSlotPegawai(pegawaiId, targetSelect) {
            $(targetSelect).html('<option></option>').val('').trigger('change');

            if (!pegawaiId) {
                return;
            }

            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/tukar/slot-pegawai') ?>',
                dataType: 'JSON',
                data: {
                    [csrfToken]: csrfHash,
                    pegawai_id: pegawaiId
                },
                success: function(result) {
                    if (result['sukses']) {
                        let options = '<option></option>';

                        (result['slots'] || []).forEach(function(item) {
                            let text = item.tanggal + ' - ' + (item.nama_shift ?? '-') + ' [' + item.status_hari + ']';
                            options += '<option value="' + item.id + '">' + text + '</option>';
                        });

                        $(targetSelect).html(options).trigger('change');
                    } else {
                        notifikasi('danger', 'right', result['pesan'] || 'Gagal memuat slot pegawai');
                    }
                },
                error: function(xhr) {
                    if (xhr.status == 403) {
                        notifikasi('info', 'right', 'Token Kadaluarsa, Silahkan Reload Halaman Terlebih Dahulu');
                    } else {
                        console.log(xhr.status + ': ' + xhr.statusText);
                    }
                }
            });
        }

        $('#pegawai_a_id').on('change', function() {
            loadSlotPegawai($(this).val(), '#jadwal_kerja_a_id');
        });

        $('#pegawai_b_id').on('change', function() {
            loadSlotPegawai($(this).val(), '#jadwal_kerja_b_id');
        });

        let data_swap = $('#swap-tabel').DataTable({
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
                url: '<?= base_url("admin/tukar"); ?>',
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
                    data: 'nama_pegawai_a'
                },
                {
                    data: 'tanggal_a'
                },
                {
                    data: 'nama_shift_a'
                },
                {
                    data: 'nama_pegawai_b'
                },
                {
                    data: 'tanggal_b'
                },
                {
                    data: 'nama_shift_b'
                },
                {
                    data: 'tipe_swap'
                },
                {
                    data: 'tipe_pengajuan'
                },
                {
                    data: 'status'
                },
                {
                    data: 'action_button'
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
                    targets: [0, 11],
                    orderable: false
                },
                {
                    targets: [0, 11],
                    searchable: false
                },
                {
                    targets: [0, 3, 6, 8, 9, 10, 11],
                    className: 'text-center'
                }
            ]
        });


        $('#form_tambah_swap').on('submit', function(e) {
            e.preventDefault();
            clearErrorsTambah();
            $("#block-konten-tambah").LoadingOverlay("show");

            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/tukar/simpan-langsung') ?>',
                dataType: 'JSON',
                data: {
                    [csrfToken]: csrfHash,
                    pegawai_a_id: $('#pegawai_a_id').val(),
                    pegawai_b_id: $('#pegawai_b_id').val(),
                    jadwal_kerja_a_id: $('#jadwal_kerja_a_id').val(),
                    jadwal_kerja_b_id: $('#jadwal_kerja_b_id').val(),
                    alasan: $('#alasan').val()
                },
                success: function(result) {
                    $("#block-konten-tambah").LoadingOverlay("hide");
                    console.log(result);
                    if (result['sukses']) {
                        resetFormTambah();
                        notifikasi('success', 'right', result['pesan']);
                        data_swap.ajax.reload();
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

        function tutupModal() {
            $('#detail-id').html('');
            $('#detail-pegawai-a').html('');
            $('#detail-tanggal-a').html('');
            $('#detail-pegawai-b').html('');
            $('#detail-tanggal-b').html('');
            $('#detail-tipe-swap').html('');
            $('#detail-tipe-pengajuan').html('');
            $('#detail-status').html('');
            $('#detail-alasan').html('');
            $('#detail-diajukan-oleh').html('');
            $('#detail-disetujui-oleh').html('');
            $('#detail-disetujui-at').html('');
            $('#detail-catatan-approval').html('');
            jQuery('#modal-detail').modal('hide');
        }

        $('#modal-detail').on('click', '#tutup-modal', function() {
            $('#modal-detail').addClass('fade');
            tutupModal();
        });

        $('#swap-tabel').on('click', '#act-detail', function() {
            let id = $(this).data('id');
            $("#block-content-detail").LoadingOverlay("show");
            $('#modal-detail').removeClass('fade');

            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/tukar/detail') ?>',
                dataType: 'JSON',
                data: {
                    [csrfToken]: csrfHash,
                    id: id
                },
                success: function(result) {
                    $("#block-content-detail").LoadingOverlay("hide");

                    if (result['sukses']) {
                        const item = result['tukar_jadwal'] || {};
                        let tanggal_a = KadoelHelper.toTanggalIndonesia(item.tanggal_a) ?? '-';
                        let tanggal_b = KadoelHelper.toTanggalIndonesia(item.tanggal_b) ?? '-';

                        $('#detail-id').html(item.id ?? '-');
                        $('#detail-pegawai-a').html((item.nama_pegawai_a ?? '-') + ' (' + (item.kode_pegawai_a ?? '-') + ')');
                        $('#detail-tanggal-a').html((tanggal_a ?? '-') + ' / ' + (item.nama_shift_a ?? '-'));
                        $('#detail-pegawai-b').html((item.nama_pegawai_b ?? '-') + ' (' + (item.kode_pegawai_b ?? '-') + ')');
                        $('#detail-tanggal-b').html((tanggal_b ?? '-') + ' / ' + (item.nama_shift_b ?? '-'));
                        $('#detail-tipe-swap').html(item.tipe_swap ?? '-');
                        $('#detail-tipe-pengajuan').html(item.tipe_pengajuan ?? '-');
                        $('#detail-status').html(item.status ?? '-');
                        $('#detail-alasan').html(item.alasan ?? '-');
                        $('#detail-diajukan-oleh').html(item.diajukan_oleh_username ?? '-');
                        $('#detail-disetujui-oleh').html(item.disetujui_oleh_username ?? '-');
                        $('#detail-disetujui-at').html(item.disetujui_at ?? '-');
                        $('#detail-catatan-approval').html(item.catatan_approval ?? '-');

                        jQuery('#modal-detail').modal('show');
                    } else {
                        KadoelAjax.handleError(result);
                    }
                },
                error: function(xhr) {
                    $("#block-content-detail").LoadingOverlay("hide");

                    if (xhr.status == 403) {
                        notifikasi('info', 'right', 'Token Kadaluarsa, Silahkan Reload Halaman Terlebih Dahulu');
                    } else {
                        console.log(xhr.status + ': ' + xhr.statusText);
                    }
                }
            });
        });

        $('#swap-tabel').on('click', '#act-approve', function() {
            let id = $(this).data('id');

            Swal.fire({
                title: 'Setujui Swap Jadwal?',
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
                        url: '<?= base_url('admin/tukar/approve') ?>',
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
                                data_swap.ajax.reload();
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

        $('#swap-tabel').on('click', '#act-reject', function() {
            let id = $(this).data('id');

            Swal.fire({
                title: 'Tolak Swap Jadwal?',
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
                        url: '<?= base_url('admin/tukar/reject') ?>',
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
                                data_swap.ajax.reload();
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
    Codebase.helpersOnLoad(['jq-select2']);
</script>