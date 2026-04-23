<script src="/assets/plugins/select2/js/select2.full.min.js"></script>
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

        <?= select2('is_active'); ?>
        <?= select2('jenis_kelamin'); ?>
        <?= select2('jabatan_id'); ?>
        <?= select2_modal('edit-is_active', 'modal-ubah'); ?>
        <?= select2_modal('edit-jenis_kelamin', 'modal-ubah'); ?>
        <?= select2_modal('edit-jabatan_id', 'modal-ubah'); ?>

        let data_pegawai = $('#pegawai-tabel').DataTable({
            destroy: true,
            processing: true,
            pagingType: 'full_numbers',
            serverSide: true,
            searching: true,
            paging: true,
            info: true,
            language: {
                url: '<?= base_url("assets/plugins/DataTablesbs5/plugins/id.json"); ?>'
            },
            ajax: {
                url: '<?= base_url("admin/pegawai"); ?>',
                method: 'POST',
                data: {
                    [csrfToken]: csrfHash
                },
                async: true,
                error: function(xhr, error, code) {
                    if (xhr.status == 403) {
                        notifikasi('info', 'right', 'Token Kadaluarsa, Silahkan Reload Halaman Terlebih Dahulu');
                    } else {
                        console.log(xhr, code)
                    } // 404
                }
            },
            columns: [{
                    data: '#'
                },
                {
                    data: 'id'
                },
                {
                    data: 'nama_pegawai'
                },
                {
                    data: 'tempat_lahir'
                },
                {
                    data: 'tanggal_lahir'
                },
                {
                    data: 'is_active'
                },
                {
                    data: 'action'
                }
            ],
            responsive: true,
            order: [
                [1, 'asc'],
            ],
            columnDefs: [{
                    targets: [1],
                    visible: false
                }, {
                    targets: [0, -1, 1],
                    orderable: false
                },
                {
                    targets: [0, -1, 1],
                    searchable: false
                },
                {
                    'targets': [0, 5],
                    'className': 'text-center',
                    'width': '8%'
                },
                {
                    targets: [-1],
                    className: 'dt-body-center',
                    'width': '8%'
                }
            ]
        });

        //----------- TAMBAH DATA -----------------------------

        function clear_errors_tambah() {
            const fields = [
                'nama_pegawai',
                'jenis_kelamin',
                'tempat_lahir',
                'tanggal_lahir',
                'no_hp',
                'alamat',
                'jabatan_id',
                'foto',
                'is_active'
            ];

            fields.forEach(function(field) {
                $('#' + field).removeClass('is-invalid');
                $('#error-' + field).html('').hide();
            });
        }

        $('#form_tambah_pegawai').on('submit', function(e) {
            $("#block-konten-tambah").LoadingOverlay("show");
            clear_errors_tambah();
            e.preventDefault();
            var fd = new FormData(this);
            fd.append([csrfToken], csrfHash);

            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/pegawai/simpan') ?>',
                dataType: 'JSON',
                cache: false,
                contentType: false,
                processData: false,
                data: fd,
                success: function(result) {
                    $("#block-konten-tambah").LoadingOverlay("hide");
                    if (result['sukses']) {
                        $('#nama_pegawai').val('');
                        $('#jenis_kelamin').val('');
                        $('#tempat_lahir').val('');
                        $('#tanggal_lahir').val('');
                        $('#no_hp').val('');
                        $('#alamat').val('');
                        $('#jabatan_id').val('');
                        $('#is_active').val('');
                        $('#is_active').trigger('change');
                        $('#jabatan_id').trigger('change');
                        $('#jenis_kelamin').trigger('change');

                        clear_errors_tambah();
                        notifikasi('success', 'right', result['pesan']);
                        data_pegawai.ajax.reload();

                    } else {
                        KadoelAjax.handleError(result);
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    if (xhr.status == 403) {
                        notifikasi('info', 'right', 'Token Kadaluarsa, Silahkan Reload Halaman Terlebih Dahulu');
                    } else {
                        console.log(xhr, code)
                    } // 404
                }
            });
        });
        //--------------- END TAMBAH DATA -----------------

        //--------------- EDIT DATA -----------------------
        function clear_errors_edit() {
            const fields = [
                'edit-nama_pegawai',
                'edit-jenis_kelamin',
                'edit-tempat_lahir',
                'edit-tanggal_lahir',
                'edit-no_hp',
                'edit-alamat',
                'edit-jabatan_id',
                'edit-foto',
                'edit-is_active'
            ];

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

        function resetPreviewFotoEdit(src) {
            const fotoSrc = src || 'default.png';

            $('#edit-foto-src').val(fotoSrc);
            $('#edit-preview').attr('src', '/assets/media/pegawai/' + fotoSrc);
            $('#edit-foto').val('');
            $('#edit-btn-reset').hide();
        }

        $('#pegawai-tabel').on('click', '#act-edit', function() {
            let id = $(this).data('id');

            $("#block-content-ubah").LoadingOverlay("show");
            clear_errors_edit();
            $('#modal-ubah').removeClass('fade');

            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/pegawai/edit') ?>',
                dataType: 'JSON',
                data: {
                    [csrfToken]: csrfHash,
                    id: id
                },
                success: function(result) {
                    $("#block-content-ubah").LoadingOverlay("hide");

                    if (result['sukses']) {
                        const pegawai = result['pegawai'] || {};
                        const src = pegawai.foto ?? "default.png";

                        $('#edit-id').val(pegawai.id ?? id);
                        $('#edit-nama_pegawai').val(pegawai.nama_pegawai ?? '');
                        $('#edit-tempat_lahir').val(pegawai.tempat_lahir ?? '');
                        $('#edit-no_hp').val(pegawai.no_hp ?? '');
                        $('#edit-alamat').val(pegawai.alamat ?? '');

                        $('#edit-jenis_kelamin').val(pegawai.jenis_kelamin ?? '').trigger('change');
                        $('#edit-jabatan_id').val(pegawai.jabatan_id ?? '').trigger('change');
                        $('#edit-is_active').val(pegawai.is_active ?? '1').trigger('change');

                        setFlatpickrTanggal('#edit-tanggal_lahir', pegawai.tanggal_lahir ?? '');

                        resetPreviewFotoEdit(src);

                        $('#modal-ubah').modal('show');
                    } else {
                        KadoelAjax.handleError(result);
                    }
                },
                error: function(xhr, status, error) {
                    $("#block-content-ubah").LoadingOverlay("hide");

                    if (xhr.status == 403) {
                        notifikasi('info', 'right', 'Token Kadaluarsa, Silahkan Reload Halaman Terlebih Dahulu');
                    } else {
                        console.log(xhr.status + ': ' + xhr.statusText);
                    }
                }
            });
        });

        function tutup_modal() {
            $('#edit-id').val('');
            $('#edit-nama_pegawai').val('');
            $('#edit-tempat_lahir').val('');
            $('#edit-no_hp').val('');
            $('#edit-alamat').val('');

            $('#edit-jenis_kelamin').val('').trigger('change');
            $('#edit-jabatan_id').val('').trigger('change');
            $('#edit-is_active').val('1').trigger('change');

            setFlatpickrTanggal('#edit-tanggal_lahir', '');

            resetPreviewFotoEdit('');

            jQuery('#modal-ubah').modal('hide');
        }

        $('#modal-ubah').on('click', '#tutup-modal', function() {
            $('#modal-ubah').addClass('fade');
            tutup_modal();
        });

        $('#form_edit_pegawai').on('submit', function(e) {
            e.preventDefault();
            $('#update-data').prop('disabled', true);
            const id = $('#edit-id').val();
            clear_errors_edit();
            $("#block-content-ubah").LoadingOverlay("show");
            var fd = new FormData(this);
            fd.append([csrfToken], csrfHash);

            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/pegawai/update') ?>' + '/' + id,
                dataType: 'JSON',
                cache: false,
                contentType: false,
                processData: false,
                data: fd,
                success: function(result) {
                    $("#block-content-ubah").LoadingOverlay("hide");
                    console.log(result);
                    if (result['sukses']) {
                        tutup_modal();
                        clear_errors_edit();
                        notifikasi('success', 'right', result['pesan']);
                        data_pegawai.ajax.reload();
                    } else {
                        KadoelAjax.handleError(result);
                    }

                    $('#update-data').prop('disabled', false);
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    if (xhr.status == 403) {
                        notifikasi('info', 'right', 'Token Kadaluarsa, Silahkan Reload Halaman Terlebih Dahulu');
                    } else {
                        console.log(xhr.status + ': ' + xhr.statusText)
                    } // 404
                }
            });
        });
        //------------------ END EDIT DATA -------------------------------

        //------------------ DELETE DATA ---------------------------
        $('#pegawai-tabel').on('click', '#act-delete', function() {
            let id = $(this).data('id');
            let nama = $(this).data('nama');

            Swal.fire({
                title: 'PRESENSI',
                html: 'Hapus Pegawai ' + nama + '?',
                showClass: {
                    popup: 'animate__animated animate__zoomIn'
                },
                hideClass: {
                    popup: 'animate__animated animate__zoomOut'
                },
                imageUrl: '<?= base_url('assets/media/favicons/apple-touch-icon-180x180.png') ?>',
                imageWidth: 128,
                imageHeight: 128,
                imageAlt: 'ANTRIAN',
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
                        url: '<?= base_url('admin/pegawai/delete') ?>',
                        dataType: 'JSON',
                        data: {
                            [csrfToken]: csrfHash,
                            id: id,
                        },
                        success: function(result) {
                            $("#block-tabel").LoadingOverlay("hide");
                            if (result['sukses']) {
                                notifikasi('success', 'right', 'Data Pegawai ' + nama + ' Berhasil Dihapus');
                                data_pegawai.ajax.reload();
                            } else {
                                KadoelAjax.handleError(result);
                            }
                        },
                        error: function(xhr, status, error) {
                            if (xhr.status == 403) {
                                notifikasi('info', 'right', 'Silahkan Reload Halaman Terlebih Dahulu, Kemudian Ulangi Hapus');
                            } else {
                                console.log(xhr.status + ': ' + xhr.statusText)
                            } // 404
                        }
                    });
                }
            })
        });
        //-------------------- END DELETE DATA -----------------------------------

        //--------------- DETAIL DATA ULTRA PREMIUM -----------------------
        function reset_detail_pegawai() {
            $('#detail-foto').attr('src', '/assets/media/pegawai/default.png');
            $('#detail-qrcode').attr('src', '/assets/media/qrcode/default.png');

            $('#detail-nama_pegawai').text('-');
            $('#detail-nama_pegawai_2').text('-');
            $('#detail-summary-nama').text('-');

            $('#detail-kode_pegawai').text('-');
            $('#detail-kode_pegawai_3').text('-');
            $('#detail-kode_pegawai_4').text('-');
            $('#detail-summary-kode').text('-');

            $('#detail-jabatan').text('-');
            $('#detail-jabatan-badge').text('-');

            $('#detail-jenis_kelamin').text('-');

            $('#detail-tempat_lahir').text('-');
            $('#detail-tanggal_lahir').text('-');

            $('#detail-no_hp').text('-');

            $('#detail-alamat').text('-');

            $('#detail-status_text_3').text('-');
            $('#detail-summary-status').text('-');

            $('#detail-is_active')
                .removeClass('bg-success bg-danger bg-secondary')
                .addClass('bg-secondary')
                .text('-');
        }

        function setDetailActionButtons(data) {
            const id = data;
            const baseUrl = "<?= base_url() ?>";

            document.getElementById('btn-download-kartu').href =
                baseUrl + '/admin/pegawai/kartu/' + id;

            document.getElementById('btn-download-qr').href =
                baseUrl + '/admin/pegawai/download/' + id;
        }

        $('#pegawai-tabel').on('click', '#act-detail', function() {
            let id = $(this).data('id');

            reset_detail_pegawai();
            $("#block-content-detail").LoadingOverlay("show");

            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/pegawai/edit') ?>',
                dataType: 'JSON',
                data: {
                    [csrfToken]: csrfHash,
                    id: id
                },
                success: function(result) {
                    $("#block-content-detail").LoadingOverlay("hide");
                    console.log(result);

                    if (result['sukses']) {
                        const pegawai = result['pegawai'] || {};
                        const src = pegawai.foto ?
                            '/assets/media/pegawai/' + pegawai.foto :
                            '/assets/media/pegawai/default.png';

                        const jenisKelamin = pegawai.jenis_kelamin === 'L' ?
                            'Laki-Laki' :
                            (pegawai.jenis_kelamin === 'P' ? 'Perempuan' : '-');

                        const statusAktif = parseInt(pegawai.is_active) === 1;
                        const statusText = statusAktif ? 'Aktif' : 'Tidak Aktif';

                        const tanggalLahir = pegawai.tanggal_lahir ?
                            KadoelHelper.toTanggalIndonesia(pegawai.tanggal_lahir) :
                            '-';

                        const qrcode = pegawai.qrcode ?
                            '/assets/media/qrcode/' + pegawai.qrcode :
                            '/assets/media/qrcode/default.png';

                        $('#detail-foto').attr('src', src);

                        $('#detail-nama_pegawai').text(pegawai.nama_pegawai || '-');
                        $('#detail-nama_pegawai_2').text(pegawai.nama_pegawai || '-');
                        $('#detail-summary-nama').text(pegawai.nama_pegawai || '-');

                        $('#detail-kode_pegawai').text(pegawai.kode_pegawai || '-');
                        $('#detail-kode_pegawai_3').text(pegawai.kode_pegawai || '-');
                        $('#detail-kode_pegawai_4').text(pegawai.kode_pegawai || '-');
                        $('#detail-summary-kode').text(pegawai.kode_pegawai || '-');

                        $('#detail-jabatan').text(pegawai.nama_jabatan || '-');
                        $('#detail-jabatan-badge').text(pegawai.nama_jabatan || '-');

                        $('#detail-jenis_kelamin').text(jenisKelamin);

                        $('#detail-tempat_lahir').text(pegawai.tempat_lahir || '-');
                        $('#detail-tanggal_lahir').text(tanggalLahir);

                        $('#detail-no_hp').text(pegawai.no_hp || '-');

                        $('#detail-alamat').text(pegawai.alamat || '-');

                        $('#detail-status_text_3').text(statusText);
                        $('#detail-summary-status').text(statusText);

                        $('#detail-qrcode').attr('src', qrcode);

                        $('#detail-is_active')
                            .removeClass('bg-secondary bg-success bg-danger')
                            .addClass(statusAktif ? 'bg-success' : 'bg-danger')
                            .text(statusText);
                        setDetailActionButtons(pegawai.id);
                        $('#modal-detail').modal('show');
                    } else {
                        KadoelAjax.handleError(result);
                    }
                },
                error: function(xhr, status, error) {
                    $("#block-content-detail").LoadingOverlay("hide");

                    if (xhr.status == 403) {
                        notifikasi('info', 'right', 'Token Kadaluarsa, Silahkan Reload Halaman Terlebih Dahulu');
                    } else {
                        console.log(xhr.status + ': ' + xhr.statusText);
                        notifikasi('danger', 'right', 'Gagal mengambil detail data pegawai');
                    }
                }
            });
        });
    });
</script>
<script>
    Codebase.helpersOnLoad(['jq-select2', 'js-flatpickr']);

    const maxTanggalHariLibur = '<?= date('Y-m-d'); ?>';
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof flatpickr !== 'undefined') {
            flatpickr('#tanggal_lahir', {
                dateFormat: 'Y-m-d',
                maxDate: maxTanggalHariLibur
            });

            flatpickr('#edit-tanggal_lahir', {
                dateFormat: 'Y-m-d',
                maxDate: maxTanggalHariLibur
            });
        }
    });

    function onlyNumberKey(evt) {
        var ASCIICode = (evt.which) ? evt.which : evt.keyCode;
        if (ASCIICode > 31 && (ASCIICode < 48 || ASCIICode > 57)) {
            return false;
        }
        return true;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('foto');
        const preview = document.getElementById('preview');
        const btnReset = document.getElementById('btn-reset');

        let hadFileBefore = false;

        input.addEventListener('click', function() {
            hadFileBefore = input.value !== '';
        });

        input.addEventListener('change', function() {
            const file = input.files[0];

            if (!file) {
                if (hadFileBefore) {
                    resetImage();
                }
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                btnReset.style.display = 'block';
            };
            reader.readAsDataURL(file);
        });

        btnReset.addEventListener('click', resetImage);

        function resetImage() {
            // const src = $('#logo-src').val();
            input.value = '';
            preview.src = '/assets/media/pegawai/default.png';
            btnReset.style.display = 'none';
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('edit-foto');
        const preview = document.getElementById('edit-preview');
        const btnReset = document.getElementById('edit-btn-reset');

        let hadFileBefore = false;

        // sebelum dialog file dibuka
        input.addEventListener('click', function() {
            hadFileBefore = input.value !== '';
        });

        // setelah dialog ditutup
        input.addEventListener('change', function() {
            const file = input.files[0];

            // CANCEL → reset preview
            if (!file) {
                if (hadFileBefore) {
                    resetImage();
                }
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                btnReset.style.display = 'block';
            };
            reader.readAsDataURL(file);
        });

        // tombol reset
        btnReset.addEventListener('click', resetImage);

        function resetImage() {
            const src = $('#edit-foto-src').val();
            input.value = '';
            preview.src = '/assets/media/pegawai/' + src;
            btnReset.style.display = 'none';
        }
    });
</script>