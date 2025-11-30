<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php';
$query = mysqli_query($koneksi, "SELECT * FROM alat ORDER BY nama ASC");
?>


<!-- CSS-nya tetap, cukup letakkan setelah layout utama kalau perlu -->
<style>
    body {
        font-family: 'Poppins', sans-serif;
        background: #f4f6f9;
        margin: 0;
        padding: 0;
    }

    .container {
        margin: 0px 0px 4px;
        margin-top: 130px;
    }

    .grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(230px, 1fr));
        gap: 12px;
        /* biar semua item 1 baris tingginya sama */
        align-items: stretch;
    }

    .card {
        background: #fff;
        border-radius: 18px;
        padding: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        transition: .2s;

        /* PENTING: jadi flex column */
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    /* bungkus teks (nama, harga, stok, ket) */
    .card-body {
        display: flex;
        flex-direction: column;
        flex: 1;
        padding-bottom: 10px;
        /* isi card yang fleksibel */
    }

    .ket {
        font-size: 12px;
        color: #555;
        line-height: 1.4;
        max-height: 60px;
        overflow: hidden;

    }

    .btn-sewa {
        display: block;
        padding: 10px;
        background: linear-gradient(70deg, #a8ef84a7, #40d4e4a7);
        color: #1b63d6ff;
        text-align: center;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 500;
        transition: .2s;

        /* INI YANG NGEBUAT TOMBOL DI PALING BAWAH */
        margin-top: 30px;
    }

    .card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
    }

    .img-box {
        width: 100%;
        height: 175px;
        display: flex;
        justify-content: center;
        align-items: center;
        overflow: hidden;
        background: white;
        border-radius: 14px;
    }

    .img-box img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .nama {
        font-size: 16px;
        font-weight: 700;
        margin: 6px 0 6px;
        color: #333;
        text-align: center;
    }

    .harga {
        font-size: 14px;
        font-weight: 400;
        color: #3DB6A1;
        margin-bottom: 3px;
    }

    .stok {
        font-size: 12px;
        color: #000000ff;
        margin-bottom: 8px;
    }

    .btn-sewa {
        display: block;
        padding: 10px;
        background: linear-gradient(70deg, #a8ef84a7, #40d4e4a7);
        color: #1b63d6ff;
        text-align: center;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 500;
        transition: .2s;

        /* INI KUNCI SUPAYA SELALU NANGKRING DI PALING BAWAH */
        margin-top: auto;
    }

    .btn-sewa:hover {
        background: #35a18f;
        color: #fff;
    }

    @media (max-width: 1024px) {
        .grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 768px) {
        .grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 500px) {
        .grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container">
    <div class="grid">
        <?php while ($row = mysqli_fetch_assoc($query)) { ?>

            <div class="card">
                <?php
                $gambar = $row['gambar'] ?? '';

                if ($gambar) {
                    // Deteksi URL atau file lokal
                    if (filter_var($gambar, FILTER_VALIDATE_URL)) {
                        $src = $gambar;
                    } else {
                        $src = "uploads/" . $gambar;
                    }
                } else {
                    // placeholder kalau tidak ada gambar
                    $src = "https://via.placeholder.com/400x260?text=No+Image";
                }
                ?>

                <!-- Gambar -->
                <div class="img-box">
                    <img src="<?= htmlspecialchars($src); ?>" alt="Gambar Alat">
                </div>

                <div class="card-body">
                    <!-- Nama alat -->
                    <div class="nama"><?= htmlspecialchars($row['nama']); ?></div>

                    <!-- Harga -->
                    <div class="harga">
                        Rp <?= number_format($row['harga_per_hari'], 0, ',', '.'); ?> / hari
                    </div>

                    <!-- Stok -->
                    <div class="stok">Stok: <?= (int) $row['stok']; ?></div>

                    <!-- Keterangan -->
                    <div class="ket"><?= nl2br(htmlspecialchars($row['keterangan'])); ?></div>

                </div>
                <?php
                $url  = "login.php";
                $text = "Sewa";
                ?>

                <a href="<?= htmlspecialchars($url); ?>" class="btn-sewa">
                    <?= htmlspecialchars($text); ?>
                </a>
            </div>
        <?php } ?>
    </div>
</div>