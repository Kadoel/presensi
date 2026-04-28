<!doctype html>
<html lang="en" class="remember-theme">

<head>
    <meta charset="utf-8">
    <!--
      Available classes for <html> element:

      'dark'                  Enable dark mode - Default dark mode preference can be set in app.js file (always saved and retrieved in localStorage afterwards):
                                window.Codebase = new App({ darkMode: "system" }); // "on" or "off" or "system"
      'dark-custom-defined'   Dark mode is always set based on the preference in app.js file (no localStorage is used)
      'remember-theme'        Remembers active color theme between pages using localStorage when set through
                                - Theme helper buttons [data-toggle="theme"]
    -->
    <meta name="viewport" content="width=device-width,initial-scale=1.0">

    <title><?= $judul; ?> | Presensi Bupda</title>

    <meta name="description" content="Presensi Bupda">
    <meta name="author" content="I Kadek Adi Suandana">
    <meta name="robots" content="noindex, nofollow">

    <!-- Open Graph Meta -->
    <meta property="og:title" content="<?= $judul; ?> | Presensi Bupda">
    <meta property="og:site_name" content=" Presensi Bupda">
    <meta property="og:description" content="Presensi Bupda">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= base_url(); ?>">
    <meta property="og:image" content="/assets/media/favicons/favicon-192x192.png">

    <!-- Icons -->
    <!-- The following icons can be replaced with your own, they are used by desktop and mobile browsers -->
    <link rel="shortcut icon" href="/assets/media/favicons/favicon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/assets/media/favicons/favicon-192x192.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/media/favicons/apple-touch-icon-180x180.png">
    <!-- END Icons -->

    <!-- Stylesheets -->

    <!-- Codebase framework -->
    <link rel="stylesheet" id="css-main" href="/assets/css/codebase.min.css">
    <link rel="stylesheet" href="/assets/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css">
    <link rel="stylesheet" href="/assets/plugins/flatpickr/flatpickr.min.css">
    <link rel="stylesheet" href="/assets/plugins/flatpickr/plugins/monthSelect/style.css">
    <link rel="stylesheet" href="/assets/plugins/cooltipz/cooltipz.css">
    <link rel="stylesheet" href="/assets/plugins/DataTablesbs5/datatables.css">
    <link rel="stylesheet" href="/assets/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="/assets/plugins/sweetalert2/sweetalert2.min.css">
    <link rel="stylesheet" href="/assets/plugins/animate/animate.min.css">
    <link rel="stylesheet" id="css-main" href="/assets/css/custom/custom.css">
    <!-- You can include a specific file from css/themes/ folder to alter the default color theme of the template. eg: -->

    <link rel="stylesheet" id="css-theme" href="/assets/css/themes/corporate.min.css">
</head>

<body>
    <!-- Page Container -->
    <!--
      Available classes for #page-container:

      SIDEBAR & SIDE OVERLAY

        'sidebar-r'                                 Right Sidebar and left Side Overlay (default is left Sidebar and right Side Overlay)
        'sidebar-mini'                              Mini hoverable Sidebar (screen width > 991px)
        'sidebar-o'                                 Visible Sidebar by default (screen width > 991px)
        'sidebar-o-xs'                              Visible Sidebar by default (screen width < 992px)
        'sidebar-dark'                              Dark themed sidebar

        'side-overlay-hover'                        Hoverable Side Overlay (screen width > 991px)
        'side-overlay-o'                            Visible Side Overlay by default

        'enable-page-overlay'                       Enables a visible clickable Page Overlay (closes Side Overlay on click) when Side Overlay opens

        'side-scroll'                               Enables custom scrolling on Sidebar and Side Overlay instead of native scrolling (screen width > 991px)

      HEADER

        ''                                          Static Header if no class is added
        'page-header-fixed'                         Fixed Header

      HEADER STYLE

        ''                                          Classic Header style if no class is added
        'page-header-modern'                        Modern Header style
        'page-header-dark'                          Dark themed Header (works only with classic Header style)
        'page-header-glass'                         Light themed Header with transparency by default
                                                    (absolute position, perfect for light images underneath - solid light background on scroll if the Header is also set as fixed)
        'page-header-glass page-header-dark'        Dark themed Header with transparency by default
                                                    (absolute position, perfect for dark images underneath - solid dark background on scroll if the Header is also set as fixed)

      MAIN CONTENT LAYOUT

        ''                                          Full width Main Content if no class is added
        'main-content-boxed'                        Full width Main Content with a specific maximum width (screen width > 1200px)
        'main-content-narrow'                       Full width Main Content with a percentage width (screen width > 1200px)
    -->
    <div id="page-container" class="sidebar-dark side-scroll page-header-fixed page-header-dark main-content-boxed">
        <!-- Sidebar -->
        <?= $this->include('theme/pegawai/sidebar'); ?>
        <!-- END Sidebar -->
        <!-- Header -->
        <?= $this->include('theme/pegawai/header'); ?>
        <!-- END Header -->

        <!-- Main Container -->
        <main id="main-container">
            <!-- Hero -->
            <div class="bg-image" style="background-image: url('assets/media/photos/photo27@2x.jpg');">
                <div class="bg-white-90">
                    <div class="content text-center">
                        <div class="pt-5 pb-3">
                            <h1 class="h2 fw-bold text-black-75 mb-2"><?= $judul; ?></h1>
                            <h2 class="h5 fw-medium text-muted"><?= $caption; ?></h2>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END Hero -->

            <!-- Page Content -->
            <div class="content content-full">
                <?= $this->renderSection('content'); ?>
            </div>
            <!-- END Page Content -->
        </main>
        <!-- END Main Container -->

        <!-- Footer -->
        <footer id="page-footer">
            <div class="content py-3">
                <div class="row fs-sm">
                    <div class="col-sm-6 order-sm-2 py-1 text-center text-sm-end">
                        Crafted with <i class="fa fa-hand-peace text-warning"></i> by <a class="fw-semibold" href="https://www.instagram.com/kadoel" target="_blank">I Kadek Adi Suandana</a>
                    </div>
                    <div class="col-sm-6 order-sm-1 py-1 text-center text-sm-start">
                        <a class="fw-semibold" href="javascript:void()">Anika Media</a> &copy; <span data-toggle="year-copy"></span>
                    </div>
                </div>
            </div>
        </footer>
        <!-- END Footer -->
    </div>
    <!-- END Page Container -->

    <!--
        Codebase JS

        Core libraries and functionality
        webpack is putting everything together at assets/_js/main/app.js
    -->
    <script src="/assets/plugins/jquery3/jquery.min.js"></script>
    <script src="/assets/plugins/DataTablesbs5/datatables.min.js"></script>
    <script src="/assets/plugins/bootstrap-notify/bootstrap-notify.min.js"></script>
    <script src="/assets/plugins/loadingoverlay/loadingoverlay.min.js"></script>
    <script src="/assets/plugins/sweetalert2/sweetalert2.all.min.js"></script>
    <script src="/assets/plugins/flatpickr/flatpickr.min.js"></script>
    <script src="/assets/js/helper/kadoel-helper.js"></script>
    <script src="/assets/js/helper/kadoel-ajax.js"></script>
    <script src="assets/js/codebase.app.min.js"></script>

    <!-- Page JS Code -->
    <?php
    $segment1 = trim((string) current_url(true)->getSegment(1)); // admin / pegawai
    $segment2 = trim((string) current_url(true)->getSegment(2)); // pengguna / dst

    $jsView = null;

    if ($segment1 !== '') {
        // CASE 1: /admin atau /pegawai => beranda/index
        if ($segment2 === '') {
            $candidate = 'pages/' . $segment1 . '/beranda/js';

            if (is_file(APPPATH . 'Views/' . $candidate . '.php')) {
                $jsView = $candidate;
            }
        }
        // CASE 2: /admin/pengguna
        else {
            $candidate = 'pages/' . $segment1 . '/' . $segment2 . '/js';

            if (is_file(APPPATH . 'Views/' . $candidate . '.php')) {
                $jsView = $candidate;
            }
        }
    }
    ?>

    <?php if ($jsView): ?>
        <?= $this->include($jsView) ?>
    <?php endif; ?>
</body>

</html>