<script src="/assets/plugins/select2/js/select2.full.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        let csrfToken = '<?= csrf_token(); ?>';
        let csrfHash = '<?= csrf_hash(); ?>';

        <?= loadingoverlay_fa(); ?>
        <?= notifikasi(); ?>

        <?= select2('jabatan_id'); ?>
        <?= select2_modal('edit-jabatan_id', 'modal-ubah'); ?>

        const fieldsTambah = [
            'jabatan_id',
            'gaji_pokok',
            'tunjangan',
            'potongan_telat_per_menit',
            'potongan_pulang_cepat_per_menit',
            'potongan_alpa_per_hari',
            'is_active'
        ];

        const fieldsEdit = fieldsTambah.map(field => 'edit-' + field);

        function normalizeNominal(value) {
            value = String(value || '').trim();

            // dari database: 3200000.00 / 3200000.50
            if (/^\d+\.\d{1,2}$/.test(value)) {
                value = value.split('.')[0];
            }

            // dari input user: 3.000 / 3.200.000
            value = value.replace(/\D/g, '');

            value = value.replace(/^0+/, '');

            return value;
        }

        function formatRupiah(value) {
            const angka = normalizeNominal(value);

            if (angka === '') {
                return '';
            }

            return angka.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        function bindRupiahInput(selector) {
            $(selector).on('input', function() {
                $(this).val(formatRupiah($(this).val()));
            });
        }

        $('.rupiah').on('input', function() {
            $(this).val(formatRupiah($(this).val()));
        });

        bindRupiahInput('#gaji_pokok');
        bindRupiahInput('#tunjangan');
        bindRupiahInput('#potongan_telat_per_menit');
        bindRupiahInput('#potongan_pulang_cepat_per_menit');
        bindRupiahInput('#potongan_alpa_per_hari');

        bindRupiahInput('#edit-gaji_pokok');
        bindRupiahInput('#edit-tunjangan');
        bindRupiahInput('#edit-potongan_telat_per_menit');
        bindRupiahInput('#edit-potongan_pulang_cepat_per_menit');
        bindRupiahInput('#edit-potongan_alpa_per_hari');

        function clearErrors(fields) {
            fields.forEach(function(field) {
                $('#' + field).removeClass('is-invalid');
                $('#error-' + field).html('').hide();
            });
        }

        function resetTambah() {
            $('#jabatan_id').val('').trigger('change');
            $('#gaji_pokok').val('0');
            $('#tunjangan').val('0');
            $('#potongan_telat_per_menit').val('0');
            $('#potongan_pulang_cepat_per_menit').val('0');
            $('#potongan_alpa_per_hari').val('0');
            $('#is_active').val('1');
            clearErrors(fieldsTambah);
        }

        function tutupModal() {
            $('#edit-id').val('');
            $('#edit-jabatan_id').val('').trigger('change');
            fieldsEdit.forEach(function(field) {
                if (field !== 'edit-jabatan_id' && field !== 'edit-is_active') {
                    $('#' + field).val('0');
                }
            });
            $('#edit-is_active').val('1');
            clearErrors(fieldsEdit);
            $('#modal-ubah').modal('hide');
        }

        let data_pengaturan_gaji = $('#pengaturan-gaji-tabel').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            searching: true,
            paging: true,
            info: true,
            scrollX: true,
            language: {
                url: '<?= base_url("assets/plugins/DataTablesbs5/plugins/id.json"); ?>'
            },
            ajax: {
                url: '<?= base_url("admin/pengaturan-gaji"); ?>',
                method: 'POST',
                data: function(d) {
                    d[csrfToken] = csrfHash;
                },
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
                    data: 'nama_jabatan'
                },
                {
                    data: 'gaji_pokok'
                },
                {
                    data: 'tunjangan'
                },
                {
                    data: 'potongan_telat_per_menit'
                },
                {
                    data: 'potongan_pulang_cepat_per_menit'
                },
                {
                    data: 'potongan_alpa_per_hari'
                },
                {
                    data: 'is_active'
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
                    targets: [0, 9],
                    orderable: false,
                    searchable: false
                },
                {
                    targets: [0, 8, 9],
                    className: 'text-center'
                }
            ]
        });

        $('#form_tambah_pengaturan_gaji').on('submit', function(e) {
            e.preventDefault();
            clearErrors(fieldsTambah);
            $('#block-konten-tambah').LoadingOverlay('show');

            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/pengaturan-gaji/simpan'); ?>',
                dataType: 'JSON',
                data: $(this).serialize() + '&' + csrfToken + '=' + csrfHash,
                success: function(result) {
                    $('#block-konten-tambah').LoadingOverlay('hide');
                    if (result.sukses) {
                        resetTambah();
                        notifikasi('success', 'right', result.pesan);
                        data_pengaturan_gaji.ajax.reload();
                    } else {
                        KadoelAjax.handleError(result);
                    }
                },
                error: function(xhr) {
                    $('#block-konten-tambah').LoadingOverlay('hide');
                    console.log(xhr.status + ': ' + xhr.statusText);
                }
            });
        });

        $('#pengaturan-gaji-tabel').on('click', '#act-edit', function() {
            let id = $(this).data('id');
            clearErrors(fieldsEdit);
            $('#block-content-ubah').LoadingOverlay('show');
            $('#modal-ubah').modal('show');

            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/pengaturan-gaji/edit'); ?>',
                dataType: 'JSON',
                data: {
                    [csrfToken]: csrfHash,
                    id: id
                },
                success: function(result) {
                    $('#block-content-ubah').LoadingOverlay('hide');
                    if (result.sukses) {
                        const item = result.pengaturan_gaji || {};
                        $('#edit-id').val(item.id);
                        $('#edit-jabatan_id').val(item.jabatan_id).trigger('change');
                        $('#edit-gaji_pokok').val(formatRupiah(item.gaji_pokok));
                        $('#edit-tunjangan').val(formatRupiah(item.tunjangan));
                        $('#edit-potongan_telat_per_menit').val(formatRupiah(item.potongan_telat_per_menit));
                        $('#edit-potongan_pulang_cepat_per_menit').val(formatRupiah(item.potongan_pulang_cepat_per_menit));
                        $('#edit-potongan_alpa_per_hari').val(formatRupiah(item.potongan_alpa_per_hari));
                        $('#edit-is_active').val(item.is_active ?? 1);
                    } else {
                        KadoelAjax.handleError(result);
                    }
                },
                error: function(xhr) {
                    $('#block-content-ubah').LoadingOverlay('hide');
                    console.log(xhr.status + ': ' + xhr.statusText);
                }
            });
        });

        $('#form_edit_pengaturan_gaji').on('submit', function(e) {
            e.preventDefault();
            clearErrors(fieldsEdit);
            $('#update-data').prop('disabled', true);
            $('#block-content-ubah').LoadingOverlay('show');
            const id = $('#edit-id').val();

            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/pengaturan-gaji/update'); ?>/' + id,
                dataType: 'JSON',
                data: $(this).serialize() + '&' + csrfToken + '=' + csrfHash,
                success: function(result) {
                    $('#block-content-ubah').LoadingOverlay('hide');
                    $('#update-data').prop('disabled', false);
                    if (result.sukses) {
                        tutupModal();
                        notifikasi('success', 'right', result.pesan);
                        data_pengaturan_gaji.ajax.reload();
                    } else {
                        KadoelAjax.handleError(result);
                    }
                },
                error: function(xhr) {
                    $('#block-content-ubah').LoadingOverlay('hide');
                    $('#update-data').prop('disabled', false);
                    console.log(xhr.status + ': ' + xhr.statusText);
                }
            });
        });

        $('#modal-ubah').on('click', '#tutup-modal', function() {
            tutupModal();
        });

        $('#pengaturan-gaji-tabel').on('click', '#act-delete', function() {
            let id = $(this).data('id');

            Swal.fire({
                title: 'PRESENSI',
                html: 'Hapus Pengaturan Gaji Ini?',
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
                if (!result.isConfirmed) return;

                $('#block-tabel').LoadingOverlay('show');
                $.ajax({
                    type: 'POST',
                    url: '<?= base_url('admin/pengaturan-gaji/delete'); ?>',
                    dataType: 'JSON',
                    data: {
                        [csrfToken]: csrfHash,
                        id: id
                    },
                    success: function(result) {
                        $('#block-tabel').LoadingOverlay('hide');
                        if (result.sukses) {
                            notifikasi('success', 'right', result.pesan);
                            data_pengaturan_gaji.ajax.reload();
                        } else {
                            KadoelAjax.handleError(result);
                        }
                    },
                    error: function(xhr) {
                        $('#block-tabel').LoadingOverlay('hide');
                        console.log(xhr.status + ': ' + xhr.statusText);
                    }
                });
            });
        });
    });
</script>

<script>
    Codebase.helpersOnLoad(['jq-select2']);
</script>