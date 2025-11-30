<?php
// file: admin/proses.php
include '../koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Ambil aksi dan subaksi
$aksi = $_POST['aksi'] ?? $_GET['aksi'] ?? '';
$subaksi = $_POST['subaksi'] ?? $_GET['subaksi'] ?? '';


switch ($aksi) {

  /* ============================================================
       A. HAPUS PENDAKI
    ============================================================ */
  case 'hapus_pendaki':

    $id = intval($_POST['id']);

    // cek apakah masih dipakai registrasi
    $cek = $koneksi->prepare("SELECT COUNT(*) AS jml FROM sania_registrasi WHERE id_pendaki=?");
    $cek->bind_param("i", $id);
    $cek->execute();
    $res = $cek->get_result()->fetch_assoc();

    if ($res['jml'] > 0) {
      echo "<script>alert('Pendaki masih dipakai pada registrasi!');window.location.href='../dashboard.php?p=pendaki';</script>";
      exit;
    }

    $stmt = $koneksi->prepare("DELETE FROM sania_pendaki WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    echo "<script>alert('Pendaki berhasil dihapus!');window.location.href='../dashboard.php?p=pendaki';</script>";
    exit;



    /* ============================================================
       B. UPDATE PENDAKI
    ============================================================ */
  case 'update_pendaki':

    $id     = intval($_POST['id']);
    $nik    = $_POST['nik'];
    $nama   = $_POST['nama_lengkap'];
    $usia   = intval($_POST['usia']);
    $no_hp  = $_POST['no_hp'];
    $alamat = $_POST['alamat'];

    $stmt = $koneksi->prepare("
            UPDATE sania_pendaki 
            SET nik=?, nama_lengkap=?, usia=?, no_hp=?, alamat=?
            WHERE id=?
        ");
    $stmt->bind_param("ssissi", $nik, $nama, $usia, $no_hp, $alamat, $id);
    $stmt->execute();

    echo "<script>alert('Data pendaki berhasil diperbarui!');window.location.href='../dashboard.php?p=pendaki';</script>";
    exit;



    /* ============================================================
       C. INSERT REGISTRASI (ADMIN)
    ============================================================ */
  case 'insert_registrasi':

    $id_pendaki      = intval($_POST['id_pendaki'] ?? 0);
    $tgl_naik        = trim($_POST['tgl_naik'] ?? '');
    $tgl_turun       = trim($_POST['tgl_turun'] ?? '');
    $jumlah_anggota  = intval($_POST['jumlah_anggota'] ?? 0);
    $status          = trim($_POST['status'] ?? '');

    if ($id_pendaki <= 0) {
      echo "<script>alert('Pilih pendaki!');window.history.back();</script>";
      exit;
    }

    $stmt = $koneksi->prepare("
            INSERT INTO sania_registrasi 
            (id_pendaki, tgl_naik, tgl_turun, jumlah_anggota, status)
            VALUES (?,?,?,?,?)
        ");
    $stmt->bind_param("issis", $id_pendaki, $tgl_naik, $tgl_turun, $jumlah_anggota, $status);
    $stmt->execute();

    echo "<script>alert('Registrasi berhasil disimpan!');window.location.href='../dashboard.php?p=regis';</script>";
    exit;




    /* ============================================================
       D. UPDATE REGISTRASI (ADMIN)
    ============================================================ */
  case 'update_registrasi':

    $id_regis        = intval($_POST['id_regis'] ?? 0);
    $tgl_naik        = trim($_POST['tgl_naik'] ?? '');
    $tgl_turun       = trim($_POST['tgl_turun'] ?? '');
    $jumlah_anggota  = intval($_POST['jumlah_anggota'] ?? 0);
    $status          = trim($_POST['status'] ?? '');

    $stmt = $koneksi->prepare("
            UPDATE sania_registrasi
            SET tgl_naik=?, tgl_turun=?, jumlah_anggota=?, status=?
            WHERE id_regis=?
        ");
    $stmt->bind_param("ssisi", $tgl_naik, $tgl_turun, $jumlah_anggota, $status, $id_regis);
    $stmt->execute();

    echo "<script>alert('Registrasi diperbarui!');window.location.href='../dashboard.php?p=regis';</script>";
    exit;



    /* ============================================================
       E. INSERT REGISTRASI USER (DENGAN FOTO & BUKTI)
    ============================================================ */
  case 'insert_registrasi_user':

    $id_pendaki     = intval($_POST['id_pendaki'] ?? 0);
    $tgl_naik       = trim($_POST['tgl_naik'] ?? '');
    $tgl_turun      = trim($_POST['tgl_turun'] ?? '');
    $jumlah_anggota = intval($_POST['jumlah_anggota'] ?? 0);
    $metode_bayar   = trim($_POST['metode_bayar'] ?? 'cash');

    if ($id_pendaki <= 0) {
      $_SESSION['flash_error'] = "Data pendaki tidak valid.";
      header("Location: ../dashboard.php?p=formpendaki&step=regis");
      exit;
    }

    // Folder
    $dir_bukti = "../uploads/bukti/";
    $dir_foto  = "../uploads/foto_tim/";
    if (!is_dir($dir_bukti)) mkdir($dir_bukti, 0777, true);
    if (!is_dir($dir_foto)) mkdir($dir_foto, 0777, true);

    $bukti_path = "";
    $foto_path  = "";

    // Upload bukti (wajib jika transfer/QRIS)
    if (($metode_bayar === 'transfer' || $metode_bayar === 'qris')) {
      if ($_FILES['bukti_bayar']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['flash_error'] = "Bukti pembayaran WAJIB diupload!";
        header("Location: ../dashboard.php?p=formpendaki&step=regis");
        exit;
      }
    }

    if ($_FILES['bukti_bayar']['error'] === UPLOAD_ERR_OK) {
      $ext = pathinfo($_FILES['bukti_bayar']['name'], PATHINFO_EXTENSION);
      $nama = "bukti_" . time() . "_" . rand(1000, 9999) . "." . $ext;
      move_uploaded_file($_FILES['bukti_bayar']['tmp_name'], $dir_bukti . $nama);
      $bukti_path = "uploads/bukti/" . $nama;
    }

    // Upload foto tim
    if ($_FILES['foto_tim']['error'] === UPLOAD_ERR_OK) {
      $ext = pathinfo($_FILES['foto_tim']['name'], PATHINFO_EXTENSION);
      $nama = "tim_" . time() . "_" . rand(1000, 9999) . "." . $ext;
      move_uploaded_file($_FILES['foto_tim']['tmp_name'], $dir_foto . $nama);
      $foto_path = "uploads/foto_tim/" . $nama;
    }

    // Insert DB
    $stmt = $koneksi->prepare("
            INSERT INTO sania_registrasi 
            (id_pendaki, tgl_naik, tgl_turun, jumlah_anggota, status, metode_bayar, bukti_bayar, foto_tim)
            VALUES (?,?,?,?, 'Pending', ?, ?, ?)
        ");

    $stmt->bind_param(
      "ississs",
      $id_pendaki,
      $tgl_naik,
      $tgl_turun,
      $jumlah_anggota,
      $metode_bayar,
      $bukti_path,
      $foto_path
    );

    $stmt->execute();
    header("Location: ../dashboard.php?p=regis");
    exit;




    /* ============================================================
       F. HAPUS REGISTRASI (ADMIN & USER)
    ============================================================ */
  case 'hapus_registrasi':

    $id_regis = intval($_POST['id_regis'] ?? 0);

    if ($id_regis <= 0) {
      header("Location: ../dashboard.php?p=regis");
      exit;
    }

    // ambil file untuk dihapus
    $q = $koneksi->query("SELECT bukti_bayar, foto_tim FROM sania_registrasi WHERE id_regis = $id_regis");
    $data = $q->fetch_assoc();

    if ($data) {
      if (!empty($data['bukti_bayar']) && file_exists("../" . $data['bukti_bayar'])) {
        unlink("../" . $data['bukti_bayar']);
      }
      if (!empty($data['foto_tim']) && file_exists("../" . $data['foto_tim'])) {
        unlink("../" . $data['foto_tim']);
      }
    }

    // hapus DB
    $koneksi->query("DELETE FROM sania_registrasi WHERE id_regis = $id_regis");

    header("Location: ../dashboard.php?p=regis");
    exit;




    /* ============================================================
       G. MANAJEMEN ALAT
    ============================================================ */
  case 'alat':

    function proses_gambar($koneksi, $gambar_lama = null)
    {
      if (!empty($_POST['gambar_link'])) {
        $link = trim($_POST['gambar_link']);
        return $link;
      }

      if (!empty($_FILES['gambar_file']['name'])) {
        $folder = "../uploads/";
        if (!is_dir($folder)) mkdir($folder, 0777, true);

        $nama = time() . "_" . $_FILES['gambar_file']['name'];
        move_uploaded_file($_FILES['gambar_file']['tmp_name'], $folder . $nama);
        return $nama;
      }

      return $gambar_lama;
    }

    /* ---- SUBAKSI TAMBAH ---- */
    if ($subaksi == 'tambah') {
      $nama  = $_POST['nama'];
      $harga = intval($_POST['harga_per_hari']);
      $stok  = intval($_POST['stok']);
      $ket   = $_POST['keterangan'];

      $gambar = proses_gambar($koneksi, null);

      $koneksi->query("INSERT INTO alat (nama, harga_per_hari, stok, keterangan, gambar)
                             VALUES ('$nama', '$harga', '$stok', '$ket', '$gambar')");

      echo "<script>alert('Alat ditambahkan!');window.location='../dashboard.php?p=alat';</script>";
      exit;
    }

    /* ---- SUBAKSI UPDATE ---- */
    if ($subaksi == 'update') {

      $id    = intval($_POST['alat_id']);
      $nama  = $_POST['nama'];
      $harga = intval($_POST['harga_per_hari']);
      $stok  = intval($_POST['stok']);
      $ket   = $_POST['keterangan'];

      // ambil data lama untuk gambar jika tidak diubah
      $dataLama = $koneksi->query("SELECT * FROM alat WHERE alat_id=$id")->fetch_assoc();
      $gambarLama = $dataLama['gambar'];

      // proses gambar (pakai link/file/upload)
      $gambar = proses_gambar($koneksi, $gambarLama);

      $koneksi->query("
        UPDATE alat 
        SET nama='$nama', harga_per_hari='$harga', stok='$stok',
            keterangan='$ket', gambar='$gambar'
        WHERE alat_id=$id
    ");

      echo "<script>alert('Data alat berhasil diperbarui!');window.location='../dashboard.php?p=alat';</script>";
      exit;
    }


    /* ---- SUBAKSI HAPUS ---- */
    if ($subaksi == 'hapus') {

      $id = intval($_GET['id']);

      $data = $koneksi->query("SELECT gambar FROM alat WHERE alat_id=$id")->fetch_assoc();

      if ($data && $data['gambar'] && file_exists("../uploads/" . $data['gambar'])) {
        unlink("../uploads/" . $data['gambar']);
      }

      $koneksi->query("DELETE FROM alat WHERE alat_id=$id");

      echo "<script>alert('Alat dihapus!');window.location='../dashboard.php?p=alat';</script>";
      exit;
    }

    // Jika subaksi tidak dikenal
    header("Location: ../dashboard.php?p=alat");
    exit;




    /* ============================================================
       DEFAULT
    ============================================================ */
  default:
    // Jika tidak ada aksi → kembalikan ke dashboard tanpa error
    header("Location: ../dashboard.php?p=dashboard");
    exit;
}
