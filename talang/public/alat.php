<?php
// --------- SETUP & LOGIN CHECK ----------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . '/../koneksi.php';

// Wajib login user (pendaki)
if (empty($_SESSION['user_role']) || empty($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$userId = (int)$_SESSION['user_id'];

// Inisialisasi keranjang di session
if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}
$keranjang = &$_SESSION['keranjang'];

// ------------ UPDATE QTY (dari input di modal) ------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['qty']) && is_array($_POST['qty'])) {
    foreach ($_POST['qty'] as $id => $q) {
        $id = (int)$id;
        $q  = (int)$q;

        if ($q <= 0) {
            unset($keranjang[$id]);
        } else {
            $keranjang[$id] = $q;
        }
    }
}

// total item di keranjang (untuk badge di icon)
$totalItemKeranjang = array_sum($keranjang);

// --------- HANDLE AKSI (tambah / hapus / kosongkan) ----------
if (isset($_GET['aksi'])) {
    $aksi = $_GET['aksi'];

    if ($aksi === 'tambah') {
        $alat_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($alat_id > 0) {
            if (isset($keranjang[$alat_id])) {
                $keranjang[$alat_id]++;
            } else {
                $keranjang[$alat_id] = 1;
            }
        }

        // kembali ke dashboard camp
        header("Location: dashboard.php?p=camp");
        exit;
    }

    if ($aksi === 'hapus') {
        $alat_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($alat_id > 0 && isset($keranjang[$alat_id])) {
            unset($keranjang[$alat_id]);
        }

        header("Location: dashboard.php?p=camp");
        exit;
    }

    if ($aksi === 'kosongkan') {
        $keranjang = [];
        header("Location: dashboard.php?p=camp");
        exit;
    }
}

// --------- PROSES SEWA (POST) ----------
$pesan_sukses = '';
$pesan_error  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proses_sewa'])) {

    if (empty($keranjang)) {
        $pesan_error = "Keranjang masih kosong.";
    } else {
        $id_regis = isset($_POST['id_regis']) ? (int)$_POST['id_regis'] : 0;

        if ($id_regis <= 0) {
            $pesan_error = "Silakan pilih data registrasi pendakian.";
        } else {
            // Ambil lama sewa (hari)
            $lama_hari = isset($_POST['lama_hari']) ? (int)$_POST['lama_hari'] : 1;
            if ($lama_hari < 1) $lama_hari = 1;

            // Ambil data alat berdasarkan isi keranjang
            $ids = array_keys($keranjang);

            if (!empty($ids)) {
                $idStr = implode(',', array_map('intval', $ids));

                $qAlat = mysqli_query($koneksi, "SELECT * FROM alat WHERE alat_id IN ($idStr)");

                $detail = [];

                while ($row = mysqli_fetch_assoc($qAlat)) {
                    $alat_id = $row['alat_id'];
                    $qty     = $keranjang[$alat_id];
                    $harga   = $row['harga_per_hari'];
                    $sub     = $qty * $harga;              // total per hari utk alat ini
                    $total   = $sub * $lama_hari;          // total untuk N hari

                    $detail[] = [
                        'alat_id'    => $alat_id,
                        'qty'        => $qty,
                        'harga'      => $harga,
                        'sub_harian' => $sub,
                        'total'      => $total,
                    ];
                }
            } else {
                $detail = [];
            }

            if (empty($detail)) {
                $pesan_error = "Terjadi kesalahan saat membaca data alat.";
            } else {
                // ==== SIMPAN KE TABEL sewa_alat (supaya nyambung dengan bayar.php) ====
                //
                // Asumsi struktur:
                // sewa_alat(
                //   sewa_id INT AI PK,
                //   registrasi_id INT,
                //   alat_id INT,
                //   jumlah INT,
                //   hari INT,
                //   harga_total INT,
                //   status_pembayaran ENUM('belum_bayar','menunggu_verifikasi','lunas',...),
                //   bukti_pembayaran VARCHAR ... (boleh NULL)
                // )

                mysqli_begin_transaction($koneksi);

                try {
                    $statusAwal = 'belum_bayar';

                    $stmt = mysqli_prepare(
                        $koneksi,
                        "INSERT INTO sewa_alat 
                         (registrasi_id, alat_id, jumlah, hari, harga_total, status_pembayaran)
                         VALUES (?, ?, ?, ?, ?, ?)"
                    );

                    foreach ($detail as $d) {
                        mysqli_stmt_bind_param(
                            $stmt,
                            "iiiiis",
                            $id_regis,
                            $d['alat_id'],
                            $d['qty'],
                            $lama_hari,
                            $d['total'],
                            $statusAwal
                        );
                        mysqli_stmt_execute($stmt);
                    }

                    mysqli_commit($koneksi);

                    // kosongkan keranjang
                    $keranjang = [];

                    // LANGSUNG ARAHKAN KE HALAMAN PEMBAYARAN
                    header("Location: dashboard.php?p=bayar&view=form&regis_id=" . $id_regis);
                    exit;
                } catch (Exception $e) {
                    mysqli_rollback($koneksi);
                    $pesan_error = "Gagal menyimpan data sewa.";
                }
            }
        }
    }
}

// --------- DATA UNTUK LIST ALAT & KERANJANG ----------

// Daftar semua alat (untuk grid kartu)
$qListAlat = mysqli_query($koneksi, "SELECT * FROM alat ORDER BY nama ASC");

// Detail alat untuk isi keranjang
$alatData = [];
$total    = 0;

if (!empty($keranjang)) {
    $ids = array_keys($keranjang);

    if (!empty($ids)) {
        $idStr = implode(',', array_map('intval', $ids));

        $q = mysqli_query($koneksi, "SELECT * FROM alat WHERE alat_id IN ($idStr)");
        while ($row = mysqli_fetch_assoc($q)) {
            $alatData[$row['alat_id']] = $row;
        }
    }
}

// Data registrasi pendakian milik user ini
$qReg = mysqli_query($koneksi, "
    SELECT r.id_regis, r.tgl_naik, r.tgl_turun, r.status
    FROM sania_registrasi r
    JOIN sania_pendaki p ON r.id_pendaki = p.id
    WHERE p.user_id = $userId
    ORDER BY r.id_regis DESC
");
?>

<style>
    /* ===== GRID KARTU ALAT ===== */
    .alat-section-title {
        font-size: 22px;
        font-weight: 700;
    }

    .alat-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        /* top margin dibesarkan supaya nggak nempel ke header hijau */
        margin: 20px 0 14px;
        /* angka 20 boleh kamu ubah 16–24 sesuai selera */
    }


    .alat-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(220px, 1fr));
        gap: 14px;
        margin-bottom: 30px;
    }

    .alat-card {
        background: #ffffff;
        border-radius: 18px;
        padding: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
        transition: .2s;
    }

    .alat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 22px rgba(0, 0, 0, 0.10);
    }

    .alat-img-box {
        width: 100%;
        height: 160px;
        border-radius: 14px;
        background: #fff;
        display: flex;
        justify-content: center;
        align-items: center;
        overflow: hidden;
        margin-bottom: 8px;
    }

    .alat-img-box img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .alat-nama {
        font-size: 16px;
        font-weight: 700;
        margin: 4px 0;
    }

    .alat-harga {
        font-size: 14px;
        color: #22c55e;
        margin-bottom: 2px;
    }

    .alat-stok {
        font-size: 12px;
        color: #374151;
        margin-bottom: 6px;
    }

    .alat-ket {
        font-size: 12px;
        color: #6b7280;
        height: 40px;
        overflow: hidden;
    }

    .btn-sewa {
        display: block;
        margin-top: 10px;
        padding: 8px 10px;
        border-radius: 999px;
        text-align: center;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        background: linear-gradient(70deg, #a8ef84a7, #40d4e4a7);
        color: #14532d;
    }

    .btn-sewa:hover {
        filter: brightness(1.05);
    }

    /* ===== CART BUTTON HEADER ===== */
    .cart-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 999px;
        border: none;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
        background: #f1f5f9;
        color: #0f172a;
    }

    .cart-btn span.badge {
        min-width: 18px;
        height: 18px;
        border-radius: 999px;
        background: #ef4444;
        color: #fff;
        font-size: 11px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 5px;
    }

    /* ===== TABEL KERANJANG & BUTTON UMUM ===== */
    .btn {
        display: inline-flex;
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 14px;
        text-decoration: none;
        border: none;
        cursor: pointer;
        align-items: center;
        justify-content: center;
    }

    .btn-secondary {
        background: #3b82f6;
        color: #fff;
    }

    .btn-danger {
        background: #ef4444;
        color: #fff;
    }

    .btn-primary {
        background: #22c55e;
        color: #fff;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        margin-top: 8px;
    }

    th,
    td {
        padding: 8px 10px;
        border: 1px solid #e5e7eb;
        font-size: 14px;
    }

    th {
        background: #f3f4f6;
    }

    .alert-success {
        background: #dcfce7;
        color: #166534;
        padding: 10px 12px;
        border-radius: 10px;
        margin: 10px 0;
        font-size: 14px;
        border: 1px solid #bbf7d0;
    }

    .alert-error {
        background: #fee2e2;
        color: #b91c1c;
        padding: 10px 12px;
        border-radius: 10px;
        margin: 10px 0;
        font-size: 14px;
        border: 1px solid #fecaca;
    }

    /* ===== MODAL KERANJANG ===== */
    .cart-modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
        display: none;
        /* di-toggle via JS */
        align-items: center;
        justify-content: center;
        z-index: 999;
    }

    .cart-modal {
        background: #ffffff;
        border-radius: 18px;
        max-width: 900px;
        width: 95%;
        max-height: 80vh;
        overflow-y: auto;
        padding: 20px 24px 24px;
        box-shadow: 0 20px 40px rgba(15, 23, 42, 0.35);
    }

    .cart-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .cart-modal-title {
        font-size: 20px;
        font-weight: 700;
    }

    .cart-modal-close {
        border: none;
        background: transparent;
        font-size: 20px;
        cursor: pointer;
        line-height: 1;
    }

    .cart-modal-body {
        margin-top: 4px;
    }

    /* ===== CART BUTTON HEADER ===== */
    .cart-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 999px;
        border: none;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        background: #f1f5f9;
        color: #0f172a;
    }

    .cart-icon {
        font-size: 35px;
        /* BESAR ICON */
        line-height: 1;
    }

    .cart-text {
        font-size: 15px;
        font-weight: 600;
    }

    .cart-btn span.badge {
        min-width: 18px;
        height: 18px;
        border-radius: 999px;
        background: #ef4444;
        color: #fff;
        font-size: 11px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 5px;
        margin-left: 2px;
        /* biar badge nggak terlalu nempel */
    }


    @media (max-width:1024px) {
        .alat-grid {
            grid-template-columns: repeat(2, minmax(220px, 1fr));
        }
    }

    @media (max-width:768px) {
        .alat-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- ============ BAGIAN 1: DAFTAR ALAT ============ -->
<section>
    <div class="alat-header">
        <div class="alat-section-title">Daftar Peralatan Camp</div>

        <button type="button" class="cart-btn" onclick="toggleCart()">
            <span class="cart-icon">🛒</span>
            <span class="cart-text">Keranjang</span>
            <?php if ($totalItemKeranjang > 0): ?>
                <span class="badge"><?= (int)$totalItemKeranjang; ?></span>
            <?php endif; ?>
        </button>
    </div>


    <div class="alat-grid">
        <?php while ($row = mysqli_fetch_assoc($qListAlat)): ?>
            <?php
            $gambar = $row['gambar'] ?? '';
            if ($gambar) {
                if (filter_var($gambar, FILTER_VALIDATE_URL)) {
                    $src = $gambar;
                } else {
                    $src = "uploads/" . $gambar;
                }
            } else {
                $src = "https://via.placeholder.com/400x260?text=No+Image";
            }
            ?>
            <div class="alat-card">
                <div class="alat-img-box">
                    <img src="<?= htmlspecialchars($src); ?>" alt="Gambar Alat">
                </div>
                <div class="alat-nama"><?= htmlspecialchars($row['nama']); ?></div>
                <div class="alat-harga">Rp <?= number_format($row['harga_per_hari'], 0, ',', '.'); ?> / hari</div>
                <div class="alat-stok">Stok: <?= (int)$row['stok']; ?></div>
                <div class="alat-ket"><?= nl2br(htmlspecialchars($row['keterangan'])); ?></div>

                <a href="dashboard.php?p=camp&aksi=tambah&id=<?= (int)$row['alat_id']; ?>" class="btn-sewa">
                    Tambah ke Keranjang
                </a>
            </div>
        <?php endwhile; ?>
    </div>
</section>

<!-- ============ MODAL KERANJANG SEWA ============ -->
<div id="cart-backdrop" class="cart-modal-backdrop" onclick="backdropClick(event)">
    <div class="cart-modal">
        <div class="cart-modal-header">
            <div class="cart-modal-title">Keranjang Sewa</div>
            <button type="button" class="cart-modal-close" onclick="toggleCart()">
                ✕
            </button>
        </div>

        <div class="cart-modal-body">
            <form action="dashboard.php?p=camp" method="post">
                <a href="dashboard.php?p=camp" class="btn btn-secondary">← Kembali ke Daftar Alat (Landing)</a>

                <?php if ($pesan_sukses): ?>
                    <div class="alert-success"><?= htmlspecialchars($pesan_sukses); ?></div>
                <?php endif; ?>

                <?php if ($pesan_error): ?>
                    <div class="alert-error"><?= htmlspecialchars($pesan_error); ?></div>
                <?php endif; ?>

                <?php if (empty($keranjang)): ?>

                    <p>Keranjang masih kosong.</p>

                <?php else: ?>

                    <table>
                        <tr>
                            <th>Alat</th>
                            <th>Qty</th>
                            <th>Harga / hari</th>
                            <th>Subtotal / hari</th>
                            <th>Aksi</th>
                        </tr>

                        <?php foreach ($keranjang as $alat_id => $qty): ?>
                            <?php
                            if (!isset($alatData[$alat_id])) continue;
                            $alat  = $alatData[$alat_id];
                            $harga = $alat['harga_per_hari'];
                            $sub   = $harga * $qty;
                            $total += $sub;
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($alat['nama']); ?></td>
                                <td>
                                    <!-- INPUT QTY YANG BISA DIUBAH -->
                                    <input type="number"
                                        name="qty[<?= (int)$alat_id; ?>]"
                                        value="<?= (int)$qty; ?>"
                                        min="1"
                                        style="width:60px;text-align:center;">
                                </td>
                                <td>Rp <?= number_format($harga, 0, ',', '.'); ?></td>
                                <td>Rp <?= number_format($sub, 0, ',', '.'); ?></td>
                                <td>
                                    <a class="btn btn-danger"
                                        href="dashboard.php?p=camp&aksi=hapus&id=<?= (int)$alat_id; ?>"
                                        onclick="return confirm('Hapus alat ini dari keranjang?')">
                                        Hapus
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <tr>
                            <td colspan="3"><strong>Total / hari</strong></td>
                            <td colspan="2"><strong>Rp <?= number_format($total, 0, ',', '.'); ?></strong></td>
                        </tr>
                    </table>

                    <br>

                    <a class="btn btn-danger"
                        href="dashboard.php?p=camp&aksi=kosongkan"
                        onclick="return confirm('Kosongkan semua isi keranjang?')">
                        Kosongkan Keranjang
                    </a>

                    <br><br>

                    <!-- INPUT LAMA SEWA (HARI) -->
                    <label>Lama sewa (hari):</label><br>
                    <input type="number" name="lama_hari" value="1" min="1" style="width:80px;">
                    <br><br>

                    <label>Pilih Data Registrasi Pendakian:</label><br>
                    <select name="id_regis" required>
                        <option value="">-- Pilih --</option>
                        <?php while ($reg = mysqli_fetch_assoc($qReg)): ?>
                            <option value="<?= (int)$reg['id_regis']; ?>">
                                <?= '#' . $reg['id_regis'] . ' | Naik: ' . date('d-m-Y H:i', strtotime($reg['tgl_naik'])); ?>
                                (<?= htmlspecialchars($reg['status']); ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <br><br>
                    <button type="submit" name="proses_sewa" class="btn btn-primary">
                        Proses Sewa
                    </button>

                <?php endif; ?>
            </form>
        </div>

    </div>
</div>

<script>
    function toggleCart() {
        var backdrop = document.getElementById('cart-backdrop');
        if (!backdrop) return;

        if (backdrop.style.display === 'none' || backdrop.style.display === '') {
            backdrop.style.display = 'flex';
        } else {
            backdrop.style.display = 'none';
        }
    }

    function backdropClick(e) {
        // kalau klik di area gelap (bukan di dalam modal), tutup
        if (e.target.id === 'cart-backdrop') {
            toggleCart();
        }
    }
</script>