<script src="/assets/plugins/fullcalender/index.global.min.js"></script>

<script>
    $(document).ready(function() {
        const calendarEl = document.getElementById('kalender-jadwal-pegawai');

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
            initialDate: ($('#filter-bulan-jadwal').val() || '<?= date('Y-m'); ?>') + '-01',

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

            events: '<?= base_url('pegawai/jadwal/kalender'); ?>',

            eventDidMount: function(info) {
                const p = info.event.extendedProps;

                const tooltipText = [
                    'Status: ' + labelStatus(p.status_hari),
                    'Shift: ' + (p.nama_shift || '-'),
                    'Masuk: ' + formatJam(p.jam_masuk),
                    'Pulang: ' + formatJam(p.jam_pulang),
                    'Catatan: ' + (p.catatan || '-')
                ].join('\n');

                info.el.setAttribute('title', tooltipText);
            },

            eventContent: function(arg) {
                const p = arg.event.extendedProps;
                const warna = p.warna || '#6c757d';

                const viewType = arg.view.type;

                const wrapper = document.createElement('div');
                wrapper.className = 'fc-jadwal-dot-wrapper';

                const dot = document.createElement('span');
                dot.className = 'fc-jadwal-dot';
                dot.style.backgroundColor = warna;

                const text = document.createElement('div');
                text.className = 'fc-jadwal-text';

                const title = document.createElement('div');
                title.className = 'fc-jadwal-title';
                title.textContent = arg.event.title;

                text.appendChild(title);

                // 🔥 LOGIC PER VIEW
                if (viewType === 'multiMonthYear') {
                    // ❗ VIEW TAHUN → SUPER MINIMAL
                    title.style.fontSize = '11px';
                } else if (viewType === 'dayGridMonth') {
                    // ✅ VIEW BULAN → pakai jam
                    if (p.status_hari === 'kerja') {
                        const jam = document.createElement('div');
                        jam.className = 'fc-jadwal-jam';
                        jam.textContent = formatJam(p.jam_masuk) + ' - ' + formatJam(p.jam_pulang);

                        text.appendChild(jam);
                    }
                } else if (viewType === 'listMonth') {
                    // 📱 VIEW LIST → lengkap
                    if (p.status_hari === 'kerja') {
                        const jam = document.createElement('div');
                        jam.className = 'fc-jadwal-jam';
                        jam.textContent = formatJam(p.jam_masuk) + ' - ' + formatJam(p.jam_pulang);

                        text.appendChild(jam);
                    }
                }

                wrapper.appendChild(dot);
                wrapper.appendChild(text);

                return {
                    domNodes: [wrapper]
                };
            },

            eventClick: function(info) {
                const p = info.event.extendedProps;

                $('#detail-tanggal').text(formatTanggal(p.tanggal || info.event.startStr));
                $('#detail-status').html(badgeStatus(p.status_hari));
                $('#detail-shift').text(p.nama_shift || '-');
                $('#detail-jam-masuk').text(formatJam(p.jam_masuk));
                $('#detail-jam-pulang').text(formatJam(p.jam_pulang));
                $('#detail-catatan').text(p.catatan || '-');

                $('#modal-detail-jadwal').modal('show');
            },

            datesSet: function() {
                syncFilterDariCalendar(calendar);
            }
        });

        calendar.render();

        $('#filter-bulan-jadwal').on('change', function() {
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

        $('#filter-bulan-jadwal').val(year + '-' + month);
    }

    function labelStatus(status) {
        const labels = {
            kerja: 'Kerja',
            libur: 'Libur',
            izin: 'Izin',
            sakit: 'Sakit'
        };

        return labels[status] || '-';
    }

    function badgeStatus(status) {
        const map = {
            kerja: '<span class="badge bg-success">Kerja</span>',
            libur: '<span class="badge bg-danger">Libur</span>',
            izin: '<span class="badge bg-warning text-dark">Izin</span>',
            sakit: '<span class="badge bg-info text-dark">Sakit</span>'
        };

        return map[status] || '<span class="badge bg-secondary">-</span>';
    }

    function formatJam(value) {
        if (!value) return '-';
        return String(value).substring(0, 5);
    }

    function formatTanggal(value) {
        if (!value) return '-';

        if (typeof KadoelHelper !== 'undefined' && KadoelHelper.toTanggalIndonesia) {
            return KadoelHelper.toTanggalIndonesia(value);
        }

        return value;
    }
</script>