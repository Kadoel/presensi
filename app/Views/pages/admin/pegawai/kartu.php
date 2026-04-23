<?php
$pegawai = $kartu['pegawai'];
$usaha   = $kartu['usaha'];
$ukuran  = $kartu['ukuran'];

$cardWidth  = (float) $ukuran['width_mm'];
$cardHeight = (float) $ukuran['height_mm'];
$isB1 = strtoupper($ukuran['kode']) === 'B1';

/*
|--------------------------------------------------------------------------
| Preset ukuran presisi final v2
|--------------------------------------------------------------------------
*/
if ($isB1) {
    $headerH            = '16mm';
    $bodyTopPad         = '2.8mm';
    $bodySidePad        = '2.8mm';
    $bodyBottomPad      = '2.8mm';
    $bodyGap            = '1.8mm';

    $rowPhoto           = '19.5mm';
    $rowIdentity        = '18.5mm';
    $rowQr              = '30mm';

    $logoSize           = '8.6mm';
    $brandNameFont      = '4.0mm';
    $brandSubFont       = '1.85mm';

    $photoOuter         = '18mm';
    $photoInnerPad      = '0.9mm';

    $panelRadius        = '4mm';
    $panelPadY          = '1.8mm';
    $panelPadX          = '2.4mm';

    $miniLabelFont      = '1.45mm';
    $dividerWidth       = '15mm';
    $dividerHeight      = '0.42mm';

    $pegawaiNameFont    = '3.8mm';
    $pegawaiNameMinH    = '5.8mm';

    $jabatanFont        = '1.8mm';
    $jabatanPadY        = '0.9mm';
    $jabatanPadX        = '2.2mm';

    $qrTitleFont        = '1.45mm';
    $qrBoxSize          = '17.5mm';
    $qrBoxPad           = '1.0mm';
    $qrCodeFont         = '1.75mm';
    $qrCaptionFont      = '1.25mm';
} else {
    $headerH            = '18mm';
    $bodyTopPad         = '3.2mm';
    $bodySidePad        = '3.2mm';
    $bodyBottomPad      = '3.2mm';
    $bodyGap            = '2.1mm';

    $rowPhoto           = '22mm';
    $rowIdentity        = '21.5mm';
    $rowQr              = '36.5mm';

    $logoSize           = '10mm';
    $brandNameFont      = '4.5mm';
    $brandSubFont       = '2.1mm';

    $photoOuter         = '22mm';
    $photoInnerPad      = '1.0mm';

    $panelRadius        = '4.5mm';
    $panelPadY          = '2.2mm';
    $panelPadX          = '3mm';

    $miniLabelFont      = '1.7mm';
    $dividerWidth       = '18mm';
    $dividerHeight      = '0.42mm';

    $pegawaiNameFont    = '4.3mm';
    $pegawaiNameMinH    = '7mm';

    $jabatanFont        = '2mm';
    $jabatanPadY        = '1.0mm';
    $jabatanPadX        = '2.4mm';

    $qrTitleFont        = '1.7mm';
    $qrBoxSize          = '21mm';
    $qrBoxPad           = '1.2mm';
    $qrCodeFont         = '2mm';
    $qrCaptionFont      = '1.45mm';
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title><?= esc($judul) ?> - <?= esc($pegawai['nama_pegawai']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        :root {
            --card-w: <?= $cardWidth ?>mm;
            --card-h: <?= $cardHeight ?>mm;

            --header-h: <?= $headerH ?>;
            --body-gap: <?= $bodyGap ?>;

            --row-photo: <?= $rowPhoto ?>;
            --row-identity: <?= $rowIdentity ?>;
            --row-qr: <?= $rowQr ?>;

            --radius-panel: <?= $panelRadius ?>;

            --blue-1: #0f172a;
            --blue-2: #1e3a8a;
            --blue-3: #2563eb;
            --text-white: #ffffff;
            --text-soft: rgba(255, 255, 255, .84);
            --line-soft: rgba(255, 255, 255, .22);
            --shadow: 0 10px 24px rgba(15, 23, 42, .18);
            --bg-screen: #eef2f7;
        }

        * {
            box-sizing: border-box;
        }

        @page {
            size: <?= $cardWidth ?>mm <?= $cardHeight ?>mm;
            margin: 0;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: var(--bg-screen);
            font-family: "Inter", "Segoe UI", Arial, sans-serif;
        }

        body {
            min-height: 100vh;
        }

        .screen-toolbar {
            position: sticky;
            top: 0;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 18px;
            background: rgba(255, 255, 255, .94);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(148, 163, 184, .18);
        }

        .screen-toolbar__left h1 {
            margin: 0;
            font-size: 17px;
            font-weight: 800;
            color: #0f172a;
        }

        .screen-toolbar__left p {
            margin: 4px 0 0;
            font-size: 13px;
            color: #64748b;
        }

        .screen-toolbar__actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn {
            border: 0;
            outline: none;
            cursor: pointer;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 13px;
            font-weight: 700;
            transition: .2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff;
        }

        .btn-secondary {
            background: #fff;
            color: #0f172a;
            border: 1px solid rgba(148, 163, 184, .35);
        }

        .stage {
            min-height: calc(100vh - 74px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .card-wrap {
            width: var(--card-w);
            height: var(--card-h);
        }

        .id-card {
            position: relative;
            width: 100%;
            height: 100%;
            overflow: hidden;
            border: 1px solid rgba(148, 163, 184, .18);
            box-shadow: 0 18px 45px rgba(15, 23, 42, .18);
            background:
                linear-gradient(145deg, var(--blue-1) 0%, var(--blue-2) 52%, var(--blue-3) 100%);
        }

        .id-card::before {
            content: "";
            position: absolute;
            inset: 0;
            background: url("<?= esc($usaha['logo_url']) ?>") no-repeat center;
            background-size: 56%;
            opacity: .05;
            pointer-events: none;
            z-index: 0;
        }

        .id-card::after {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at top right, rgba(255, 255, 255, .16), transparent 28%),
                radial-gradient(circle at bottom left, rgba(255, 255, 255, .10), transparent 22%);
            pointer-events: none;
            z-index: 0;
        }

        .card-inner {
            position: relative;
            z-index: 2;
            width: 100%;
            height: 100%;
            display: grid;
            grid-template-rows: var(--header-h) 1fr;
        }

        /* =========================
           HEADER
        ========================= */
        .card-top {
            height: 100%;
            display: grid;
            grid-template-columns: auto 1fr;
            align-items: center;
            gap: <?= $isB1 ? '2.4mm' : '2.8mm' ?>;
            padding: <?= $isB1 ? '2.2mm 2.8mm' : '2.6mm 3.2mm' ?>;
            background: rgba(255, 255, 255, .05);
            border-bottom: 1px solid rgba(255, 255, 255, .12);
            backdrop-filter: blur(8px);
        }

        .logo-box {
            width: <?= $logoSize ?>;
            height: <?= $logoSize ?>;
            min-width: <?= $logoSize ?>;
            border-radius: 50%;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, .10);
            border: 1px solid rgba(255, 255, 255, .16);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .08);
        }

        .logo-box img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            background: transparent;
            display: block;
        }

        .brand {
            min-width: 0;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: <?= $isB1 ? '.45mm' : '.55mm' ?>;
            color: #fff;
        }

        .brand .usaha,
        .brand .sub {
            display: block;
            width: 100%;
            white-space: nowrap;
            overflow: hidden;
            line-height: 1;
            margin: 0;
            padding: 0;
            text-align: left;
        }

        .brand .usaha {
            font-size: <?= $brandNameFont ?>;
            font-weight: 900;
        }

        .brand .sub {
            font-size: <?= $brandSubFont ?>;
            font-weight: 600;
            opacity: .94;
        }

        /* =========================
           BODY
        ========================= */
        .card-body {
            height: 100%;
            min-height: 0;
            padding: <?= $bodyTopPad ?> <?= $bodySidePad ?> <?= $bodyBottomPad ?>;
            display: grid;
            gap: var(--body-gap);
            grid-template-rows: var(--row-photo) var(--row-identity) var(--row-qr);
        }

        /* =========================
           FOTO
        ========================= */
        .photo-area {
            min-height: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .photo-ring {
            width: <?= $photoOuter ?>;
            height: <?= $photoOuter ?>;
            padding: <?= $photoInnerPad ?>;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(255, 255, 255, .86), rgba(255, 255, 255, .22));
            box-shadow: 0 10px 25px rgba(15, 23, 42, .18);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .photo-frame {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, .26);
            background: rgba(255, 255, 255, .18);
        }

        .photo-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        /* =========================
           PANEL UMUM
        ========================= */
        .glass-panel {
            background: linear-gradient(180deg,
                    rgba(255, 255, 255, .18) 0%,
                    rgba(255, 255, 255, .13) 100%);
            border: 1px solid rgba(255, 255, 255, .20);
            border-radius: var(--radius-panel);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 12px 30px rgba(15, 23, 42, .12);
            overflow: hidden;
            min-width: 0;
            height: 100%;
        }

        .section-divider {
            position: relative;
            width: <?= $dividerWidth ?>;
            height: <?= $dividerHeight ?>;
            margin: <?= $isB1 ? '.9mm auto 1mm' : '1mm auto 1.2mm' ?>;
            border-radius: 999px;
            flex: 0 0 auto;
            background:
                linear-gradient(90deg,
                    rgba(255, 255, 255, 0.05),
                    rgba(255, 255, 255, 0.55),
                    rgba(255, 255, 255, 0.05));
            border-top: 1px solid rgba(255, 255, 255, .22);
            border-bottom: 1px solid rgba(255, 255, 255, .08);
            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, .18),
                0 2px 8px rgba(15, 23, 42, .10);
            opacity: .92;
        }

        /* =========================
           IDENTITAS
        ========================= */
        .identity-panel {
            padding: <?= $panelPadY ?> <?= $panelPadX ?>;
            color: #fff;
            display: grid;
            grid-template-rows: auto auto 1fr auto;
            align-items: center;
            justify-items: center;
        }

        .mini-label {
            margin: 0;
            text-align: center;
            font-size: <?= $miniLabelFont ?>;
            text-transform: uppercase;
            letter-spacing: .18em;
            font-weight: 800;
            opacity: .84;
            line-height: 1;
        }

        .pegawai-nama-wrap {
            width: 100%;
            min-width: 0;
            min-height: <?= $pegawaiNameMinH ?>;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pegawai-nama {
            display: block;
            width: 100%;
            max-width: 100%;
            margin: 0;
            text-align: center;
            font-size: <?= $pegawaiNameFont ?>;
            font-weight: 900;
            line-height: 1;
            color: #fff;
            white-space: nowrap !important;
            word-break: normal !important;
            overflow-wrap: normal !important;
            overflow: hidden;
            text-overflow: clip;
        }

        .jabatan-box {
            width: 100%;
            display: flex;
            justify-content: center;
            margin-top: <?= $isB1 ? '.7mm' : '.9mm' ?>;
        }

        .pegawai-jabatan {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: <?= $jabatanPadY ?> <?= $jabatanPadX ?>;
            border-radius: 999px;
            background: rgba(255, 255, 255, .18);
            border: 1px solid rgba(255, 255, 255, .22);
            color: #fff;
            font-size: <?= $jabatanFont ?>;
            font-weight: 800;
            line-height: 1;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .10);
        }

        /* =========================
           QR PANEL
        ========================= */
        .qr-panel {
            padding: <?= $panelPadY ?> <?= $panelPadX ?>;
            color: #fff;
            display: grid;
            grid-template-rows: auto auto 1fr auto auto;
            justify-items: center;
            align-items: start;
        }

        .qr-title {
            margin: 0;
            text-align: center;
            font-size: <?= $qrTitleFont ?>;
            text-transform: uppercase;
            letter-spacing: .16em;
            font-weight: 800;
            opacity: .86;
            line-height: 1;
        }

        .qr-box-wrap {
            width: 100%;
            min-width: 0;
            min-height: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-box {
            width: <?= $qrBoxSize ?>;
            height: <?= $qrBoxSize ?>;
            background: #fff;
            border-radius: <?= $isB1 ? '2.8mm' : '3.2mm' ?>;
            padding: <?= $qrBoxPad ?>;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow);
        }

        .qr-box img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        .qr-code {
            width: 100%;
            margin-top: <?= $isB1 ? '.8mm' : '1mm' ?>;
            text-align: center;
            font-size: <?= $qrCodeFont ?>;
            font-weight: 900;
            line-height: 1.05;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: clip;
        }

        .qr-caption {
            width: 100%;
            margin-top: <?= $isB1 ? '.35mm' : '.5mm' ?>;
            text-align: center;
            font-size: <?= $qrCaptionFont ?>;
            line-height: 1.05;
            color: rgba(255, 255, 255, .84);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: clip;
        }

        /* =========================
           PRINT
        ========================= */
        @media print {

            html,
            body {
                width: var(--card-w);
                height: var(--card-h);
                background: #fff;
            }

            body {
                margin: 0;
                padding: 0;
            }

            .screen-toolbar {
                display: none !important;
            }

            .stage {
                min-height: auto;
                padding: 0;
                margin: 0;
                display: block;
            }

            .card-wrap {
                width: var(--card-w);
                height: var(--card-h);
                margin: 0;
            }

            .id-card {
                width: 100%;
                height: 100%;
                box-shadow: none;
                border: none;
                break-inside: avoid;
                page-break-inside: avoid;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        .powered-by {
            position: absolute;
            bottom: <?= $isB1 ? '2.2mm' : '2.6mm' ?>;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
            z-index: 3;
        }

        .powered-text {
            font-size: <?= $isB1 ? '1.3mm' : '1.6mm' ?>;
            color: rgba(255, 255, 255, .75);
            margin-bottom: <?= $isB1 ? '0.8mm' : '1mm' ?>;
            letter-spacing: 0.08em;
        }

        .powered-logos {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: <?= $isB1 ? '2mm' : '2.5mm' ?>;
        }

        .powered-logo {
            width: <?= $isB1 ? '5.5mm' : '7mm' ?>;
            height: <?= $isB1 ? '5.5mm' : '7mm' ?>;
            border-radius: 50%;
            overflow: hidden;

            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .18);
            backdrop-filter: blur(6px);

            display: flex;
            align-items: center;
            justify-content: center;

            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, .2),
                0 4px 10px rgba(0, 0, 0, .15);
        }

        .powered-logo img {
            width: 80%;
            height: 80%;
            object-fit: contain;
        }
    </style>
</head>

<body>

    <div class="screen-toolbar">
        <div class="screen-toolbar__left">
            <h1>Kartu Pegawai <?= esc($ukuran['label']) ?></h1>
            <p><?= esc($pegawai['nama_pegawai']) ?> · final v2 presisi B1/B2</p>
        </div>
        <div class="screen-toolbar__actions">
            <a href="<?= current_url() . '?ukuran=B1' ?>" class="btn btn-secondary">B1</a>
            <a href="<?= current_url() . '?ukuran=B2' ?>" class="btn btn-secondary">B2</a>
            <button type="button" class="btn btn-primary" onclick="window.print()">Print</button>
        </div>
    </div>

    <div class="stage">
        <div class="card-wrap">
            <div class="id-card">
                <div class="card-inner">

                    <div class="card-top">
                        <div class="logo-box">
                            <img src="<?= esc($usaha['logo_url']) ?>" alt="Logo">
                        </div>

                        <div class="brand">
                            <div class="usaha js-fit-brand"><?= esc($usaha['nama_usaha']) ?></div>
                            <div class="sub js-fit-brand"><?= esc($usaha['alamat'] ?? '-') ?></div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="photo-area">
                            <div class="photo-ring">
                                <div class="photo-frame">
                                    <img src="<?= esc($pegawai['foto_url']) ?>" alt="<?= esc($pegawai['nama_pegawai']) ?>">
                                </div>
                            </div>
                        </div>

                        <div class="glass-panel identity-panel">
                            <div class="mini-label">Identitas Pegawai</div>
                            <div class="section-divider"></div>

                            <div class="pegawai-nama-wrap">
                                <div class="pegawai-nama js-fit-name"><?= esc($pegawai['nama_pegawai']) ?></div>
                            </div>

                            <div class="jabatan-box">
                                <div class="pegawai-jabatan"><?= esc($pegawai['jabatan']) ?></div>
                            </div>
                        </div>

                        <div class="glass-panel qr-panel">
                            <div class="qr-title">Scan QR Pegawai</div>
                            <div class="section-divider"></div>

                            <div class="qr-box-wrap">
                                <div class="qr-box">
                                    <img src="<?= esc($pegawai['qr_url']) ?>" alt="QR <?= esc($pegawai['kode_pegawai']) ?>">
                                </div>
                            </div>

                            <div class="qr-code js-fit-code"><?= esc($pegawai['kode_pegawai']) ?></div>
                            <div class="qr-caption">Gunakan untuk identifikasi presensi</div>
                        </div>
                    </div>
                    <div class="powered-by">
                        <div class="powered-text">Powered By</div>

                        <div class="powered-logos">
                            <div class="powered-logo">
                                <img src="<?= base_url('assets/media/photos/logo.png') ?>" alt="Logo 1">
                            </div>
                            <div class="powered-logo">
                                <img src="<?= base_url('assets/media/photos/da.png') ?>" alt="Logo 2">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function fitSingleLine(selector, options = {}) {
            const elements = document.querySelectorAll(selector);
            const minFont = options.minFont || 6;
            const step = options.step || 0.25;

            elements.forEach((el) => {
                const initial = parseFloat(window.getComputedStyle(el).fontSize);
                let current = initial;

                el.style.whiteSpace = 'nowrap';
                el.style.wordBreak = 'normal';
                el.style.overflowWrap = 'normal';
                el.style.fontSize = initial + 'px';

                while (el.scrollWidth > el.clientWidth && current > minFont) {
                    current -= step;
                    el.style.fontSize = current + 'px';
                }
            });
        }

        function fitAllCardText() {
            fitSingleLine('.js-fit-brand', {
                minFont: <?= $isB1 ? '5.5' : '6.2' ?>,
                step: 0.25
            });

            fitSingleLine('.js-fit-name', {
                minFont: <?= $isB1 ? '5.0' : '6.0' ?>,
                step: 0.20
            });

            fitSingleLine('.js-fit-code', {
                minFont: <?= $isB1 ? '5.2' : '6.0' ?>,
                step: 0.15
            });
        }

        window.addEventListener('load', fitAllCardText);
        window.addEventListener('resize', fitAllCardText);
        window.addEventListener('beforeprint', fitAllCardText);
    </script>

</body>

</html>