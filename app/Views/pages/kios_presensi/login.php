<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title><?= esc($pageTitle ?? 'Login Kios Presensi'); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="/assets/media/favicons/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eef2f6;
            font-family: Inter, Arial, sans-serif;
            color: #1f2937;
            padding: 16px;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background: #fff;
            border-radius: 20px;
            padding: 28px;
            box-shadow: 0 12px 40px rgba(15, 23, 42, .12);
            border: 1px solid #d8e0ea;
        }

        .logo-wrap {
            width: 74px;
            height: 74px;
            border-radius: 18px;
            background: #f8fafc;
            border: 1px solid #d8e0ea;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
            overflow: hidden;
        }

        .logo-wrap img {
            width: 56px;
            height: 56px;
            object-fit: contain;
        }

        h1 {
            margin: 0 0 8px;
            text-align: center;
            font-size: 24px;
            font-weight: 800;
        }

        p {
            margin: 0 0 22px;
            text-align: center;
            color: #64748b;
            line-height: 1.5;
        }

        .input-wrap {
            position: relative;
            margin-bottom: 14px;
        }

        .input-wrap i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
        }

        input {
            width: 100%;
            height: 52px;
            border-radius: 14px;
            border: 1px solid #cbd5e1;
            padding: 0 14px 0 44px;
            font-size: 18px;
            outline: none;
        }

        input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
        }

        button {
            width: 100%;
            height: 52px;
            border: 0;
            border-radius: 14px;
            background: #2563eb;
            color: #fff;
            font-size: 16px;
            font-weight: 800;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        button:hover {
            background: #1d4ed8;
        }

        .alert {
            padding: 12px 14px;
            border-radius: 12px;
            margin-bottom: 14px;
            background: #fee2e2;
            color: #991b1b;
            font-weight: 700;
            line-height: 1.4;
        }
    </style>
</head>

<body>
    <form class="login-card" method="post" action="<?= base_url('presensi/login'); ?>" autocomplete="off">
        <?= csrf_field(); ?>

        <div class="logo-wrap">
            <img src="<?= base_url('assets/media/photos/logo.png'); ?>" alt="Logo">
        </div>

        <h1>PRESENSI</h1>
        <p>Masukkan PIN</p>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?= esc(session()->getFlashdata('error')); ?>
            </div>
        <?php endif; ?>

        <div class="input-wrap">
            <i class="fa-solid fa-lock"></i>
            <input
                type="password"
                name="pin"
                placeholder="PIN Halaman Presensi"
                inputmode="numeric"
                autofocus
                required>
        </div>

        <button type="submit">
            <i class="fa-solid fa-right-to-bracket"></i>
            Masuk
        </button>
    </form>
</body>

</html>