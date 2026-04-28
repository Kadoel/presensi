<script>
    $(document).ready(function() {
        loadSummary();
        loadRiwayatPresensi();
    });

    function loadSummary() {
        $.ajax({
            type: 'GET',
            url: '<?= base_url('pegawai/summary'); ?>',
            dataType: 'json',
            success: function(res) {
                if (!res.sukses) return;

                injectJadwalHariIni(res.jadwal_hari_ini);
                injectStatusPresensi(res.presensi_hari_ini);
                injectRingkasanBulan(res.ringkasan_bulan);
            },
            error: function() {
                $('#jadwal-kosong').removeClass('d-none');
                $('#jadwal-ada').addClass('d-none');
                $('#status-presensi-text').text('Gagal memuat data');
                setBadge('#status-presensi-badge', 'danger', 'Error');
            }
        });
    }

    function injectJadwalHariIni(jadwal) {
        if (!jadwal) {
            $('#jadwal-kosong').removeClass('d-none');
            $('#jadwal-ada').addClass('d-none');
            return;
        }

        $('#jadwal-kosong').addClass('d-none');
        $('#jadwal-ada').removeClass('d-none');

        $('#jadwal-shift').text(jadwal.nama_shift ?? '-');
        $('#jadwal-jam-masuk').text(formatJamOnly(jadwal.jam_masuk));
        $('#jadwal-jam-pulang').text(formatJamOnly(jadwal.jam_pulang));
    }

    function injectStatusPresensi(presensi) {
        $('#status-presensi-text').text(getStatusText(presensi));
        $('#status-jam-datang').text(formatJam(presensi?.jam_datang));
        $('#status-jam-pulang').text(formatJam(presensi?.jam_pulang));

        const badge = getStatusBadgeConfig(presensi?.hasil_presensi, presensi);
        setBadge('#status-presensi-badge', badge.color, badge.label);
    }

    function injectRingkasanBulan(data) {
        data = data ?? {};

        $('#hadir').text(data.hadir ?? 0);
        $('#izin').text(data.izin ?? 0);
        $('#sakit').text(data.sakit ?? 0);
        $('#libur').text(data.libur ?? 0);
        $('#alpa').text(data.alpa ?? 0);
    }

    function loadRiwayatPresensi() {
        $.ajax({
            type: 'GET',
            url: '<?= base_url('pegawai/riwayat-presensi'); ?>',
            dataType: 'json',
            success: function(res) {
                injectRiwayatPresensi(res);
            },
            error: function() {
                $('.riwayat-row').addClass('d-none');
                $('#riwayat-kosong').removeClass('d-none')
                    .find('td')
                    .removeClass('text-muted')
                    .addClass('text-danger')
                    .text('Gagal memuat data');
            }
        });
    }

    function injectRiwayatPresensi(data) {
        $('.riwayat-row').addClass('d-none');

        if (!data || data.length === 0) {
            $('#riwayat-kosong').removeClass('d-none')
                .find('td')
                .removeClass('text-danger')
                .addClass('text-muted')
                .text('Tidak ada data');
            return;
        }

        $('#riwayat-kosong').addClass('d-none');

        $.each(data.slice(0, 5), function(index, item) {
            const row = $('.riwayat-row[data-index="' + index + '"]');
            const badge = getStatusBadgeConfig(item.hasil_presensi, item);

            row.removeClass('d-none');
            row.find('.riwayat-tanggal').text(item.tanggal ?? '-');
            row.find('.riwayat-shift').text(item.nama_shift ?? '-');
            row.find('.riwayat-jam-datang').text(formatJam(item.jam_datang));
            row.find('.riwayat-jam-pulang').text(formatJam(item.jam_pulang));

            const badgeEl = row.find('.riwayat-status-badge');
            resetBadgeClass(badgeEl);
            badgeEl.addClass('bg-' + badge.color).text(badge.label);
        });
    }

    function getStatusText(presensi) {
        if (!presensi) return 'Keterangan:';
        return presensi.hasil_presensi ? labelStatus(presensi.hasil_presensi) : 'Belum sinkron';
    }

    function getStatusBadgeConfig(status, presensi = null) {
        if (!presensi) return {
            color: 'warning',
            label: 'Belum Presensi'
        };

        const config = {
            hadir: {
                color: 'success',
                label: 'Hadir'
            },
            izin: {
                color: 'info',
                label: 'Izin'
            },
            sakit: {
                color: 'primary',
                label: 'Sakit'
            },
            libur: {
                color: 'secondary',
                label: 'Libur'
            },
            alpa: {
                color: 'danger',
                label: 'Alpa'
            }
        };

        return config[status] ?? {
            color: 'secondary',
            label: '-'
        };
    }

    function labelStatus(status) {
        const labels = {
            hadir: 'Hadir',
            izin: 'Izin',
            sakit: 'Sakit',
            libur: 'Libur',
            alpa: 'Alpa'
        };

        return labels[status] ?? '-';
    }

    function setBadge(selector, color, label) {
        const el = $(selector);
        resetBadgeClass(el);
        el.addClass('bg-' + color).text(label);
    }

    function resetBadgeClass(el) {
        el.removeClass('bg-success bg-info bg-primary bg-secondary bg-danger bg-warning');
    }

    function formatJam(value) {
        if (!value) return '-';

        value = String(value);

        if (value.includes(' ')) {
            return value.substring(11, 16);
        }

        return value.substring(0, 5);
    }

    function formatJamOnly(value) {
        if (!value) return '-';
        return String(value).substring(0, 5);
    }
</script>