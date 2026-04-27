<script type="text/javascript">
    $(document).ready(function() {
        <?= loadingoverlay_fa(); ?>
        <?= notifikasi(); ?>

        loadSummary();
        loadPresensiHariIni();
        loadAktivitasTerbaru();

        function loadSummary() {
            $.ajax({
                type: 'GET',
                url: '<?= base_url('admin/summary'); ?>',
                dataType: 'json',
                beforeSend: function() {
                    $('#block-ringkasan-beranda').LoadingOverlay('show');
                },
                success: function(res) {
                    // KPI
                    $('#total-pegawai').text(res.total_pegawai ?? 0);
                    $('#izin-pending').text(res.izin_pending ?? 0);
                    $('#tukar-jadwal-pending').text(res.tukar_jadwal_pending ?? 0);

                    // Jadwal Hari Ini
                    $('#jadwal-kerja').text(res.jadwal_kerja ?? 0);
                    $('#jadwal-izin').text(res.jadwal_izin ?? 0);
                    $('#jadwal-sakit').text(res.jadwal_sakit ?? 0);
                    $('#jadwal-libur').text(res.jadwal_libur ?? 0);
                    $('#total-jadwal').text(res.total_jadwal ?? 0);

                    // Presensi Hari Ini
                    $('#total-presensi').text(res.total_presensi ?? 0);
                    $('#tepat-datang').text(res.tepat_datang ?? 0);
                    $('#telat-datang').text(res.telat_datang ?? 0);
                    $('#tepat-pulang').text(res.tepat_pulang ?? 0);
                    $('#pulang-cepat').text(res.pulang_cepat ?? 0);

                    // Sinkron Hari Ini
                    $('#belum-sinkron').text(res.belum_sinkron ?? 0);
                    $('#hadir').text(res.hadir ?? 0);
                    $('#izin').text(res.izin ?? 0);
                    $('#sakit').text(res.sakit ?? 0);
                    $('#libur').text(res.libur ?? 0);
                    $('#alpa').text(res.alpa ?? 0);

                    updateProgress(res);
                },
                error: function(xhr) {
                    const res = xhr.responseJSON;

                    if (typeof KadoelAjax !== 'undefined') {
                        KadoelAjax.handleError(res || {
                            pesan: 'Gagal memuat ringkasan beranda'
                        });
                        return;
                    }

                    notifikasi('danger', 'right', 'Gagal memuat ringkasan beranda');
                },
                complete: function() {
                    $('#block-ringkasan-beranda').LoadingOverlay('hide');
                }
            });
        }

        function updateProgress(res) {
            const totalJadwal = res.total_jadwal ?? 0;
            const totalPresensi = res.total_presensi ?? 0;
            const belumSinkron = res.belum_sinkron ?? 0;
            const sudahSinkron = res.sudah_sinkron ?? Math.max(totalJadwal - belumSinkron, 0);

            const progressPresensi = res.progress_presensi ?? 0;
            const progressSinkron = res.progress_sinkron ?? 0;

            $('#progress-presensi-label').text(progressPresensi + '%');
            $('#progress-presensi-bar').css('width', progressPresensi + '%');
            $('#progress-presensi-text').text(totalPresensi + ' presensi dari ' + totalJadwal + ' jadwal');

            $('#progress-sinkron-label').text(progressSinkron + '%');
            $('#progress-sinkron-bar').css('width', progressSinkron + '%');
            $('#progress-sinkron-text').text(sudahSinkron + ' selesai, ' + belumSinkron + ' belum sinkron');
        }

        function loadPresensiHariIni() {
            $.ajax({
                type: 'GET',
                url: '<?= base_url('admin/presensi-hari-ini'); ?>',
                dataType: 'json',
                beforeSend: function() {
                    $('#block-presensi-hari-ini').LoadingOverlay('show');
                },
                success: function(res) {
                    let html = '';

                    if (!res || res.length === 0) {
                        html = `
                            <tr>
                                <td colspan="9" class="text-center text-muted">
                                    Belum ada data presensi hari ini
                                </td>
                            </tr>
                        `;
                    } else {
                        $.each(res, function(index, item) {
                            html += `
                                <tr>
                                    <td class="text-center">${index + 1}</td>
                                    <td>${escapeHtml(item.kode_pegawai ?? '-')}</td>
                                    <td>${escapeHtml(item.nama_pegawai ?? '-')}</td>
                                    <td>${escapeHtml(item.nama_shift ?? '-')}</td>
                                    <td>${formatJam(item.jam_datang)}</td>
                                    <td>${badgeStatusDatang(item.status_datang)}</td>
                                    <td>${formatJam(item.jam_pulang)}</td>
                                    <td>${badgeStatusPulang(item.status_pulang)}</td>
                                    <td>${badgeHasilPresensi(item.hasil_presensi)}</td>
                                </tr>
                            `;
                        });
                    }

                    $('#presensi-hari-ini-body').html(html);
                },
                error: function() {
                    $('#presensi-hari-ini-body').html(`
                        <tr>
                            <td colspan="9" class="text-center text-danger">
                                Gagal memuat data presensi
                            </td>
                        </tr>
                    `);
                },
                complete: function() {
                    $('#block-presensi-hari-ini').LoadingOverlay('hide');
                }
            });
        }

        function loadAktivitasTerbaru() {
            $.ajax({
                type: 'GET',
                url: '<?= base_url('admin/aktivitas-terbaru'); ?>',
                dataType: 'json',
                beforeSend: function() {
                    $('#block-aktivitas-terbaru').LoadingOverlay('show');
                },
                success: function(res) {
                    let html = '';

                    if (!res || res.length === 0) {
                        html = `<div class="text-center text-muted py-4">Belum ada aktivitas</div>`;
                    } else {
                        $.each(res, function(index, item) {
                            html += `
                                <div class="d-flex py-3 border-bottom">
                                    <div class="flex-shrink-0 me-3">
                                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white"
                                            style="width:36px;height:36px;">
                                            <i class="fa fa-history"></i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold">${escapeHtml(item.action ?? '-')}</div>
                                        <div class="fs-sm text-muted">${escapeHtml(item.description ?? item.table_name ?? '-')}</div>
                                        <div class="fs-xs text-muted">${formatTanggalWaktu(item.created_at)}</div>
                                    </div>
                                </div>
                            `;
                        });
                    }

                    $('#aktivitas-terbaru-list').html(html);
                },
                error: function() {
                    $('#aktivitas-terbaru-list').html(`
                        <div class="text-center text-danger py-4">
                            Gagal memuat aktivitas terbaru
                        </div>
                    `);
                },
                complete: function() {
                    $('#block-aktivitas-terbaru').LoadingOverlay('hide');
                }
            });
        }

        function badgeStatusDatang(status) {
            const labels = {
                tepat_waktu: 'Tepat Waktu',
                telat: 'Telat'
            };

            const badges = {
                tepat_waktu: 'success',
                telat: 'warning'
            };

            if (!status) {
                return '<span class="badge bg-secondary">-</span>';
            }

            return `<span class="badge bg-${badges[status] ?? 'secondary'}">${labels[status] ?? escapeHtml(status)}</span>`;
        }

        function badgeStatusPulang(status) {
            const labels = {
                tepat_waktu: 'Tepat Waktu',
                pulang_cepat: 'Pulang Cepat'
            };

            const badges = {
                tepat_waktu: 'success',
                pulang_cepat: 'warning'
            };

            if (!status) {
                return '<span class="badge bg-secondary">-</span>';
            }

            return `<span class="badge bg-${badges[status] ?? 'secondary'}">${labels[status] ?? escapeHtml(status)}</span>`;
        }

        function badgeHasilPresensi(status) {
            const labels = {
                hadir: 'Hadir',
                alpa: 'Alpa',
                izin: 'Izin',
                sakit: 'Sakit',
                libur: 'Libur'
            };

            const badges = {
                hadir: 'success',
                alpa: 'danger',
                izin: 'info',
                sakit: 'primary',
                libur: 'secondary'
            };

            if (!status) {
                return '<span class="badge bg-light text-dark">Belum Sinkron</span>';
            }

            return `<span class="badge bg-${badges[status] ?? 'secondary'}">${labels[status] ?? escapeHtml(status)}</span>`;
        }

        function formatJam(value) {
            if (!value) return '-';

            value = String(value);

            if (value.includes(' ')) {
                return value.substring(11, 16);
            }

            return value.substring(0, 5);
        }

        function formatTanggalWaktu(value) {
            if (!value) return '-';
            return value;
        }

        function escapeHtml(value) {
            return $('<div>').text(value).html();
        }
    });
</script>