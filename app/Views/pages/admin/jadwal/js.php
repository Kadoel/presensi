<script src="/assets/plugins/select2/js/select2.full.min.js"></script>
<script src="/assets/plugins/fullcalender/index.global.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        // =========================================================
        // GLOBAL CONFIG & HELPERS FROM TEMPLATE
        // =========================================================
        let csrfToken = '<?= csrf_token(); ?>';
        let csrfHash = '<?= csrf_hash(); ?>';
        let calendarJadwal = null;

        <?= loadingoverlay_fa(); ?>
        <?= notifikasi(); ?>

        // =========================================================
        // INIT SELECT2
        // =========================================================
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

        // =========================================================
        // HELPER: GENERAL UI
        // =========================================================
        function escapeHtml(value) {
            return $('<div>').text(value).html();
        }

        function badgeStatusHari(status) {
            const map = {
                kerja: '<span class="badge bg-success">Kerja</span>',
                libur: '<span class="badge bg-danger">Libur</span>',
                izin: '<span class="badge bg-warning text-dark">Izin</span>',
                sakit: '<span class="badge bg-info text-dark">Sakit</span>',
                cuti: '<span class="badge bg-danger">Cuti</span>',
            };

            return map[status] || '<span class="badge bg-secondary">-</span>';
        }

        function badgeSumberData(sumber) {
            const map = {
                manual: '<span class="badge bg-primary">Manual</span>',
                pengajuan_izin: '<span class="badge bg-dark">Pengajuan Izin</span>',
                hari_libur: '<span class="badge bg-danger">Hari Libur</span>'
            };

            return map[sumber] || '<span class="badge bg-secondary">-</span>';
        }

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

        // =========================================================
        // HELPER: FILTER BULAN + SYNC DATATABLE & CALENDAR
        // =========================================================
        function ambilBulanJadwal() {
            return $('#filter-bulan-jadwal').val() || '<?= date('Y-m'); ?>';
        }

        function setFilterBulanDariTanggal(tanggal) {
            if (!tanggal || tanggal.length < 7) return;

            const bulan = tanggal.substring(0, 7);

            $('#filter-bulan-jadwal').val(bulan);

            if (typeof data_jadwal !== 'undefined') {
                data_jadwal.ajax.reload();
            }

            syncKalenderDenganFilterBulan();
        }

        function updateBulanDariField(selector) {
            const val = $(selector).val();

            if (!val) return;

            const tanggalPertama = val.split(',')[0].trim();

            setFilterBulanDariTanggal(tanggalPertama);
        }

        // =========================================================
        // HELPER: GENERATE FORM - PEGAWAI UNIQUE PER SECTION
        // =========================================================
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

        // =========================================================
        // HELPER: TOGGLE SHIFT FIELD
        // =========================================================
        function toggleShiftEdit() {
            let status = $('#edit-status_hari').val();

            if (status === 'kerja') {
                $('#wrap-edit-shift_id').show();
            } else {
                $('#wrap-edit-shift_id').hide();
                $('#edit-shift_id').val('').trigger('change');
            }
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

        // =========================================================
        // HELPER: CLEAR ERRORS
        // =========================================================
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

        // =========================================================
        // HELPER: RESET FORMS
        // =========================================================
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

        // =========================================================
        // DATATABLE: JADWAL KERJA
        // =========================================================
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
                data: function(d) {
                    d[csrfToken] = csrfHash;
                    d.bulan = ambilBulanJadwal();
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

        // =========================================================
        // FULLCALENDAR: KALENDER JADWAL KERJA
        // =========================================================
        const calendarEl = document.getElementById('kalender-jadwal');

        if (calendarEl && typeof FullCalendar !== 'undefined') {
            calendarJadwal = new FullCalendar.Calendar(calendarEl, {
                initialView: 'multiMonthYear',
                height: 'auto',
                locale: 'id',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'multiMonthYear,dayGridMonth,listMonth'
                },
                buttonText: {
                    today: 'Hari Ini',
                    month: 'Bulan',
                    year: 'Tahun',
                    list: 'Daftar'
                },
                views: {
                    dayGridMonth: {
                        buttonText: 'Bulan'
                    },
                    multiMonthYear: {
                        buttonText: 'Tahun',
                        multiMonthMaxColumns: 3
                    },
                    listMonth: {
                        buttonText: 'Daftar'
                    }
                },
                events: '<?= base_url('admin/jadwal/kalender'); ?>',
                eventClick: function(info) {
                    const tanggal = info.event.extendedProps.tanggal || info.event.startStr;
                    loadDetailTanggal(tanggal);
                },
                dateClick: function(info) {
                    loadDetailTanggal(info.dateStr);
                },
                eventContent: function(arg) {
                    const p = arg.event.extendedProps;
                    let warning = '';

                    if (p.bermasalah) {
                        warning = `
                            <div class="fw-bold">
                                ⚠ ${p.kurang_jadwal > 0 ? 'Kurang ' + p.kurang_jadwal : 'Lebih ' + p.lebih_jadwal}
                            </div>
                        `;
                    }

                    return {
                        html: `
                            <div class="fc-jadwal-detail">
                                <div><b>${p.total_jadwal || 0}</b> / ${p.total_pegawai_aktif || 0}</div>
                                <div>K:${p.total_kerja || 0} L:${p.total_libur || 0} S:${p.total_sakit || 0} I:${p.total_izin || 0} L:${p.total_cuti || 0}</div>
                                ${warning}
                            </div>
                        `
                    };
                },
                datesSet: function() {
                    highlightBulanFilter();
                },
            });

            calendarJadwal.render();

            window.reloadKalenderJadwal = function() {
                calendarJadwal.refetchEvents();
                highlightBulanFilter();
            };
        }

        function highlightBulanFilter() {
            const bulan = $('#filter-bulan-jadwal').val();

            if (!bulan) {
                return;
            }

            setTimeout(function() {
                $('.fc-daygrid-day').removeClass('fc-bulan-filter-aktif');
                $('.fc-daygrid-day[data-date^="' + bulan + '"]').addClass('fc-bulan-filter-aktif');
            }, 100);
        }

        function syncKalenderDenganFilterBulan() {
            const bulan = $('#filter-bulan-jadwal').val();

            if (!bulan || !calendarJadwal) {
                return;
            }

            calendarJadwal.gotoDate(bulan + '-01');
            highlightBulanFilter();
        }

        function loadDetailTanggal(tanggal) {
            $('#detail-tanggal-title').text(KadoelHelper.toTanggalIndonesia(tanggal));
            $('#detail-tanggal-body').html(`
                <tr>
                    <td colspan="6" class="text-center text-muted">Memuat data...</td>
                </tr>
            `);

            $('#modal-detail-tanggal').modal('show');
            $('#block-detail-tanggal').LoadingOverlay('show');

            $.ajax({
                type: 'GET',
                url: '<?= base_url('admin/jadwal/detail-tanggal'); ?>',
                dataType: 'JSON',
                data: {
                    tanggal: tanggal
                },
                success: function(result) {
                    $('#block-detail-tanggal').LoadingOverlay('hide');

                    if (!result.sukses) {
                        KadoelAjax.handleError(result);
                        return;
                    }

                    const items = result.items || [];
                    let html = '';

                    if (items.length < 1) {
                        html = `
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    Tidak ada jadwal pada tanggal ini
                                </td>
                            </tr>
                        `;
                    } else {
                        items.forEach(function(item) {
                            html += `
                                <tr>
                                    <td>${escapeHtml(item.kode_pegawai || '-')}</td>
                                    <td>${escapeHtml(item.nama_pegawai || '-')}</td>
                                    <td>${badgeStatusHari(item.status_hari)}</td>
                                    <td>${escapeHtml(item.nama_shift || '-')}</td>
                                    <td>${badgeSumberData(item.sumber_data)}</td>
                                    <td>${escapeHtml(item.catatan || '-')}</td>
                                </tr>
                            `;
                        });
                    }

                    $('#detail-tanggal-body').html(html);
                },
                error: function(xhr) {
                    $('#block-detail-tanggal').LoadingOverlay('hide');

                    if (xhr.status == 403) {
                        notifikasi('info', 'right', 'Token Kadaluarsa, Silahkan Reload Halaman Terlebih Dahulu');
                        return;
                    }

                    KadoelAjax.handleError(xhr.responseJSON || {
                        pesan: 'Gagal memuat detail jadwal'
                    });
                }
            });
        }

        // =========================================================
        // EVENT: GLOBAL FILTERS & FORM UI
        // =========================================================
        $('#filter-bulan-jadwal').on('change', function() {
            data_jadwal.ajax.reload();
            syncKalenderDenganFilterBulan();
        });

        $('.section-pegawai').on('change', function() {
            syncDisabledPegawaiOptions();
        });

        $('#edit-status_hari').on('change', function() {
            toggleShiftEdit();
        });

        $('#individu-status_hari').on('change', function() {
            toggleShiftIndividu();
        });

        $('#reset-generate').on('click', function() {
            resetGenerateForm();
        });

        // =========================================================
        // FORM: GENERATE JADWAL MASSAL
        // =========================================================
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
                        updateBulanDariField('#tanggal');
                        resetGenerateForm();
                        notifikasi('success', 'right', result.pesan);
                        data_jadwal.ajax.reload();

                        if (typeof window.reloadKalenderJadwal === 'function') {
                            window.reloadKalenderJadwal();
                        }

                        if (result.warning_hari_libur && result.hari_libur && result.hari_libur.length) {
                            let infoLibur = result.hari_libur.map(function(item) {
                                return `<li><b>${KadoelHelper.toTanggalIndonesia(item.tanggal)}</b> - ${item.nama_libur}</li>`;
                            }).join('');

                            Swal.fire({
                                title: 'Info Hari Libur Global',
                                html: `
                                    <div class="text-start">
                                        <p>Jadwal berhasil digenerate, tetapi tanggal berikut merupakan <b>hari libur global</b>:</p>
                                        <ul>${infoLibur}</ul>
                                        <p>Pastikan jadwal yang dibuat memang sesuai kebutuhan operasional.</p>
                                        <p>Jika ingin dioverride ke libur global, buka <b>menu Hari Libur</b> lalu edit hari libur pada tanggal terkait.</p>
                                    </div>
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

        // =========================================================
        // MODAL + FORM: EDIT JADWAL
        // =========================================================
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
                    $('#update-data').prop('disabled', false);

                    if (result.sukses) {
                        updateBulanDariField('#edit-tanggal');
                        tutup_modal();
                        clear_errors_edit();
                        notifikasi('success', 'right', result.pesan);
                        data_jadwal.ajax.reload();

                        if (typeof window.reloadKalenderJadwal === 'function') {
                            window.reloadKalenderJadwal();
                        }

                        if (result.warning_hari_libur && result.hari_libur && result.status_hari_kerja && result.hari_libur.length) {
                            let infoLibur = result.hari_libur.map(function(item) {
                                return `<b>${KadoelHelper.toTanggalIndonesia(item.tanggal)}</b> - ${item.nama_libur}`;
                            }).join('');

                            Swal.fire({
                                title: 'Info Hari Libur Global',
                                html: `
                                    <div class="text-start">
                                        <p>Jadwal berhasil diubah, tetapi tanggal <b>${infoLibur}</b> merupakan <b>hari libur global</b>.</p>
                                        <ul>
                                            <li>Buka <b>menu Hari Libur</b></li>
                                            <li>Edit hari libur pada tanggal <b>${infoLibur}</b></li>
                                            <li>Atur apakah pegawai tetap <b>kerja</b> atau <b>libur</b></li>
                                        </ul>
                                    </div>
                                `,
                                icon: 'warning',
                                confirmButtonText: 'Mengerti'
                            });
                        }
                    } else {
                        KadoelAjax.handleError(result);
                    }
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

        // =========================================================
        // MODAL + FORM: COPY JADWAL PEGAWAI
        // =========================================================
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
                        updateBulanDariField('#copy-tanggal_mulai');
                        $('#modal-copy-jadwal').modal('hide');
                        notifikasi('success', 'right', result.pesan);

                        if (typeof data_jadwal !== 'undefined') {
                            data_jadwal.ajax.reload();
                        }

                        if (typeof window.reloadKalenderJadwal === 'function') {
                            window.reloadKalenderJadwal();
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

        // =========================================================
        // MODAL + FORM: TAMBAH JADWAL INDIVIDU
        // =========================================================
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
                        updateBulanDariField('#individu-tanggal');
                        $('#modal-individu-jadwal').modal('hide');
                        notifikasi('success', 'right', result.pesan);

                        if (typeof window.reloadKalenderJadwal === 'function') {
                            window.reloadKalenderJadwal();
                        }

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

        // =========================================================
        // INITIAL STATE
        // =========================================================
        toggleShiftEdit();
        syncDisabledPegawaiOptions();
        toggleShiftIndividu();
    });
</script>

<script>
    // =========================================================
    // FLATPICKR INIT
    // =========================================================
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