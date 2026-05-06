<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title><?= esc($pageTitle ?? 'Kios Presensi'); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="/assets/media/favicons/favicon.png">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        * {
            box-sizing: border-box;
        }

        :root {
            --bg-page: #eef2f6;
            --bg-card: #f8fafc;
            --bg-topbar: #f3f4f6;
            --border-soft: #d8e0ea;
            --border-dashed: #bfd3ff;
            --text-main: #1f2937;
            --text-muted: #64748b;
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --success: #16a34a;
            --success-dark: #15803d;
            --danger: #dc2626;
            --danger-dark: #b91c1c;
            --orange: #ea580c;
            --orange-dark: #c2410c;
            --camera-bg: #0b1730;
            --camera-text: #dbeafe;
            --disabled-bg: #dbe4f0;
            --disabled-text: #9aa7b8;
            --glow-success: rgba(74, 222, 128, .95);
        }

        body {
            margin: 0;
            font-family: Inter, Arial, sans-serif;
            background: var(--bg-page);
            color: var(--text-main);
        }

        .container {
            max-width: 1024px;
            margin: 0 auto;
            padding: 10px;
        }

        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-soft);
            border-radius: 20px;
            box-shadow: 0 2px 10px rgba(15, 23, 42, .04);
        }

        .topbar-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            min-height: 116px;
            padding: 18px 22px;
            margin-bottom: 18px;
            background: var(--bg-topbar);
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 18px;
            min-width: 0;
        }

        .brand-logo-wrap {
            width: 72px;
            height: 72px;
            border-radius: 16px;
            background: #fff;
            border: 1px solid var(--border-soft);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            overflow: hidden;
        }

        .brand-logo {
            width: 54px;
            height: 54px;
            object-fit: contain;
            display: block;
        }

        .brand-name {
            font-size: 28px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.1;
            margin-bottom: 6px;
            word-break: break-word;
        }

        .brand-subtitle {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-muted);
        }

        .topbar-right {
            text-align: right;
            flex-shrink: 0;
        }

        .clock-label {
            font-size: 14px;
            font-weight: 700;
            color: var(--text-muted);
            margin-bottom: 8px;
        }

        .clock-time {
            font-size: 54px;
            line-height: 1;
            font-weight: 800;
            color: #111827;
            letter-spacing: 1px;
        }

        .mode-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-bottom: 16px;
        }

        .mode-btn,
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border: 0;
            cursor: pointer;
            transition: transform .18s ease, opacity .18s ease, box-shadow .18s ease, background-color .18s ease, filter .18s ease;
            font-weight: 800;
        }

        .mode-btn {
            border-radius: 18px;
            padding: 28px 20px;
            font-size: 20px;
            color: #fff;
            position: relative;
        }

        .mode-btn i {
            font-size: 20px;
        }

        .mode-btn:hover,
        .btn:hover {
            transform: translateY(-1px);
        }

        .mode-btn.datang {
            background: var(--success);
        }

        .mode-btn.pulang {
            background: var(--orange);
        }

        .mode-btn.selected {
            opacity: .35;
            transform: scale(.98);
        }

        .mode-btn.selected::after {
            content: " (Dipilih)";
            font-size: 12px;
            display: block;
            margin-top: 6px;
        }

        .mode-btn.mode-pop {
            animation: modePop .22s ease;
        }

        /* .mode-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        } */

        @keyframes modePop {
            0% {
                transform: scale(.98);
            }

            60% {
                transform: scale(1.02);
            }

            100% {
                transform: scale(.98);
            }
        }

        .main-card {
            padding: 18px;
        }

        .status-banner {
            display: flex;
            align-items: center;
            gap: 8px;
            border-radius: 14px;
            padding: 16px 18px;
            margin-bottom: 18px;
            font-size: 16px;
            font-weight: 700;
            transition: all .2s ease;
        }

        .status-banner.mode-datang {
            border: 1px solid #bbf7d0;
            background: #f0fdf4;
            color: var(--success-dark);
        }

        .status-banner.mode-pulang {
            border: 1px solid #fdba74;
            background: #fff7ed;
            color: var(--orange-dark);
        }

        .status-banner.mode-info {
            border: 1px solid var(--border-dashed);
            background: #eaf1ff;
            color: var(--primary-dark);
        }

        .status-banner.mode-error {
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: var(--danger-dark);
        }

        .status-banner.mode-success {
            border: 1px solid #86efac;
            background: #dcfce7;
            color: #166534;
        }

        .camera-card,
        .identity-card {
            background: var(--bg-card);
            border-radius: 18px;
        }

        .camera-card {
            border: 1px dashed var(--border-dashed);
            padding: 18px;
            margin-bottom: 18px;
        }

        .identity-card {
            border: 1px solid var(--border-soft);
            padding: 18px;
        }

        .camera-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 18px;
        }

        .section-title,
        .identity-title {
            font-size: 18px;
            font-weight: 800;
            color: var(--text-main);
        }

        .camera-phase-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            background: #eaf1ff;
            color: var(--primary-dark);
            font-size: 13px;
            font-weight: 800;
            white-space: nowrap;
            transition: all .2s ease;
        }

        .camera-phase-badge.mode-scan {
            background: #eaf1ff;
            color: var(--primary-dark);
        }

        .camera-phase-badge.mode-selfie {
            background: #f0fdf4;
            color: var(--success-dark);
        }

        .camera-box {
            width: 100%;
            height: 400px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin-bottom: 14px;
            border-radius: 18px;
            background: var(--camera-bg);
            color: var(--camera-text);
            font-size: 18px;
            font-weight: 600;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .06);
        }

        .camera-box::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(11, 23, 48, .18), rgba(11, 23, 48, .04));
            pointer-events: none;
            z-index: 1;
        }

        .camera-box video,
        .camera-box canvas,
        .camera-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: opacity .25s ease, transform .25s ease;
        }

        .camera-box video {
            position: absolute;
            inset: 0;
        }

        .camera-mode-frame {
            position: absolute;
            inset: 16px;
            border: 2px dashed rgba(255, 255, 255, .55);
            border-radius: 20px;
            z-index: 2;
            transition: all .2s ease, box-shadow .2s ease, border-color .2s ease;
            pointer-events: none;
        }

        .camera-mode-frame.scan {
            inset: 28px 22%;
        }

        .camera-mode-frame.selfie {
            inset: 18px;
            border-style: solid;
            border-color: rgba(255, 255, 255, .4);
        }

        .camera-mode-frame.glow-success {
            border-color: var(--glow-success);
            box-shadow:
                0 0 0 2px rgba(74, 222, 128, .28),
                0 0 24px rgba(74, 222, 128, .9),
                inset 0 0 24px rgba(74, 222, 128, .35);
        }

        .camera-flash {
            position: absolute;
            inset: 0;
            background: radial-gradient(circle, rgba(74, 222, 128, .65) 0%, rgba(34, 197, 94, .35) 45%, rgba(0, 0, 0, 0) 100%);
            opacity: 0;
            pointer-events: none;
            z-index: 5;
        }

        .camera-flash.active {
            animation: flashValid .6s ease;
        }

        @keyframes flashValid {
            0% {
                opacity: 0;
            }

            20% {
                opacity: 1;
            }

            100% {
                opacity: 0;
            }
        }

        #camera-placeholder {
            position: relative;
            z-index: 3;
            text-align: center;
            padding: 0 18px;
            line-height: 1.5;
            max-width: 78%;
        }

        .qr-scan-status {
            position: absolute;
            left: 12px;
            right: 12px;
            bottom: 12px;
            z-index: 4;
            padding: 10px 12px;
            border-radius: 12px;
            background: rgba(15, 23, 42, .72);
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            text-align: center;
        }

        .countdown-box {
            position: absolute;
            top: 50%;
            left: 50%;
            z-index: 6;
            transform: translate(-50%, -50%);
            width: 150px;
            height: 150px;
            pointer-events: none;
        }

        .countdown-svg {
            width: 100%;
            height: 100%;
            transform: rotate(-90deg);
            overflow: visible;
        }

        .countdown-track {
            fill: none;
            stroke: rgba(255, 255, 255, .18);
            stroke-width: 10;
        }

        .countdown-progress {
            fill: none;
            stroke: #4ade80;
            stroke-width: 10;
            stroke-linecap: round;
            transition: stroke-dashoffset 1s linear;
            filter: drop-shadow(0 0 8px rgba(74, 222, 128, .5));
        }

        .countdown-number {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 42px;
            font-weight: 800;
            text-shadow: 0 2px 10px rgba(0, 0, 0, .35);
        }

        .countdown-box.active {
            animation: pulseCountdown .85s ease infinite;
        }

        @keyframes pulseCountdown {
            0% {
                transform: translate(-50%, -50%) scale(1);
            }

            50% {
                transform: translate(-50%, -50%) scale(1.04);
            }

            100% {
                transform: translate(-50%, -50%) scale(1);
            }
        }

        .btn {
            border-radius: 14px;
            padding: 13px 18px;
            font-size: 15px;
        }

        .btn i {
            font-size: 16px;
        }

        .btn-light {
            background: #eef2f7;
            color: #374151;
            border: 1px solid #d7dee8;
        }

        .btn-disabled {
            background: var(--disabled-bg);
            color: var(--disabled-text);
            cursor: not-allowed;
            pointer-events: none;
        }

        .action-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 14px;
        }

        .action-grid .btn {
            width: 100%;
        }

        #btn-reset {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            height: 48px;
            font-size: 16px;
        }

        #btn-reset:not(.btn-disabled) {
            background: var(--danger);
            color: #fff;
            border-color: var(--danger);
        }

        #btn-reset:not(.btn-disabled):hover {
            background: var(--danger-dark);
        }

        #btn-submit:not(.btn-disabled) {
            background: var(--primary);
            color: #fff;
            border: 1px solid var(--primary);
        }

        #btn-submit:not(.btn-disabled):hover {
            background: var(--primary-dark);
        }

        #btn-reset:not(.btn-disabled):active,
        #btn-submit:not(.btn-disabled):active {
            transform: scale(.97);
        }

        .input {
            width: 100%;
            height: 48px;
            border-radius: 14px;
            border: 2px solid #3b82f6;
            padding: 0 14px;
            font-size: 16px;
            outline: none;
            background: #fff;
            color: #111827;
            margin-bottom: 14px;
        }

        .identity-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 18px 20px;
        }

        .identity-item .small {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 700;
            color: var(--text-muted);
            margin-bottom: 8px;
        }

        .identity-item .small i {
            color: #2563eb;
            font-size: 14px;
            background: #e0ecff;
            padding: 6px;
            border-radius: 50%;
        }

        .identity-item .value {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
            line-height: 1.2;
            word-break: break-word;
            overflow-wrap: break-word;
        }

        .hidden {
            display: none !important;
        }

        .fade-in {
            animation: fadeIn .25s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(.99);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @media (max-width: 900px) {
            .topbar-card {
                flex-direction: column;
                align-items: flex-start;
            }

            .topbar-right {
                width: 100%;
                text-align: left;
            }

            .brand-name {
                font-size: 22px;
            }

            .clock-time {
                font-size: 42px;
            }

            .camera-box {
                height: 300px;
            }

            .action-grid,
            .identity-grid {
                grid-template-columns: 1fr;
            }

            .camera-card-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .camera-mode-frame.scan {
                inset: 28px 16%;
            }

            .countdown-box {
                width: 128px;
                height: 128px;
            }
        }

        @media (max-width: 560px) {
            .mode-grid {
                grid-template-columns: 1fr;
            }

            .camera-box {
                height: 240px;
            }

            .camera-mode-frame.scan {
                inset: 24px 10%;
            }

            .countdown-box {
                width: 104px;
                height: 104px;
            }

            .countdown-number {
                font-size: 34px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card topbar-card">
            <div class="topbar-left">
                <div class="brand-logo-wrap">
                    <img src="<?= base_url('assets/media/photos/logo.png'); ?>" alt="Logo Usaha" class="brand-logo">
                </div>

                <div class="brand-info">
                    <div class="brand-name"><?= esc($namaUsaha ?? 'Nama Usaha'); ?></div>
                    <div class="brand-subtitle"><?= esc($subTitleApp ?? 'Sistem Presensi Pegawai'); ?></div>
                </div>
            </div>

            <div class="topbar-right">
                <div class="clock-label">Waktu Server</div>
                <div id="clock-time" class="clock-time">00:00:00</div>
            </div>
        </div>

        <div class="mode-grid">
            <button id="btn-mode-datang" class="mode-btn datang" type="button">
                <i class="fa-solid fa-right-to-bracket"></i>
                <span>Presensi Datang</span>
            </button>

            <button id="btn-mode-pulang" class="mode-btn pulang" type="button">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Presensi Pulang</span>
            </button>
        </div>

        <div class="card main-card">
            <div id="status-banner" class="status-banner mode-info">
                Silahkan pilih mode presensi
            </div>

            <div class="camera-card">
                <div class="camera-card-header">
                    <div id="camera-card-title" class="section-title">Kamera Presensi</div>
                    <div id="camera-phase-badge" class="camera-phase-badge mode-scan">
                        <i class="fa-solid fa-camera"></i>
                        <span>Menunggu Mode</span>
                    </div>
                </div>

                <div class="camera-box">
                    <video id="camera" autoplay playsinline class="hidden"></video>
                    <canvas id="canvas" class="hidden"></canvas>
                    <img id="selfie-preview" class="hidden" alt="Preview selfie">
                    <div id="camera-placeholder">Kamera akan aktif setelah mode dipilih.</div>
                    <div id="qr-scan-status" class="qr-scan-status hidden">Mencari QRCode...</div>
                    <div id="camera-mode-frame" class="camera-mode-frame scan"></div>
                    <div id="camera-flash" class="camera-flash"></div>

                    <div id="countdown-box" class="countdown-box hidden">
                        <svg class="countdown-svg" viewBox="0 0 120 120">
                            <circle class="countdown-track" cx="60" cy="60" r="50"></circle>
                            <circle id="countdown-progress" class="countdown-progress" cx="60" cy="60" r="50"></circle>
                        </svg>
                        <div id="countdown-number" class="countdown-number">5</div>
                    </div>
                </div>

                <input
                    type="text"
                    id="scan_value"
                    class="input"
                    placeholder="Pilih mode presensi terlebih dahulu"
                    readonly
                    hidden
                    autofocus>

                <div class="action-grid">
                    <button id="btn-reset" type="button" class="btn btn-light btn-disabled" disabled>
                        <i class="fa-solid fa-rotate-left"></i>
                        <span>Reset</span>
                    </button>

                    <button id="btn-submit" type="button" class="btn btn-disabled" disabled>
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Kirim Presensi</span>
                    </button>
                </div>
            </div>

            <div class="identity-card">
                <div class="identity-title">Identitas Pegawai</div>

                <div class="identity-grid">
                    <div class="identity-item">
                        <div class="small"><i class="fa-solid fa-id-card"></i>ID Pegawai</div>
                        <div id="pegawai-kode" class="value">-</div>
                    </div>

                    <div class="identity-item">
                        <div class="small"><i class="fa-solid fa-user"></i>Nama</div>
                        <div id="pegawai-nama" class="value">-</div>
                    </div>

                    <div class="identity-item">
                        <div class="small"><i class="fa-solid fa-clock"></i>Shift Hari Ini</div>
                        <div id="pegawai-shift" class="value">-</div>
                    </div>

                    <div class="identity-item">
                        <div class="small"><i class="fa-solid fa-clipboard-list"></i>Status Jadwal</div>
                        <div id="pegawai-status" class="value">-</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="/assets/js/helper/kadoel-helper.js"></script>
    <script>
        const previewUrl = <?= json_encode(site_url('presensi/preview')); ?>;
        const submitUrl = <?= json_encode(site_url('presensi/submit')); ?>;

        const COUNTDOWN_SECONDS = 5;
        const CIRCLE_RADIUS = 50;
        const CIRCLE_CIRCUMFERENCE = 2 * Math.PI * CIRCLE_RADIUS;

        const clockTime = document.getElementById('clock-time');
        const statusBanner = document.getElementById('status-banner');
        const cameraCardTitle = document.getElementById('camera-card-title');
        const cameraPhaseBadge = document.getElementById('camera-phase-badge');
        const cameraModeFrame = document.getElementById('camera-mode-frame');
        const cameraFlash = document.getElementById('camera-flash');
        const countdownBox = document.getElementById('countdown-box');
        const countdownNumber = document.getElementById('countdown-number');
        const countdownProgress = document.getElementById('countdown-progress');

        const btnModeDatang = document.getElementById('btn-mode-datang');
        const btnModePulang = document.getElementById('btn-mode-pulang');

        const scanInput = document.getElementById('scan_value');
        const btnReset = document.getElementById('btn-reset');
        const btnSubmit = document.getElementById('btn-submit');

        const pegawaiKode = document.getElementById('pegawai-kode');
        const pegawaiNama = document.getElementById('pegawai-nama');
        const pegawaiShift = document.getElementById('pegawai-shift');
        const pegawaiStatus = document.getElementById('pegawai-status');

        const camera = document.getElementById('camera');
        const canvas = document.getElementById('canvas');
        const selfiePreview = document.getElementById('selfie-preview');
        const cameraPlaceholder = document.getElementById('camera-placeholder');
        const qrScanStatus = document.getElementById('qr-scan-status');

        const successSound = new Audio('/assets/media/audio/sukses.mp3');
        const errorSound = new Audio('/assets/media/audio/gagal.mp3');

        let currentMode = null;
        let currentScanValue = '';
        let currentSelfieBase64 = '';
        let previewData = null;
        let mediaStream = null;
        let qrDetector = null;
        let qrScanLoopId = null;
        let qrLastDetectedValue = '';
        let qrIsProcessing = false;
        let countdownTimer = null;
        let countdownValue = COUNTDOWN_SECONDS;
        let isAutoCapturing = false;
        let isQrLocked = false;

        countdownProgress.style.strokeDasharray = CIRCLE_CIRCUMFERENCE;
        countdownProgress.style.strokeDashoffset = 0;

        function updateClock() {
            const now = new Date();
            const hh = String(now.getHours()).padStart(2, '0');
            const mm = String(now.getMinutes()).padStart(2, '0');
            const ss = String(now.getSeconds()).padStart(2, '0');
            clockTime.textContent = `${hh}:${mm}:${ss}`;
        }

        function setBanner(message, type = 'info') {
            statusBanner.className = 'status-banner';

            let iconHtml = '<i class="fa-solid fa-circle-info"></i>';

            if (type === 'datang') {
                statusBanner.classList.add('mode-datang');
                iconHtml = '<i class="fa-solid fa-right-to-bracket"></i>';
            } else if (type === 'pulang') {
                statusBanner.classList.add('mode-pulang');
                iconHtml = '<i class="fa-solid fa-right-from-bracket"></i>';
            } else if (type === 'success') {
                statusBanner.classList.add('mode-success');
                iconHtml = '<i class="fa-solid fa-circle-check"></i>';
            } else if (type === 'error') {
                statusBanner.classList.add('mode-error');
                iconHtml = '<i class="fa-solid fa-circle-xmark"></i>';
            } else {
                statusBanner.classList.add('mode-info');
            }

            statusBanner.innerHTML = `${iconHtml}<span>${message}</span>`;
        }

        function setCameraPhase(text, phase = 'scan') {
            cameraPhaseBadge.className = 'camera-phase-badge';
            cameraPhaseBadge.classList.add(phase === 'selfie' ? 'mode-selfie' : 'mode-scan');
            cameraPhaseBadge.innerHTML = `<i class="fa-solid ${phase === 'selfie' ? 'fa-user-check' : 'fa-qrcode'}"></i><span>${text}</span>`;
            cameraCardTitle.innerHTML = phase === 'selfie' ? '<i class="fa-solid fa-user-check"></i> Ambil Selfie Otomatis' : '<i class="fa-solid fa-qrcode"></i> Scan QRCode';
            cameraModeFrame.className = 'camera-mode-frame ' + (phase === 'selfie' ? 'selfie' : 'scan');
        }

        function setQrFrameGlow(isGlow) {
            cameraModeFrame.classList.toggle('glow-success', isGlow);
        }

        function flashValidEffect() {
            cameraFlash.classList.remove('active');
            void cameraFlash.offsetWidth;
            cameraFlash.classList.add('active');
            setQrFrameGlow(true);
            setTimeout(() => setQrFrameGlow(false), 1200);
        }

        function setResetEnabled(enabled) {
            btnReset.disabled = !enabled;
            btnReset.classList.toggle('btn-disabled', !enabled);
        }

        function setSubmitEnabled(enabled) {
            btnSubmit.disabled = !enabled;
            btnSubmit.classList.toggle('btn-disabled', !enabled);
        }

        function animateModeButton(button) {
            button.classList.remove('mode-pop');
            void button.offsetWidth;
            button.classList.add('mode-pop');
        }

        function setQrScanStatus(message = '', show = false) {
            qrScanStatus.innerHTML = message;
            qrScanStatus.classList.toggle('hidden', !show);
        }

        function resetIdentity() {
            pegawaiKode.textContent = '-';
            pegawaiNama.textContent = '-';
            pegawaiShift.textContent = '-';
            pegawaiStatus.textContent = '-';
        }

        function updateCountdownProgress(secondsLeft) {
            const progressRatio = secondsLeft / COUNTDOWN_SECONDS;
            const dashOffset = CIRCLE_CIRCUMFERENCE * (1 - progressRatio);
            countdownProgress.style.strokeDashoffset = dashOffset;
            countdownNumber.textContent = secondsLeft;
        }

        function stopCountdown() {
            if (countdownTimer) {
                clearInterval(countdownTimer);
                countdownTimer = null;
            }

            countdownValue = COUNTDOWN_SECONDS;
            isAutoCapturing = false;
            countdownBox.classList.add('hidden');
            countdownBox.classList.remove('active');
            updateCountdownProgress(COUNTDOWN_SECONDS);
        }

        function resetPreviewMedia() {
            currentSelfieBase64 = '';
            selfiePreview.src = '';
            selfiePreview.classList.add('hidden');
            canvas.classList.add('hidden');
            setSubmitEnabled(false);
            stopCountdown();
        }

        function hideLiveCamera() {
            camera.srcObject = null;
            camera.classList.add('hidden');
            cameraPlaceholder.classList.remove('hidden');
        }

        function stopScanLoop() {
            if (qrScanLoopId) {
                cancelAnimationFrame(qrScanLoopId);
                qrScanLoopId = null;
            }

            qrIsProcessing = false;
            setQrScanStatus('', false);
        }

        function stopCamera() {
            stopScanLoop();
            stopCountdown();

            if (mediaStream) {
                mediaStream.getTracks().forEach(track => track.stop());
                mediaStream = null;
            }

            hideLiveCamera();
        }

        async function ensureFrontCamera() {
            if (mediaStream) return mediaStream;

            mediaStream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'user'
                },
                audio: false
            });

            return mediaStream;
        }

        async function showLiveCamera() {
            await ensureFrontCamera();

            camera.srcObject = mediaStream;
            camera.classList.remove('hidden');
            cameraPlaceholder.classList.add('hidden');
            selfiePreview.classList.add('hidden');
            camera.classList.remove('fade-in');
            void camera.offsetWidth;
            camera.classList.add('fade-in');

            await camera.play();
        }

        function prepareModeState() {
            previewData = null;
            currentScanValue = '';
            qrLastDetectedValue = '';
            resetIdentity();
            resetPreviewMedia();
            stopCamera();
            setResetEnabled(false);

            if (currentMode === 'datang') {
                setBanner('Mode datang dipilih. Silahkan scan QRCode pegawai.', 'datang');
            } else if (currentMode === 'pulang') {
                setBanner('Mode pulang dipilih. Silahkan scan QRCode pegawai.', 'pulang');
            } else {
                setBanner('Silahkan pilih mode presensi', 'info');
            }

            setCameraPhase('Mode Scan QRCode', 'scan');
            cameraCardTitle.innerHTML = '<i class="fa-solid fa-camera"></i> Kamera Presensi';
            cameraPlaceholder.textContent = 'Kamera akan aktif untuk scan QRCode.';
            scanInput.focus();
        }

        function resetToInitialState() {
            currentMode = null;
            currentScanValue = '';
            currentSelfieBase64 = '';
            previewData = null;
            qrLastDetectedValue = '';

            btnModeDatang.classList.remove('selected');
            btnModePulang.classList.remove('selected');

            isQrLocked = false;
            btnModeDatang.disabled = false;
            btnModePulang.disabled = false;

            btnModeDatang.style.pointerEvents = 'auto';
            btnModePulang.style.pointerEvents = 'auto';

            resetIdentity();
            resetPreviewMedia();
            stopCamera();
            setResetEnabled(false);
            scanInput.value = '';
            setQrFrameGlow(false);

            setBanner('Silahkan pilih mode presensi', 'info');
            setCameraPhase('Menunggu Mode', 'scan');
            cameraCardTitle.innerHTML = '<i class="fa-solid fa-camera"></i> Kamera Presensi';
            cameraPlaceholder.textContent = 'Kamera akan aktif setelah mode dipilih.';
        }

        function initQrDetector() {
            if ('BarcodeDetector' in window) {
                try {
                    qrDetector = new BarcodeDetector({
                        formats: ['qr_code']
                    });
                    return true;
                } catch (error) {
                    qrDetector = null;
                    return false;
                }
            }

            qrDetector = null;
            return false;
        }

        async function startQrScanLoop() {
            if (!mediaStream || !qrDetector || camera.readyState < 2) return;

            stopScanLoop();
            setQrScanStatus('<i class="fa-solid fa-qrcode"></i> Mencari QRCode...', true);

            const scanFrame = async () => {
                if (!mediaStream || camera.readyState < 2) {
                    qrScanLoopId = requestAnimationFrame(scanFrame);
                    return;
                }

                try {
                    const barcodes = await qrDetector.detect(camera);

                    if (Array.isArray(barcodes) && barcodes.length > 0) {
                        const rawValue = (barcodes[0].rawValue || '').trim();

                        if (rawValue && rawValue !== qrLastDetectedValue && !qrIsProcessing && !isAutoCapturing) {
                            qrIsProcessing = true;
                            qrLastDetectedValue = rawValue;
                            scanInput.value = rawValue;
                            currentScanValue = rawValue;
                            setQrScanStatus('QRCode terdeteksi. Memvalidasi...', true);
                            setQrFrameGlow(true);

                            try {
                                await validateQRCode();
                            } finally {
                                setTimeout(() => {
                                    qrIsProcessing = false;
                                }, 1200);
                            }
                        }
                    } else {
                        if (!isAutoCapturing) {
                            setQrFrameGlow(false);
                        }
                    }
                } catch (error) {}

                qrScanLoopId = requestAnimationFrame(scanFrame);
            };

            qrScanLoopId = requestAnimationFrame(scanFrame);
        }

        async function startQrCamera() {
            if (!currentMode) return;

            if (!initQrDetector()) {
                setBanner('Browser ini belum mendukung auto scan QRCode.', 'error');
                return;
            }

            try {
                stopScanLoop();
                qrLastDetectedValue = '';

                await showLiveCamera();
                setCameraPhase('Mode Scan QRCode', 'scan');
                cameraPlaceholder.textContent = 'Arahkan QRCode ke kamera depan.';
                // setBanner('Silahkan scan QRCode', 'info');

                startQrScanLoop();
            } catch (error) {
                stopCamera();
                setBanner('Kamera tidak dapat diakses.', 'error');
            }
        }

        function captureSelfie() {
            if (!previewData || !previewData.boleh_presensi) {
                setBanner('Validasi QRCode terlebih dahulu.', 'error');
                return;
            }

            if (!mediaStream || camera.readyState < 2) {
                setBanner('Kamera selfie belum aktif.', 'error');
                return;
            }

            const context = canvas.getContext('2d');
            canvas.width = camera.videoWidth || 1280;
            canvas.height = camera.videoHeight || 720;
            context.drawImage(camera, 0, 0, canvas.width, canvas.height);

            currentSelfieBase64 = canvas.toDataURL('image/jpeg', 0.9);
            selfiePreview.src = currentSelfieBase64;
            selfiePreview.classList.remove('hidden');
            selfiePreview.classList.add('fade-in');

            hideLiveCamera();
            cameraPlaceholder.classList.add('hidden');
            setSubmitEnabled(true);
            stopCountdown();

            setBanner('Selfie berhasil diambil otomatis. Klik Kirim Presensi.', 'success');
            setQrScanStatus('', false);
        }

        function startSelfieCountdown() {
            stopCountdown();
            isAutoCapturing = true;
            countdownValue = COUNTDOWN_SECONDS;
            countdownBox.classList.remove('hidden');
            countdownBox.classList.add('active');
            updateCountdownProgress(countdownValue);

            setQrScanStatus(`Bersiap untuk selfie (${countdownValue})`, true);
            setBanner('Bersiap, selfie otomatis akan diambil.', 'info');

            countdownTimer = setInterval(() => {
                countdownValue -= 1;

                if (countdownValue <= 0) {
                    setQrScanStatus('Mengambil selfie...', true);
                    captureSelfie();
                    return;
                }

                updateCountdownProgress(countdownValue);
                setQrScanStatus(`Bersiap untuk selfie (${countdownValue})`, true);
            }, 1000);
        }

        async function startSelfieMode() {
            try {
                stopScanLoop();
                await showLiveCamera();

                setCameraPhase('Mode Selfie Otomatis', 'selfie');
                setBanner('QRCode valid. Siapkan wajah, selfie otomatis akan dimulai.', 'success');
                startSelfieCountdown();
            } catch (error) {
                stopCountdown();
                setBanner('Kamera tidak dapat diakses untuk selfie.', 'error');
            }
        }

        function fillIdentity(data) {
            const pegawai = data.pegawai || {};
            const shift = data.shift || {};

            pegawaiKode.textContent = pegawai.kode_pegawai || '-';
            pegawaiNama.textContent = pegawai.nama_pegawai || '-';
            pegawaiShift.textContent = shift.nama_shift || '-';
            pegawaiStatus.textContent = data.status_harian || '-';
        }

        async function validateQRCode() {
            if (!currentMode) {
                setBanner('Silahkan pilih mode presensi terlebih dahulu.', 'error');
                return;
            }

            const scanValue = scanInput.value.trim();

            if (!scanValue) {
                setBanner('Kode pegawai belum terbaca dari QRCode.', 'error');
                return;
            }

            currentScanValue = scanValue;
            setBanner('Memvalidasi QRCode pegawai...', 'info');

            const formData = new FormData();
            formData.append('scan_value', scanValue);
            formData.append('mode', currentMode);

            try {
                const response = await fetch(previewUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const hasil = await response.json();

                if (!hasil.sukses) {
                    resetIdentity();
                    previewData = null;
                    resetPreviewMedia();

                    showAlert('error', 'Presensi Gagal', hasil.pesan || 'QRCode tidak valid.', () => {
                        resetToInitialState();
                    });
                    return;
                }

                flashValidEffect();

                const data = hasil.data || {};
                previewData = data;

                fillIdentity(data);
                setResetEnabled(true);

                let pesanPreview = hasil.pesan || 'QRCode valid.';

                if (data.mode === 'datang') {
                    if (data.preview_status === 'telat' && Number(data.preview_telat) > 0) {
                        pesanPreview += '. Status: ' + (KadoelHelper.toCapitalizeEachWord(data.preview_status) || '-') + ' (' + data.preview_telat + ' menit)';
                    } else {
                        pesanPreview += '. Status: ' + (KadoelHelper.toCapitalizeEachWord(data.preview_status) || '-');
                    }
                }

                if (data.mode === 'pulang') {
                    if (data.preview_status === 'pulang_cepat' && Number(data.preview_pulang_cepat) > 0) {
                        pesanPreview += '. Status: ' + (KadoelHelper.toCapitalizeEachWord(data.preview_status) || '-') + ' (' + data.preview_pulang_cepat + ' menit)';
                    } else {
                        pesanPreview += '. Status: ' + (KadoelHelper.toCapitalizeEachWord(data.preview_status) || '-');
                    }
                }

                console.log(pesanPreview);

                isQrLocked = true;

                // 🔒 lock semua tombol mode
                btnModeDatang.disabled = true;
                btnModePulang.disabled = true;

                btnModeDatang.style.pointerEvents = 'none';
                btnModePulang.style.pointerEvents = 'none';

                setBanner(pesanPreview, 'success');
                await startSelfieMode();
            } catch (error) {
                previewData = null;
                resetIdentity();
                resetPreviewMedia();
                setResetEnabled(false);
                setBanner('Gagal memvalidasi QRCode.', 'error');
            }
        }

        async function submitPresensi() {
            if (!currentMode) {
                setBanner('Mode presensi belum dipilih.', 'error');
                return;
            }

            if (!previewData || !previewData.boleh_presensi) {
                setBanner('QRCode belum valid.', 'error');
                return;
            }

            if (!currentSelfieBase64) {
                setBanner('Selfie belum tersedia. Tunggu auto capture selesai.', 'error');
                return;
            }

            setBanner('Mengirim presensi...', 'info');

            const formData = new FormData();
            formData.append('scan_value', currentScanValue);
            formData.append('selfie_base64', currentSelfieBase64);
            formData.append('mode', currentMode);
            formData.append('scan_at', previewData.scan_at || '');

            try {
                const response = await fetch(submitUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const hasil = await response.json();

                if (!hasil.sukses) {
                    showAlert('error', 'Presensi Gagal', hasil.pesan || 'Presensi gagal diproses.', () => {
                        resetToInitialState();
                    });
                    return;
                }

                let pesan = hasil.pesan || 'Presensi berhasil diproses.';

                if (hasil.tipe === 'datang') {
                    pesan += '. <br />Status: ' + (KadoelHelper.toCapitalizeEachWord(hasil.status) || '-');
                    if (hasil.status === 'telat') {
                        pesan += ' (' + hasil.menit_telat + ' menit)';
                    }
                }

                if (hasil.tipe === 'pulang') {
                    pesan += '. <br />Status: ' + (KadoelHelper.toCapitalizeEachWord(hasil.status) || '-');
                    if (hasil.status === 'pulang_cepat') {
                        pesan += ' (' + hasil.menit_pulang_cepat + ' menit)';
                    }
                }

                showAlert('success', 'Presensi Berhasil', pesan, () => {
                    resetToInitialState();
                });
            } catch (error) {
                showAlert('error', 'Error', 'Terjadi kesalahan saat mengirim presensi.', () => {
                    resetToInitialState();
                });
            }
        }


        function playAlertSound(type) {
            let sound = null;

            if (type === 'success') sound = successSound;
            else if (type === 'error') sound = errorSound;

            if (!sound) return;

            try {
                sound.currentTime = 0;
                sound.play().catch(() => {});
            } catch (error) {}
        }

        function getColorByType(type) {
            if (type === 'success') return '#16a34a';
            if (type === 'error') return '#dc2626';
            if (type === 'warning') return '#f59e0b';
            return '#2563eb';
        }

        function showAlert(type, title, text, callback = null) {
            playAlertSound(type);

            Swal.fire({
                title: title,
                imageUrl: '/assets/media/photos/logo.png',
                imageWidth: 80,
                imageHeight: 80,
                html: `<b>${text}</b>`,
                confirmButtonText: 'OK',
                confirmButtonColor: getColorByType(type),
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: true
            }).then(() => {
                if (callback) callback();
            });
        }

        btnModeDatang.addEventListener('click', async () => {
            if (isQrLocked) return; // 🔒 kalau sudah scan, blok semua

            currentMode = 'datang';

            btnModeDatang.classList.add('selected');
            btnModePulang.classList.remove('selected');

            animateModeButton(btnModeDatang);

            prepareModeState();
            await startQrCamera();
        });

        btnModePulang.addEventListener('click', async () => {
            if (isQrLocked) return;

            currentMode = 'pulang';

            btnModePulang.classList.add('selected');
            btnModeDatang.classList.remove('selected');

            animateModeButton(btnModePulang);

            prepareModeState();
            await startQrCamera();
        });

        btnReset.addEventListener('click', () => resetToInitialState());
        btnSubmit.addEventListener('click', submitPresensi);

        setResetEnabled(false);
        setSubmitEnabled(false);
        setInterval(updateClock, 1000);
        updateClock();
        resetToInitialState();
    </script>
</body>

</html>