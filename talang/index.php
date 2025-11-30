<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Registrasi Pendakian G.Talang</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <!-- WRAPPER UTAMA -->
    <div class="page-root">

        <!-- NAVBAR -->
        <header class="site-header">
            <nav class="nav">
                <!-- BRAND / LOGO -->
                <div class="brand">
                    <div>
                        <div class="brand-mark">
                            🏔️ Mt.Talang<span class="brand-dot"></span>
                        </div>
                        <div class="brand-sub">Trekking & Camping</div>
                    </div>
                </div>

                <!-- LINK NAVIGASI -->
                <div class="nav-links">
                    <a href="index.php?p=home">🏡 Home</a>
                    <a href="index.php?p=info">📚 Informasi</a>
                    <a href="index.php?p=pendaki" class="text-success">🧑‍🤝‍🧑 Pendaki</a>
                    <a href="index.php?p=alat">🎒 Peralatan Camp</a>
                    <!-- scroll ke footer kontak -->
                    <a href="#kontak">☎️ Kontak</a>
                </div>

                <!-- TOMBOL LOGIN -->
                <a href="login.php" class="btn-nav" style="text-decoration: none;">Login</a>
            </nav>
        </header>

        <!-- MAIN CONTENT -->
        <main>
            <?php
            $page = $_GET['p'] ?? 'home';

            switch ($page) {
                case 'home':
                    include 'home.php';
                    break;

                case 'info':
                    include 'info.php';
                    break;

                case 'pendaki':
                    include 'datapendaki.php';
                    break;

                case 'alat':
                    include 'p_alat.php';
                    break;

                case 'sewa':
                    include 'public/formsewa.php';
                    break;

                default:
                    echo '<div style="padding:40px;text-align:center;">404 &mdash; Halaman tidak ditemukan.</div>';
            }
            ?>
        </main>

        <!-- FOOTER -->
        <footer class="site-footer">
            <div class="footer-new">
                <div class="footer-wrap">

                    <!-- NAVIGASI KIRI -->
                    <div class="footer-nav">
                        <h4>Navigasi</h4>
                        <div class="nav-grid">
                            <a href="index.php?p=home">🏡 Home</a>
                            <a href="index.php?p=info#map">🧭 Map Jalur</a>
                            <a href="index.php?p=info#kontak">☎️ Kontak</a>
                        </div>
                    </div>

                    <!-- BRAND TENGAH -->
                    <div class="footer-brand">
                        <h3>🏔️ Mt.Talang Trekking & Camping</h3>
                        <p>Pengalaman hiking yang tertib, aman, dan tetap santai bareng alam Talang.</p>
                    </div>

                    <!-- KONTAK KANAN -->
                    <div class="footer-contact" id="kontak">
                        <h4>Kontak Basecamp</h4>
                        <p>📍 Basecamp Bukik Bulek, Nagari Kampung Batu Dalam, Danau Kembar, Kab. Solok.</p>
                        <p>📱 WhatsApp Admin: +62 812-3456-7890</p>
                        <p>✉️ Email: admin@talang.id</p>
                        <p>🕒 Jam Layanan: 07.00 – 22.00 WIB</p>
                    </div>

                </div>
            </div>

            <div class="footer-copy">
                © 2025 Mt.Talang Trekking & Camping. All rights reserved.
            </div>
        </footer>

    </div>
</body>

</html>