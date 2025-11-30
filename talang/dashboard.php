<?php
session_start();

$p = $_GET['p'] ?? 'dashboard';
$step = $_GET['step'] ?? '';

include 'koneksi.php';

// --------------------------
// CEK LOGIN
// --------------------------
if (empty($_SESSION['user_role'])) {
    header('Location: login.php');
    exit;
}

// AMBIL DATA SESSION
$role       = $_SESSION['user_role'];                        // 'admin' atau 'user'
$userId     = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$namaLogin  = $_SESSION['nama_lengkap'] ?? 'Pendaki';


// Validasi koneksi
if (!isset($koneksi) || !($koneksi instanceof mysqli)) {
    die('<strong>Error:</strong> Koneksi database ($koneksi) tidak ditemukan. Periksa koneksi.php');
}

// --- Inisialisasi statistik ---
$totalPendaki     = 0;
$totalRegistrasi  = 0;
$totalAktif       = 0;
$totalSelesai     = 0;
$recent           = [];

// --------------------------
// MODE ADMIN  (statistik global)
// --------------------------
if ($role === 'admin') {

    // total pendaki
    $q1 = $koneksi->query("SELECT COUNT(*) AS jml FROM sania_pendaki");
    if ($q1 && $row = $q1->fetch_assoc()) $totalPendaki = (int)$row['jml'];

    // total registrasi
    $q2 = $koneksi->query("SELECT COUNT(*) AS jml FROM sania_registrasi");
    if ($q2 && $row = $q2->fetch_assoc()) $totalRegistrasi = (int)$row['jml'];

    // status Disetujui = sedang pendakian / aktif
    $q3 = $koneksi->query("SELECT COUNT(*) AS jml FROM sania_registrasi WHERE status = 'Disetujui'");
    if ($q3 && $row = $q3->fetch_assoc()) $totalAktif = (int)$row['jml'];

    // status Selesai
    $q4 = $koneksi->query("SELECT COUNT(*) AS jml FROM sania_registrasi WHERE status = 'Selesai'");
    if ($q4 && $row = $q4->fetch_assoc()) $totalSelesai = (int)$row['jml'];

    // daftar 5 registrasi terakhir (semua pendaki)
    $sqlRecent = "
        SELECT r.id_regis, p.nama_lengkap, r.tgl_turun AS tgl_daftar, r.status
        FROM sania_registrasi r
        JOIN sania_pendaki p ON r.id_pendaki = p.id
        ORDER BY r.id_regis DESC
        LIMIT 5
    ";

    $q5 = $koneksi->query($sqlRecent);
    if ($q5) {
        while ($row = $q5->fetch_assoc()) {
            $recent[] = $row;
        }
    }
} else {
    // --------------------------
    // MODE USER / PENDAKI (statistik per user)
    // --------------------------

    // Bangun base query untuk user ini: join registrasi dengan pendaki
    $sqlBase = "
        FROM sania_registrasi r
        JOIN sania_pendaki p ON r.id_pendaki = p.id
        WHERE p.user_id = $userId
    ";

    // total semua registrasi milik user ini
    $q1 = $koneksi->query("SELECT COUNT(*) AS jml " . $sqlBase);
    if ($q1 && $row = $q1->fetch_assoc()) $totalRegistrasi = (int)$row['jml'];

    // pendakian aktif (Disetujui) milik user ini
    $q2 = $koneksi->query("SELECT COUNT(*) AS jml " . $sqlBase . " AND r.status = 'Disetujui'");
    if ($q2 && $row = $q2->fetch_assoc()) $totalAktif = (int)$row['jml'];

    // pendakian selesai milik user ini
    $q3 = $koneksi->query("SELECT COUNT(*) AS jml " . $sqlBase . " AND r.status = 'Selesai'");
    if ($q3 && $row = $q3->fetch_assoc()) $totalSelesai = (int)$row['jml'];

    // riwayat 5 registrasi terakhir user ini
    $sqlRecent = "
        SELECT r.id_regis, r.tgl_turun AS tgl_daftar, r.status, p.nama_lengkap
        " . $sqlBase . "
        ORDER BY r.id_regis DESC
        LIMIT 5
    ";
    $q5 = $koneksi->query($sqlRecent);
    if ($q5) {
        while ($row = $q5->fetch_assoc()) {
            $recent[] = $row;
        }
    }
}

?>


<head>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="dash.css">
</head>

<div class="dash-page">
    <div class="dash-layout">

        <!-- SIDEBAR KIRI -->
        <aside class="dash-sidebar">
            <div class="dash-sidebar-header">
                <div class="dash-sidebar-title">
                    Mt. Talang <?= ($role === 'admin') ? 'Admin' : 'Pendaki' ?>
                </div>
                <div class="dash-sidebar-sub">
                    <?= ($role === 'admin') ? 'Panel pendakian (Admin)' : 'Dashboard pendaki' ?>
                </div>
            </div>

            <nav class="dash-sidebar-menu">

                <?php if ($role === 'admin'): ?>

                    <a href="dashboard.php?p=dashboard"
                        class="dash-sidebar-link <?= ($p == 'dashboard' ? 'dash-active' : '') ?>">
                        <span class="icon">📈</span>
                        <span>Dashboard</span>
                    </a>

                    <a href="dashboard.php?p=pendaki"
                        class="dash-sidebar-link <?= ($p == 'pendaki' ? 'dash-active' : '') ?>">
                        <span class="icon">🧍‍♂️</span>
                        <span>Data Pendaki</span>
                    </a>

                    <a href="dashboard.php?p=regis"
                        class="dash-sidebar-link <?= ($p == 'regis' ? 'dash-active' : '') ?>">
                        <span class="icon">📄</span>
                        <span>Data Registrasi</span>
                    </a>

                    <a href="dashboard.php?p=alat"
                        class="dash-sidebar-link <?= ($p == 'alat' ? 'dash-active' : '') ?>">
                        <span class="icon">🎒</span>
                        <span>Peralatan Camp</span>
                    </a>

                    <a href="dashboard.php?p=penyewa"
                        class="dash-sidebar-link <?= ($p == 'sewa' ? 'dash-active' : '') ?>">
                        <span class="icon">🛒</span>
                        <span>Kelola Sewa</span>
                    </a>

                    <a href="dashboard.php?p=pembayaran"
                        class="dash-sidebar-link <?= ($p == 'pembayaran' ? 'dash-active' : '') ?>">
                        <span class="icon">💳</span>
                        <span>Payment</span>
                    </a>
                <?php else: ?>
                    <!-- MENU UNTUK USER / PENDAKI -->
                    <a href="dashboard.php?p=dashboard"
                        class="dash-sidebar-link <?= ($p == 'dashboard' ? 'dash-active' : '') ?>">
                        <span class="icon">📈</span>
                        <span>Dashboard</span>
                    </a>

                    <a href="dashboard.php?p=formpendaki"
                        class="dash-sidebar-link <?= ($p == 'formpendaki' && $step != 'regis' ? 'dash-active' : '') ?>">
                        <span class="icon">🥾</span>
                        <span>Daftar Pendakian</span>
                    </a>

                    <a href="dashboard.php?p=formpendaki&step=regis"
                        class="dash-sidebar-link <?= ($p == 'formpendaki' && $step == 'regis' ? 'dash-active' : '') ?>">
                        <span class="icon">📄</span>
                        <span>Daftar Registrasi</span>
                    </a>
                    <a href="dashboard.php?p=camp"
                        class="dash-sidebar-link <?= ($p == 'camp' ? 'dash-active' : '') ?>">
                        <span class="icon">🎒</span>
                        <span>Peralatan Camp</span>
                    </a>

                    <a href="dashboard.php?p=bayar"
                        class="dash-sidebar-link <?= ($p == 'bayar' ? 'dash-active' : '') ?>">
                        <span class="icon">💳</span>
                        <span>Payment</span>
                    </a>

                    <a href="dashboard.php?p=riwayat"
                        class="dash-sidebar-link <?= ($p == 'riwayat' ? 'dash-active' : '') ?>">
                        <span class="icon">📜</span>
                        <span>Riwayat Pendakian</span>
                    </a>
                <?php endif; ?>

            </nav>

            <div class="dash-sidebar-footer">
                <a href="logout.php" class="dash-sidebar-link dash-logout">
                    <span class="icon">🚪</span>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <main class="dash-main">
            <!-- HERO DASHBOARD -->
            <section class="dash-hero">
                <div class="dash-hero-main">
                    <?php if ($role === 'admin'): ?>
                        <h1 class="dash-hero-title">Dashboard Pendakian Gunung Talang</h1>
                        <p class="dash-hero-sub">
                            Ringkasan cepat aktivitas pendaki, status registrasi, dan perjalanan yang sedang berjalan.
                        </p>
                    <?php else: ?>
                        <h1 class="dash-hero-title">Halo, <?= htmlspecialchars($namaLogin) ?></h1>
                        <p class="dash-hero-sub">
                            Ini ringkasan aktivitas pendakianmu di Gunung Talang.
                        </p>
                    <?php endif; ?>
                    <div class="dash-hero-badge">
                        ⛰️ Mt. Talang • 2.597 mdpl
                        <span style="width:1px;height:14px;background:#16a34a;opacity:.4;margin-inline:8px;"></span>
                        Jalur Bukik Bulek Kampung Batu
                    </div>
                </div>
                <div>
                    <div style="font-size:12px;color:#0f172a;margin-bottom:4px;">
                        <?= ($role === 'admin') ? 'Total registrasi masuk' : 'Total registrasi kamu' ?>
                    </div>
                    <div style="font-size:24px;font-weight:800;color:#15803d;">
                        <?= number_format($totalRegistrasi) ?>
                    </div>
                    <div style="font-size:11px;color:#6b7280;">
                        <?= ($role === 'admin')
                            ? 'Akumulasi seluruh data'
                            : 'Semua pendaftaran pendakian yang kamu buat' ?>
                    </div>
                </div>
            </section>
            <?php
            $page = $_GET['p'] ?? 'dashboard';

            switch ($page) {
                case 'dashboard':
                    // boleh pakai 1 file yang sama, di dalam file cek lagi rolenya
                    include 'dashome.php';
                    break;

                // ================= ADMIN ONLY =================
                case 'regis':
                    if ($role !== 'admin') {
                        echo "403 Forbidden";
                        break;
                    }
                    include 'admin/regis.php';
                    break;

                case 'pendaki':
                    if ($role !== 'admin') {
                        echo "403 Forbidden";
                        break;
                    }
                    include 'admin/pendaki.php';
                    break;
                case 'penyewa':
                    if ($role !== 'admin') {
                        echo "403 Forbidden";
                        break;
                    }
                    include 'admin/sewa.php';
                    break;
                case 'alat':
                    if ($role !== 'admin') {
                        echo "403 Forbidden";
                        break;
                    }
                    include 'admin/alat.php';
                    break;

                case 'pembayaran':
                    if ($role !== 'admin') {
                        echo "403 Forbidden";
                        break;
                    }
                    include 'admin/pembayaran.php';
                    break;
                case 'cetak':
                    if ($role !== 'admin') {
                        echo "403 Forbidden";
                        break;
                    }
                    include 'admin/tiket.php';
                    break;

                // ================= USER PAGES =================
                case 'riwayat':
                    // riwayat pendakian (semua registrasi user ini)
                    include 'public/riwayat.php';
                    break;

                case 'formpendaki':
                    // detail/data pendakian milik user sendiri
                    include 'public/formpendaki.php';
                    break;

                case 'sewa':
                    // layout/form sewa peralatan camp
                    include 'public/formsewa.php'; // atau file lain sesuai strukturmu
                    break;
                case 'camp':
                    include 'public/alat.php';
                    break;
                case 'bayar':
                    include 'public/bayar.php';
                    break;

                default:
                    echo "404 Halaman tidak ditemukan";
            }

            ?>
            <?php
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            if (!empty($_SESSION['flash_success'])): ?>
                <div class="alert-success" style="
        background:#dcfce7;
        border-radius:12px;
        padding:10px 16px;
        margin:10px 40px;
        color:#166534;
        font-size:14px;
        border:1px solid #bbf7d0;
    ">
                    <?= htmlspecialchars($_SESSION['flash_success']); ?>
                </div>
            <?php
                unset($_SESSION['flash_success']);
            endif;

            if (!empty($_SESSION['flash_error'])): ?>
                <div class="alert-error" style="
        background:#fee2e2;
        border-radius:12px;
        padding:10px 16px;
        margin:10px 40px;
        color:#b91c1c;
        font-size:14px;
        border:1px solid #fecaca;
    ">
                    <?= htmlspecialchars($_SESSION['flash_error']); ?>
                </div>
            <?php
                unset($_SESSION['flash_error']);
            endif;
            ?>


        </main>

    </div>
</div>