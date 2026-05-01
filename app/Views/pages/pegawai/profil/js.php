<script type="text/javascript">
    $(document).ready(function() {
        let csrfToken = '<?= csrf_token(); ?>';
        let csrfHash = '<?= csrf_hash(); ?>';
        let profilAktif = null;

        <?= loadingoverlay_fa(); ?>
        <?= notifikasi(); ?>

        loadProfil();

        function assetPegawai(fileName, withCache = false) {
            const file = fileName || 'default.png';
            let url = '/assets/media/pegawai/' + file;

            if (withCache) {
                url += '?v=' + Date.now();
            }

            return url;
        }

        function assetQr(fileName, withCache = false) {
            const file = fileName || 'default.png';
            let url = '/assets/media/qrcode/' + file;

            if (withCache) {
                url += '?v=' + Date.now();
            }

            return url;
        }

        function clear_errors_edit() {
            const fields = ['no_hp', 'alamat', 'foto'];

            fields.forEach(function(field) {
                $('#' + field).removeClass('is-invalid');
                $('#error-' + field).html('').hide();
            });
        }

        function loadProfil() {
            $('#block-content-detail').LoadingOverlay('show');

            $.ajax({
                type: 'GET',
                url: '<?= base_url('pegawai/profil/data'); ?>',
                dataType: 'JSON',
                success: function(result) {
                    $('#block-content-detail').LoadingOverlay('hide');

                    if (result['sukses']) {
                        profilAktif = result['profil'] || {};
                        renderProfil(profilAktif, true);
                    } else {
                        KadoelAjax.handleError(result);
                    }
                },
                error: function(xhr) {
                    $('#block-content-detail').LoadingOverlay('hide');

                    if (xhr.status == 403) {
                        notifikasi('info', 'right', 'Token Kadaluarsa, Silahkan Reload Halaman Terlebih Dahulu');
                    } else {
                        console.log(xhr.status + ': ' + xhr.statusText);
                        notifikasi('danger', 'right', 'Gagal mengambil data profil');
                    }
                }
            });
        }

        function renderProfil(pegawai, forceRefreshImage = false) {
            const src = assetPegawai(pegawai.foto, forceRefreshImage);
            const qrcode = assetQr(pegawai.qrcode, forceRefreshImage);

            const jenisKelamin = pegawai.jenis_kelamin === 'L' ?
                'Laki-Laki' :
                (pegawai.jenis_kelamin === 'P' ? 'Perempuan' : '-');

            const statusAktif = parseInt(pegawai.is_active) === 1;
            const statusText = statusAktif ? 'Aktif' : 'Tidak Aktif';

            const tanggalLahir = pegawai.tanggal_lahir ?
                KadoelHelper.toTanggalIndonesia(pegawai.tanggal_lahir) :
                '-';

            $('#detail-foto').attr('src', src);
            $('#detail-qrcode').attr('src', qrcode);

            $('#detail-nama_pegawai').text(pegawai.nama_pegawai || '-');
            $('#detail-nama_pegawai_2').text(pegawai.nama_pegawai || '-');
            $('#detail-summary-nama').text(pegawai.nama_pegawai || '-');

            $('#detail-kode_pegawai').text(pegawai.kode_pegawai || '-');
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

            $('#detail-is_active')
                .removeClass('bg-secondary bg-success bg-danger')
                .addClass(statusAktif ? 'bg-success' : 'bg-danger')
                .text(statusText);
        }

        function resetPreviewFoto(src, withCache = false) {
            const fotoSrc = src || 'default.png';

            $('#foto-src').val(fotoSrc);
            $('#preview').attr('src', assetPegawai(fotoSrc, withCache));
            $('#foto').val('');
            $('#btn-reset').hide();
        }

        $('#btn-edit-profil').on('click', function() {
            if (!profilAktif) {
                return;
            }

            clear_errors_edit();

            $('#no_hp').val(profilAktif.no_hp ?? '');
            $('#alamat').val(profilAktif.alamat ?? '');
            resetPreviewFoto(profilAktif.foto ?? 'default.png', true);

            $('#modal-ubah').removeClass('fade');
            $('#modal-ubah').modal('show');
        });

        function tutup_modal() {
            $('#no_hp').val('');
            $('#alamat').val('');

            resetPreviewFoto(profilAktif?.foto ?? 'default.png', true);

            clear_errors_edit();
            $('#modal-ubah').modal('hide');
        }

        $('#modal-ubah').on('click', '#tutup-modal', function() {
            $('#modal-ubah').addClass('fade');
            tutup_modal();
        });

        $('#form_edit_profil').on('submit', function(e) {
            e.preventDefault();

            $('#update-data').prop('disabled', true);
            clear_errors_edit();
            $('#block-content-ubah').LoadingOverlay('show');

            const fd = new FormData(this);
            fd.append([csrfToken], csrfHash);

            $.ajax({
                type: 'POST',
                url: '<?= base_url('pegawai/profil/update'); ?>',
                dataType: 'JSON',
                cache: false,
                contentType: false,
                processData: false,
                data: fd,
                success: function(result) {
                    $('#block-content-ubah').LoadingOverlay('hide');

                    if (result['sukses']) {
                        $('#foto').val('');
                        $('#btn-reset').hide();

                        notifikasi('success', 'right', result['pesan']);

                        $('#modal-ubah').modal('hide');

                        loadProfil();
                    } else {
                        KadoelAjax.handleError(result);
                    }

                    $('#update-data').prop('disabled', false);
                },
                error: function(xhr) {
                    $('#block-content-ubah').LoadingOverlay('hide');
                    $('#update-data').prop('disabled', false);

                    if (xhr.status == 403) {
                        notifikasi('info', 'right', 'Token Kadaluarsa, Silahkan Reload Halaman Terlebih Dahulu');
                    } else {
                        console.log(xhr.status + ': ' + xhr.statusText);
                    }
                }
            });
        });

        window.resetImageProfil = function() {
            const src = $('#foto-src').val() || 'default.png';

            $('#foto').val('');
            $('#preview').attr('src', assetPegawai(src, true));
            $('#btn-reset').hide();
        };

        $('#foto').on('click', function() {
            $(this).data('hadFileBefore', this.value !== '');
        });

        $('#foto').on('change', function() {
            const file = this.files && this.files[0];
            const hadFileBefore = $(this).data('hadFileBefore') === true;

            if (!file) {
                if (hadFileBefore) {
                    window.resetImageProfil();
                }
                return;
            }

            const reader = new FileReader();

            reader.onload = function(e) {
                $('#preview').attr('src', e.target.result);
                $('#btn-reset').show();
            };

            reader.readAsDataURL(file);
        });

        $('#btn-reset').on('click', function() {
            window.resetImageProfil();
        });
    });

    function onlyNumberKey(evt) {
        var ASCIICode = evt.which ? evt.which : evt.keyCode;

        if (ASCIICode > 31 && (ASCIICode < 48 || ASCIICode > 57)) {
            return false;
        }

        return true;
    }
</script>