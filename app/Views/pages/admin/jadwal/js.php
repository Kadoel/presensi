<script src="/assets/plugins/select2/js/select2.full.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        let csrfToken = '<?= csrf_token(); ?>';
        let csrfHash = '<?= csrf_hash(); ?>';

        <?= loadingoverlay_fa(); ?>
        <?= notifikasi(); ?>

        $('.section-pegawai').select2({
            placeholder: '-- Pilih Pegawai --',
            allowClear: true,
            width: '100%'
        });

        <?= select2_modal('edit-pegawai_id', 'modal-ubah'); ?>
        <?= select2_modal('edit-status_hari', 'modal-ubah'); ?>
        <?= select2_modal('edit-shift_id', 'modal-ubah'); ?>

        <?= select2_modal('copy-pegawai_sumber_id', 'modal-copy-jadwal'); ?>
        <?= select2_modal('copy-pegawai_tujuan_id', 'modal-copy-jadwal'); ?>

        <?= select2_modal('individu-pegawai_id', 'modal-individu-jadwal'); ?>
        <?= select2_modal('individu-status_hari', 'modal-individu-jadwal'); ?>
        <?= select2_modal('individu-shift_id', 'modal-individu-jadwal'); ?>

        function toggleShiftEdit() {
            let status = $('#edit-status_hari').val();

            if (status === 'kerja') {
                $('#wrap-edit-shift_id').show();
            } else {
                $('#wrap-edit-shift_id').hide();
                $('#edit-shift_id').val('').trigger('change');
            }
        }

        $('#edit-status_hari').on('change', function() {
            toggleShiftEdit();
        });

        function selectedPegawaiValues() {
            let selected = [];

            $('.section-pegawai').each(function() {
                const values = $(this).val() || [];
                selected = selected.concat(values.map(String));
            });

            return selected;
        }

        function syncDisabledPegawaiOptions() {
            const selected = selectedPegawaiValues();

            $('.section-pegawai').each(function() {
                const currentSelect = $(this);
                const currentValues = (currentSelect.val() || []).map(String);

                currentSelect.find('option').each(function() {
                    const optionValue = String($(this).attr('value'));
                    const selectedInOtherSection = selected.includes(optionValue) && !currentValues.includes(optionValue);

                    $(this).prop('disabled', selectedInOtherSection);
                });

                currentSelect.trigger('change.select2');
            });
        }

        $('.section-pegawai').on('change', function() {
            syncDisabledPegawaiOptions();
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
                [4, 'asc']
            ],
            rowGroup: {
                dataSrc: ['bulan_jadwal', 'tanggal']
            },
            columnDefs: [{
                    targets: [1, 2, 4],
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
            const fields = ['tanggal', 'catatan'];

            fields.forEach(function(field) {
                $('#' + field).removeClass('is-invalid');
                $('#error-' + field).html('').hide();
            });

            $('#error-pegawai').addClass('d-none').html('');
            $('#error-shift_pegawai').addClass('d-none').html('');
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

        function tampilkanErrorGenerate(result) {
            const errors = result.errors || {};

            if (errors.tanggal) {
                $('#tanggal').addClass('is-invalid');
                $('#error-tanggal').html(errors.tanggal).show();
            }

            if (errors.catatan) {
                $('#catatan').addClass('is-invalid');
                $('#error-catatan').html(errors.catatan).show();
            }

            if (errors.pegawai) {
                $('#error-pegawai').removeClass('d-none').html(errors.pegawai);
            }

            if (errors.shift_pegawai) {
                $('#error-shift_pegawai').removeClass('d-none').html(errors.shift_pegawai);
            }

            if (!Object.keys(errors).length && result.pesan) {
                notifikasi('danger', 'right', result.pesan);
            }
        }

        function resetGenerateForm() {
            $('#tanggal').val('');
            $('#catatan').val('');
            $('.section-pegawai').val([]).trigger('change');

            const el = document.querySelector('#tanggal');
            if (el && el._flatpickr) {
                el._flatpickr.clear();
            }

            clear_errors_tambah();
            syncDisabledPegawaiOptions();
        }

        $('#reset-generate').on('click', function() {
            resetGenerateForm();
        });

        $('#form_tambah_jadwal').on('submit', function(e) {
            e.preventDefault();

            $('#block-konten-tambah').LoadingOverlay('show');
            clear_errors_tambah();

            let fd = new FormData(this);
            fd.append(csrfToken, csrfHash);

            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/jadwal/simpan') ?>',
                dataType: 'JSON',
                cache: false,
                contentType: false,
                processData: false,
                data: fd,
                success: function(result) {
                    $('#block-konten-tambah').LoadingOverlay('hide');

                    if (result.sukses) {
                        resetGenerateForm();
                        notifikasi('success', 'right', result.pesan);
                        data_jadwal.ajax.reload();

                        if (result.warning_hari_libur && result.hari_libur && result.hari_libur.length) {
                            let infoLibur = result.hari_libur.map(function(item) {
                                return `<li><b>${KadoelHelper.toTanggalIndonesia(item.tanggal)}</b> - ${item.nama_libur}</li>`;
                            }).join('');

                            Swal.fire({
                                title: 'Info Hari Libur Global',
                                html: `
                                    <p>Jadwal berhasil digenerate, tetapi tanggal berikut merupakan <b>hari libur global</b>:</p>
                                    <ul style="text-align:left;">${infoLibur}</ul>
                                    <p>Pastikan jadwal yang dibuat memang sesuai kebutuhan operasional. Jika ingin dioverride ke libur global
                                    Silahkan buka <b>menu Hari Libur</b>, edit hari libur dengan tanggal <b>${infoLibur}</b> untuk menetapkan apakah pegawai tetap <b>kerja</b> atau <b>libur</b>. pada tanggal di atas</p>
                                `,
                                icon: 'warning',
                                confirmButtonText: 'Mengerti'
                            });
                        }
                    } else {
                        tampilkanErrorGenerate(result);
                    }
                },
                error: function(xhr) {
                    $('#block-konten-tambah').LoadingOverlay('hide');

                    if (xhr.status == 403) {
                        notifikasi('info', 'right', 'Token Kadaluarsa, Silahkan Reload Halaman Terlebih Dahulu');
                    } else {
                        notifikasi('danger', 'right', 'Gagal generate jadwal kerja');
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

            $('#block-content-ubah').LoadingOverlay('show');
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
                    $('#block-content-ubah').LoadingOverlay('hide');

                    if (result.sukses) {
                        const jadwal = result.jadwal || {};
                        const hariLibur = result.hari_libur || null;

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
                error: function(xhr) {
                    $('#block-content-ubah').LoadingOverlay('hide');

                    if (xhr.status == 403) {
                        notifikasi('info', 'right', 'Token Kadaluarsa, Silahkan Reload Halaman Terlebih Dahulu');
                    } else {
                        notifikasi('danger', 'right', 'Gagal mengambil data jadwal kerja');
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
            e.preventDefault();

            $('#update-data').prop('disabled', true);
            const id = $('#edit-id').val();

            clear_errors_edit();
            $('#block-content-ubah').LoadingOverlay('show');

            let fd = new FormData(this);
            fd.append(csrfToken, csrfHash);

            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/jadwal/update') ?>/' + id,
                dataType: 'JSON',
                cache: false,
                contentType: false,
                processData: false,
                data: fd,
                success: function(result) {
                    $('#block-content-ubah').LoadingOverlay('hide');

                    if (result.sukses) {
                        tutup_modal();
                        clear_errors_edit();
                        notifikasi('success', 'right', result.pesan);
                        data_jadwal.ajax.reload();

                        if (result.warning_hari_libur && result.hari_libur && result.status_hari_kerja && result.hari_libur.length) {
                            let infoLibur = result.hari_libur.map(function(item) {
                                return `<b>${KadoelHelper.toTanggalIndonesia(item.tanggal)}</b> - ${item.nama_libur}`;
                            }).join('');

                            Swal.fire({
                                title: 'Info Hari Libur Global',
                                html: `
                                        <div class="text-start">
                                            <p>Jadwal berhasil diubah, tetapi tanggal <b>${infoLibur}</b> merupakan <b>hari libur global</b>, jadi pastikan jadwal sesuai kebutuhan operasional. Jika ingin dioverride ke libur global:</p>

                                            <ul>
                                                <li>Buka <b>menu Hari Libur</b></li>
                                                <li>Edit hari libur pada tanggal <b>${infoLibur}</b></li>
                                                <li>Atur apakah pegawai tetap <b>kerja</b> atau <b>libur</b> dengan mencentang / tidak mencentang daftar pegawai</li>
                                            </ul>

                                            <p></p>
                                        </div>
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
                error: function(xhr) {
                    $('#block-content-ubah').LoadingOverlay('hide');
                    $('#update-data').prop('disabled', false);

                    if (xhr.status == 403) {
                        notifikasi('info', 'right', 'Token Kadaluarsa, Silahkan Reload Halaman Terlebih Dahulu');
                    } else {
                        notifikasi('danger', 'right', 'Gagal mengubah jadwal kerja');
                    }
                }
            });
        });

        function clear_errors_copy() {
            const fields = [
                'copy-pegawai_sumber_id',
                'copy-pegawai_tujuan_id',
                'copy-tanggal_mulai',
                'copy-tanggal_selesai',
                'copy-catatan'
            ];

            fields.forEach(function(field) {
                $('#' + field).removeClass('is-invalid');
                $('#error-' + field).html('').hide();
            });
        }

        function resetCopyJadwal() {
            $('#copy-pegawai_sumber_id').val('').trigger('change');
            $('#copy-pegawai_tujuan_id').val('').trigger('change');
            $('#copy-tanggal_mulai').val('');
            $('#copy-tanggal_selesai').val('');
            $('#copy-catatan').val('');

            if (document.querySelector('#copy-tanggal_mulai')?._flatpickr) {
                document.querySelector('#copy-tanggal_mulai')._flatpickr.clear();
            }

            if (document.querySelector('#copy-tanggal_selesai')?._flatpickr) {
                document.querySelector('#copy-tanggal_selesai')._flatpickr.clear();
            }

            clear_errors_copy();
        }

        $('#btn-copy-jadwal').on('click', function() {
            resetCopyJadwal();
            $('#modal-copy-jadwal').modal('show');
        });

        $('#tutup-modal-copy-jadwal').on('click', function() {
            $('#modal-copy-jadwal').modal('hide');
        });

        $('#form-copy-jadwal').on('submit', function(e) {
            e.preventDefault();

            $('#submit-copy-jadwal').prop('disabled', true);
            $('#block-content-copy-jadwal').LoadingOverlay('show');
            clear_errors_copy();

            let fd = new FormData(this);
            fd.append(csrfToken, csrfHash);

            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/jadwal/copy') ?>',
                dataType: 'JSON',
                data: fd,
                cache: false,
                contentType: false,
                processData: false,
                success: function(result) {
                    $('#block-content-copy-jadwal').LoadingOverlay('hide');
                    $('#submit-copy-jadwal').prop('disabled', false);

                    if (result.sukses) {
                        $('#modal-copy-jadwal').modal('hide');
                        notifikasi('success', 'right', result.pesan);

                        if (typeof data_jadwal !== 'undefined') {
                            data_jadwal.ajax.reload();
                        }
                    } else {
                        KadoelAjax.handleError(result, {
                            clearFields: [
                                'copy-pegawai_sumber_id',
                                'copy-pegawai_tujuan_id',
                                'copy-tanggal_mulai',
                                'copy-tanggal_selesai',
                                'copy-catatan'
                            ]
                        });
                    }
                },
                error: function(xhr) {
                    $('#block-content-copy-jadwal').LoadingOverlay('hide');
                    $('#submit-copy-jadwal').prop('disabled', false);

                    if (xhr.status == 403) {
                        notifikasi('info', 'right', 'Token Kadaluarsa, Silahkan Reload Halaman Terlebih Dahulu');
                        return;
                    }

                    const res = xhr.responseJSON;
                    KadoelAjax.handleError(res || {
                        pesan: 'Terjadi kesalahan server'
                    });
                }
            });
        });

        function clear_errors_individu() {
            const fields = [
                'individu-pegawai_id',
                'individu-tanggal',
                'individu-status_hari',
                'individu-shift_id',
                'individu-catatan'
            ];

            fields.forEach(function(field) {
                $('#' + field).removeClass('is-invalid');
                $('#error-' + field).html('').hide();
            });
        }

        function toggleShiftIndividu() {
            const status = $('#individu-status_hari').val();

            if (status === 'kerja') {
                $('#wrap-individu-shift_id').show();
            } else {
                $('#wrap-individu-shift_id').hide();
                $('#individu-shift_id').val('').trigger('change');
            }
        }

        $('#individu-status_hari').on('change', function() {
            toggleShiftIndividu();
        });

        function resetIndividuJadwal() {
            $('#individu-pegawai_id').val('').trigger('change');
            $('#individu-tanggal').val('');
            $('#individu-status_hari').val('').trigger('change');
            $('#individu-shift_id').val('').trigger('change');
            $('#individu-catatan').val('');

            const el = document.querySelector('#individu-tanggal');
            if (el && el._flatpickr) {
                el._flatpickr.clear();
            }

            toggleShiftIndividu();
            clear_errors_individu();
        }

        $('#btn-individu-jadwal').on('click', function() {
            resetIndividuJadwal();
            $('#modal-individu-jadwal').modal('show');
        });

        $('#tutup-modal-individu-jadwal').on('click', function() {
            $('#modal-individu-jadwal').modal('hide');
        });

        $('#form-individu-jadwal').on('submit', function(e) {
            e.preventDefault();

            $('#submit-individu-jadwal').prop('disabled', true);
            $('#block-content-individu-jadwal').LoadingOverlay('show');
            clear_errors_individu();

            let fd = new FormData(this);
            fd.append(csrfToken, csrfHash);

            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/jadwal/individu') ?>',
                dataType: 'JSON',
                data: fd,
                cache: false,
                contentType: false,
                processData: false,
                success: function(result) {
                    $('#block-content-individu-jadwal').LoadingOverlay('hide');
                    $('#submit-individu-jadwal').prop('disabled', false);

                    if (result.sukses) {
                        $('#modal-individu-jadwal').modal('hide');
                        notifikasi('success', 'right', result.pesan);

                        if (typeof data_jadwal !== 'undefined') {
                            data_jadwal.ajax.reload();
                        }
                    } else {
                        KadoelAjax.handleError(result, {
                            clearFields: [
                                'individu-pegawai_id',
                                'individu-tanggal',
                                'individu-status_hari',
                                'individu-shift_id',
                                'individu-catatan'
                            ]
                        });
                    }
                },
                error: function(xhr) {
                    $('#block-content-individu-jadwal').LoadingOverlay('hide');
                    $('#submit-individu-jadwal').prop('disabled', false);

                    if (xhr.status == 403) {
                        notifikasi('info', 'right', 'Token Kadaluarsa, Silahkan Reload Halaman Terlebih Dahulu');
                        return;
                    }

                    const res = xhr.responseJSON;
                    KadoelAjax.handleError(res || {
                        pesan: 'Terjadi kesalahan server'
                    });
                }
            });
        });

        toggleShiftEdit();
        syncDisabledPegawaiOptions();
        toggleShiftIndividu();
    });
</script>
<script>
    Codebase.helpersOnLoad(['jq-select2', 'js-flatpickr']);

    const minTanggal = '<?= date('Y-m-d'); ?>';

    document.addEventListener('DOMContentLoaded', function() {
        if (typeof flatpickr !== 'undefined') {
            flatpickr('#tanggal', {
                dateFormat: 'Y-m-d',
                mode: 'multiple',
                minDate: minTanggal
            });

            flatpickr('#edit-tanggal', {
                dateFormat: 'Y-m-d',
                minDate: minTanggal,
                clickOpens: false,
                allowInput: false
            });

            flatpickr('#copy-tanggal_mulai', {
                dateFormat: 'Y-m-d',
                minDate: minTanggal
            });

            flatpickr('#copy-tanggal_selesai', {
                dateFormat: 'Y-m-d',
                minDate: minTanggal
            });

            flatpickr('#individu-tanggal', {
                dateFormat: 'Y-m-d',
                mode: 'multiple',
                minDate: minTanggal
            });
        }
    });
</script>