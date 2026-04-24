<script type="text/javascript">
    $(document).ready(function() {
        let csrfToken = '<?= csrf_token(); ?>';
        let csrfHash = '<?= csrf_hash(); ?>';

        <?= loadingoverlay_fa(); ?>
        <?= notifikasi(); ?>

        <?php if (session()->getFlashdata('sukses')) : ?>
            notifikasi('success', 'right', '<?= session()->getFlashdata('sukses'); ?>');
        <?php elseif (session()->getFlashdata('gagal')) : ?>
            notifikasi('danger', 'right', '<?= session()->getFlashdata("gagal"); ?>');
        <?php endif; ?>

        function ambilTanggalFilter() {
            return $('#filter-tanggal').val();
        }

        function refreshRingkasan() {
            $("#block-ringkasan").LoadingOverlay("show");

            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/presensi/ringkasan') ?>',
                dataType: 'JSON',
                data: {
                    [csrfToken]: csrfHash,
                    tanggal: ambilTanggalFilter()
                },
                success: function(result) {
                    $("#block-ringkasan").LoadingOverlay("hide");

                    if (result.sukses) {
                        const ringkasan = result.ringkasan || {};

                        $('#ringkasan-total-jadwal').text(ringkasan.total_jadwal || 0);
                        $('#ringkasan-total-presensi').text(ringkasan.total_presensi || 0);
                        $('#ringkasan-belum-presensi').text(ringkasan.belum_presensi || 0);

                        $('#ringkasan-hadir').text(ringkasan.hadir || 0);
                        $('#ringkasan-telat').text(ringkasan.telat || 0);
                        $('#ringkasan-alpa').text(ringkasan.alpa || 0);
                        $('#ringkasan-izin').text(ringkasan.izin || 0);
                        $('#ringkasan-sakit').text(ringkasan.sakit || 0);
                        $('#ringkasan-libur').text(ringkasan.libur || 0);

                        $('#ringkasan-belum-pulang').text(ringkasan.belum_pulang || 0);
                        $('#ringkasan-pulang').text(ringkasan.pulang || 0);
                        $('#ringkasan-pulang-cepat').text(ringkasan.pulang_cepat || 0);
                    } else {
                        KadoelAjax.handleError(result);
                    }
                },
                error: function(xhr) {
                    $("#block-ringkasan").LoadingOverlay("hide");

                    if (xhr.status == 403) {
                        notifikasi('info', 'right', 'Token Kadaluarsa, Silahkan Reload Halaman Terlebih Dahulu');
                    } else {
                        notifikasi('danger', 'right', 'Gagal mengambil ringkasan presensi');
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
                    data: 'is_manual'
                },
                {
                    data: 'action'
                }
            ],
            responsive: true,
            order: [
                [1, 'desc']
            ],
            columnDefs: [{
                    targets: [1],
                    visible: false
                },
                {
                    targets: [0, 11],
                    orderable: false
                },
                {
                    targets: [0, 11],
                    searchable: false
                },
                {
                    targets: [0, 7, 9, 10, 11],
                    className: 'text-center'
                },
                {
                    targets: [0, 11],
                    width: '8%'
                }
            ]
        });

        if (typeof flatpickr !== 'undefined') {
            flatpickr('#filter-tanggal', {
                dateFormat: 'Y-m-d',
                onChange: function() {
                    data_presensi.ajax.reload();
                    refreshRingkasan();
                }
            });
        }

        $('#btn-refresh-ringkasan').on('click', function() {
            refreshRingkasan();
        });

        $('#btn-sinkron-presensi').on('click', function() {
            let tanggal = ambilTanggalFilter();

            Swal.fire({
                title: 'PRESENSI',
                html: 'Sinkron presensi untuk tanggal <b>' + KadoelHelper.toTanggalIndonesia(tanggal) + '</b>?',
                showClass: {
                    popup: 'animate__animated animate__zoomIn'
                },
                hideClass: {
                    popup: 'animate__animated animate__zoomOut'
                },
                imageUrl: '<?= base_url('assets/media/favicons/apple-touch-icon-180x180.png') ?>',
                imageWidth: 128,
                imageHeight: 128,
                imageAlt: 'PRESENSI',
                showCancelButton: true,
                confirmButtonColor: '#65A30D',
                cancelButtonColor: '#d33',
                confirmButtonText: '<i class="fa fa-check"></i> Proses',
                cancelButtonText: '<i class="fas fa-times"></i> Batal',
                allowEscapeKey: false,
                allowOutsideClick: false,
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#block-presensi").LoadingOverlay("show");

                    $.ajax({
                        type: 'POST',
                        url: '<?= base_url('admin/presensi/sinkron') ?>',
                        dataType: 'JSON',
                        data: {
                            [csrfToken]: csrfHash,
                            tanggal: tanggal
                        },
                        success: function(result) {
                            $("#block-presensi").LoadingOverlay("hide");

                            if (result.sukses) {
                                notifikasi('success', 'right', result.pesan);
                                data_presensi.ajax.reload();
                                refreshRingkasan();
                            } else {
                                KadoelAjax.handleError(result);
                            }
                        },
                        error: function(xhr) {
                            $("#block-presensi").LoadingOverlay("hide");

                            if (xhr.status == 403) {
                                notifikasi('info', 'right', 'Silahkan Reload Halaman Terlebih Dahulu, Kemudian Ulangi Proses');
                            } else {
                                notifikasi('danger', 'right', 'Gagal generate ALPA');
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
            $('#detail-is_manual').text('-');
            $('#detail-catatan_admin').text('-');
        }

        $('#presensi-tabel').on('click', '#act-detail', function() {
            let id = $(this).data('id');

            resetDetail();
            $("#block-content-detail").LoadingOverlay("show");

            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/presensi/detail') ?>',
                dataType: 'JSON',
                data: {
                    [csrfToken]: csrfHash,
                    id: id
                },
                success: function(result) {
                    $("#block-content-detail").LoadingOverlay("hide");

                    if (result.sukses) {
                        const p = result.presensi || {};

                        $('#detail-kode_pegawai').text(p.kode_pegawai || '-');
                        $('#detail-nama_pegawai').text(p.nama_pegawai || '-');
                        $('#detail-jenis_kelamin').text(
                            p.jenis_kelamin === 'L' ? 'Laki-Laki' :
                            (p.jenis_kelamin === 'P' ? 'Perempuan' : '-')
                        );
                        $('#detail-no_hp').text(p.no_hp || '-');
                        $('#detail-alamat').text(p.alamat || '-');
                        $('#detail-tanggal').text(p.tanggal || '-');
                        $('#detail-shift').text(p.nama_shift || '-');
                        $('#detail-jam_datang').text(p.jam_datang || '-');
                        $('#detail-status_datang').text(p.status_datang || '-');
                        $('#detail-jam_pulang').text(p.jam_pulang || '-');
                        $('#detail-status_pulang').text(p.status_pulang || '-');
                        $('#detail-menit_telat').text(p.menit_telat ?? 0);
                        $('#detail-menit_pulang_cepat').text(p.menit_pulang_cepat ?? 0);
                        $('#detail-is_manual').text(parseInt(p.is_manual) === 1 ? 'Manual' : 'Scan');
                        $('#detail-catatan_admin').text(p.catatan_admin || '-');

                        $('#modal-detail').modal('show');
                    } else {
                        KadoelAjax.handleError(result);
                    }
                },
                error: function(xhr) {
                    $("#block-content-detail").LoadingOverlay("hide");

                    if (xhr.status == 403) {
                        notifikasi('info', 'right', 'Token Kadaluarsa, Silahkan Reload Halaman Terlebih Dahulu');
                    } else {
                        notifikasi('danger', 'right', 'Gagal mengambil detail presensi');
                    }
                }
            });
        });
    });
</script>