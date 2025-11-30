<?php
session_start();
include 'koneksi.php';

$errorLogin  = "";
$errorReg    = "";
$successReg  = "";

/* ======================
   PROSES LOGIN
====================== */
if (isset($_POST['login'])) {

    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if ($email === "" || $password === "") {
        $errorLogin = "Email dan password wajib diisi.";
    } else {
        $stmt = $koneksi->prepare("
            SELECT id, email, password, nama, level
            FROM sania_admin
            WHERE email = ?
            LIMIT 1
        ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $emailDB, $hash, $nama, $level);
            $stmt->fetch();

            if (md5($password) === $hash) {
                $_SESSION['user_role']    = $level;
                $_SESSION['user_id']      = $id;
                $_SESSION['nama_lengkap'] = $nama;

                header("Location: dashboard.php");
                exit;
            } else {
                $errorLogin = "Password salah!";
            }
        } else {
            $errorLogin = "Email tidak ditemukan.";
        }

        $stmt->close();
    }
}

/* ======================
   PROSES REGISTER
====================== */
if (isset($_POST['register'])) {

    $nama     = trim($_POST['reg_nama']);
    $email    = trim($_POST['reg_email']);
    $password = trim($_POST['reg_password']);

    if ($nama === "" || $email === "" || $password === "") {
        $errorReg = "Semua field wajib diisi.";
    } else {
        $cek = $koneksi->prepare("SELECT email FROM sania_admin WHERE email=?");
        $cek->bind_param("s", $email);
        $cek->execute();
        $cek->store_result();

        if ($cek->num_rows > 0) {
            $errorReg = "Email sudah terdaftar.";
        } else {
            $passHash = md5($password);

            $stmt = $koneksi->prepare("
                INSERT INTO sania_admin (email, password, nama, level)
                VALUES (?, ?, ?, 'user')
            ");
            $stmt->bind_param("sss", $email, $passHash, $nama);

            if ($stmt->execute()) {
                $successReg = "Registrasi berhasil! Silakan login.";
            } else {
                $errorReg = "Gagal menyimpan data.";
            }

            $stmt->close();
        }

        $cek->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Login • Mt. Talang</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #16a34a;
            --primary-soft: #bbf7d0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Poppins", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: url('img/img6.jpg') center/cover no-repeat fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        /* CARD UTAMA */
        .auth-card-single {
            position: relative;
            max-width: 780px;
            width: 100%;
            margin: auto;
            padding: 34px 36px 26px;
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 28px;
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.45);
            overflow: hidden;
        }

        .auth-card-single::before {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 0 0,
                    rgba(255, 255, 255, 0.65),
                    transparent 60%);
            opacity: .9;
            pointer-events: none;
        }

        .auth-content {
            position: relative;
            z-index: 1;
            color: #16181fff;
        }

        /* HEADER MT. TALANG */
        .auth-title {
            font-size: 30px;
            font-weight: 800;
            color: #f9fafb;
            text-shadow: 0 2px 6px rgba(15, 23, 42, 0.45);
            letter-spacing: 0.04em;
            margin-bottom: 4px;
        }

        .auth-sub {
            color: #555656ff;
            font-size: 14px;
            margin-bottom: 18px;
        }

        /* Badge header gunung */
        .info-badge {
            display: inline-flex;
            align-items: center;
            gap: 50px;
            padding: 8px 10px;
            border-radius: 799px;
            background: linear-gradient(120deg,
                    rgba(15, 23, 42, 0.25),
                    rgba(148, 163, 184, 0.25));
            border: 1px solid rgba(255, 255, 255, 0.45);
            box-shadow: 0 14px 34px rgba(15, 23, 42, 0.45);
            margin-bottom: 20px;
            backdrop-filter: blur(2px);
            -webkit-backdrop-filter: blur(10px);
        }

        /* icon bulat di kiri */
        .info-badge-icon {
            width: 50px;
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 29px;
        }

        /* pill ketinggian & jalur */
        .info-badge-pill {
            text-align: center;
            width: 150px;
            padding: 6px 14px;
            border-radius: 999px;
            background: rgba(28, 170, 242, 0.55);
            color: #ffffffff;
            font-size: 12px;
            font-weight: 500;
            white-space: nowrap;
        }


        .auth-quote {
            font-style: italic;
            font-weight: bold;
            font-size: 15px;
            color: #474747ff;
            margin-bottom: 22px;
        }

        /* FORM WRAPPER */
        .auth-form {
            margin-top: 10px;
        }

        /* Tabs login/register */
        .auth-tabs {
            display: inline-flex;
            padding: 3px;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.04);
            border: 1px solid #e5e7eb;
            margin-bottom: 18px;
        }

        .auth-tab {
            border: none;
            background: transparent;
            color: #6b7280;
            font-size: 13px;
            font-weight: 500;
            padding: 6px 18px;
            border-radius: 999px;
            cursor: pointer;
            transition: .18s ease;
        }

        .auth-tab.active {
            background: #46a6e6ff;
            color: #ffffffff;
            box-shadow: 0 10px 24px rgba(125, 194, 226, 0.45);
        }

        /* INPUT */
        .auth-group {
            margin-bottom: 14px;
        }

        .auth-group label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 4px;
            color: #111827;
        }

        .auth-input {
            width: 100%;
            border-radius: 999px;
            border: 1px solid #d1d5db;
            padding: 10px 14px;
            background: rgba(255, 255, 255, 0.92);
            font-size: 13px;
            outline: none;
            transition: .18s ease;
        }

        .auth-input::placeholder {
            color: #9ca3af;
        }

        .auth-input:focus {
            border-color: #16a34a;
            box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.25);
            background: #ffffff;
        }

        /* MESSAGE */
        .msg {
            padding: 9px 12px;
            border-radius: 12px;
            font-size: 12px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .msg-error {
            background: rgba(248, 113, 113, 0.15);
            color: #b91c1c;
            border: 1px solid rgba(248, 113, 113, 0.6);
        }

        .msg-success {
            background: rgba(22, 163, 74, 0.12);
            color: #166534;
            border: 1px solid rgba(22, 188, 230, 0.6);
        }

        /* BUTTONS */
        .btn-main {
            width: 100%;
            margin-top: 12px;
            padding: 11px 16px;
            border-radius: 999px;
            background: linear-gradient(120deg,
                    rgba(21, 163, 210, 0.85),
                    rgba(148, 163, 184, 0.45));
            color: white;
            border: none;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            box-shadow: 0 16px 34px rgba(46, 51, 48, 0.4);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: transform .13s ease, box-shadow .13s ease, filter .13s ease;
        }

        .btn-main:hover {
            transform: translateY(-1px);
            filter: brightness(1.02);
            box-shadow: 0 20px 44px rgba(8, 80, 111, 0.55);
        }

        /* FOOTER DI DALAM CARD */
        .auth-footer {
            margin-top: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            font-size: 12px;
            flex-wrap: wrap;
        }

        .auth-footer span {
            color: #4b5563;
        }

        .auth-link {
            color: #1fdadaff;
            text-decoration: none;
            font-weight: 500;
        }

        .auth-link:hover {
            text-decoration: underline;
        }

        .btn-back-home {
            padding: 7px 14px;
            border-radius: 999px;
            border: 1px solid rgba(148, 163, 184, 0.9);
            background: rgba(255, 255, 255, 0.85);
            color: #1f2937;
            font-size: 12px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: .16s ease;
        }

        .btn-back-home:hover {
            background: #e5e7eb;
            transform: translateY(-1px);
        }

        /* REGISTER FORM HIDDEN DEFAULT */
        #regForm {
            display: none;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            body {
                padding: 16px;
            }

            .auth-card-single {
                padding: 26px 20px 20px;
                border-radius: 22px;
            }

            .auth-title {
                font-size: 24px;
            }

            .auth-footer {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>

    <script>
        function showForm(id) {
            const loginForm = document.getElementById('loginForm');
            const regForm = document.getElementById('regForm');
            const tabLogin = document.getElementById('tab-login');
            const tabReg = document.getElementById('tab-reg');

            if (id === 'login') {
                loginForm.style.display = 'block';
                regForm.style.display = 'none';
                tabLogin.classList.add('active');
                tabReg.classList.remove('active');
            } else {
                loginForm.style.display = 'none';
                regForm.style.display = 'block';
                tabReg.classList.add('active');
                tabLogin.classList.remove('active');
            }
        }
    </script>
</head>

<body>

    <div class="auth-card-single">
        <div class="auth-content">

            <div class="auth-title">Mt. Talang</div>
            <div class="auth-sub">
                Panel login untuk admin dan pengguna pendakian Gunung Talang.
            </div>

            <div class="info-badge">
                <div class="info-badge-icon">⛰️</div>
                <div class="info-badge-pill">2.597 mdpl</div>
                <div class="info-badge-pill">Jalur Bukik Bulek<br>Kampung Batu</div>
            </div>


            <div class="auth-quote">
                “Naik pelan, nikmati jalannya. Gunung tidak ke mana-mana.”
            </div>

            <!-- FORM LOGIN / REGISTER -->
            <div class="auth-form">
                <div class="auth-tabs">
                    <button id="tab-login" class="auth-tab active" type="button" onclick="showForm('login')">
                        Login
                    </button>
                    <button id="tab-reg" class="auth-tab" type="button" onclick="showForm('reg')">
                        Registrasi Akun
                    </button>
                </div>

                <!-- LOGIN -->
                <div id="loginForm">
                    <?php if ($errorLogin): ?>
                        <div class="msg msg-error"><?= $errorLogin ?></div>
                    <?php endif; ?>

                    <form method="POST" autocomplete="off">
                        <div class="auth-group">
                            <label>Email</label>
                            <input type="email" name="email" class="auth-input" placeholder="contoh: kamu@mail.com" required>
                        </div>

                        <div class="auth-group">
                            <label>Password</label>
                            <input type="password" name="password" class="auth-input" placeholder="Masukkan password" required>
                        </div>

                        <button class="btn-main" name="login" type="submit">
                            Masuk
                        </button>
                    </form>
                </div>

                <!-- REGISTER -->
                <div id="regForm">
                    <?php if ($errorReg): ?>
                        <div class="msg msg-error"><?= $errorReg ?></div>
                    <?php endif; ?>
                    <?php if ($successReg): ?>
                        <div class="msg msg-success"><?= $successReg ?></div>
                    <?php endif; ?>

                    <form method="POST" autocomplete="off">
                        <div class="auth-group">
                            <label>Nama Lengkap</label>
                            <input type="text" name="reg_nama" class="auth-input" placeholder="Nama lengkapmu" required>
                        </div>

                        <div class="auth-group">
                            <label>Email</label>
                            <input type="email" name="reg_email" class="auth-input" placeholder="Email aktif" required>
                        </div>

                        <div class="auth-group">
                            <label>Password</label>
                            <input type="password" name="reg_password" class="auth-input" placeholder="Minimal 6 karakter" required>
                        </div>

                        <button class="btn-main" name="register" type="submit">
                            Buat Akun
                        </button>
                    </form>
                </div>

                <!-- FOOTER DALAM CARD: TEKS + BACK HOME -->
                <div class="auth-footer">
                    <span>
                        Belum punya akun?
                        <a href="#" class="auth-link" onclick="showForm('reg'); return false;">
                            Daftar di sini
                        </a>
                    </span>

                    <a href="index.php?p=home" class="btn-back-home">
                        ← Kembali ke beranda
                    </a>
                </div>
            </div>

        </div>
    </div>

</body>

</html>