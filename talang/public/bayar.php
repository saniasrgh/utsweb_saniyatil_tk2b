<?php
// File: bayar.php (dipanggil dari dashboard.php)
include 'koneksi.php';

if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = (int)$_SESSION['user_id'];

// ======================================================================
// TENTUKAN MODE HALAMAN: LIST TAGIHAN / FORM PEMBAYARAN
// ======================================================================
$view = $_GET['view'] ?? 'list';

// ======================================================================
// 1. PROSES SUBMIT FORM PEMBAYARAN (POST)
// ======================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_pembayaran'])) {
    $regis_id = (int)($_POST['regis_id'] ?? 0);
    if ($regis_id <= 0) {
        echo "<script>alert('Registrasi pendakian tidak diketahui.'); 
              window.location='dashboard.php?p=bayar';</script>";
        exit;
    }

    // pastikan registrasi ini milik user yang login
    $sqlCheck = "
        SELECT r.id_regis, p.user_id
        FROM sania_registrasi r
        JOIN sania_pendaki p ON r.id_pendaki = p.id
        WHERE r.id_regis = $regis_id
          AND p.user_id  = $userId
        LIMIT 1
    ";
    $cekRes = mysqli_query($koneksi, $sqlCheck);
    if (!$cekRes || mysqli_num_rows($cekRes) == 0) {
        echo "<script>alert('Registrasi tidak ditemukan / bukan milik akun ini.'); 
              window.location='dashboard.php?p=bayar';</script>";
        exit;
    }

    $metode = $_POST['metode_bayar'] ?? 'cash';
    $metode = in_array($metode, ['cash', 'transfer', 'qris']) ? $metode : 'cash';

    $bukti_file = '';

    // Kalau bukan cash → wajib upload bukti
    if ($metode !== 'cash') {
        if (!isset($_FILES['bukti']) || $_FILES['bukti']['error'] !== UPLOAD_ERR_OK) {
            echo "<script>alert('Bukti pembayaran wajib diupload untuk metode ini.');history.back();</script>";
            exit;
        }

        $dir = "bukti/";
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $ext  = pathinfo($_FILES['bukti']['name'], PATHINFO_EXTENSION);
        $nama = "bayar_" . time() . "_" . rand(1000, 9999) . "." . $ext;

        if (!move_uploaded_file($_FILES['bukti']['tmp_name'], $dir . $nama)) {
            echo "<script>alert('Gagal menyimpan bukti pembayaran.');history.back();</script>";
            exit;
        }

        // simpan NAMA FILE saja
        $bukti_file = $nama;
    }

    // 1a. update metode_bayar di tabel registrasi
    $stmt1 = $koneksi->prepare("UPDATE sania_registrasi SET metode_bayar=? WHERE id_regis=?");
    $stmt1->bind_param("si", $metode, $regis_id);
    $stmt1->execute();
    $stmt1->close();

    // 1b. update semua sewa di registrasi ini
    if ($metode === 'cash') {
        $statusBaru = 'belum_bayar';
        $stmt2 = $koneksi->prepare("
            UPDATE sewa_alat
            SET status_pembayaran = ?
            WHERE registrasi_id = ?
              AND status_pembayaran <> 'lunas'
        ");
        $stmt2->bind_param("si", $statusBaru, $regis_id);
    } else {
        $statusBaru = 'menunggu_verifikasi';
        $stmt2 = $koneksi->prepare("
            UPDATE sewa_alat
            SET status_pembayaran = ?, bukti_pembayaran = ?
            WHERE registrasi_id = ?
              AND status_pembayaran <> 'lunas'
        ");
        $stmt2->bind_param("ssi", $statusBaru, $bukti_file, $regis_id);
    }

    $stmt2->execute();
    $stmt2->close();

    echo "<script>
            alert('Data pembayaran tersimpan. Untuk cash, silakan bayar di basecamp. Untuk transfer/QRIS, menunggu verifikasi admin.');
            window.location='dashboard.php?p=bayar';
          </script>";
    exit;
}

// ======================================================================
// 2. VIEW: FORM PEMBAYARAN (POPUP)
// ======================================================================
if ($view === 'form') {

    $regis_id = (int)($_GET['regis_id'] ?? 0);

    if ($regis_id <= 0) {
        echo "<script>alert('Registrasi pendakian tidak diketahui.'); 
              window.location='dashboard.php?p=bayar';</script>";
        exit;
    }

    $sqlReg = "
        SELECT r.id_regis, r.tgl_naik, r.tgl_turun, r.status, r.metode_bayar,
               r.jumlah_anggota,
               p.nama_lengkap
        FROM sania_registrasi r
        JOIN sania_pendaki p ON r.id_pendaki = p.id
        WHERE r.id_regis = $regis_id
          AND p.user_id  = $userId
        LIMIT 1
    ";
    $resReg = mysqli_query($koneksi, $sqlReg);
    $regis  = mysqli_fetch_assoc($resReg);

    if (!$regis) {
        echo "<script>alert('Registrasi tidak ditemukan / bukan milik akun ini.'); 
              window.location='dashboard.php?p=bayar';</script>";
        exit;
    }

    // Ambil semua sewa yg belum lunas
    $sqlSewa = "
        SELECT sewa_id, jumlah, hari, harga_total
        FROM sewa_alat
        WHERE registrasi_id = $regis_id
          AND status_pembayaran <> 'lunas'
    ";
    $resSewa = mysqli_query($koneksi, $sqlSewa);

    $items = [];
    $total_tagihan = 0;
    while ($row = mysqli_fetch_assoc($resSewa)) {
        $items[] = $row;
        $total_tagihan += (int)$row['harga_total'];
    }

    if (empty($items)) {
        echo "<script>alert('Tidak ada sewa untuk dibayar.'); 
              window.location='dashboard.php?p=bayar';</script>";
        exit;
    }

    $jumlah_anggota    = (int)($regis['jumlah_anggota'] ?? 0);
    $biaya_registrasi  = $jumlah_anggota * 25000;
    $total_tagihan    += $biaya_registrasi;
?>

    <!-- CSS popup pembayaran -->
    <style>
        .pay-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 999;
        }

        .pay-box {
            width: 640px;
            max-width: 95%;
            background: #ffffff;
            border-radius: 18px;
            padding: 20px 22px 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            font-family: "Poppins", system-ui, sans-serif;
        }

        .pay-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .pay-title {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
        }

        .pay-close {
            background: none;
            border: none;
            font-size: 24px;
            color: #6b7280;
            cursor: pointer;
        }

        .pay-close:hover {
            color: #111827;
        }

        .pay-sub {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 12px;
        }

        .pay-label {
            display: block;
            margin-top: 12px;
            font-size: 13px;
            font-weight: 500;
        }

        .pay-input,
        .pay-select {
            width: 100%;
            padding: 8px 10px;
            border-radius: 10px;
            border: 1px solid #d1d5db;
            margin-top: 5px;
            font-size: 13px;
        }

        .pay-input[readonly] {
            background: #f9fafb;
        }

        .pay-total {
            margin-top: 12px;
            padding: 10px 14px;
            border-radius: 12px;
            border: 1px solid #34d399;
            background: #ecfdf5;
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            color: #065f46;
        }

        .pay-note {
            color: #6b7280;
            font-size: 11px;
            margin-top: 4px;
        }

        .pay-actions {
            margin-top: 18px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .pay-btn {
            padding: 10px 16px;
            border-radius: 999px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
        }

        .pay-btn-cancel {
            background: #f3f4f6;
            color: #374151;
        }

        .pay-btn-cancel:hover {
            background: #e5e7eb;
        }

        .pay-btn-submit {
            background: linear-gradient(90deg, #22c55e, #16a34a);
            color: #fff;
        }

        .pay-btn-submit:hover {
            filter: brightness(1.05);
        }
    </style>

    <div class="pay-overlay" id="pay-layer">
        <div class="pay-box">

            <div class="pay-header">
                <div class="pay-title">Form Pembayaran</div>
                <button class="pay-close" onclick="window.location='dashboard.php?p=bayar';">&times;</button>
            </div>

            <div class="pay-sub">
                Lengkapi detail pembayaran untuk registrasi pendakianmu.
            </div>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="regis_id" value="<?= $regis_id ?>">

                <label class="pay-label">Registrasi</label>
                <input class="pay-input" value="Regis #<?= $regis_id ?> | <?= $regis['tgl_naik'] ?> → <?= $regis['tgl_turun'] ?>" readonly>

                <label class="pay-label">Nama Pendaki</label>
                <input class="pay-input" value="<?= htmlspecialchars($regis['nama_lengkap']) ?>" readonly>

                <div class="pay-total">
                    <span>Total Tagihan (registrasi + sewa)</span>
                    <span>Rp <?= number_format($total_tagihan, 0, ',', '.') ?></span>
                </div>
                <div class="pay-note">
                    Sudah termasuk biaya registrasi: <?= $jumlah_anggota ?> orang × Rp 25.000 = Rp <?= number_format($biaya_registrasi, 0, ',', '.') ?>.
                </div>

                <label class="pay-label">Metode Pembayaran</label>
                <select name="metode_bayar" id="metode" class="pay-select">
                    <option value="cash">Cash (bayar di basecamp)</option>
                    <option value="transfer">Transfer</option>
                    <option value="qris">QRIS</option>
                </select>

                <div class="pay-note">Untuk cash tidak perlu upload bukti.</div>

                <div id="bukti-area">
                    <label class="pay-label">Bukti Pembayaran</label>
                    <input type="file" name="bukti" class="pay-input">
                </div>

                <div class="pay-actions">
                    <button type="button" class="pay-btn pay-btn-cancel"
                        onclick="window.location='dashboard.php?p=bayar';">
                        Batal
                    </button>
                    <button type="submit" name="submit_pembayaran" class="pay-btn pay-btn-submit">
                        Konfirmasi Pembayaran
                    </button>
                </div>
            </form>

        </div>
    </div>

    <script>
        const metode = document.getElementById('metode');
        const buktiArea = document.getElementById('bukti-area');

        const toggleBukti = () => {
            buktiArea.style.display = (metode.value === 'cash') ? 'none' : 'block';
        };
        metode.addEventListener('change', toggleBukti);
        toggleBukti();

        // close overlay jika klik luar box
        document.getElementById('pay-layer').addEventListener('click', e => {
            if (e.target.id === 'pay-layer') {
                window.location = 'dashboard.php?p=bayar';
            }
        });
    </script>

<?php
    exit;
}

// ======================================================================
// 3. VIEW: LIST TAGIHAN (DALAM DASHBOARD STYLE)
// ======================================================================

$sql = "SELECT 
        sa.sewa_id,
        sa.jumlah,
        sa.hari,
        sa.harga_total,
        sa.status_pembayaran,
        sa.bukti_pembayaran,
        a.nama          AS nama_alat,
        r.id_regis,
        r.tgl_naik,
        r.tgl_turun,
        r.status        AS status_regis,
        r.metode_bayar,
        r.jumlah_anggota,
        p.nama_lengkap  AS nama_pendaki
    FROM sewa_alat sa
    JOIN sania_registrasi r ON sa.registrasi_id = r.id_regis
    JOIN sania_pendaki   p ON r.id_pendaki     = p.id
    JOIN alat            a ON sa.alat_id       = a.alat_id
    WHERE p.user_id = $userId
      AND sa.status_pembayaran <> 'lunas'
    ORDER BY sa.sewa_id DESC
";

$data  = mysqli_query($koneksi, $sql);
$items = [];
$total_tagihan = 0;

if ($data) {
    while ($row = mysqli_fetch_assoc($data)) {
        $items[] = $row;
        $total_tagihan += (int)$row['harga_total'];
    }
}

$regis_id_for_payment = !empty($items) ? (int)$items[0]['id_regis'] : 0;

// biaya registrasi utk list
$biaya_registrasi     = 0;
$jumlah_anggota_list  = 0;

if ($regis_id_for_payment > 0) {
    $qRegList = mysqli_query(
        $koneksi,
        "SELECT jumlah_anggota FROM sania_registrasi WHERE id_regis = $regis_id_for_payment LIMIT 1"
    );
    if ($qRegList && $rList = mysqli_fetch_assoc($qRegList)) {
        $jumlah_anggota_list = (int)$rList['jumlah_anggota'];
        $biaya_registrasi    = $jumlah_anggota_list * 25000;
        $total_tagihan      += $biaya_registrasi;
    }
}
?>

<!-- Style kecil khusus halaman bayar (tidak mengganggu dashboard lain) -->
<style>
    .bayar-total-box {
        margin-top: 16px;
        background: #ecfdf5;
        border-radius: 12px;
        padding: 10px 14px;
        color: #065f46;
        font-size: 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: 1px solid #a7f3d0;
    }

    .bayar-total-box span:last-child {
        font-weight: 700;
    }

    .bayar-muted {
        color: #6b7280;
        font-size: 13px;
    }

    .bayar-badge {
        padding: 3px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 600;
        display: inline-block;
    }

    .bayar-badge-belum_bayar {
        background: #e5e7eb;
        color: #374151;
    }

    .bayar-badge-menunggu_verifikasi {
        background: #fef3c7;
        color: #92400e;
    }

    .bayar-badge-ditolak {
        background: #fee2e2;
        color: #b91c1c;
    }

    .bayar-header-space {
        margin-top: 30px;
        /* atau 16 / 20 px sesukamu */
    }
</style>

<div class="dash-section-header bayar-header-space">
    <div>
        <div class="dash-section-title">Pembayaran Sewa Peralatan</div>
        <div class="dash-section-sub">
            Berikut daftar sewa peralatan yang belum lunas. Total sudah termasuk biaya registrasi pendakian.
        </div>
    </div>

    <?php if ($regis_id_for_payment > 0): ?>
        <a href="dashboard.php?p=bayar&view=form&regis_id=<?= $regis_id_for_payment; ?>"
            class="btn-primary"
            style="text-decoration:none; display:inline-flex; align-items:center;">
            Lakukan Pembayaran
        </a>
    <?php endif; ?>
</div>

<div class="dash-table-card">

    <?php if (empty($items)): ?>

        <p class="bayar-muted">Data sewa tidak ditemukan.</p>

    <?php else: ?>

        <div class="dash-table-wrap">
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>ID Sewa</th>
                        <th>Registrasi</th>
                        <th>Nama Pendaki</th>
                        <th>Alat</th>
                        <th>Jumlah</th>
                        <th>Hari</th>
                        <th>Total</th>
                        <th>Status Pembayaran</th>
                    </tr>
                </thead>
                <tbody>

                    <?php if ($biaya_registrasi > 0): ?>
                        <tr>
                            <td>-</td>
                            <td>#<?= $regis_id_for_payment; ?></td>
                            <td><?= htmlspecialchars($items[0]['nama_pendaki']); ?></td>
                            <td>
                                Biaya Registrasi Pendakian<br>
                                <span class="bayar-muted" style="font-size:11px;">
                                    25.000 / orang (<?= $jumlah_anggota_list; ?> orang)
                                </span>
                            </td>
                            <td><?= $jumlah_anggota_list; ?></td>
                            <td>1</td>
                            <td>Rp <?= number_format($biaya_registrasi, 0, ',', '.'); ?></td>
                            <td>
                                <span class="bayar-badge bayar-badge-belum_bayar">belum_bayar</span>
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($items as $row): ?>
                        <?php
                        $statusPay = $row['status_pembayaran'];
                        $classPay  = 'bayar-badge-' . $statusPay;
                        ?>
                        <tr>
                            <td>#<?= $row['sewa_id']; ?></td>
                            <td>
                                #<?= $row['id_regis']; ?><br>
                                <span class="bayar-muted" style="font-size:11px;">
                                    <?= htmlspecialchars($row['tgl_naik']); ?> → <?= htmlspecialchars($row['tgl_turun']); ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($row['nama_pendaki']); ?></td>
                            <td><?= htmlspecialchars($row['nama_alat']); ?></td>
                            <td><?= (int)$row['jumlah']; ?></td>
                            <td><?= (int)$row['hari']; ?></td>
                            <td>Rp <?= number_format($row['harga_total'], 0, ',', '.'); ?></td>
                            <td>
                                <span class="bayar-badge <?= $classPay; ?>">
                                    <?= $statusPay; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                </tbody>
            </table>
        </div>

        <div class="bayar-total-box">
            <span>Total tagihan (registrasi + sewa, belum lunas)</span>
            <span>Rp <?= number_format($total_tagihan, 0, ',', '.'); ?></span>
        </div>

    <?php endif; ?>

</div>