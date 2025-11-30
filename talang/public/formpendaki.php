<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'koneksi.php';

// ====== WAJIB LOGIN DULU ======
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // sesuaikan nama file login
    exit;
}

$pesan_sukses = "";
$userId = $_SESSION['user_id'];

// =============================
//  AMBIL DATA PENDAKI DARI DB
//  BERDASARKAN user_id
// =============================
$stmt = $koneksi->prepare("
    SELECT id, nik, nama_lengkap, usia, no_hp, alamat
    FROM sania_pendaki
    WHERE user_id = ?
    LIMIT 1
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$dataPendaki = $result->fetch_assoc();
$stmt->close();

$id_pendaki   = $dataPendaki['id']           ?? 0;
$nama_pendaki = $dataPendaki['nama_lengkap'] ?? '';
$nik_pendaki  = $dataPendaki['nik']          ?? '';

// ====== TENTUKAN STEP ======
if (isset($_GET['step'])) {
    $step = $_GET['step'];
} else {
    $step = ($id_pendaki > 0) ? 'after' : 'pendaki';
}
if ($step == 'regis' && $id_pendaki == 0) {
    $step = 'belum_pendaki';
}

// =============================
//  PROSES INPUT PENDAKI
// =============================
if (isset($_POST['submit_pendaftaran'])) {

    if ($id_pendaki > 0) {
        header("Location: dashboard.php?p=formpendaki&step=after");
        exit;
    }

    $nik    = $_POST['nik'];
    $nama   = $_POST['nama_lengkap'];
    $usia   = $_POST['usia'];
    $no_hp  = $_POST['no_hp'];
    $alamat = $_POST['alamat'];

    if (strlen($nik) != 16) {
        echo "<script>
                alert('NIK harus 16 digit');
                window.history.back();
              </script>";
        exit;
    }

    $cek = $koneksi->prepare("SELECT id FROM sania_pendaki WHERE nik = ?");
    $cek->bind_param("s", $nik);
    $cek->execute();
    $cek->store_result();

    if ($cek->num_rows > 0) {
        echo "<script>
            alert('NIK sudah terdaftar!');
            window.location='dashboard.php?p=formpendaki&step=after';
          </script>";
        exit;
    }
    $cek->close();

    $stmt = $koneksi->prepare("
        INSERT INTO sania_pendaki (user_id, nik, nama_lengkap, usia, no_hp, alamat)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("ississ", $userId, $nik, $nama, $usia, $no_hp, $alamat);

    if ($stmt->execute()) {
        $id_pendaki   = $stmt->insert_id;
        $nama_pendaki = $nama;
        $nik_pendaki  = $nik;

        header("Location: dashboard.php?p=formpendaki&step=after");
        exit;
    } else {
        echo "<script>
                alert('Gagal menyimpan data! {$stmt->error}');
                window.history.back();
              </script>";
        exit;
    }
}
?>


<html>

<head>
    <title>Form Pendaftaran Pendaki</title>
    <style>
        body.halaman-pendaki .dash-main {
            background: url('img/img7.jpg') center/cover no-repeat fixed !important;
        }

        .talang-section {
            padding: 80px 60px 80px;
            background: transparent;
            justify-content: center;
            margin: 0 -40px 0 -30px;
        }

        .talang-card {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            background: rgba(128, 125, 125, 0.5);
            border-radius: 26px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.25);
            padding: 35px 30px;
        }

        .talang-card .form-title {
            font-size: 28px;
            font-weight: 800;
            color: rgba(255, 255, 255, 1);
            margin-bottom: 6px;
            text-align: left;
        }

        .talang-card .form-sub {
            font-size: 14px;
            color: white;
            margin-bottom: 15px;
        }

        label {
            font-weight: 500;
            font-size: 16px;
            color: #ffffffff;
            margin-top: 30px;
        }

        .text-muted {
            color: #1c9e3cff;
            margin-bottom: 10px;
        }

        .form-control {
            width: 100%;
            border-radius: 14px;
            border: 1px solid #d7eee1;
            padding: 12px 15px;
            background: #f8fff8;
            transition: 0.25s ease;
            font-size: 14px;
        }

        .form-control:focus {
            border-color: #1a8a54;
            box-shadow: 0 8px 18px rgba(19, 112, 70, 0.10);
            transform: translateY(-1px);
        }

        /* === BASE UNTUK SEMUA BUTTON === */
        .btn-primary-theme,
        .btn-cancel {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;

            padding: 11px 26px;
            border-radius: 999px;
            border: none;

            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.02em;
            text-decoration: none;
            cursor: pointer;

            transition: transform .18s ease, box-shadow .18s ease, filter .18s ease, background .18s ease;
            box-shadow: 0 10px 20px rgba(15, 23, 42, 0.28);
        }

        /* Tombol hijau utama */
        .btn-primary-theme {
            background: radial-gradient(circle at 0% 0%, #bbf7d0 0, #16a34a 45%, #15803d 100%);
            color: #ecfdf5;
        }

        /* Tombol abu-abu (secondary) */
        .btn-cancel {
            background: rgba(248, 250, 252, 0.92);
            color: #0f172a;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.18);
            border: 1px solid rgba(148, 163, 184, 0.6);
        }

        /* Hover efek mengambang */
        .btn-primary-theme:hover,
        .btn-cancel:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 26px rgba(15, 23, 42, 0.35);
            filter: brightness(1.03);
        }

        /* Klik (active) sedikit turun */
        .btn-primary-theme:active,
        .btn-cancel:active {
            transform: translateY(0);
            box-shadow: 0 6px 14px rgba(15, 23, 42, 0.25);
        }

        /* Fokus keyboard (aksesibilitas) */
        .btn-primary-theme:focus-visible,
        .btn-cancel:focus-visible {
            outline: 2px solid #22c55e;
            outline-offset: 3px;
        }

        /* Efek “glow” halus saat hover untuk tombol utama */
        .btn-primary-theme::before {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: inherit;
            background: radial-gradient(circle at 10% 0%, rgba(255, 255, 255, 0.45), transparent 55%);
            opacity: 0;
            transition: opacity .2s ease;
            pointer-events: none;
        }

        .btn-primary-theme:hover::before {
            opacity: 1;
        }

        .d-flex {
            display: flex;
            flex-wrap: wrap;
        }

        .gap-2 {
            gap: 10px;
        }


        @media (max-width: 768px) {
            .form-card {
                padding: 24px;
                margin: 30px 15px;
            }

            .btn-primary-theme,
            .btn-cancel {
                width: 100%;
                text-align: center;
            }
        }

        .alert-success {
            background: #dcfce7;
            border-radius: 12px;
            padding: 10px 16px;
            margin-bottom: 16px;
            color: #166534;
            font-size: 14px;
        }

        .dash-hero {
            margin-bottom: 0 !important;
        }

        .dash-main {
            padding-bottom: 0 !important;
        }

        .talang-section {
            margin-bottom: -32px;
        }

        .input-group-custom {
            margin-bottom: 12px !important;
        }
    </style>
</head>

<body class="halaman-pendaki">
    <div class="talang-section">
        <div class="talang-card">
            <h1 class="form-title">
                <?php if ($step == 'regis' && $id_pendaki > 0): ?>
                    Form Registrasi Pendakian
                <?php elseif ($step == 'after'): ?>
                    Pendaftaran Pendaki Berhasil
                <?php else: ?>
                    Form Pendaftaran Pendaki
                <?php endif; ?>
            </h1>

            <div class="form-sub">
                <?php if ($step == 'pendaki'): ?>
                    Isi data dengan lengkap untuk pendakian yang aman.
                <?php elseif ($step == 'regis'): ?>
                    Lengkapi registrasi pendakian. Setelah dikirim, tunggu admin menyetujui pendaftaranmu.
                <?php elseif ($step == 'after'): ?>
                    Kamu sudah terdaftar sebagai pendaki. Selanjutnya, buat registrasi pendakianmu.
                <?php endif; ?>
            </div>


            <?php if ($pesan_sukses): ?>
                <div class="alert-success"><?= $pesan_sukses ?></div>
            <?php endif; ?>

            <?php if ($step == 'pendaki'): ?>

                <!-- ============= FORM PENDAFTARAN PENDAKI ============== -->
                <form action="" method="POST">
                    <div class="mb-3">
                        <label>NIK</label>
                        <input type="text" name="nik" class="form-control" maxlength="16" required>
                        <small class="text-muted">Masukkan 16 digit NIK</small>
                    </div>

                    <div class="mb-3">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Umur</label>
                        <input type="number" name="usia" class="form-control" min="1" max="120" required>
                    </div>

                    <div class="mb-3">
                        <label>No HP</label>
                        <input type="text" name="no_hp" class="form-control" maxlength="15" required>
                        <small class="text-muted">Masukkan nomor HP tanpa spasi</small>
                    </div>

                    <div class="mb-3">
                        <label>Alamat</label>
                        <textarea name="alamat" class="form-control" rows="3" required></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" name="submit_pendaftaran" class="btn-primary-theme">Simpan</button>
                        <a href="index.php?page=data_pendaki" class="btn-cancel">Batal</a>
                    </div>
                </form>


            <?php elseif ($step == 'after' && $id_pendaki > 0): ?>

                <!-- ============= HALAMAN UCAPAN SETELAH DAFTAR ============== -->
                <h1 class="form-title">Pendaftaran Berhasil!</h1>
                <div class="form-sub">
                    Selamat, Anda telah terdaftar sebagai pendaki.<br>
                    Langkah selanjutnya, silakan lakukan registrasi pendakian.
                </div>

                <div class="alert-success">
                    Pendaki: <strong><?= htmlspecialchars($nama_pendaki) ?> (<?= htmlspecialchars($nik_pendaki) ?>)</strong>
                </div>

                <div style="display:flex; gap:10px;">
                    <a href="dashboard.php?p=formpendaki&step=regis"
                        class="btn-primary-theme" style="text-decoration:none; padding:12px 20px;">
                        Lakukan Registrasi Sekarang
                    </a>

                    <a href="dashboard.php?p=dashboard"
                        class="btn-cancel" style="text-decoration:none; padding:12px 20px;">
                        Nanti Saja
                    </a>
                </div>

            <?php elseif ($step == 'regis' && $id_pendaki > 0): ?>

                <form action="admin/proses.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="aksi" value="insert_registrasi_user">
                    <input type="hidden" name="id_pendaki" value="<?= $id_pendaki ?>">

                    <div class="mb-3 input-group-custom">
                        <label class="form-label">Pendaki</label>
                        <input type="text"
                            class="form-control"
                            value="<?= htmlspecialchars($nama_pendaki) ?> (<?= htmlspecialchars($nik_pendaki) ?>)"
                            readonly>
                    </div>

                    <div class="mb-3 input-group-custom">
                        <label class="form-label">Tanggal Naik</label>
                        <input type="datetime-local" name="tgl_naik" class="form-control" required>
                    </div>

                    <div class="mb-3 input-group-custom">
                        <label class="form-label">Tanggal Turun</label>
                        <input type="datetime-local" name="tgl_turun" class="form-control" required>
                    </div>

                    <div class="mb-3 input-group-custom">
                        <label class="form-label">Jumlah Anggota</label>
                        <input type="number" name="jumlah_anggota" class="form-control" min="1" required>
                    </div>

                    <!-- FOTO TIM WAJIB -->
                    <div class="mb-3 input-group-custom">
                        <label class="form-label">Foto Anggota Tim (WAJIB)</label>
                        <input type="file" name="foto_tim" class="form-control" accept="image/*" required>
                        <small class="text-muted" style="color:#fff;">Foto harus diambil di area Basecamp sebelum pendakian.</small>
                    </div>

                    <div class="d-flex gap-2" style="margin-top:20px;">
                        <button type="submit" class="btn-primary-theme">Kirim Registrasi</button>
                        <a href="dashboard?p=dashboard" class="btn-cancel">Batal</a>
                    </div>
                </form>

            <?php elseif ($step == 'belum_pendaki'): ?>

                <div class="alert-success" style="background:#fee2e2; color:#b91c1c;">
                    Anda belum melakukan pendaftaran pendaki.<br>
                    Silakan isi data pendaki terlebih dahulu.
                </div>

                <a href="dashboard.php?p=formpendaki&step=pendaki"
                    class="btn-primary-theme" style="text-decoration:none;">
                    Isi Data Pendaki Sekarang
                </a>

                <div class="alert-success">
                    Data pendaki tidak ditemukan. Silakan isi form pendaftaran dulu.
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>