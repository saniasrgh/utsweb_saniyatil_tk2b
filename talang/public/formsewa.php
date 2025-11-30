<?php
// session_start();
include 'koneksi.php';

// Pastikan user login
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = (int)$_SESSION['user_id'];

// =====================================================
// 1. PROSES SIMPAN SEWA (POST)
// =====================================================
if (isset($_POST['submit'])) {

    $registrasi_id = (int)($_POST['registrasi_id'] ?? 0);
    $alat_id       = (int)($_POST['alat_id'] ?? 0);
    $jumlah        = (int)($_POST['jumlah'] ?? 0);
    $hari          = (int)($_POST['hari'] ?? 0);
    $harga         = (int)($_POST['harga_per_hari'] ?? 0);

    if ($registrasi_id <= 0) {
        echo "<script>alert('Registrasi pendakian tidak diketahui. Silakan daftar pendakian dulu.');history.back();</script>";
        exit;
    }

    if ($alat_id <= 0 || $jumlah <= 0 || $hari <= 0 || $harga <= 0) {
        echo "<script>alert('Data sewa tidak lengkap / tidak valid');history.back();</script>";
        exit;
    }

    $total = $jumlah * $hari * $harga;

    $sql = "INSERT INTO sewa_alat 
            (registrasi_id, alat_id, jumlah, hari, harga_total, status_pembayaran)
            VALUES 
            ($registrasi_id, $alat_id, $jumlah, $hari, $total, 'belum_bayar')";

    if (mysqli_query($koneksi, $sql)) {
        $sewa_id = mysqli_insert_id($koneksi);

        echo "<script>
                alert('Sewa berhasil disimpan! Silakan lakukan pembayaran.');
                window.location='dashboard.php?p=bayar&sewa_id={$sewa_id}';
              </script>";
        exit;
    } else {
        echo "<script>alert('Gagal menyimpan sewa!');</script>";
    }
}

// =====================================================
// 2. LOAD DATA UNTUK FORM (GET)
// =====================================================

// 2.1 Ambil data alat
$alat_id        = (int)($_GET['alat_id'] ?? 0);
$harga_per_hari = (int)($_GET['harga'] ?? 0);

$alatRes = mysqli_query($koneksi, "SELECT * FROM alat WHERE alat_id = $alat_id");
$dataAlat = mysqli_fetch_assoc($alatRes);

if (!$dataAlat) {
    echo "<script>alert('Data alat tidak ditemukan'); window.location='dashboard.php?p=camp';</script>";
    exit;
}

// kalau di tabel alat sudah ada harga_per_hari, pakai itu saja
if (isset($dataAlat['harga_per_hari']) && (int)$dataAlat['harga_per_hari'] > 0) {
    $harga_per_hari = (int)$dataAlat['harga_per_hari'];
}

// 2.2 Ambil id_pendaki dari user_id
$pendakiRes = mysqli_query(
    $koneksi,
    "SELECT id, nama_lengkap FROM sania_pendaki WHERE user_id = $userId LIMIT 1"
);
$pendakiRow = mysqli_fetch_assoc($pendakiRes);
$pendaki_id = (int)($pendakiRow['id'] ?? 0);

if ($pendaki_id <= 0) {
    echo "<script>alert('Data pendaki belum ada. Silakan isi form pendaki dulu.'); 
          window.location='dashboard.php?p=formpendaki&step=pendaki';</script>";
    exit;
}

// 2.3 Ambil registrasi yang akan dipakai otomatis
//     - Kalau ada ?regis_id di URL, pakai itu (dan cek milik user)
//     - Kalau tidak ada, pakai registrasi terakhir yang Disetujui milik user ini

$registrasi_id = (int)($_GET['regis_id'] ?? 0);

if ($registrasi_id > 0) {
    // cek registrasi tersebut milik pendaki ini
    $sqlReg = "
        SELECT id_regis, tgl_naik, tgl_turun, status
        FROM sania_registrasi
        WHERE id_regis   = $registrasi_id
          AND id_pendaki = $pendaki_id
        LIMIT 1
    ";
} else {
    // ambil registrasi terakhir yang Disetujui milik pendaki ini
    $sqlReg = "
        SELECT id_regis, tgl_naik, tgl_turun, status
        FROM sania_registrasi
        WHERE id_pendaki = $pendaki_id
        ORDER BY id_regis DESC
        LIMIT 1
    ";
}

$regRes = mysqli_query($koneksi, $sqlReg);
$regis  = mysqli_fetch_assoc($regRes);

if (!$regis) {
    echo "<script>alert('Kamu belum memiliki registrasi pendakian. Silakan daftar pendakian dulu.'); 
          window.location='dashboard.php?p=formpendaki&step=regis';</script>";
    exit;
}

$registrasi_id = (int)$regis['id_regis'];

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Form Sewa Alat</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: Poppins;
            background: #f4f6f9;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 120px auto;
            background: #fff;
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #3DB6A1;
            margin-bottom: 20px;
        }

        label {
            font-weight: 500;
            display: block;
            margin-top: 15px;
        }

        input,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 10px;
            margin-top: 5px;
            font-family: Poppins;
        }

        .btn {
            margin-top: 20px;
            width: 100%;
            padding: 12px;
            background: #3DB6A1;
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn:hover {
            background: #35a18f;
        }

        .total-box {
            background: #e8fff7;
            padding: 12px;
            border-radius: 12px;
            margin-top: 15px;
            font-size: 16px;
            color: #333;
            font-weight: 500;
        }
    </style>
</head>

<body>

    <div class="container">

        <h2>Form Sewa Peralatan</h2>

        <form action="" method="POST">

            <!-- data tersembunyi untuk proses -->
            <input type="hidden" name="alat_id" value="<?= $alat_id ?>">
            <input type="hidden" name="registrasi_id" value="<?= $registrasi_id ?>">
            <input type="hidden" name="harga_per_hari" id="harga" value="<?= $harga_per_hari ?>">

            <!-- INFORMASI REGISTRASI (read only) -->
            <label>Registrasi Pendakian</label>
            <input type="text"
                value="Regis #<?= $registrasi_id; ?> | <?= $regis['tgl_naik']; ?> → <?= $regis['tgl_turun']; ?> (<?= $regis['status']; ?>)"
                readonly>
            <label>Nama Pendaki</label>
            <input type="text"
                value="<?= htmlspecialchars($pendakiRow['nama_lengkap']); ?>"
                readonly>

            <label>Nama Alat</label>
            <input type="text" value="<?= htmlspecialchars($dataAlat['nama']); ?>" readonly>

            <label>Harga per Hari</label>
            <input type="text" value="Rp <?= number_format($harga_per_hari, 0, ',', '.'); ?>" readonly>

            <label>Jumlah</label>
            <input type="number" name="jumlah" id="jumlah" min="1" required>

            <label>Durasi (hari)</label>
            <input type="number" name="hari" id="hari" min="1" required>

            <div class="total-box">
                Total Harga: <span id="total">Rp 0</span>
            </div>

            <button class="btn" type="submit" name="submit">Simpan Sewa</button>
        </form>

    </div>

    <script>
        let jumlah = document.getElementById('jumlah');
        let hari = document.getElementById('hari');
        let harga = parseInt(document.getElementById('harga').value) || 0;
        let totalBox = document.getElementById('total');

        function hitungTotal() {
            let jml = parseInt(jumlah.value) || 0;
            let day = parseInt(hari.value) || 0;
            let total = jml * day * harga;

            totalBox.innerText = 'Rp ' + total.toLocaleString('id-ID');
        }

        jumlah.addEventListener('input', hitungTotal);
        hari.addEventListener('input', hitungTotal);
    </script>

</body>

</html>