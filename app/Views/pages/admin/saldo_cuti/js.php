<script type="text/javascript">
    $(document).ready(function() {
        let csrfToken = '<?= csrf_token(); ?>';
        let csrfHash = '<?= csrf_hash(); ?>';

        <?= loadingoverlay_fa(); ?>
        <?= notifikasi(); ?>

        function clear_errors_generate() {
            const fields = ['tahun', 'jatah'];
            fields.forEach(function(field) {
                $('#' + field).removeClass('is-invalid');
                $('#error-' + field).html('').hide();
            });
        }

        function loadRingkasan() {
            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/saldo-cuti/ringkasan') ?>',
                dataType: 'JSON',
                data: {
                    [csrfToken]: csrfHash,
                    tahun: $('#tahun_filter').val()
                },
                success: function(result) {
                    if (result['sukses']) {
                        const item = result['ringkasan'] || {};
                        $('#summary-total-pegawai').text(item.total_pegawai ?? 0);
                        $('#summary-total-jatah').text(item.total_jatah ?? 0);
                        $('#summary-total-terpakai').text(item.total_terpakai ?? 0);
                        $('#summary-total-sisa').text(item.total_sisa ?? 0);
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

        let data_saldo_cuti = $('#saldo-cuti-tabel').DataTable({
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
                url: '<?= base_url("admin/saldo-cuti"); ?>',
                method: 'POST',
                data: function(d) {
                    d[csrfToken] = csrfHash;
                    d.tahun_filter = $('#tahun_filter').val();
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
                    data: 'tahun'
                },
                {
                    data: 'jatah'
                },
                {
                    data: 'terpakai'
                },
                {
                    data: 'sisa'
                },
                {
                    data: 'is_active'
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
                    targets: [0],
                    orderable: false,
                    searchable: false
                },
                {
                    targets: [0, 3, 4, 5, 6, 7],
                    className: 'text-center'
                }
            ]
        });

        loadRingkasan();

        $('#tahun_filter').on('change keyup', function() {
            data_saldo_cuti.ajax.reload();
            loadRingkasan();
        });

        const tahunSekarang = new Date().getFullYear();
        $('#form_generate_saldo_cuti').on('submit', function(e) {
            e.preventDefault();
            clear_errors_generate();

            let tahun = parseInt($('#tahun').val() || 0);
            if (tahun < tahunSekarang) {
                notifikasi('warning', 'right', 'Tidak boleh memilih tahun sebelumnya');
                $('#tahun').val(tahunSekarang);
                return;
            }

            Swal.fire({
                title: 'Generate Saldo Cuti?',
                html: 'Saldo cuti akan digenerate untuk semua pegawai aktif pada tahun <b>' + $('#tahun').val() + '</b>.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="fa fa-gears"></i> Generate',
                cancelButtonText: '<i class="fa fa-times"></i> Batal',
                allowEscapeKey: false,
                allowOutsideClick: false,
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#block-generate').LoadingOverlay('show');

                    let fd = new FormData(this);
                    fd.append([csrfToken], csrfHash);

                    $.ajax({
                        type: 'POST',
                        url: '<?= base_url('admin/saldo-cuti/generate') ?>',
                        dataType: 'JSON',
                        cache: false,
                        contentType: false,
                        processData: false,
                        data: fd,
                        success: function(result) {
                            $('#block-generate').LoadingOverlay('hide');

                            if (result['sukses']) {
                                $('#tahun_filter').val($('#tahun').val());
                                clear_errors_generate();
                                notifikasi('success', 'right', result['pesan']);
                                data_saldo_cuti.ajax.reload();
                                loadRingkasan();
                            } else {
                                KadoelAjax.handleError(result);
                            }
                        },
                        error: function(xhr) {
                            $('#block-generate').LoadingOverlay('hide');

                            if (xhr.status == 403) {
                                notifikasi('info', 'right', 'Token Kadaluarsa, Silahkan Reload Halaman Terlebih Dahulu');
                            } else {
                                console.log(xhr.status + ': ' + xhr.statusText);
                            }
                        }
                    });
                }
            });
        });
    });
</script>