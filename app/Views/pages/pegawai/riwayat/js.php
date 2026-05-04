<script src="/assets/plugins/fullcalender/index.global.min.js"></script>

<script>
    $(document).ready(function() {
        const calendarEl = document.getElementById('kalender-riwayat-presensi');

        if (!calendarEl || typeof FullCalendar === 'undefined') {
            return;
        }

        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            height: 'auto',
            locale: 'id',
            displayEventTime: false,
            eventDisplay: 'block',
            dayMaxEvents: false,
            dayMaxEventRows: false,
            initialDate: ($('#filter-bulan-riwayat').val() || '<?= date('Y-m'); ?>') + '-01',

            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,multiMonthYear,listMonth'
            },

            buttonText: {
                today: 'Hari Ini',
                month: 'Bulan',
                year: 'Tahun',
                list: 'Daftar'
            },

            views: {
                dayGridMonth: {
                    buttonText: 'Bulan',
                    dayMaxEvents: false,
                    dayMaxEventRows: false
                },
                multiMonthYear: {
                    buttonText: 'Tahun',
                    multiMonthMaxColumns: 3,
                    dayMaxEvents: false,
                    dayMaxEventRows: false
                },
                listMonth: {
                    buttonText: 'Daftar'
                }
            },

            events: '<?= base_url('pegawai/riwayat/kalender'); ?>',

            eventDidMount: function(info) {
                const p = info.event.extendedProps;

                const tooltipText = [
                    'Hasil: ' + labelHasil(p.hasil_presensi),
                    'Shift: ' + (p.nama_shift || '-'),
                    'Datang: ' + formatJam(p.jam_datang),
                    'Pulang: ' + formatJam(p.jam_pulang),
                    'Status Datang: ' + labelStatusDatang(p.status_datang),
                    'Status Pulang: ' + labelStatusPulang(p.status_pulang),
                    'Sumber: ' + labelSumber(p.sumber_presensi)
                ].join('\n');

                info.el.setAttribute('title', tooltipText);
            },

            eventContent: function(arg) {
                const p = arg.event.extendedProps;
                const warna = p.warna || '#adb5bd';
                const viewType = arg.view.type;

                const wrapper = document.createElement('div');

                const dot = document.createElement('span');
                dot.style.backgroundColor = warna;

                if (viewType === 'multiMonthYear') {
                    wrapper.className = 'fc-riwayat-year-dot-wrapper';

                    dot.className = 'fc-riwayat-year-dot';

                    wrapper.appendChild(dot);

                    return {
                        domNodes: [wrapper]
                    };
                }

                wrapper.className = 'fc-riwayat-dot-wrapper';

                dot.className = 'fc-riwayat-dot';

                const text = document.createElement('div');
                text.className = 'fc-riwayat-text';

                const title = document.createElement('div');
                title.className = 'fc-riwayat-title';
                title.textContent = arg.event.title;

                const jam = document.createElement('div');
                jam.className = 'fc-riwayat-jam';
                jam.textContent = 'D: ' + formatJam(p.jam_datang) + ' | P: ' + formatJam(p.jam_pulang);

                text.appendChild(title);
                text.appendChild(jam);

                wrapper.appendChild(dot);
                wrapper.appendChild(text);

                return {
                    domNodes: [wrapper]
                };
            },

            eventClick: function(info) {
                const p = info.event.extendedProps;

                $('#detail-tanggal').text(formatTanggal(p.tanggal || info.event.startStr));
                $('#detail-hasil').html(badgeHasil(p.hasil_presensi));
                $('#detail-shift').text(p.nama_shift || '-');
                $('#detail-jam-datang').text(formatJam(p.jam_datang));
                $('#detail-status-datang').html(badgeStatusDatang(p.status_datang, p.menit_telat));
                $('#detail-jam-pulang').text(formatJam(p.jam_pulang));
                $('#detail-status-pulang').html(badgeStatusPulang(p.status_pulang, p.menit_pulang_cepat));
                $('#detail-sumber').html(badgeSumber(p.sumber_presensi, p.is_manual));
                $('#detail-catatan').text(p.catatan_admin || '-');

                $('#modal-detail-riwayat').modal('show');
            },

            datesSet: function() {
                syncFilterDariCalendar(calendar);
            }
        });

        calendar.render();

        $('#filter-bulan-riwayat').on('change', function() {
            const bulan = $(this).val();

            if (!bulan) {
                return;
            }

            calendar.gotoDate(bulan + '-01');
            calendar.refetchEvents();
        });
    });

    function syncFilterDariCalendar(calendar) {
        const date = calendar.getDate();
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');

        $('#filter-bulan-riwayat').val(year + '-' + month);
    }

    function labelHasil(value) {
        const labels = {
            hadir: 'Hadir',
            alpa: 'Alpa',
            izin: 'Izin',
            sakit: 'Sakit',
            libur: 'Libur',
            cuti: 'Cuti',
        };

        return labels[value] || 'Belum Sinkron';
    }

    function badgeHasil(value) {
        const map = {
            hadir: '<span class="badge bg-success">Hadir</span>',
            alpa: '<span class="badge bg-danger">Alpa</span>',
            cuti: '<span class="badge bg-danger">Cuti</span>',
            izin: '<span class="badge bg-info">Izin</span>',
            sakit: '<span class="badge bg-primary">Sakit</span>',
            libur: '<span class="badge bg-secondary">Libur</span>'
        };

        return map[value] || '<span class="badge bg-light text-dark border">Belum Sinkron</span>';
    }

    function labelStatusDatang(value) {
        const labels = {
            tepat_waktu: 'Tepat Waktu',
            telat: 'Telat'
        };

        return labels[value] || '-';
    }

    function labelStatusPulang(value) {
        const labels = {
            tepat_waktu: 'Tepat Waktu',
            pulang_cepat: 'Pulang Cepat'
        };

        return labels[value] || '-';
    }

    function badgeStatusDatang(value, menitTelat) {
        if (value === 'tepat_waktu') {
            return '<span class="badge bg-success">Tepat Waktu</span>';
        }

        if (value === 'telat') {
            return '<span class="badge bg-warning text-dark">Telat ' + (menitTelat || 0) + ' menit</span>';
        }

        return '<span class="badge bg-secondary">-</span>';
    }

    function badgeStatusPulang(value, menitPulangCepat) {
        if (value === 'tepat_waktu') {
            return '<span class="badge bg-success">Tepat Waktu</span>';
        }

        if (value === 'pulang_cepat') {
            return '<span class="badge bg-warning text-dark">Pulang Cepat ' + (menitPulangCepat || 0) + ' menit</span>';
        }

        return '<span class="badge bg-secondary">-</span>';
    }

    function labelSumber(value) {
        const labels = {
            scan: 'Scan',
            sinkron: 'Sinkron',
            lupa_presensi: 'Lupa Presensi'
        };

        return labels[value] || '-';
    }

    function badgeSumber(value, isManual) {
        const map = {
            scan: '<span class="badge bg-dark">Scan</span>',
            sinkron: '<span class="badge bg-info">Sinkron</span>',
            lupa_presensi: '<span class="badge bg-warning text-dark">Lupa Presensi</span>'
        };

        if (map[value]) {
            return map[value];
        }

        if (Number(isManual) === 1) {
            return '<span class="badge bg-warning text-dark">Manual</span>';
        }

        return '<span class="badge bg-secondary">-</span>';
    }

    function formatJam(value) {
        if (!value) return '-';

        value = String(value);

        if (value.includes(' ')) {
            return value.substring(11, 16);
        }

        return value.substring(0, 5);
    }

    function formatTanggal(value) {
        if (!value) return '-';

        if (typeof KadoelHelper !== 'undefined' && KadoelHelper.toTanggalIndonesia) {
            return KadoelHelper.toTanggalIndonesia(value);
        }

        return value;
    }
</script>