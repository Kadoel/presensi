<?php
$isPdf = (bool) ($isPdf ?? false);
$status = (string) ($slip->status ?? 'draft');
?>

<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Slip Gaji <?= esc($slip->nama_pegawai ?? '-'); ?> - <?= esc($slip->bulan ?? '-'); ?></title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            color: #111827;
            margin: 0;
            padding: <?= $isPdf ? '0' : '24px'; ?>;
            background: <?= $isPdf ? '#ffffff' : '#f3f4f6'; ?>;
        }

        .page {
            width: 100%;
            max-width: 820px;
            margin: 0 auto;
            background: #ffffff;
            padding: 28px;
            border: 1px solid #d1d5db;
        }

        .header {
            border-bottom: 3px solid #111827;
            padding-bottom: 14px;
            margin-bottom: 18px;
        }

        .title {
            font-size: 20px;
            font-weight: bold;
            margin: 0;
            text-align: center;
            letter-spacing: .5px;
        }

        .subtitle {
            margin-top: 6px;
            text-align: center;
            font-size: 13px;
            color: #4b5563;
        }

        .status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: bold;
            color: #ffffff;
            background: <?= $status === 'final' ? '#16a34a' : '#f59e0b'; ?>;
        }

        .info-table,
        .salary-table,
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 5px 0;
            vertical-align: top;
        }

        .label {
            width: 145px;
            color: #4b5563;
        }

        .separator {
            width: 12px;
            text-align: center;
            color: #6b7280;
        }

        .value {
            font-weight: bold;
        }

        .section-title {
            margin-top: 18px;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: bold;
            background: #e5e7eb;
            padding: 8px 10px;
            border: 1px solid #d1d5db;
        }

        .salary-table th,
        .salary-table td,
        .summary-table th,
        .summary-table td {
            border: 1px solid #d1d5db;
            padding: 8px 10px;
        }

        .salary-table th,
        .summary-table th {
            background: #f3f4f6;
            text-align: center;
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-bold {
            font-weight: bold;
        }

        .text-success {
            color: #15803d;
        }

        .text-danger {
            color: #b91c1c;
        }

        .net-row td {
            background: #dcfce7;
            font-size: 14px;
            font-weight: bold;
        }

        .footer {
            margin-top: 28px;
            width: 100%;
        }

        .signature {
            width: 230px;
            float: right;
            text-align: center;
        }

        .signature-space {
            height: 64px;
        }

        .note {
            margin-top: 18px;
            font-size: 10px;
            color: #6b7280;
            border-top: 1px dashed #d1d5db;
            padding-top: 8px;
        }

        .actions {
            max-width: 820px;
            margin: 0 auto 12px auto;
            text-align: right;
        }

        .btn {
            display: inline-block;
            padding: 9px 14px;
            background: #2563eb;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }

            .actions {
                display: none;
            }

            .page {
                border: none;
                padding: 0;
                max-width: none;
            }
        }
    </style>
</head>

<body>
    <?php if (! $isPdf): ?>
        <div class="actions">
            <a href="javascript:window.print()" class="btn">Cetak Preview</a>
        </div>
    <?php endif; ?>

    <div class="page">
        <div class="header">
            <h1 class="title">SLIP GAJI PEGAWAI</h1>
            <div class="subtitle">
                Periode <?= esc(slipBulanIndonesia($slip->bulan ?? null)); ?>
                &nbsp;|&nbsp;
                <span class="status"><?= strtoupper(esc($status)); ?></span>
            </div>
        </div>

        <table class="info-table">
            <tr>
                <td class="label">Kode Pegawai</td>
                <td class="separator">:</td>
                <td class="value"><?= esc($slip->kode_pegawai ?? '-'); ?></td>
                <td class="label">Bulan</td>
                <td class="separator">:</td>
                <td class="value"><?= esc(slipBulanIndonesia($slip->bulan ?? null)); ?></td>
            </tr>
            <tr>
                <td class="label">Nama Pegawai</td>
                <td class="separator">:</td>
                <td class="value"><?= esc($slip->nama_pegawai ?? '-'); ?></td>
                <td class="label">Jabatan</td>
                <td class="separator">:</td>
                <td class="value"><?= esc($slip->nama_jabatan ?? '-'); ?></td>
            </tr>
        </table>

        <div class="section-title">Ringkasan Presensi</div>

        <table class="summary-table">
            <thead>
                <tr>
                    <th>Hadir</th>
                    <th>Izin</th>
                    <th>Sakit</th>
                    <th>Cuti</th>
                    <th>Libur</th>
                    <th>Alpa</th>
                    <th>Menit Datang Telat</th>
                    <th>Menit Pulang Cepat</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center"><?= slipAngka($slip->total_hadir ?? 0); ?></td>
                    <td class="text-center"><?= slipAngka($slip->total_izin ?? 0); ?></td>
                    <td class="text-center"><?= slipAngka($slip->total_sakit ?? 0); ?></td>
                    <td class="text-center"><?= slipAngka($slip->total_cuti ?? 0); ?></td>
                    <td class="text-center"><?= slipAngka($slip->total_libur ?? 0); ?></td>
                    <td class="text-center"><?= slipAngka($slip->total_alpa ?? 0); ?></td>
                    <td class="text-center"><?= esc(slipMenit($slip->total_menit_telat ?? 0)); ?></td>
                    <td class="text-center"><?= esc(slipMenit($slip->total_menit_pulang_cepat ?? 0)); ?></td>
                </tr>
            </tbody>
        </table>

        <div class="section-title">Rincian Gaji</div>

        <table class="salary-table">
            <thead>
                <tr>
                    <th style="width: 70%;">Komponen</th>
                    <th style="width: 30%;">Nominal</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Gaji Pokok</td>
                    <td class="text-right"><?= slipRupiah($slip->gaji_pokok ?? 0); ?></td>
                </tr>
                <tr>
                    <td>Tunjangan</td>
                    <td class="text-right"><?= slipRupiah($slip->tunjangan ?? 0); ?></td>
                </tr>
                <tr>
                    <td class="text-bold">Gaji Kotor</td>
                    <td class="text-right text-bold"><?= slipRupiah($slip->gaji_kotor ?? 0); ?></td>
                </tr>
                <tr>
                    <td>Potongan Telat</td>
                    <td class="text-right text-danger"><?= slipRupiah($slip->potongan_telat ?? 0); ?></td>
                </tr>
                <tr>
                    <td>Potongan Pulang Cepat</td>
                    <td class="text-right text-danger"><?= slipRupiah($slip->potongan_pulang_cepat ?? 0); ?></td>
                </tr>
                <tr>
                    <td>Potongan Alpa</td>
                    <td class="text-right text-danger"><?= slipRupiah($slip->potongan_alpa ?? 0); ?></td>
                </tr>
                <tr>
                    <td class="text-bold">Total Potongan</td>
                    <td class="text-right text-bold text-danger"><?= slipRupiah($slip->total_potongan ?? 0); ?></td>
                </tr>
                <tr class="net-row">
                    <td>Gaji Bersih</td>
                    <td class="text-right"><?= slipRupiah($slip->gaji_bersih ?? 0); ?></td>
                </tr>
            </tbody>
        </table>

        <div class="footer clearfix">
            <div class="signature">
                <div><?= esc(tanggal_indonesia(date('Y-m-d'))); ?></div>
                <div>Admin</div>
                <div class="signature-space"></div>
                <div class="text-bold">(____________________)</div>
            </div>
        </div>

        <div class="note">
            Slip gaji ini dibuat otomatis oleh sistem. Data presensi dan penggajian sudah dikunci pada status final.
        </div>
    </div>
</body>

</html>