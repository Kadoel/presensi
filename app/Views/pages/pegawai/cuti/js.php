<script type="text/javascript">
    $(document).ready(function() {
        let csrfToken = '<?= csrf_token(); ?>';
        let csrfHash = '<?= csrf_hash(); ?>';

        <?= loadingoverlay_fa(); ?>
        <?= notifikasi(); ?>

        const minTanggalHariIni = '<?= date('Y-m-d'); ?>';

        let data_pengajuan = null;
        let fpTanggalMulai = null;
        let fpTanggalSelesai = null;
        let fpEditTanggalMulai = null;
        let fpEditTanggalSelesai = null;

        Codebase.helpersOnLoad(['jq-select2']);

        function initTooltips() {
            $('[data-bs-toggle="tooltip"]').tooltip();
        }

        function initFlatpickr() {
            if (typeof flatpickr === 'undefined') return;

            fpTanggalMulai = flatpickr('#tanggal_mulai', {
                dateFormat: 'Y-m-d',
                minDate: minTanggalHariIni,
                allowInput: false,
                onChange: function(selectedDates, dateStr) {
                    if (!fpTanggalSelesai) return;

                    fpTanggalSelesai.set('minDate', dateStr || minTanggalHariIni);

                    const selesai = fpTanggalSelesai.input.value;
                    if (selesai && dateStr && selesai < dateStr) {
                        fpTanggalSelesai.setDate(dateStr, false, 'Y-m-d');
                    }
                }
            });

            fpTanggalSelesai = flatpickr('#tanggal_selesai', {
                dateFormat: 'Y-m-d',
                minDate: minTanggalHariIni,
                allowInput: false
            });

            fpEditTanggalMulai = flatpickr('#edit-tanggal_mulai', {
                dateFormat: 'Y-m-d',
                minDate: minTanggalHariIni,
                allowInput: false,
                onChange: function(selectedDates, dateStr) {
                    if (!fpEditTanggalSelesai) return;

                    fpEditTanggalSelesai.set('minDate', dateStr || minTanggalHariIni);

                    const selesai = fpEditTanggalSelesai.input.value;
                    if (selesai && dateStr && selesai < dateStr) {
                        fpEditTanggalSelesai.setDate(dateStr, false, 'Y-m-d');
                    }
                }
            });

            fpEditTanggalSelesai = flatpickr('#edit-tanggal_selesai', {
                dateFormat: 'Y-m-d',
                minDate: minTanggalHariIni,
                allowInput: false
            });
        }

        function clearErrors(fields) {
            fields.forEach(function(field) {
                $('#' + field).removeClass('is-invalid');
                $('#error-' + field).html('').hide();
            });
        }

        function clear_errors_tambah() {
            clearErrors(['tanggal_mulai', 'tanggal_selesai', 'alasan', 'lampiran']);
        }

        function clear_errors_edit() {
            clearErrors(['edit-tanggal_mulai', 'edit-tanggal_selesai', 'edit-alasan', 'edit-lampiran']);
        }

        function resetTambah() {
            $('#jenis').val('cuti');
            $('#alasan').val('');
            $('#lampiran').val('');

            if (fpTanggalMulai) fpTanggalMulai.clear();
            if (fpTanggalSelesai) {
                fpTanggalSelesai.clear();
                fpTanggalSelesai.set('minDate', minTanggalHariIni);
            }

            clear_errors_tambah();
        }

        function setEditForm(item) {
            $('#edit-id').val(item.id || '');
            $('#edit-jenis').val('cuti');
            $('#edit-alasan').val(item.alasan || '');
            $('#edit-lampiran').val('');

            if (fpEditTanggalMulai) {
                fpEditTanggalMulai.setDate(item.tanggal_mulai || '', false, 'Y-m-d');
            }

            if (fpEditTanggalSelesai) {
                fpEditTanggalSelesai.set('minDate', item.tanggal_mulai || minTanggalHariIni);
                fpEditTanggalSelesai.setDate(item.tanggal_selesai || '', false, 'Y-m-d');
            }
        }

        function tutup_modal() {
            $('#edit-id').val('');
            $('#edit-jenis').val('cuti');
            $('#edit-alasan').val('');
            $('#edit-lampiran').val('');

            if (fpEditTanggalMulai) fpEditTanggalMulai.clear();
            if (fpEditTanggalSelesai) {
                fpEditTanggalSelesai.clear();
                fpEditTanggalSelesai.set('minDate', minTanggalHariIni);
            }

            clear_errors_edit();
            $('#modal-ubah').modal('hide');
        }

        $('#pengajuan-cuti-tabel').on('draw.dt', function() {
            initTooltips();
        });

        data_pengajuan = $('#pengajuan-cuti-tabel').DataTable({
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
                url: '<?= base_url("pegawai/cuti"); ?>',
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
                    data: 'tanggal_mulai'
                },
                {
                    data: 'tanggal_selesai'
                },
                {
                    data: 'jumlah_hari'
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
                    targets: [0, 4, 6, 7, 8],
                    className: 'text-center'
                }
            ]
        });

        $('#form_tambah_pengajuan').on('submit', function(e) {
            e.preventDefault();

            $('#jenis').val('cuti');
            $('#block-konten-tambah').LoadingOverlay('show');
            clear_errors_tambah();

            let fd = new FormData(this);
            fd.append([csrfToken], csrfHash);

            $.ajax({
                type: 'POST',
                url: '<?= base_url('pegawai/cuti/simpan') ?>',
                dataType: 'JSON',
                cache: false,
                contentType: false,
                processData: false,
                data: fd,
                success: function(result) {
                    $('#block-konten-tambah').LoadingOverlay('hide');

                    if (result.sukses) {
                        resetTambah();
                        notifikasi('success', 'right', result.pesan);
                        data_pengajuan.ajax.reload();
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

        $('#pengajuan-cuti-tabel').on('click', '#act-edit', function() {
            let id = $(this).data('id');

            $('#block-content-ubah').LoadingOverlay('show');
            clear_errors_edit();
            $('#modal-ubah').removeClass('fade');

            $.ajax({
                type: 'POST',
                url: '<?= base_url('pegawai/cuti/edit') ?>',
                dataType: 'JSON',
                data: {
                    [csrfToken]: csrfHash,
                    id: id
                },
                success: function(result) {
                    $('#block-content-ubah').LoadingOverlay('hide');

                    if (result.sukses) {
                        setEditForm(result.pengajuan_izin || {});
                        $('#modal-ubah').modal('show');
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

        $('#modal-ubah').on('click', '#tutup-modal', function() {
            $('#modal-ubah').addClass('fade');
            tutup_modal();
        });

        $('#form_edit_pengajuan').on('submit', function(e) {
            e.preventDefault();

            $('#edit-jenis').val('cuti');
            $('#update-data').prop('disabled', true);
            $('#block-content-ubah').LoadingOverlay('show');
            clear_errors_edit();

            const id = $('#edit-id').val();
            let fd = new FormData(this);
            fd.append([csrfToken], csrfHash);

            $.ajax({
                type: 'POST',
                url: '<?= base_url('pegawai/cuti/update') ?>/' + id,
                dataType: 'JSON',
                cache: false,
                contentType: false,
                processData: false,
                data: fd,
                success: function(result) {
                    $('#block-content-ubah').LoadingOverlay('hide');

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
                    $('#block-content-ubah').LoadingOverlay('hide');
                    $('#update-data').prop('disabled', false);
                    console.log(xhr.status + ': ' + xhr.statusText);
                }
            });
        });

        $('#pengajuan-cuti-tabel').on('click', '#act-delete', function() {
            let id = $(this).data('id');

            Swal.fire({
                title: 'PRESENSI',
                html: 'Hapus Data Pengajuan Cuti Ini?',
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
                    url: '<?= base_url('pegawai/cuti/delete') ?>',
                    dataType: 'JSON',
                    data: {
                        [csrfToken]: csrfHash,
                        id: id
                    },
                    success: function(result) {
                        $('#block-tabel').LoadingOverlay('hide');

                        if (result.sukses) {
                            notifikasi('success', 'right', result.pesan);
                            data_pengajuan.ajax.reload();
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

        initFlatpickr();
    });
</script>