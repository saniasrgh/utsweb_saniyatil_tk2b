<?php
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // registrasi pendakian yang dipilih user
    // $registrasi_id = $_POST['registrasi_id'] ?? null;
    $registrasi_id = $_POST['registrasi_id'];


    if (!$registrasi_id) {
        echo "<script>alert('Registrasi pendakian belum dipilih');history.back();</script>";
        exit;
    }

    // array jumlah & hari per alat: jumlah[alat_id], hari[alat_id]
    $jumlahArr = $_POST['jumlah'] ?? [];
    $hariArr   = $_POST['hari'] ?? [];

    if (empty($jumlahArr)) {
        echo "<script>alert('Tidak ada alat yang dipilih');history.back();</script>";
        exit;
    }

    // ====== Upload bukti pembayaran (opsional) ======
    $nama_bukti = null;

    if (!empty($_FILES['bukti']['name'])) {
        $bukti_name = $_FILES['bukti']['name'];
        $bukti_tmp  = $_FILES['bukti']['tmp_name'];

        $folder = "bukti/";
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        $ext       = pathinfo($bukti_name, PATHINFO_EXTENSION);
        $nama_baru = 'bukti_' . time() . '.' . $ext;

        if (move_uploaded_file($bukti_tmp, $folder . $nama_baru)) {
            $nama_bukti = $nama_baru;
        }
    }

    // status pembayaran default
    $status_pembayaran = $nama_bukti ? 'menunggu_verifikasi' : 'belum_bayar';

    // total semua alat (kalau mau dipakai nanti, misal buat summary)
    $total_semua = 0;

    // ====== LOOP setiap alat ======
    foreach ($jumlahArr as $alat_id => $jumlah) {

        $jumlah = (int)$jumlah;
        $hari   = (int)($hariArr[$alat_id] ?? 0);

        // skip kalau 0 atau tidak diisi
        if ($jumlah <= 0 || $hari <= 0) {
            continue;
        }

        // ambil harga & stok alat
        $qAlat = mysqli_query($koneksi, "SELECT nama, harga_per_hari, stok 
                                         FROM alat 
                                         WHERE alat_id = '$alat_id'");
        $alat = mysqli_fetch_assoc($qAlat);

        if (!$alat) {
            continue; // alat tidak ditemukan
        }

        $harga_per_hari = (int)$alat['harga_per_hari'];
        $stok           = (int)$alat['stok'];

        // cek stok
        if ($stok < $jumlah) {
            echo "<script>alert('Stok {$alat['nama']} tidak mencukupi!');history.back();</script>";
            exit;
        }

        // hitung total per alat
        $total = $jumlah * $hari * $harga_per_hari;
        $total_semua += $total;

        // simpan ke tabel sewa_alat
        $sqlInsert = "
            INSERT INTO sewa_alat
                (registrasi_id, alat_id, jumlah, hari, harga_total, status_pembayaran, bukti_pembayaran)
            VALUES
                ('$registrasi_id', '$alat_id', '$jumlah', '$hari', '$total', '$status_pembayaran, " .
            ($nama_bukti ? "'" . $nama_bukti . "'" : "NULL") . ")
        ";

        mysqli_query($koneksi, $sqlInsert);

        // kurangi stok alat
        mysqli_query($koneksi, "UPDATE alat 
                                SET stok = stok - $jumlah 
                                WHERE alat_id = '$alat_id'");
    }

    echo "<script>
            alert('Penyewaan berhasil disimpan!');
            window.location='dashboard.php?p=penyewa';
          </script>";
}
