<?php if (session()->get('is_login') === true) {
    header("Location: " . base_url(session()->get('role')));
    exit;
} ?>
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

    <title>Login | Presensi</title>

    <meta name="description" content="Sistem Antrian SKANADA">
    <meta name="author" content="I Kadek Adi Suandana">
    <meta name="robots" content="noindex, nofollow">

    <!-- Open Graph Meta -->
    <meta property="og:title" content="Login | Presensi">
    <meta property="og:site_name" content="Presensi">
    <meta property="og:description" content="Sistem Antrian SKANADA">
    <meta property="og:type" content="website">
    <meta property="og:url" content="">
    <meta property="og:image" content="">

    <!-- Icons -->
    <!-- The following icons can be replaced with your own, they are used by desktop and mobile browsers -->
    <link rel="shortcut icon" href="/assets/media/favicons/favicon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/assets/media/favicons/favicon-192x192.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/media/favicons/apple-touch-icon-180x180.png">
    <!-- END Icons -->

    <!-- Stylesheets -->

    <!-- Codebase framework -->
    <link rel="stylesheet" id="css-main" href="/assets/css/codebase.min.css">

    <!-- You can include a specific file from css/themes/ folder to alter the default color theme of the template. eg: -->
    <link rel="stylesheet" id="css-theme" href="assets/css/themes/flat.min.css">
    <!-- END Stylesheets -->

    <!-- Load and set color theme + dark mode preference (blocking script to prevent flashing) -->
    <script src="/assets/js/setTheme.js"></script>
    <style>
        /* Parent relative sudah di form-floating */
        .password-toggle {
            position: absolute;
            top: 50%;
            /* vertikal center terhadap input */
            right: 0.75rem;
            /* jarak dari kanan */
            transform: translateY(-50%);
            cursor: pointer;
            line-height: 1;
            /* pastikan icon tidak bergeser */
            z-index: 2;
            color: #6c757d;
            transition: color 0.2s;
        }

        .password-toggle:hover {
            color: #495057;
        }

        .password-input {
            padding-right: 2.5rem;
            /* ruang untuk icon */
        }
    </style>

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
    <div id="page-container" class="main-content-boxed">

        <!-- Main Container -->
        <main id="main-container">
            <!-- Page Content -->
            <div class="bg-body-dark">
                <div class="hero-static content content-full px-1">
                    <div class="row mx-0 justify-content-center">
                        <div class="col-lg-8 col-xl-6">
                            <!-- Header -->
                            <div class="py-4 text-center">
                                <img src="<?= base_url('assets/media/favicons/logo.png') ?>" width="602" height="383" class="img-fluid">
                            </div>
                            <!-- END Header -->

                            <!-- Sign In Form -->
                            <!-- jQuery Validation functionality is initialized with .js-validation-signin class in js/pages/op_auth_signin.min.js which was auto compiled from _js/pages/op_auth_signin.js -->
                            <!-- For more examples you can check out https://github.com/jzaefferer/jquery-validation -->
                            <form class="js-validation-signin" action="/auth" method="POST" autocomplete="off">
                                <div class="block block-themed block-rounded block-fx-shadow">
                                    <div class="block-header bg-gd-dusk">
                                        <h3 class="block-title">Silahkan Masuk</h3>
                                    </div>
                                    <div class="block-content">
                                        <?php if (session()->getFlashdata('gagal-login')) : ?>
                                            <div class="alert alert-danger alert-dismissible" role="alert">
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                                <h3 class="alert-heading fs-5 fw-bold mb-1">Error</h3>
                                                <p class="mb-0"><?= session()->getFlashdata('gagal-login') ?></p>
                                            </div>
                                        <?php endif; ?>

                                        <?= csrf_field(); ?>
                                        <div class="form-floating mb-4">
                                            <input type="text" class="form-control <?= validation_show_error('username') != null ? 'is-invalid' : '' ?>" id="username" name="username" placeholder="Masukkan Username" autocomplete="off">
                                            <label class="form-label" for="username">Username</label>
                                            <div class="invalid-feedback animated fadeIn"><?= validation_show_error('username'); ?></div>
                                        </div>
                                        <div class="form-floating mb-4 position-relative">
                                            <input type="password"
                                                class="form-control <?= validation_show_error('password') ? 'is-invalid' : '' ?> pe-5"
                                                id="password"
                                                name="password"
                                                placeholder="Masukkan Password"
                                                autocomplete="off">

                                            <label for="password">Password</label>

                                            <!-- Toggle Icon -->
                                            <span class="password-toggle" onclick="togglePassword()">
                                                <i id="eyeIcon" class="fa fa-eye"></i>
                                            </span>

                                            <div class="invalid-feedback">
                                                <?= validation_show_error('password'); ?>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-6 d-sm-flex align-items-center push">

                                            </div>
                                            <div class="col-sm-6 text-sm-end push">
                                                <button type="submit" class="btn btn-lg btn-alt-primary fw-medium text-end">
                                                    Masuk
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <!-- END Sign In Form -->
                        </div>
                    </div>
                </div>
            </div>
            <!-- END Page Content -->
        </main>
        <!-- END Main Container -->
    </div>
    <!-- END Page Container -->

    <!--
        Codebase JS

        Core libraries and functionality
        webpack is putting everything together at assets/_js/main/app.js
    -->
    <script src="/assets/js/codebase.app.min.js"></script>
    <script>
        // Cegah halaman tampil dari cache ketika tombol back ditekan
        window.addEventListener("pageshow", function(event) {
            if (event.persisted || window.performance.getEntriesByType("navigation")[0].type === "back_forward") {
                window.location.reload();
            }
        });
    </script>
    <script>
        function togglePassword() {
            const password = document.getElementById("password");
            const eyeIcon = document.getElementById("eyeIcon");

            if (password.type === "password") {
                password.type = "text";
                eyeIcon.classList.replace("fa-eye", "fa-eye-slash");
            } else {
                password.type = "password";
                eyeIcon.classList.replace("fa-eye-slash", "fa-eye");
            }
        }
    </script>

</body>

</html>