<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title><?= esc($judul ?? 'Verifikasi Slip Gaji'); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            margin: 0;
            padding: 24px;
            color: #111827;
        }

        .card {
            max-width: 520px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 14px;
            padding: 28px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .08);
            text-align: center;
        }

        .badge-valid {
            display: inline-block;
            background: #16a34a;
            color: #fff;
            padding: 8px 14px;
            border-radius: 999px;
            font-weight: bold;
            margin-bottom: 16px;
        }

        .badge-invalid {
            display: inline-block;
            background: #dc2626;
            color: #fff;
            padding: 8px 14px;
            border-radius: 999px;
            font-weight: bold;
            margin-bottom: 16px;
        }

        table {
            width: 100%;
            margin-top: 18px;
            border-collapse: collapse;
            text-align: left;
        }

        td {
            padding: 8px 4px;
            border-bottom: 1px solid #e5e7eb;
        }

        td:first-child {
            color: #6b7280;
            width: 42%;
        }

        .badge-valid i,
        .badge-invalid i {
            margin-right: 6px;
        }

        .text-success {
            color: #16a34a;
        }

        .text-danger {
            color: #dc2626;
        }
    </style>
</head>

<body>
    <div class="card">
        <?php if ($valid): ?>
            <div class="badge-valid"><i class="fa fa-circle-check"></i> VALID</div>
            <h2><i class="fa fa-shield-alt text-success"></i> Slip Gaji Terverifikasi</h2>
            <p>Slip ini terdaftar di sistem dan sudah berstatus final.</p>

            <table>
                <tr>
                    <td><i class="fa fa-id-card text-success"></i> Kode Pegawai</td>
                    <td>: <b><?= esc($slip->kode_pegawai ?? '-'); ?></b></td>
                </tr>

                <tr>
                    <td><i class="fa fa-user text-success"></i> Nama Pegawai</td>
                    <td>: <b><?= esc($slip->nama_pegawai ?? '-'); ?></b></td>
                </tr>

                <tr>
                    <td><i class="fa fa-briefcase text-success"></i> Jabatan</td>
                    <td>: <?= esc($slip->nama_jabatan ?? '-'); ?></td>
                </tr>

                <tr>
                    <td><i class="fa fa-calendar text-success"></i> Periode</td>
                    <td>: <?= esc(slipBulanIndonesia($slip->bulan ?? null)); ?></td>
                </tr>
            </table>
        <?php else: ?>
            <div class="badge-invalid"><i class="fa fa-circle-xmark"></i> INVALID</div>
            <h2><i class="fa fa-triangle-exclamation text-danger"></i> Slip Tidak Valid</h2>
            <p>Slip gaji tidak ditemukan atau belum berstatus final.</p>
        <?php endif; ?>
    </div>
</body>

</html>