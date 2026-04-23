<script src="/assets/plugins/select2/js/select2.full.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        let csrfToken = '<?= csrf_token(); ?>';
        let csrfHash = '<?= csrf_hash(); ?>';

        <?= loadingoverlay_fa(); ?>
        <?= notifikasi(); ?>
        <?= select2('filter_user_id'); ?>
        <?= select2('filter_action'); ?>
        <?= select2('filter_table_name'); ?>

        <?php if (session()->getFlashdata('sukses')) : ?>
            notifikasi('success', 'right', '<?= session()->getFlashdata('sukses'); ?>');
        <?php elseif (session()->getFlashdata('gagal')) : ?>
            notifikasi('danger', 'right', '<?= session()->getFlashdata("gagal"); ?>');
        <?php endif; ?>

        flatpickr("#filter_tanggal_range", {
            mode: "range",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d M Y",
            allowInput: false,
            onClose: function(selectedDates, dateStr, instance) {
                if (selectedDates.length === 2 || dateStr === '') {
                    reloadTable();
                }
            }
        });

        let data_audit_log = $('#audit-log-tabel').DataTable({
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
                url: '<?= base_url("admin/log"); ?>',
                method: 'POST',
                data: function(d) {
                    let tanggalRange = $('#filter_tanggal_range').val();
                    let tanggalAwal = '';
                    let tanggalAkhir = '';

                    if (tanggalRange) {
                        let parts = tanggalRange.split(' to ');
                        tanggalAwal = parts[0] ?? '';
                        tanggalAkhir = parts[1] ?? '';
                    }

                    d[csrfToken] = csrfHash;
                    d.filter_user_id = $('#filter_user_id').val();
                    d.filter_action = $('#filter_action').val();
                    d.filter_table_name = $('#filter_table_name').val();
                    d.filter_tanggal_awal = tanggalAwal;
                    d.filter_tanggal_akhir = tanggalAkhir;
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
                    data: 'user_nama'
                },
                {
                    data: 'action'
                },
                {
                    data: 'table_name'
                },
                {
                    data: 'row_id'
                },
                {
                    data: 'description'
                },
                {
                    data: 'ip_address'
                },
                {
                    data: 'created_at'
                },
                {
                    data: 'action_button'
                }
            ],
            responsive: true,
            order: [
                [1, 'desc'],
            ],
            columnDefs: [{
                    targets: [1],
                    visible: false
                },
                {
                    targets: [0, -1, 1],
                    orderable: false
                },
                {
                    targets: [0, -1, 1],
                    searchable: false
                },
                {
                    targets: [0, 3, 4, 5, 8],
                    className: 'text-center'
                },
                {
                    targets: [-1],
                    className: 'dt-body-center',
                    width: '8%'
                }
            ]
        });

        function reloadTable() {
            $("#block-tabel").LoadingOverlay("show");
            data_audit_log.ajax.reload(function() {
                $("#block-tabel").LoadingOverlay("hide");
            }, true);
        }

        $('#filter_user_id, #filter_action, #filter_table_name').on('change', function() {
            reloadTable();
        });

        $('#btn-reset-filter').on('click', function() {
            $('#filter_user_id').val('').trigger('change');
            $('#filter_action').val('').trigger('change');
            $('#filter_table_name').val('').trigger('change');

            let fp = document.querySelector("#filter_tanggal_range")._flatpickr;
            if (fp) {
                fp.clear();
            }

            reloadTable();
        });

        function tutup_modal() {
            $('#detail-id').html('');
            $('#detail-user').html('');
            $('#detail-action').html('');
            $('#detail-table_name').html('');
            $('#detail-row_id').html('');
            $('#detail-description').html('');
            $('#detail-ip_address').html('');
            $('#detail-user_agent').html('');
            $('#detail-created_at').html('');

            jQuery('#modal-detail').modal('hide');
        }

        $('#modal-detail').on('click', '#tutup-modal', function() {
            $('#modal-detail').addClass('fade');
            tutup_modal();
        });

        $('#audit-log-tabel').on('click', '#act-detail', function() {
            let id = $(this).data('id');
            $("#block-content-detail").LoadingOverlay("show");
            $('#modal-detail').removeClass('fade');

            $.ajax({
                type: 'POST',
                url: '<?= base_url('admin/log/detail') ?>',
                dataType: 'JSON',
                data: {
                    [csrfToken]: csrfHash,
                    id: id
                },
                success: function(result) {
                    $("#block-content-detail").LoadingOverlay("hide");

                    if (result['sukses']) {
                        const audit = result['audit_log'] || {};

                        $('#detail-id').html(audit.id ?? '-');
                        $('#detail-user').html(audit.username ? audit.username + ' (ID: ' + audit.user_id + ')' : 'System / Unknown');
                        $('#detail-action').html(audit.action ?? '-');
                        $('#detail-table_name').html(audit.table_name ?? '-');
                        $('#detail-row_id').html(audit.row_id ?? '-');
                        $('#detail-description').html(audit.description ?? '-');
                        $('#detail-ip_address').html(audit.ip_address ?? '-');
                        $('#detail-user_agent').html(audit.user_agent ?? '-');
                        $('#detail-created_at').html(audit.created_at ?? '-');

                        jQuery('#modal-detail').modal('show');
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
                    }
                }
            });
        });
    });
</script>
<script>
    Codebase.helpersOnLoad(['jq-select2']);
</script>