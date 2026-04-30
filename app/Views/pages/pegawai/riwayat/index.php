<?= $this->extend('theme/pegawai/body'); ?>
<?= $this->section('content'); ?>

<style>
    .fc-day-today {
        background: rgba(13, 110, 253, 0.12) !important;
        outline: 2px solid #0d6efd;
        outline-offset: -2px;
    }

    .fc-event {
        cursor: pointer;
        background: transparent !important;
        border: 0 !important;
        color: inherit !important;
        box-shadow: none !important;
    }

    .fc-event-main {
        color: inherit !important;
        padding: 2px 4px !important;
        width: 100% !important;
    }

    .fc-daygrid-event {
        white-space: normal !important;
    }

    .fc-list-event td {
        vertical-align: middle !important;
        background: transparent !important;
    }

    .fc-list-event-title,
    .fc-list-event-time {
        font-weight: 600;
        background: transparent !important;
    }

    .fc-list-event-title a {
        display: block !important;
        width: 100% !important;
    }

    .fc-list-event-dot {
        display: none !important;
    }

    .fc-riwayat-dot-wrapper {
        display: flex;
        align-items: center;
        gap: 6px;
        width: 100%;
        line-height: 1.2;
    }

    .fc-riwayat-dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .fc-riwayat-text {
        font-size: 12px;
        line-height: 1.2;
        overflow: hidden;
    }

    .fc-riwayat-title {
        font-weight: 600;
    }

    .fc-riwayat-jam {
        font-size: 11px;
        opacity: .8;
    }

    .fc-multimonth .fc-riwayat-title {
        font-size: 11px !important;
        line-height: 1.1 !important;
    }

    .fc-riwayat-year-dot-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
    }

    .fc-riwayat-year-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
    }
</style>

<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default d-flex flex-column flex-md-row align-items-start align-items-md-center gap-2">
            <h3 class="block-title">
                <i class="fa fa-clock-rotate-left me-1"></i> Kalender Riwayat Presensi
            </h3>

            <div class="ms-md-auto">
                <input type="month"
                    id="filter-bulan-riwayat"
                    class="form-control form-control-sm"
                    value="<?= date('Y-m'); ?>"
                    style="width: 160px;">
            </div>
        </div>

        <div class="block-content block-content-full">
            <div id="kalender-riwayat-presensi"></div>
        </div>
    </div>
</div>

<div class="modal" id="modal-detail-riwayat" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="block block-rounded shadow-none mb-0">
                <div class="block-header block-header-default">
                    <h3 class="block-title">
                        <i class="fa fa-circle-info me-1"></i> Detail Presensi
                    </h3>
                    <div class="block-options">
                        <button type="button" class="btn-block-option" data-bs-dismiss="modal">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>

                <div class="block-content">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <th style="width: 38%;">Tanggal</th>
                            <td id="detail-tanggal">-</td>
                        </tr>
                        <tr>
                            <th>Hasil</th>
                            <td id="detail-hasil">-</td>
                        </tr>
                        <tr>
                            <th>Shift</th>
                            <td id="detail-shift">-</td>
                        </tr>
                        <tr>
                            <th>Jam Datang</th>
                            <td id="detail-jam-datang">-</td>
                        </tr>
                        <tr>
                            <th>Status Datang</th>
                            <td id="detail-status-datang">-</td>
                        </tr>
                        <tr>
                            <th>Jam Pulang</th>
                            <td id="detail-jam-pulang">-</td>
                        </tr>
                        <tr>
                            <th>Status Pulang</th>
                            <td id="detail-status-pulang">-</td>
                        </tr>
                        <tr>
                            <th>Sumber</th>
                            <td id="detail-sumber">-</td>
                        </tr>
                        <tr>
                            <th>Catatan</th>
                            <td id="detail-catatan">-</td>
                        </tr>
                    </table>
                </div>

                <div class="block-content block-content-full text-end border-top">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection('content'); ?>