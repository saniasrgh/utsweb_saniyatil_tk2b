<?php
include 'koneksi.php';

// Cek kalau mode edit (PHP fallback jika user akses ?edit=.. langsung)
$editMode = false;
$dataEdit = null;

if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    $resultEdit = mysqli_query($koneksi, "SELECT * FROM alat WHERE alat_id = $editId");
    if ($resultEdit && mysqli_num_rows($resultEdit) > 0) {
        $editMode = true;
        $dataEdit = mysqli_fetch_assoc($resultEdit);
    }
}

// Ambil semua data alat untuk tabel
$query = mysqli_query($koneksi, "SELECT * FROM alat ORDER BY nama ASC");
?>

<!doctype html>
<html lang="id">

<head>
    <title>Admin - Kelola Peralatan Camp</title>

    <style>
        .container {
            max-width: 1100px;
            margin: 10px auto 20px;
            /* jarak atas & bawah dipendekkan */
            padding: 0 20px;
        }

        /* BACKDROP */
        .float-bg {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .45);
            backdrop-filter: blur(4px);
            display: none;
            z-index: 1000;
        }

        /* CARD FLOATING FORM */
        .float-form {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 45%;
            max-width: 550px;
            background: #ffffff;
            padding: 28px;
            border-radius: 18px;
            box-shadow: 0 12px 35px rgba(0, 0, 0, .15);
            display: none;
            z-index: 1001;
            animation: pop .25s ease;
        }

        /* POP ANIMATION */
        @keyframes pop {
            from {
                transform: translate(-50%, -45%) scale(.9);
                opacity: 0;
            }

            to {
                transform: translate(-50%, -50%) scale(1);
                opacity: 1;
            }
        }

        .float-form h2 {
            margin-top: 0;
            margin-bottom: 15px;
            font-weight: 600;
        }

        /* BUTTON BAR */
        .btn-area {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 20px;
        }

        /* ADD BUTTON */
        .btn-open {
            background: #2db66e;
            color: #fff;
            padding: 10px 16px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: .2s;
        }

        .btn-open:hover {
            background: #25a361;
            transform: translateY(-2px);
        }

        /* CANCEL */
        .btn-cancel {
            background: #bbb;
            color: #fff;
            border: none;
            padding: 10px 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: .2s;
        }

        .btn-cancel:hover {
            background: #a5a5a5;
        }

        /* SAVE BUTTON */
        .btn-save {
            background: #0d6efd;
            color: #fff;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: .2s;
        }

        .btn-save:hover {
            background: #0a58ca;
            transform: translateY(-2px);
        }

        /* ============================INPUT MODERN============================ */

        input[type="text"],
        input[type="number"],
        textarea {
            width: 100%;
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid rgba(0, 0, 0, .15);
            font-size: 14px;
            background: rgba(255, 255, 255, 0.7);
            transition: .2s ease;
        }

        input:focus,
        textarea:focus {
            border-color: #3DB6A1;
            box-shadow: 0 0 0 3px rgba(61, 182, 161, 0.25);
            outline: none;
        }

        .header-table {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            position: relative;
            z-index: 1 !important;
        }


        .form-group {
            margin-bottom: 10px;
        }

        label {
            display: block;
            font-size: 14px;
            margin-bottom: 4px;
            font-weight: 500;
        }

        input[type="file"] {
            font-size: 13px;
        }

        .note {
            font-size: 12px;
            color: #383636ff;
        }

        /* ===================== TABEL ESTETIK ===================== */

        /* CARD PEMBUNGKUS TABEL */
        .table-card {
            background: rgba(255, 255, 255, 0.10);
            /* putih transparan */
            border-radius: 24px;
            padding: 16px 0;
            border: 1px solid rgba(255, 255, 255, 0.4);
            /* border kaca */
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.15);
            backdrop-filter: blur(11px);
            /* efek blur */
            -webkit-backdrop-filter: blur(18px);
            /* untuk Safari */
            overflow: hidden;
        }

        /* WRAPPER BIAR ADA SCROLL DI HP KECIL */
        .table-scroll {
            width: 100%;
            overflow-x: auto;
        }

        /* TABEL UTAMA */
        .table-alat {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 14px;
        }

        .table-alat th {
            padding: 14px 18px;
            font-weight: 600;
            text-align: left;
            white-space: nowrap;
        }

        .table-alat th:first-child {
            padding-left: 24px;
        }

        .table-alat th:last-child {
            padding-right: 24px;
        }

        /* BODY */
        .table-alat td {
            padding: 14px 18px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .table-alat td:first-child {
            padding-left: 24px;
        }

        .table-alat td:last-child {
            padding-right: 24px;
        }

        /* semua baris default agak transparan */
        .table-alat tbody tr {
            background: rgba(255, 255, 255, 0.08);
        }

        /* baris genap sedikit lebih terang */
        .table-alat tbody tr:nth-child(even) {
            background: rgba(255, 255, 255, 0.16);
        }

        /* saat hover lebih jelas */
        .table-alat tbody tr:hover {
            background: rgba(255, 255, 255, 0.28);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.35);
        }

        .table-alat thead {
            background: linear-gradient(90deg,
                    rgba(22, 163, 74, 0.95),
                    rgba(34, 197, 94, 0.9));
            color: #ffffff;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }



        /* NOMOR URUT */
        .td-no {
            font-weight: 600;
            color: #6b7280;
        }

        /* BADGE HARGA */
        .td-harga {
            font-family: "JetBrains Mono", ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        }

        .td-harga span {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            background: #ecfdf3;
            color: #15803d;
            font-size: 12px;
        }

        /* BADGE STOK */
        .td-stok span {
            display: inline-block;
            min-width: 36px;
            text-align: center;
            padding: 4px 10px;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            font-weight: 600;
            font-size: 12px;
        }

        /* KETERANGAN */
        .td-ket {
            color: #4b5563;
            max-width: 260px;
        }

        .table-alat td {
            border-bottom: 1px solid rgba(255, 255, 255, 0.2) !important;
        }


        /* Gambar */
        .img-thumb {
            width: 56px;
            height: 56px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(15, 23, 42, 0.15);
        }


        /* Aksi */
        .aksi {
            display: flex;
            gap: 6px;
            justify-content: flex-end;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2) !important;
        }

        .table-alat td:last-child {
            border-bottom: none !important;
        }


        .btn-edit,
        .btn-hapus {
            border-radius: 999px;
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 500;
            border: none;
            text-decoration: none;
            cursor: pointer;
            transition: transform .15s ease, box-shadow .15s ease, opacity .15s ease;
        }

        .btn-edit {
            background: #0f172a;
            color: #fff;
        }

        .btn-edit:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.35);
        }

        .btn-hapus {
            background: #ef4444;
            color: #fff;
        }

        .btn-hapus:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(248, 113, 113, 0.4);
        }

        /* Background khusus halaman alat, nempel ke area kanan (dash-main) */
        body.halaman-alat .dash-main {
            background: url('img/img8.jpg') center/cover no-repeat fixed !important;
            padding-bottom: 0 !important;
            /* biar tidak ada putih di bawah */
        }

        /* Supaya jarak antara hero dan tabel rapat, tidak ada strip putih */
        body.halaman-alat .dash-hero {
            margin-bottom: 0 !important;
        }

        .page-bg {
            margin-top: 10px;
            padding-top: 30px;
            min-height: 100vh;
            width: 90%;
            background: transparent;
            padding-left: 25px;
            /* background pindah ke .dash-main */
            padding-bottom: 40px;
            /* kalau mau ada nafas dikit di bawah */
            margin: 0;
        }

        /* KARTU TABEL – biar nggak main 100vw lagi */
        .dash-table-card {
            position: relative;
            background: transparent;
            width: 100%;
            margin: 0;
        }

        /* kalau di HP, kecilin sedikit supaya ga mentok */
        @media (max-width: 768px) {
            .page-bg {
                margin: 0 -16px;
                padding: 0 16px 30px 16px;
            }
        }


        @media (max-width: 768px) {
            .float-form {
                width: 90%;
            }

            .table-card {
                border-radius: 14px;
                padding-inline: 8px;
            }

            .table-alat th,
            .table-alat td {
                white-space: nowrap;
            }
        }
    </style>
</head>

<body class="halaman-alat">
    <div class="page-bg">
        <div class="dash-table-card">
            <div class="container">
                <div class="header-table">
                    <h2 class="m-0">Daftar Peralatan Camp</h2>
                    <button class="btn-open" onclick="openForm()">Tambah Alat Camp</button>
                </div>

                <!-- FORM TAMBAH / EDIT ALAT -->
                <!-- LAYER BACKGROUND -->
                <div id="float-bg" class="float-bg"></div>

                <!-- FORM MENGAMBANG -->
                <div id="float-form" class="float-form">
                    <h2 id="float-title"><?= $editMode ? 'Edit Alat Camp' : 'Tambah Alat Camp'; ?></h2>

                    <form id="form-alat" action="admin/proses.php?aksi=alat" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="aksi" value="alat">
                        <input type="hidden" name="subaksi" id="subaksi" value="<?= $editMode ? 'update' : 'tambah'; ?>">
                        <input type="hidden" name="alat_id" id="alat_id" value="<?= $editMode ? (int)$dataEdit['alat_id'] : ''; ?>">

                        <div class="form-group">
                            <label>Nama Alat</label>
                            <input type="text" name="nama" id="nama" required
                                value="<?= $editMode ? htmlspecialchars($dataEdit['nama']) : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label>Harga per Hari (Rp)</label>
                            <input type="number" name="harga_per_hari" id="harga_per_hari" min="0" required
                                value="<?= $editMode ? (int)$dataEdit['harga_per_hari'] : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label>Stok</label>
                            <input type="number" name="stok" id="stok" min="0" required
                                value="<?= $editMode ? (int)$dataEdit['stok'] : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label>Keterangan</label>
                            <textarea name="keterangan" id="keterangan"><?= $editMode ? htmlspecialchars($dataEdit['keterangan']) : ''; ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Gambar (Link URL)</label>
                            <input type="text" name="gambar_link" id="gambar_link"
                                value="<?php if ($editMode && filter_var($dataEdit['gambar'], FILTER_VALIDATE_URL)) echo htmlspecialchars($dataEdit['gambar']); ?>">
                        </div>

                        <div class="form-group">
                            <label>Gambar (Upload File)</label>
                            <input type="file" name="gambar_file" id="gambar_file" accept="image/*">
                            <div id="current-image-wrap" style="margin-top:8px;">
                                <?php if ($editMode && !empty($dataEdit['gambar'])) {
                                    $g = $dataEdit['gambar'];
                                    $srcEdit = filter_var($g, FILTER_VALIDATE_URL) ? $g : "uploads/" . $g;
                                ?>
                                    <img id="current-image" src="<?= htmlspecialchars($srcEdit); ?>" alt="Gambar saat ini" class="img-thumb">
                                <?php } ?>
                            </div>
                        </div>

                        <div class="btn-area">
                            <button type="button" class="btn-cancel" onclick="closeForm()">Batal</button>
                            <button type="submit" class="btn-save"><?= $editMode ? 'Update' : 'Simpan'; ?></button>
                        </div>
                    </form>
                </div>

                <!-- TABEL DATA ALAT -->
                <div class="table-card">
                    <div class="table-scroll">
                        <table class="table-alat">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Gambar</th>
                                    <th>Nama</th>
                                    <th>Harga/Hari</th>
                                    <th>Stok</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                mysqli_data_seek($query, 0);
                                while ($row = mysqli_fetch_assoc($query)) {
                                    $gambar = $row['gambar'];

                                    if ($gambar) {
                                        if (filter_var($gambar, FILTER_VALIDATE_URL)) {
                                            $src = $gambar;
                                        } else {
                                            $src = "uploads/" . $gambar;
                                        }
                                    } else {
                                        $src = "https://via.placeholder.com/80x80?text=No+Image";
                                    }
                                ?>
                                    <tr>
                                        <!-- No -->
                                        <td class="td-no"><?= $no++; ?></td>

                                        <!-- Gambar -->
                                        <td>
                                            <img src="<?= htmlspecialchars($src); ?>" class="img-thumb" alt="Gambar">
                                        </td>

                                        <!-- Nama -->
                                        <td><?= htmlspecialchars($row['nama']); ?></td>

                                        <!-- Harga -->
                                        <td class="td-harga">
                                            <span>Rp <?= number_format($row['harga_per_hari'], 0, ',', '.'); ?></span>
                                        </td>

                                        <!-- Stok -->
                                        <td class="td-stok">
                                            <span><?= (int)$row['stok']; ?></span>
                                        </td>

                                        <!-- Keterangan -->
                                        <td class="td-ket"><?= htmlspecialchars($row['keterangan']); ?></td>

                                        <!-- Aksi -->
                                        <td class="aksi">
                                            <a href="#"
                                                class="btn-edit"
                                                onclick="openEditForm(
                                                    '<?= $row['alat_id'] ?>',
                                                    '<?= htmlspecialchars($row['nama'], ENT_QUOTES) ?>',
                                                    '<?= $row['harga_per_hari'] ?>',
                                                    '<?= $row['stok'] ?>',
                                                    '<?= htmlspecialchars($row['keterangan'], ENT_QUOTES) ?>',
                                                    '<?= htmlspecialchars($src, ENT_QUOTES) ?>'
                                                    )">
                                                Edit
                                            </a>

                                            <a href="admin/proses.php?aksi=alat&subaksi=hapus&id=<?= $row['alat_id'] ?>"
                                                onclick="return confirm('Yakin ingin hapus?')" class="btn-hapus">
                                                Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php } ?>

                                <?php if (mysqli_num_rows($query) == 0) { ?>
                                    <tr>
                                        <td colspan="7" style="text-align:center; padding:15px;">
                                            Belum ada data alat
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Open tambah form (reset ke mode tambah)
            function openForm() {
                document.getElementById('float-bg').style.display = 'block';
                document.getElementById('float-form').style.display = 'block';

                const sub = document.getElementById('subaksi');
                if (sub) sub.value = 'tambah';
                document.getElementById('float-title').innerText = 'Tambah Alat Camp';
                document.getElementById('alat_id').value = '';

                document.getElementById('nama').value = '';
                document.getElementById('harga_per_hari').value = '';
                document.getElementById('stok').value = '';
                document.getElementById('keterangan').value = '';
                document.getElementById('gambar_link').value = '';
                const fileInput = document.getElementById('gambar_file');
                if (fileInput) fileInput.value = '';

                const oldImg = document.getElementById('current-image');
                if (oldImg) oldImg.remove();
            }

            function closeForm() {
                document.getElementById('float-bg').style.display = 'none';
                document.getElementById('float-form').style.display = 'none';
            }

            function openEditForm(id, nama, harga, stok, ket, gambarSrc) {
                document.getElementById('float-bg').style.display = 'block';
                document.getElementById('float-form').style.display = 'block';

                document.querySelector('#float-form h2').innerText = 'Edit Alat Camp';
                document.querySelector('input[name=subaksi]').value = 'update';
                document.getElementById('alat_id').value = id;

                document.querySelector('input[name=nama]').value = nama;
                document.querySelector('input[name=harga_per_hari]').value = harga;
                document.querySelector('input[name=stok]').value = stok;
                document.querySelector('textarea[name=keterangan]').value = ket;

                if (gambarSrc.startsWith('http')) {
                    document.querySelector('input[name=gambar_link]').value = gambarSrc;
                } else {
                    document.querySelector('input[name=gambar_link]').value = '';
                }

                document.querySelector('input[name=gambar_file]').value = '';

                let oldImg = document.getElementById('current-image');
                if (oldImg) oldImg.remove();

                let imgPreview = document.createElement('img');
                imgPreview.src = gambarSrc;
                imgPreview.id = "current-image";
                imgPreview.style.width = "80px";
                imgPreview.style.marginTop = "8px";
                imgPreview.style.borderRadius = "8px";

                document.querySelector('input[name=gambar_file]').parentNode.append(imgPreview);
            }
        </script>
    </div>
</body>

</html>