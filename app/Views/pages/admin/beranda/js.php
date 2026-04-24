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
                    $('.block').LoadingOverlay('show');
                },
                success: function(res) {
                    $('#total-pegawai').text(res.total_pegawai ?? 0);
                    $('#hadir-hari-ini').text(res.hadir_hari_ini ?? 0);
                    $('#terlambat-hari-ini').text(res.terlambat_hari_ini ?? 0);
                    $('#izin-sakit-hari-ini').text(res.izin_sakit_hari_ini ?? 0);
                    $('#alpa-hari-ini').text(res.alpa_hari_ini ?? 0);
                    $('#izin-pending').text(res.izin_pending ?? 0);
                    $('#tukar-jadwal-pending').text(res.tukar_jadwal_pending ?? 0);
                },
                error: function(xhr) {
                    notifikasi('danger', 'right', 'Gagal memuat ringkasan beranda');
                },
                complete: function() {
                    $('.block').LoadingOverlay('hide');
                }
            });
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
                                <td colspan="8" class="text-center text-muted">
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
                                </tr>
                            `;
                        });
                    }

                    $('#presensi-hari-ini-body').html(html);
                },
                error: function() {
                    $('#presensi-hari-ini-body').html(`
                        <tr>
                            <td colspan="8" class="text-center text-danger">
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
                                        <span class="avatar avatar-sm rounded-circle bg-primary text-white">
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
            status = status ?? '-';

            const badges = {
                hadir: 'success',
                telat: 'warning',
                izin: 'info',
                sakit: 'primary',
                alpa: 'danger',
                libur: 'secondary'
            };

            return `<span class="badge bg-${badges[status] ?? 'secondary'}">${escapeHtml(status)}</span>`;
        }

        function badgeStatusPulang(status) {
            status = status ?? '-';

            const badges = {
                pulang: 'success',
                belum_pulang: 'secondary',
                pulang_cepat: 'warning'
            };

            return `<span class="badge bg-${badges[status] ?? 'secondary'}">${escapeHtml(status)}</span>`;
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