<script type="text/javascript">
    $(document).ready(function() {
        let csrfToken = '<?= csrf_token(); ?>';
        let csrfHash = '<?= csrf_hash(); ?>';
        <?= notifikasi(); ?>

        <?php if (session()->getFlashdata('sukses')) : ?>
            notifikasi('success', 'right', '<?= session()->getFlashdata('sukses'); ?>');
        <?php elseif (session()->getFlashdata('gagal')) : ?>
            notifikasi('danger', 'right', '<?= session()->getFlashdata("gagal"); ?>');
        <?php endif; ?>

        function clear_errors_tambah() {
            const fields = [
                'nama_usaha',
                'alamat',
                'telepon',
                'email',
                'logo'
            ];

            fields.forEach(function(field) {
                $('#' + field).removeClass('is-invalid');
                $('#error-' + field).html('').hide();
            });
        }

        $('#form-pengaturan').on('submit', function(e) {
            $("#block-konten-tambah").LoadingOverlay("show");
            clear_errors_tambah();
            e.preventDefault();

            var fd = new FormData(this);
            fd.append([csrfToken], csrfHash);

            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/pengaturan/simpan') ?>',
                dataType: 'JSON',
                cache: false,
                contentType: false,
                processData: false,
                data: fd,
                success: function(result) {
                    $("#block-konten-tambah").LoadingOverlay("hide");

                    if (result['sukses']) {
                        $('#logo').val('');
                        $('#btn-reset').hide();
                        notifikasi('success', 'right', result['pesan']);
                    } else {
                        KadoelAjax.handleError(result);
                    }
                },
                error: function(xhr) {
                    $("#block-konten-tambah").LoadingOverlay("hide");

                    if (xhr.status == 403) {
                        notifikasi('info', 'right', 'Token Kadaluarsa, Silahkan Reload Halaman Terlebih Dahulu');
                    } else {
                        console.log(xhr);
                    }
                }
            });
        });
    });
</script>

<script>
    function onlyNumberKey(evt) {
        var ASCIICode = (evt.which) ? evt.which : evt.keyCode;
        if (ASCIICode > 31 && (ASCIICode < 48 || ASCIICode > 57)) {
            return false;
        }
        return true;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('logo');
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
            const src = $('#logo-src').val();
            input.value = '';
            preview.src = src;
            btnReset.style.display = 'none';
        }
    });
</script>