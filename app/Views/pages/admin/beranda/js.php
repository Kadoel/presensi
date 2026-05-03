<script src="/assets/plugins/chartjs/chart.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        let aktivitasScrollFrame = null;
        let chartHasilPresensi = null;
        let chartStatusPresensi = null;
        let chartMingguan = null;
        let chartBulanan = null;

        <?= loadingoverlay_fa(); ?>
        <?= notifikasi(); ?>

        loadSummary();
        loadPresensiHariIni();
        loadAktivitasTerbaru();
        loadGrafikMingguan();
        loadGrafikBulanan();

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
                    $('#jadwal-cuti').text(res.jadwal_cuti ?? 0);
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
                    $('#cuti').text(res.cuti ?? 0);
                    $('#alpa').text(res.alpa ?? 0);

                    updateProgress(res);
                    renderGrafikBeranda(res);

                    const tanggalList = res.tanggal_belum_sinkron ?? [];
                    showStickyNotif(tanggalList);
                    console.log(tanggalList);
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

        function showStickyNotif(tanggalList) {
            const container = $('#sticky-belum-sinkron');
            const list = $('#list-belum-sinkron');

            if (!tanggalList || tanggalList.length === 0) {
                container.addClass('d-none');
                return;
            }

            let listHtml = tanggalList.map(t =>
                `<li>${formatTanggalIndonesia(t)}</li>`
            ).join('');

            list.html(listHtml);
            container.removeClass('d-none');
        }

        function formatTanggalIndonesia(tanggal) {
            const bulan = [
                'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
                'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
            ];

            const d = new Date(tanggal);
            return `${d.getDate()} ${bulan[d.getMonth()]} ${d.getFullYear()}<br>`;
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

        function renderGrafikBeranda(res) {
            renderChartHasilPresensi(res);
            renderChartStatusPresensi(res);
        }

        function renderChartHasilPresensi(res) {
            const ctx = document.getElementById('chart-hasil-presensi');

            if (!ctx) return;

            if (chartHasilPresensi) {
                chartHasilPresensi.destroy();
            }

            chartHasilPresensi = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Hadir', 'Izin', 'Sakit', 'Libur', 'Alpa', 'Belum Sinkron'],
                    datasets: [{
                        data: [
                            res.hadir ?? 0,
                            res.izin ?? 0,
                            res.sakit ?? 0,
                            res.libur ?? 0,
                            res.alpa ?? 0,
                            res.belum_sinkron ?? 0
                        ],
                        backgroundColor: [
                            '#82b54b',
                            '#3c90df',
                            '#0665d0',
                            '#6c757d',
                            '#e04f1a',
                            '#f3b760'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        function renderChartStatusPresensi(res) {
            const ctx = document.getElementById('chart-status-presensi');

            if (!ctx) return;

            if (chartStatusPresensi) {
                chartStatusPresensi.destroy();
            }

            chartStatusPresensi = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Datang Tepat', 'Datang Telat', 'Pulang Tepat', 'Pulang Cepat'],
                    datasets: [{
                        label: 'Jumlah Pegawai',
                        data: [
                            res.tepat_datang ?? 0,
                            res.telat_datang ?? 0,
                            res.tepat_pulang ?? 0,
                            res.pulang_cepat ?? 0
                        ],
                        backgroundColor: [
                            '#82b54b',
                            '#f3b760',
                            '#82b54b',
                            '#f3b760'
                        ],
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }

        function loadGrafikMingguan() {
            $.ajax({
                type: 'GET',
                url: '<?= base_url('admin/grafik-mingguan'); ?>',
                dataType: 'json',
                beforeSend: function() {
                    $('#block-grafik-mingguan').LoadingOverlay('show');
                },
                success: function(res) {
                    renderChartMingguan(res);
                },
                complete: function() {
                    $('#block-grafik-mingguan').LoadingOverlay('hide');
                }
            });
        }

        function loadGrafikBulanan() {
            $.ajax({
                type: 'GET',
                url: '<?= base_url('admin/grafik-bulanan'); ?>',
                dataType: 'json',
                beforeSend: function() {
                    $('#block-grafik-bulanan').LoadingOverlay('show');
                },
                success: function(res) {
                    renderChartBulanan(res);
                },
                complete: function() {
                    $('#block-grafik-bulanan').LoadingOverlay('hide');
                }
            });
        }

        function renderChartMingguan(res) {
            const ctx = document.getElementById('chart-mingguan');

            if (!ctx) return;

            if (chartMingguan) {
                chartMingguan.destroy();
            }

            chartMingguan = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: res.labels ?? [],
                    datasets: [{
                            label: 'Jadwal',
                            data: res.jadwal ?? [],
                            backgroundColor: 'rgba(6, 101, 208, 0.85)',
                            borderColor: '#0665d0',
                            borderWidth: 1,
                            borderRadius: 10,
                            borderSkipped: false,
                            maxBarThickness: 42
                        },
                        {
                            label: 'Presensi',
                            data: res.presensi ?? [],
                            backgroundColor: 'rgba(130, 181, 75, 0.85)',
                            borderColor: '#82b54b',
                            borderWidth: 1,
                            borderRadius: 10,
                            borderSkipped: false,
                            maxBarThickness: 42
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'circle',
                                padding: 18,
                                boxWidth: 8,
                                font: {
                                    size: 12,
                                    weight: '600'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: '#111827',
                            titleFont: {
                                size: 13,
                                weight: '700'
                            },
                            bodyFont: {
                                size: 12
                            },
                            padding: 12,
                            cornerRadius: 10,
                            displayColors: true
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#6b7280',
                                font: {
                                    size: 12,
                                    weight: '600'
                                }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(107, 114, 128, 0.12)'
                            },
                            ticks: {
                                precision: 0,
                                color: '#6b7280',
                                font: {
                                    size: 12,
                                    weight: '600'
                                }
                            }
                        }
                    }
                }
            });
        }

        function renderChartBulanan(res) {
            const ctx = document.getElementById('chart-bulanan');

            if (!ctx) return;

            if (chartBulanan) {
                chartBulanan.destroy();
            }

            chartBulanan = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: res.labels ?? [],
                    datasets: [{
                        data: res.data ?? [],
                        backgroundColor: [
                            '#82b54b',
                            '#3c90df',
                            '#0665d0',
                            '#6c757d',
                            '#e04f1a'
                        ],
                        hoverOffset: 8,
                        borderColor: '#ffffff',
                        borderWidth: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'circle',
                                padding: 16,
                                boxWidth: 8,
                                font: {
                                    size: 12,
                                    weight: '600'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: '#111827',
                            padding: 12,
                            cornerRadius: 10,
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((sum, item) => sum + item, 0);
                                    const percent = total > 0 ? Math.round((value / total) * 100) : 0;

                                    return `${label}: ${value} pegawai (${percent}%)`;
                                }
                            }
                        }
                    }
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
                url: '<?= base_url("admin/aktivitas-terbaru"); ?>',
                dataType: 'json',
                beforeSend: function() {
                    $('#block-aktivitas-terbaru').LoadingOverlay('show');
                },
                success: function(res) {
                    let html = '';
                    const data = res ? res.slice(0, 10) : [];

                    if (data.length === 0) {
                        html = `<div class="text-center text-muted py-4">Belum ada aktivitas</div>`;
                        $('#aktivitas-terbaru-list').html(html);
                        return;
                    }

                    $.each(data, function(index, item) {
                        html += `
                    <div class="d-flex py-3 border-bottom aktivitas-item">
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

                    $('#aktivitas-terbaru-list').html(html + html);
                    startAutoScrollAktivitas();
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

        function startAutoScrollAktivitas() {
            const wrapper = document.getElementById('aktivitas-terbaru-wrapper');
            const list = document.getElementById('aktivitas-terbaru-list');

            if (!wrapper || !list) return;

            if (aktivitasScrollFrame) {
                cancelAnimationFrame(aktivitasScrollFrame);
            }

            let scrollY = 0;
            let paused = false;
            const speed = 0.35;

            wrapper.onmouseenter = function() {
                paused = true;
            };

            wrapper.onmouseleave = function() {
                paused = false;
            };

            function animate() {
                if (!paused) {
                    scrollY += speed;

                    if (scrollY >= list.scrollHeight / 2) {
                        scrollY = 0;
                    }

                    wrapper.scrollTop = scrollY;
                }

                aktivitasScrollFrame = requestAnimationFrame(animate);
            }

            animate();
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