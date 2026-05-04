<script src="/assets/plugins/flatpickr/plugins/monthSelect/index.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        let csrfToken = '<?= csrf_token(); ?>';
        let csrfHash = '<?= csrf_hash(); ?>';

        <?= loadingoverlay_fa(); ?>
        <?= notifikasi(); ?>

        function bulanFilter() {
            return $('#filter-bulan').val() || '<?= date('Y-m'); ?>';
        }

        function rupiah(value) {
            value = parseFloat(value || 0);
            return 'Rp ' + value.toLocaleString('id-ID');
        }

        function handleGagal(result) {
            if (typeof KadoelAjax !== 'undefined') {
                KadoelAjax.handleError(result);
                return;
            }
            notifikasi('danger', 'right', result?.pesan || 'Terjadi kesalahan');
        }

        function toggleFinalkanButton(ringkasan) {
            if (ringkasan.ada_draft) {
                $('#btn-finalkan').removeClass('d-none');
            } else {
                $('#btn-finalkan').addClass('d-none');
            }
        }

        function toggleGenerateButton(ringkasan) {
            if (ringkasan.ada_final) {
                $('#btn-generate').addClass('d-none');
            } else {
                $('#btn-generate').removeClass('d-none');
            }
        }

        function toggleExportButton(ringkasan) {
            if (ringkasan.ada_final) {
                $('#btn-export').removeClass('d-none');
                $('#btn-bulk-slip').removeClass('d-none');
            } else {
                $('#btn-export').addClass('d-none');
                $('#btn-bulk-slip').addClass('d-none');
            }
        }

        $('#btn-export').on('click', function() {
            const bulan = $('#filter-bulan').val() || '<?= date('Y-m'); ?>';
            window.open('<?= base_url('admin/penggajian/export') ?>?bulan=' + encodeURIComponent(bulan), '_blank');
        });

        function refreshRingkasan() {
            $('#block-ringkasan-penggajian').LoadingOverlay('show');
            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/penggajian/ringkasan') ?>',
                dataType: 'JSON',
                data: {
                    [csrfToken]: csrfHash,
                    bulan: bulanFilter()
                },
                success: function(result) {
                    $('#block-ringkasan-penggajian').LoadingOverlay('hide');
                    if (result.sukses) {
                        const r = result.ringkasan || {};
                        $('#ringkasan-total-data').text(r.total_data || 0);
                        $('#ringkasan-total-draft').text(r.total_draft || 0);
                        $('#ringkasan-total-final').text(r.total_final || 0);
                        $('#ringkasan-total-gaji-bersih').text(rupiah(r.total_gaji_bersih || 0));
                        toggleFinalkanButton(result);
                        toggleExportButton(result);
                        toggleGenerateButton(result);
                    } else {
                        handleGagal(result);
                    }
                },
                error: function(xhr) {
                    $('#block-ringkasan-penggajian').LoadingOverlay('hide');
                    console.log(xhr.status + ': ' + xhr.statusText);
                }
            });
        }

        let data_penggajian = $('#penggajian-tabel').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            searching: true,
            paging: true,
            info: true,
            pagingType: 'full_numbers',
            responsive: false,
            scrollX: true,
            language: {
                url: '<?= base_url("assets/plugins/DataTablesbs5/plugins/id.json"); ?>'
            },
            ajax: {
                url: '<?= base_url("admin/penggajian"); ?>',
                method: 'POST',
                data: function(d) {
                    d[csrfToken] = csrfHash;
                    d.bulan = bulanFilter();
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
                    data: 'kode_pegawai'
                },
                {
                    data: 'nama_pegawai'
                },
                {
                    data: 'nama_jabatan'
                },
                {
                    data: 'total_hadir'
                },
                {
                    data: 'total_izin'
                },
                {
                    data: 'total_sakit'
                },
                {
                    data: 'total_cuti'
                },
                {
                    data: 'total_alpa'
                },
                {
                    data: 'gaji_pokok'
                },
                {
                    data: 'tunjangan'
                },
                {
                    data: 'total_potongan'
                },
                {
                    data: 'gaji_bersih'
                },
                {
                    data: 'status'
                },
                {
                    data: 'action'
                }
            ],
            order: [
                [3, 'asc']
            ],
            columnDefs: [{
                    targets: [1],
                    visible: false
                },
                {
                    targets: [0, 15],
                    orderable: false
                },
                {
                    targets: [0, 15],
                    searchable: false
                },
                {
                    targets: [0, 5, 6, 7, 8, 9, 14, 15],
                    className: 'text-center'
                },
                {
                    targets: [10, 11, 12, 13],
                    className: 'text-end'
                }
            ]
        });

        $('#filter-bulan').on('change', function() {
            data_penggajian.ajax.reload();
            refreshRingkasan();
        });

        if (typeof flatpickr !== 'undefined') {
            flatpickr('#filter-bulan', {
                disableMobile: true,
                dateFormat: 'Y-m',
                maxDate: 'today',
                plugins: [
                    new monthSelectPlugin({
                        shorthand: true,
                        dateFormat: 'Y-m',
                        altFormat: 'F Y'
                    })
                ],
                onChange: function() {
                    data_penggajian.ajax.reload();
                    refreshRingkasan();
                }
            });
        }

        $('#btn-generate').on('click', function() {
            const bulan = bulanFilter();
            Swal.fire({
                title: 'Generate Penggajian?',
                html: 'Generate penggajian bulan <b>' + formatBulanIndonesia(bulan) + '</b>?<br><small>Draft lama akan digenerate ulang jika belum final.</small>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#65A30D',
                cancelButtonColor: '#d33',
                confirmButtonText: '<i class="fa fa-gears"></i> Generate',
                cancelButtonText: '<i class="fa fa-times"></i> Batal',
                allowEscapeKey: false,
                allowOutsideClick: false,
            }).then((res) => {
                if (!res.isConfirmed) return;
                $('#block-penggajian').LoadingOverlay('show');
                $.ajax({
                    type: 'POST',
                    url: '<?= base_url('admin/penggajian/generate') ?>',
                    dataType: 'JSON',
                    data: {
                        [csrfToken]: csrfHash,
                        bulan: bulan
                    },
                    success: function(result) {
                        $('#block-penggajian').LoadingOverlay('hide');
                        if (result.sukses) {
                            notifikasi('success', 'right', result.pesan);
                            data_penggajian.ajax.reload();
                            refreshRingkasan();
                        } else {
                            handleGagal(result);
                        }
                    },
                    error: function(xhr) {
                        $('#block-penggajian').LoadingOverlay('hide');
                        console.log(xhr.status + ': ' + xhr.statusText);
                    }
                });
            });
        });

        $('#btn-finalkan').on('click', function() {
            const bulan = bulanFilter();
            Swal.fire({
                title: 'Finalkan Penggajian?',
                html: 'Finalkan penggajian bulan <b>' + formatBulanIndonesia(bulan) + '</b>?<br><small>Data final tidak dapat digenerate ulang.</small>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#65A30D',
                cancelButtonColor: '#d33',
                confirmButtonText: '<i class="fa fa-lock"></i> Finalkan',
                cancelButtonText: '<i class="fa fa-times"></i> Batal',
                allowEscapeKey: false,
                allowOutsideClick: false,
            }).then((res) => {
                if (!res.isConfirmed) return;
                $('#block-penggajian').LoadingOverlay('show');
                $.ajax({
                    type: 'POST',
                    url: '<?= base_url('admin/penggajian/finalkan') ?>',
                    dataType: 'JSON',
                    data: {
                        [csrfToken]: csrfHash,
                        bulan: bulan
                    },
                    success: function(result) {
                        $('#block-penggajian').LoadingOverlay('hide');
                        if (result.sukses) {
                            notifikasi('success', 'right', result.pesan);
                            data_penggajian.ajax.reload();
                            refreshRingkasan();
                        } else {
                            handleGagal(result);
                        }
                    },
                    error: function(xhr) {
                        $('#block-penggajian').LoadingOverlay('hide');
                        console.log(xhr.status + ': ' + xhr.statusText);
                    }
                });
            });
        });

        function resetDetail() {
            $('[id^="detail-"]').text('-');
            setStatusHeader('draft');
        }

        $('#penggajian-tabel').on('click', '#act-detail', function() {
            const id = $(this).data('id');
            resetDetail();
            $('#block-content-detail').LoadingOverlay('show');
            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/penggajian/detail') ?>',
                dataType: 'JSON',
                data: {
                    [csrfToken]: csrfHash,
                    id: id
                },
                success: function(result) {
                    $('#block-content-detail').LoadingOverlay('hide');
                    if (result.sukses) {
                        const p = result.penggajian || {};
                        const item = result.penggajian || {};
                        setStatusHeader(item.status || 'draft');
                        $('#detail-kode_pegawai').text(p.kode_pegawai || '-');
                        $('#detail-nama_pegawai').text(p.nama_pegawai || '-');
                        $('#detail-nama_jabatan').text(p.nama_jabatan || '-');
                        $('#detail-bulan').text(p.bulan || '-');
                        $('#detail-total_hadir').text(p.total_hadir || 0);
                        $('#detail-total_izin').text(p.total_izin || 0);
                        $('#detail-total_sakit').text(p.total_sakit || 0);
                        $('#detail-total_libur').text(p.total_libur || 0);
                        $('#detail-total_cuti').text(p.total_cuti || 0);
                        $('#detail-total_alpa').text(p.total_alpa || 0);
                        $('#detail-total_menit_telat').text(p.total_menit_telat || 0);
                        $('#detail-total_menit_pulang_cepat').text(p.total_menit_pulang_cepat || 0);
                        $('#detail-gaji_pokok').text(rupiah(p.gaji_pokok || 0));
                        $('#detail-tunjangan').text(rupiah(p.tunjangan || 0));
                        $('#detail-gaji_kotor').text(rupiah(p.gaji_kotor || 0));
                        $('#detail-potongan_telat').text(rupiah(p.potongan_telat || 0));
                        $('#detail-potongan_pulang_cepat').text(rupiah(p.potongan_pulang_cepat || 0));
                        $('#detail-potongan_alpa').text(rupiah(p.potongan_alpa || 0));
                        $('#detail-total_potongan').text(rupiah(p.total_potongan || 0));
                        $('#detail-gaji_bersih').text(rupiah(p.gaji_bersih || 0));
                        $('#modal-detail').modal('show');
                    } else {
                        handleGagal(result);
                    }
                },
                error: function(xhr) {
                    $('#block-content-detail').LoadingOverlay('hide');
                    console.log(xhr.status + ': ' + xhr.statusText);
                }
            });
        });

        $('#tutup-modal-detail').on('click', function() {
            $('#modal-detail').modal('hide');
        });

        $('#penggajian-tabel').on('click', '#act-preview-slip', function() {
            const id = $(this).data('id');
            window.open('<?= base_url('admin/penggajian/slip/preview') ?>/' + id, '_blank');
        });

        $('#penggajian-tabel').on('click', '#act-pdf-slip', function() {
            const id = $(this).data('id');
            window.open('<?= base_url('admin/penggajian/slip/pdf') ?>/' + id, '_blank');
        });

        $('#btn-bulk-slip').on('click', function() {
            const bulan = $('#filter-bulan').val() || '<?= date('Y-m'); ?>';
            window.open('<?= base_url('admin/penggajian/slip/bulk') ?>?bulan=' + encodeURIComponent(bulan), '_blank');
        });

        function formatBulanIndonesia(value) {
            if (!value) return '-';

            const bulanIndo = [
                'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];

            const parts = value.split('-');
            const tahun = parts[0];
            const bulan = parseInt(parts[1], 10);

            if (!tahun || !bulan || bulan < 1 || bulan > 12) {
                return value;
            }

            return bulanIndo[bulan - 1] + ' ' + tahun;
        }

        function setStatusHeader(status) {
            const el = $('#detail-status-header');

            if (status === 'final') {
                el.removeClass('bg-warning text-white')
                    .addClass('bg-success')
                    .html('<i class="fa fa-check"></i> Final');
                return;
            }

            el.removeClass('bg-success')
                .addClass('bg-warning text-white')
                .html('<i class="fa fa-clock"></i> Draft');
        }

        refreshRingkasan();
    });
</script>