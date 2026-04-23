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

        let data_hari_libur = $('#hari-libur-tabel').DataTable({
            destroy: true,
            processing: true,
            pagingType: 'full_numbers',
            serverSide: true,
            searching: true,
            paging: true,
            info: true,
            responsive: true,
            language: {
                url: '<?= base_url("assets/plugins/DataTablesbs5/plugins/id.json"); ?>'
            },
            ajax: {
                url: '<?= base_url("admin/libur"); ?>',
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
                    data: 'tanggal'
                },
                {
                    data: 'nama_libur'
                },
                {
                    data: 'keterangan'
                },
                {
                    data: 'action'
                }
            ],
            order: [
                [2, 'asc']
            ],
            columnDefs: [{
                    targets: [1],
                    visible: false
                },
                {
                    targets: [0, 5],
                    orderable: false
                },
                {
                    targets: [0, 5],
                    searchable: false
                },
                {
                    targets: [0, 2, 5],
                    className: 'text-center'
                },
                {
                    targets: [5],
                    className: 'dt-body-center',
                    width: '8%'
                }
            ]
        });

        function clear_errors_tambah() {
            const fields = ['tanggal', 'nama_libur', 'keterangan'];

            fields.forEach(function(field) {
                $('#' + field).removeClass('is-invalid');
                $('#error-' + field).html('').hide();
            });
        }

        function clear_errors_edit() {
            const fields = ['edit-tanggal', 'edit-nama_libur', 'edit-keterangan'];

            fields.forEach(function(field) {
                $('#' + field).removeClass('is-invalid');
                $('#error-' + field).html('').hide();
            });
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

        function renderPegawaiTerdampak(items) {
            let html = '';

            if (!items.length) {
                html = `
                    <tr>
                        <td colspan="4" class="text-center">Tidak ada pegawai terdampak</td>
                    </tr>
                `;
            } else {
                items.forEach(function(item) {
                    html += `
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input tetap-kerja-item" value="${item.pegawai_id}">
                            </td>
                            <td>${item.kode_pegawai} - ${item.nama_pegawai}</td>
                            <td class="text-center">${item.nama_shift ?? '-'}</td>
                            <td>${item.catatan ?? '-'}</td>
                        </tr>
                    `;
                });
            }

            $('#list-pegawai-terdampak').html(html);
        }

        function renderPegawaiTerdampakEdit(items, checkedIds = []) {
            let html = '';
            checkedIds = checkedIds.map(function(item) {
                return parseInt(item);
            });

            if (!items.length) {
                html = `
                    <tr>
                        <td colspan="4" class="text-center">Tidak ada pegawai terdampak</td>
                    </tr>
                `;
            } else {
                items.forEach(function(item) {
                    const checked = checkedIds.includes(parseInt(item.pegawai_id)) ? 'checked' : '';

                    html += `
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input edit-tetap-kerja-item" name="edit_tetap_kerja_ids[]" value="${item.pegawai_id}" ${checked}>
                            </td>
                            <td>${item.kode_pegawai} - ${item.nama_pegawai}</td>
                            <td class="text-center">${item.nama_shift ?? '-'}</td>
                            <td>${item.catatan ?? '-'}</td>
                        </tr>
                    `;
                });
            }

            $('#edit-list-pegawai-terdampak').html(html);
        }

        function reset_form_tambah() {
            $('#tanggal').val('');
            $('#nama_libur').val('');
            $('#keterangan').val('');
            clear_errors_tambah();

            const el = document.querySelector('#tanggal');
            if (el && el._flatpickr) {
                el._flatpickr.clear();
            }
        }

        function tutup_modal_override() {
            $('#override-hari_libur_id').val('');
            $('#list-pegawai-terdampak').html('');
            jQuery('#modal-konfirmasi-override').modal('hide');
        }

        function tutup_modal_edit() {
            $('#edit-id').val('');
            $('#edit-tanggal').val('');
            $('#edit-nama_libur').val('');
            $('#edit-keterangan').val('');
            $('#edit-list-pegawai-terdampak').html(`
                <tr>
                    <td colspan="4" class="text-center">Tidak ada data</td>
                </tr>
            `);

            const el = document.querySelector('#edit-tanggal');
            if (el && el._flatpickr) {
                el._flatpickr.clear();
            }

            jQuery('#modal-ubah').modal('hide');
        }

        // =========================
        // SIMPAN HARI LIBUR
        // =========================
        $('#form_tambah_hari_libur').on('submit', function(e) {
            $("#block-konten-tambah").LoadingOverlay("show");
            clear_errors_tambah();
            e.preventDefault();

            let fd = new FormData(this);
            fd.append([csrfToken], csrfHash);

            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/libur/simpan') ?>',
                dataType: 'JSON',
                cache: false,
                contentType: false,
                processData: false,
                data: fd,
                success: function(result) {
                    $("#block-konten-tambah").LoadingOverlay("hide");

                    if (result['sukses']) {
                        reset_form_tambah();
                        data_hari_libur.ajax.reload();

                        if (result['butuh_konfirmasi']) {
                            $('#override-hari_libur_id').val(result['hari_libur_id']);
                            renderPegawaiTerdampak(result['pegawai_terdampak'] || []);
                            $('#modal-konfirmasi-override').removeClass('fade');
                            jQuery('#modal-konfirmasi-override').modal('show');
                        } else {
                            notifikasi('success', 'right', result['pesan']);
                        }
                    } else {
                        KadoelAjax.handleError(result);
                    }
                },
                error: function(xhr) {
                    $("#block-konten-tambah").LoadingOverlay("hide");

                    if (xhr.status == 403) {
                        notifikasi('info', 'right', 'Token Kadaluarsa, Silahkan Reload Halaman Terlebih Dahulu');
                    } else {
                        console.log(xhr.status + ': ' + xhr.statusText);
                    }
                }
            });
        });

        // =========================
        // KONFIRMASI OVERRIDE SIMPAN
        // =========================
        $('#simpan-override-libur').on('click', function() {
            $("#block-content-konfirmasi-override").LoadingOverlay("show");

            let hariLiburId = $('#override-hari_libur_id').val();
            let tetapKerjaIds = [];

            $('.tetap-kerja-item:checked').each(function() {
                tetapKerjaIds.push($(this).val());
            });

            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/libur/konfirmasi-override') ?>',
                dataType: 'JSON',
                data: {
                    [csrfToken]: csrfHash,
                    hari_libur_id: hariLiburId,
                    tetap_kerja_ids: tetapKerjaIds
                },
                success: function(result) {
                    $("#block-content-konfirmasi-override").LoadingOverlay("hide");

                    if (result['sukses']) {
                        notifikasi('success', 'right', result['pesan']);
                        tutup_modal_override();
                        data_hari_libur.ajax.reload();
                    } else {
                        KadoelAjax.handleError(result);
                    }
                },
                error: function(xhr) {
                    $("#block-content-konfirmasi-override").LoadingOverlay("hide");

                    if (xhr.status == 403) {
                        notifikasi('info', 'right', 'Token Kadaluarsa, Silahkan Reload Halaman Terlebih Dahulu');
                    } else {
                        console.log(xhr.status + ': ' + xhr.statusText);
                    }
                }
            });
        });

        // =========================
        // AMBIL DATA EDIT
        // =========================
        $('#hari-libur-tabel').on('click', '#act-edit', function() {
            let id = $(this).data('id');

            $("#block-content-ubah").LoadingOverlay("show");
            clear_errors_edit();
            $('#modal-ubah').removeClass('fade');

            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/libur/edit') ?>',
                dataType: 'JSON',
                data: {
                    [csrfToken]: csrfHash,
                    id: id
                },
                success: function(result) {
                    $("#block-content-ubah").LoadingOverlay("hide");

                    if (result['sukses']) {
                        const hariLibur = result['hari_libur'] || {};
                        const pegawaiTerdampak = result['pegawai_terdampak'] || [];
                        const pegawaiTetapKerjaIds = result['pegawai_tetap_kerja_ids'] || [];

                        $('#edit-id').val(hariLibur.id);
                        $('#edit-nama_libur').val(hariLibur.nama_libur ?? '');
                        $('#edit-keterangan').val(hariLibur.keterangan ?? '');
                        setFlatpickrTanggal('#edit-tanggal', hariLibur.tanggal ?? '');

                        renderPegawaiTerdampakEdit(pegawaiTerdampak, pegawaiTetapKerjaIds);

                        jQuery('#modal-ubah').modal('show');
                    } else {
                        KadoelAjax.handleError(result);
                    }
                },
                error: function(xhr) {
                    $("#block-content-ubah").LoadingOverlay("hide");

                    if (xhr.status == 403) {
                        notifikasi('info', 'right', 'Token Kadaluarsa, Silahkan Reload Halaman Terlebih Dahulu');
                    } else {
                        console.log(xhr.status + ': ' + xhr.statusText);
                    }
                }
            });
        });

        // =========================
        // TUTUP MODAL EDIT
        // =========================
        $('#modal-ubah').on('click', '#tutup-modal', function() {
            $('#modal-ubah').addClass('fade');
            tutup_modal_edit();
        });

        // =========================
        // SUBMIT EDIT
        // =========================
        $('#form_edit_hari_libur').on('submit', function(e) {
            $('#update-data').prop('disabled', true);
            $("#block-content-ubah").LoadingOverlay("show");
            clear_errors_edit();
            e.preventDefault();

            let id = $('#edit-id').val();
            let fd = new FormData(this);
            fd.append([csrfToken], csrfHash);

            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/libur/update') ?>/' + id,
                dataType: 'JSON',
                cache: false,
                contentType: false,
                processData: false,
                data: fd,
                success: function(result) {
                    $("#block-content-ubah").LoadingOverlay("hide");
                    $('#update-data').prop('disabled', false);

                    if (result['sukses']) {
                        notifikasi('success', 'right', result['pesan']);
                        tutup_modal_edit();
                        data_hari_libur.ajax.reload();
                    } else {
                        KadoelAjax.handleError(result);
                    }
                },
                error: function(xhr) {
                    $("#block-content-ubah").LoadingOverlay("hide");
                    $('#update-data').prop('disabled', false);

                    if (xhr.status == 403) {
                        notifikasi('info', 'right', 'Token Kadaluarsa, Silahkan Reload Halaman Terlebih Dahulu');
                    } else {
                        console.log(xhr.status + ': ' + xhr.statusText);
                    }
                }
            });
        });

        // =========================
        // DELETE
        // =========================
        $('#hari-libur-tabel').on('click', '#act-delete', function() {
            let id = $(this).data('id');
            let nama = $(this).data('nama');

            Swal.fire({
                title: 'PRESENSI',
                html: 'Hapus Hari Libur <b>' + nama + '</b>?',
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
                confirmButtonText: '<i class="fa fa-trash-can"></i> Hapus',
                cancelButtonText: '<i class="fas fa-times"></i> Batal',
                allowEscapeKey: false,
                allowOutsideClick: false,
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#block-tabel").LoadingOverlay("show");

                    $.ajax({
                        type: 'POST',
                        url: '<?= base_url('admin/libur/delete') ?>',
                        dataType: 'JSON',
                        data: {
                            [csrfToken]: csrfHash,
                            id: id,
                        },
                        success: function(result) {
                            $("#block-tabel").LoadingOverlay("hide");

                            if (result['sukses']) {
                                notifikasi('success', 'right', result['pesan']);
                                data_hari_libur.ajax.reload();
                            } else {
                                KadoelAjax.handleError(result);
                            }
                        },
                        error: function(xhr) {
                            $("#block-tabel").LoadingOverlay("hide");

                            if (xhr.status == 403) {
                                notifikasi('info', 'right', 'Silahkan Reload Halaman Terlebih Dahulu, Kemudian Ulangi Hapus');
                            } else {
                                console.log(xhr.status + ': ' + xhr.statusText);
                            }
                        }
                    });
                }
            });
        });
    });
</script>

<script>
    Codebase.helpersOnLoad(['jq-select2', 'js-flatpickr']);

    const minTanggalHariLibur = '<?= date('Y-m-01'); ?>';

    document.addEventListener('DOMContentLoaded', function() {
        if (typeof flatpickr !== 'undefined') {
            flatpickr('#tanggal', {
                dateFormat: 'Y-m-d',
                minDate: minTanggalHariLibur
            });

            flatpickr('#edit-tanggal', {
                dateFormat: 'Y-m-d',
                minDate: minTanggalHariLibur,
                clickOpens: false,
                allowInput: false
            });
        }
    });
</script>