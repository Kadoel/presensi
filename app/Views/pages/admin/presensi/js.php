<script src="/assets/plugins/select2/js/select2.full.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        let csrfToken = '<?= csrf_token(); ?>';
        let csrfHash = '<?= csrf_hash(); ?>';

        <?= loadingoverlay_fa(); ?>
        <?= notifikasi(); ?>

        function ambilTanggalFilter() {
            return $('#filter-tanggal').val();
        }

        function initSelect2() {
            if ($.fn.select2) {
                $('#pegawai_id').select2({
                    dropdownParent: $('#modal-lupa-presensi'),
                    placeholder: '-- Pilih Pegawai --',
                    allowClear: true,
                    width: '100%'
                });
            }
        }

        initSelect2();

        function clearErrors(mode = 'tambah') {
            const fields = mode === 'edit' ? ['edit-jam_datang', 'edit-jam_pulang', 'edit-catatan_admin'] : ['pegawai_id', 'tanggal', 'jam_datang', 'jam_pulang', 'catatan_admin'];

            fields.forEach(function(field) {
                $('#' + field).removeClass('is-invalid');
                $('#error-' + field).html('').hide();
            });
        }

        function tampilkanErrors(errors) {
            if (!errors) return;

            Object.keys(errors).forEach(function(field) {
                const target = $('#' + field);
                const error = $('#error-' + field);

                target.addClass('is-invalid');
                error.html(errors[field]).show();
            });
        }

        function handleGagal(result) {
            if (typeof KadoelAjax !== 'undefined') {
                KadoelAjax.handleError(result);
                return;
            }

            notifikasi('danger', 'right', result?.pesan || 'Terjadi kesalahan pada sistem');
        }

        function resetFormLupa() {
            $('#pegawai_id').val('').trigger('change');
            $('#tanggal').val(ambilTanggalFilter());
            $('#jam_datang').val('');
            $('#jam_pulang').val('');
            $('#catatan_admin').val('');
            clearErrors('tambah');
        }

        function formatJamUntukInput(value) {
            if (!value) return '';
            value = String(value);
            if (value.length >= 16 && value.includes(' ')) {
                return value.substring(11, 16);
            }
            if (value.length >= 5) {
                return value.substring(0, 5);
            }
            return '';
        }

        function refreshRingkasan() {
            $('#block-ringkasan').LoadingOverlay('show');

            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/presensi/ringkasan') ?>',
                dataType: 'JSON',
                data: {
                    [csrfToken]: csrfHash,
                    tanggal: ambilTanggalFilter()
                },
                success: function(result) {
                    $('#block-ringkasan').LoadingOverlay('hide');
                    console.log(result);
                    if (result.sukses) {
                        const r = result.ringkasan || {};

                        $('#ringkasan-total-jadwal').text(r.total_jadwal || 0);
                        $('#ringkasan-total-presensi').text(r.total_presensi || 0);
                        $('#ringkasan-belum-sinkron').text(r.belum_sinkron || 0);

                        $('#ringkasan-hadir').text(r.hadir || 0);
                        $('#ringkasan-alpa').text(r.alpa || 0);
                        $('#ringkasan-izin').text(r.izin || 0);
                        $('#ringkasan-sakit').text(r.sakit || 0);
                        $('#ringkasan-libur').text(r.libur || 0);

                        $('#ringkasan-tepat-waktu-datang').text(r.tepat_waktu_datang || 0);
                        $('#ringkasan-telat').text(r.telat || 0);
                        $('#ringkasan-tepat-waktu-pulang').text(r.tepat_waktu_pulang || 0);
                        $('#ringkasan-pulang-cepat').text(r.pulang_cepat || 0);
                    } else {
                        handleGagal(result);
                    }
                },
                error: function(xhr) {
                    $('#block-ringkasan').LoadingOverlay('hide');
                    if (xhr.status == 403) {
                        notifikasi('info', 'right', 'Token Kadaluarsa, Silahkan Reload Halaman Terlebih Dahulu');
                    }
                }
            });
        }

        let data_presensi = $('#presensi-tabel').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            searching: true,
            paging: true,
            info: true,
            pagingType: 'full_numbers',
            responsive: false, // ❗ WAJIB false
            scrollX: true, // ❗ INI KUNCI
            language: {
                url: '<?= base_url("assets/plugins/DataTablesbs5/plugins/id.json"); ?>'
            },
            ajax: {
                url: '<?= base_url("admin/presensi"); ?>',
                method: 'POST',
                data: function(d) {
                    d[csrfToken] = csrfHash;
                    d.tanggal = ambilTanggalFilter();
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
                    data: 'tanggal'
                },
                {
                    data: 'kode_pegawai'
                },
                {
                    data: 'nama_pegawai'
                },
                {
                    data: 'nama_shift'
                },
                {
                    data: 'jam_datang'
                },
                {
                    data: 'status_datang'
                },
                {
                    data: 'jam_pulang'
                },
                {
                    data: 'status_pulang'
                },
                {
                    data: 'hasil_presensi'
                },
                {
                    data: 'sumber_presensi'
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
                    targets: [0, 12],
                    orderable: false
                },
                {
                    targets: [0, 12],
                    searchable: false
                },
                {
                    targets: [0, 7, 9, 10, 11, 12],
                    className: 'text-center'
                },
                {
                    targets: [0, 12],
                    width: '8%'
                }
            ]
        });

        let maxHadir = 0;
        let maxAlpa = 0;

        let data_rekap = $('#rekap-tabel').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            searching: true,
            paging: true,
            info: true,
            scrollX: true,
            order: [
                [1, 'asc']
            ],
            language: {
                url: '<?= base_url("assets/plugins/DataTablesbs5/plugins/id.json"); ?>'
            },
            ajax: {
                url: '<?= base_url("admin/presensi/rekap-bulanan"); ?>',
                method: 'POST',
                data: function(d) {
                    d[csrfToken] = csrfHash;
                    d.bulan = $('#filter-bulan').val();
                },
                dataSrc: function(json) {
                    maxHadir = 0;
                    maxAlpa = 0;

                    (json.data || []).forEach(function(item) {
                        maxHadir = Math.max(maxHadir, parseInt(item.hadir || 0));
                        maxAlpa = Math.max(maxAlpa, parseInt(item.alpa || 0));
                    });

                    return json.data;
                }
            },
            columns: [{
                    data: '#'
                },
                {
                    data: 'nama_pegawai'
                },
                {
                    data: 'hadir'
                },
                {
                    data: 'izin'
                },
                {
                    data: 'sakit'
                },
                {
                    data: 'libur'
                },
                {
                    data: 'alpa'
                }
            ],
            columnDefs: [{
                    targets: [0],
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                {
                    targets: [2, 3, 4, 5, 6],
                    className: 'text-center'
                }
            ],
            createdRow: function(row, data) {
                const hadir = parseInt(data.hadir || 0);
                const alpa = parseInt(data.alpa || 0);

                $(row).removeClass('row-hadir-terbanyak row-alpa-terbanyak');

                if (alpa > 0 && alpa === maxAlpa) {
                    $(row).addClass('row-alpa-terbanyak');
                    return;
                }

                if (hadir > 0 && hadir === maxHadir) {
                    $(row).addClass('row-hadir-terbanyak');
                }
            }
        });

        $('#filter-bulan').on('change', function() {
            data_rekap.ajax.reload();
        });

        if (typeof flatpickr !== 'undefined') {
            flatpickr('#filter-tanggal', {
                dateFormat: 'Y-m-d',
                maxDate: 'today',
                onChange: function() {
                    data_presensi.ajax.reload();
                    refreshRingkasan();
                }
            });

            flatpickr('#tanggal', {
                dateFormat: 'Y-m-d',
                maxDate: 'today'
            });
        }

        $('#btn-lupa-presensi').on('click', function() {
            resetFormLupa();
            $('#modal-lupa-presensi').modal('show');
        });

        $('#tutup-modal-lupa').on('click', function() {
            $('#modal-lupa-presensi').modal('hide');
        });

        $('#form-lupa-presensi').on('submit', function(e) {
            e.preventDefault();

            $('#simpan-lupa').prop('disabled', true);
            clearErrors('tambah');
            $('#block-content-lupa').LoadingOverlay('show');

            let fd = new FormData(this);
            fd.append(csrfToken, csrfHash);

            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/presensi/lupa/simpan') ?>',
                dataType: 'JSON',
                data: fd,
                cache: false,
                contentType: false,
                processData: false,
                success: function(result) {
                    $('#block-content-lupa').LoadingOverlay('hide');
                    $('#simpan-lupa').prop('disabled', false);

                    if (result.sukses) {
                        $('#modal-lupa-presensi').modal('hide');
                        notifikasi('success', 'right', result.pesan);
                        data_presensi.ajax.reload();
                        refreshRingkasan();
                    } else {
                        handleGagal(result);
                    }
                },
                error: function(xhr) {
                    $('#block-content-lupa').LoadingOverlay('hide');
                    $('#simpan-lupa').prop('disabled', false);
                    if (xhr.status == 403) {
                        notifikasi('info', 'right', 'Token Kadaluarsa, Silahkan Reload Halaman Terlebih Dahulu');
                    }
                }
            });
        });

        $('#btn-sinkron-presensi').on('click', function() {
            let tanggal = ambilTanggalFilter();

            Swal.fire({
                title: 'PRESENSI',
                html: 'Sinkron presensi untuk tanggal <b>' + tanggal + '</b>?',
                imageUrl: '<?= base_url('assets/media/favicons/apple-touch-icon-180x180.png') ?>',
                imageWidth: 128,
                imageHeight: 128,
                showCancelButton: true,
                confirmButtonColor: '#65A30D',
                cancelButtonColor: '#d33',
                confirmButtonText: '<i class="fa fa-check"></i> Sinkron',
                cancelButtonText: '<i class="fas fa-times"></i> Batal',
                allowEscapeKey: false,
                allowOutsideClick: false,
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#block-presensi').LoadingOverlay('show');

                    $.ajax({
                        type: 'POST',
                        url: '<?= base_url('admin/presensi/sinkron') ?>',
                        dataType: 'JSON',
                        data: {
                            [csrfToken]: csrfHash,
                            tanggal: tanggal
                        },
                        success: function(result) {
                            $('#block-presensi').LoadingOverlay('hide');

                            if (result.sukses) {
                                notifikasi('success', 'right', result.pesan);
                                data_presensi.ajax.reload();
                                data_rekap.ajax.reload();
                                refreshRingkasan();
                            } else {
                                handleGagal(result);
                            }
                        },
                        error: function(xhr) {
                            $('#block-presensi').LoadingOverlay('hide');
                            if (xhr.status == 403) {
                                notifikasi('info', 'right', 'Silahkan Reload Halaman Terlebih Dahulu');
                            }
                        }
                    });
                }
            });
        });

        function resetDetail() {
            $('#detail-kode_pegawai').text('-');
            $('#detail-nama_pegawai').text('-');
            $('#detail-jenis_kelamin').text('-');
            $('#detail-no_hp').text('-');
            $('#detail-alamat').text('-');
            $('#detail-tanggal').text('-');
            $('#detail-shift').text('-');
            $('#detail-jam_datang').text('-');
            $('#detail-status_datang').text('-');
            $('#detail-jam_pulang').text('-');
            $('#detail-status_pulang').text('-');
            $('#detail-menit_telat').text('-');
            $('#detail-menit_pulang_cepat').text('-');
            $('#detail-sumber_presensi').text('-');
            $('#detail-catatan_admin').text('-');
            $('#detail-hasil_presensi').text('-');
        }

        $('#presensi-tabel').on('click', '#act-detail', function() {
            let id = $(this).data('id');

            resetDetail();
            $('#block-content-detail').LoadingOverlay('show');

            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/presensi/detail') ?>',
                dataType: 'JSON',
                data: {
                    [csrfToken]: csrfHash,
                    id: id
                },
                success: function(result) {
                    $('#block-content-detail').LoadingOverlay('hide');

                    if (result.sukses) {
                        const p = result.presensi || {};

                        $('#detail-kode_pegawai').text(p.kode_pegawai || '-');
                        $('#detail-nama_pegawai').text(p.nama_pegawai || '-');
                        $('#detail-jenis_kelamin').text(p.jenis_kelamin === 'L' ? 'Laki-Laki' : (p.jenis_kelamin === 'P' ? 'Perempuan' : '-'));
                        $('#detail-no_hp').text(p.no_hp || '-');
                        $('#detail-alamat').text(p.alamat || '-');
                        $('#detail-tanggal').text(p.tanggal || '-');
                        $('#detail-shift').text(p.nama_shift || '-');
                        $('#detail-jam_datang').text(p.jam_datang || '-');
                        $('#detail-status_datang').text(p.status_datang || '-');
                        $('#detail-jam_pulang').text(p.jam_pulang || '-');
                        $('#detail-status_pulang').text(p.status_pulang || '-');
                        $('#detail-hasil_presensi').text(p.hasil_presensi || '-');
                        $('#detail-menit_telat').text(p.menit_telat ?? 0);
                        $('#detail-menit_pulang_cepat').text(p.menit_pulang_cepat ?? 0);
                        $('#detail-sumber_presensi').text(p.sumber_presensi || '-');
                        $('#detail-catatan_admin').text(p.catatan_admin || '-');

                        $('#modal-detail').modal('show');
                    } else {
                        handleGagal(result);
                    }
                },
                error: function(xhr) {
                    $('#block-content-detail').LoadingOverlay('hide');
                    if (xhr.status == 403) {
                        notifikasi('info', 'right', 'Token Kadaluarsa, Silahkan Reload Halaman Terlebih Dahulu');
                    }
                }
            });
        });

        $('#presensi-tabel').on('click', '#act-edit-lupa', function() {
            let id = $(this).data('id');

            clearErrors('edit');
            $('#block-content-edit-lupa').LoadingOverlay('show');

            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/presensi/detail') ?>',
                dataType: 'JSON',
                data: {
                    [csrfToken]: csrfHash,
                    id: id
                },
                success: function(result) {
                    $('#block-content-edit-lupa').LoadingOverlay('hide');

                    if (result.sukses) {
                        const p = result.presensi || {};

                        $('#edit-id').val(p.id);
                        $('#edit-pegawai').val((p.nama_pegawai || '-'));
                        $('#edit-tanggal').val(p.tanggal || '-');
                        $('#edit-jam_datang').val(formatJamUntukInput(p.jam_datang));
                        $('#edit-jam_pulang').val(formatJamUntukInput(p.jam_pulang));
                        $('#edit-catatan_admin').val(p.catatan_admin || '');

                        $('#modal-edit-lupa').modal('show');
                    } else {
                        handleGagal(result);
                    }
                },
                error: function(xhr) {
                    $('#block-content-edit-lupa').LoadingOverlay('hide');
                    if (xhr.status == 403) {
                        notifikasi('info', 'right', 'Token Kadaluarsa, Silahkan Reload Halaman Terlebih Dahulu');
                    }
                }
            });
        });

        $('#tutup-modal-edit-lupa').on('click', function() {
            $('#modal-edit-lupa').modal('hide');
        });

        $('#form-edit-lupa').on('submit', function(e) {
            e.preventDefault();

            const id = $('#edit-id').val();
            $('#update-lupa').prop('disabled', true);
            clearErrors('edit');
            $('#block-content-edit-lupa').LoadingOverlay('show');

            let fd = new FormData(this);
            fd.append(csrfToken, csrfHash);

            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/presensi/lupa/update') ?>' + '/' + id,
                dataType: 'JSON',
                data: fd,
                cache: false,
                contentType: false,
                processData: false,
                success: function(result) {
                    $('#block-content-edit-lupa').LoadingOverlay('hide');
                    $('#update-lupa').prop('disabled', false);

                    if (result.sukses) {
                        $('#modal-edit-lupa').modal('hide');
                        notifikasi('success', 'right', result.pesan);
                        data_presensi.ajax.reload();
                        refreshRingkasan();
                    } else {
                        handleGagal(result);
                    }
                },
                error: function(xhr) {
                    $('#block-content-edit-lupa').LoadingOverlay('hide');
                    $('#update-lupa').prop('disabled', false);
                    if (xhr.status == 403) {
                        notifikasi('info', 'right', 'Token Kadaluarsa, Silahkan Reload Halaman Terlebih Dahulu');
                    }
                }
            });
        });

        $('#presensi-tabel').on('click', '#act-delete-lupa', function() {
            let id = $(this).data('id');
            let nama = $(this).data('nama');

            Swal.fire({
                title: 'PRESENSI',
                html: 'Hapus lupa presensi <b>' + nama + '</b>?',
                imageUrl: '<?= base_url('assets/media/favicons/apple-touch-icon-180x180.png') ?>',
                imageWidth: 128,
                imageHeight: 128,
                showCancelButton: true,
                confirmButtonColor: '#65A30D',
                cancelButtonColor: '#d33',
                confirmButtonText: '<i class="fa fa-trash-can"></i> Hapus',
                cancelButtonText: '<i class="fas fa-times"></i> Batal',
                allowEscapeKey: false,
                allowOutsideClick: false,
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#block-presensi').LoadingOverlay('show');

                    $.ajax({
                        type: 'POST',
                        url: '<?= base_url('admin/presensi/lupa/delete') ?>',
                        dataType: 'JSON',
                        data: {
                            [csrfToken]: csrfHash,
                            id: id
                        },
                        success: function(result) {
                            $('#block-presensi').LoadingOverlay('hide');

                            if (result.sukses) {
                                notifikasi('success', 'right', result.pesan);
                                data_presensi.ajax.reload();
                                refreshRingkasan();
                            } else {
                                handleGagal(result);
                            }
                        },
                        error: function(xhr) {
                            $('#block-presensi').LoadingOverlay('hide');
                            if (xhr.status == 403) {
                                notifikasi('info', 'right', 'Silahkan Reload Halaman Terlebih Dahulu');
                            }
                        }
                    });
                }
            });
        });

        refreshRingkasan();
    });
</script>