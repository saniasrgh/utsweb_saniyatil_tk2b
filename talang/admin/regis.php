<?php
include 'koneksi.php';

if (!isset($_GET['view'])) {
    $view = 'list';
} else {
    $view = $_GET['view'];
}
?>

<style>
    /* ===== CARD UTAMA DENGAN BACKGROUND FOTO ===== */
    .dash-table-card {
        background: url("img/img8.jpg") center/cover no-repeat fixed;
        padding: 28px;
        border-radius: 24px;
        position: relative;
        overflow: hidden;
    }

    .dash-table-card::before {
        content: "";
        position: absolute;
        inset: 0;
        background: rgba(245, 255, 247, 0.15);
        backdrop-filter: blur(1px);
    }

    .dash-table-card>* {
        position: relative;
        z-index: 2;
    }

    .dash-section-sub{
        color: #333333ff;
    }
    /* ===== TOMBOL HEADER TAMBAH REGISTRASI ===== */
    .reg-add-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 999px;
        background: linear-gradient(90deg, #ffffffff, #64f4e0ff);
        color: #000000ff;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        box-shadow: 0 8px 18px rgba(22, 163, 74, 0.25);
        border: none;
        cursor: pointer;
    }

    .reg-add-btn .icon {
        font-size: 14px;
    }

    .reg-add-btn:hover {
        filter: brightness(1.05);
        transform: translateY(-1px);
    }

    /* ===== TOMBOL AKSI (LIHAT / EDIT / HAPUS) ===== */
    .reg-actions {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .reg-actions form {
        margin: 0;
    }

    .reg-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        padding: 5px 11px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        text-decoration: none;
        transition: .15s ease;
        white-space: nowrap;
    }

    .reg-btn-view {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .reg-btn-view:hover {
        background: #bfdbfe;
    }

    .reg-btn-edit {
        background: #dcfce7;
        color: #15803d;
    }

    .reg-btn-edit:hover {
        background: #bbf7d0;
    }

    .reg-btn-delete {
        background: #fee2e2;
        color: #b91c1c;
    }

    .reg-btn-delete:hover {
        background: #fecaca;
    }

    .table-title {
        font-size: 22px;
        font-weight: 700;
    }

    /* ===== TABEL LIST REGISTRASI ===== */
    .reg-table {
        border-collapse: collapse;
        width: 100%;
    }

    .reg-table thead th {
        background: linear-gradient(90deg, #5ab4f9ff, #0c99eaff);
        color: #ffffff;
        font-size: 13px;
        font-weight: 700;
        text-align: center;
        padding: 12px 14px;
        letter-spacing: .2px;
        border-bottom: 0;
        white-space: nowrap;
    }

    .reg-table thead th:first-child {
        border-top-left-radius: 12px;
    }

    .reg-table thead th:last-child {
        border-top-right-radius: 12px;
    }

    .reg-table thead th+th {
        border-left: 1px solid rgba(255, 255, 255, 0.24);
    }

    .reg-table tbody td {
        padding: 10px 14px;
        font-size: 13px;
        color: #111827;
        border-bottom: 1px solid #e5e7eb;
    }

    .reg-table tbody tr:nth-child(odd) {
        background: #ffffff;
    }

    .reg-table tbody tr:nth-child(even) {
        background: #f9fafb;
    }

    .reg-table tbody tr:hover {
        background: #ecfdf3;
        transition: background 0.15s ease;
    }

    .reg-table tbody td:nth-child(1),
    .reg-table tbody td:nth-child(8),
    .reg-table tbody td:nth-child(9),
    .reg-table tbody td:nth-child(10),
    .reg-table tbody td:nth-child(11) {
        text-align: center;
    }

    .reg-foto-tim {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 14px;
        box-shadow: 0 4px 10px rgba(15, 23, 42, 0.25);
    }

    /* ===== DETAIL REGISTRASI (2 CARD ATAS + 1 CARD BAWAH) ===== */
    .reg-detail-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr);
        gap: 20px;
        align-items: flex-start;
        margin-top: 18px;
    }

    .reg-detail-card {
        background: rgba(255, 255, 255, 0.90);
        border-radius: 20px;
        padding: 18px 20px;
        border: 1px solid rgba(229, 231, 235, 0.9);
        box-shadow: 0 10px 20px rgba(15, 23, 42, 0.12);
    }

    /* card full width di bawah (Foto Tim) */
    .reg-detail-card-full {
        grid-column: 1 / -1;
        margin-top: 8px;
    }

    .reg-detail-title {
        font-size: 20px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 10px;
    }

    .reg-detail-row {
        margin-bottom: 8px;
        font-size: 13px;
    }

    .reg-detail-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #9ca3af;
        margin-bottom: 2px;
        display: block;
    }

    .reg-detail-value {
        color: #111827;
        font-weight: 500;
    }

    .reg-detail-status-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 600;
    }

    .reg-status-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .reg-status-disetujui {
        background: #dcfce7;
        color: #15803d;
    }

    .reg-status-ditolak {
        background: #fee2e2;
        color: #b91c1c;
    }

    .reg-status-selesai {
        background: #e0f2fe;
        color: #0369a1;
    }

    /* foto full di card bawah */
    .reg-foto-full {
        width: 50%;
        height: 260px;
        object-fit: cover;
        border-radius: 18px;
        margin: 10px auto;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.35);
    }

    .reg-detail-card-full {
        display: flex;
        flex-direction: column;
        align-items: center;
        /* <── ini yang bikin foto center */
    }
    .btn-ghost{
        color: #1d4ed8;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 599px;
        background: linear-gradient(90deg, #ffffffff, #64f4e0ff);
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        box-shadow: 0 8px 18px rgba(22, 163, 74, 0.25);
        border: none;
        cursor: pointer;
    }
</style>

<?php
switch ($view) {

    // ===================================================================
    // 1) LIST REGISTRASI
    // ===================================================================
    case 'list':
?>
        <section class="dash-table-card">
            <div class="container mt-4">
                <div class="dash-section-header">
                    <div class="table-title">Data Registrasi Pendakian</div>
                    <a href="dashboard.php?p=regis&view=input" class="reg-add-btn">
                        <span class="icon">＋</span>
                        <span>Tambah Registrasi</span>
                    </a>
                </div>

                <div class="dash-table-wrap" style="width:100%; overflow-x:auto; overflow-y:hidden;">
                    <table class="dash-table reg-table" style="min-width: 1400px;">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode Regis</th>
                                <th>NIK</th>
                                <th>Nama</th>
                                <th>No HP</th>
                                <th>Tanggal Naik</th>
                                <th>Tanggal Turun</th>
                                <th>Jumlah</th>
                                <th>Foto Tim</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT r.*, p.nik, p.nama_lengkap, p.no_hp 
                        FROM sania_registrasi r 
                        LEFT JOIN sania_pendaki p ON r.id_pendaki = p.id 
                        ORDER BY r.id_regis DESC";
                            $res = $koneksi->query($sql);
                            $no  = 1;

                            while ($r = $res->fetch_assoc()):
                            ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= $r['id_regis'] ?></td>
                                    <td><?= $r['nik'] ?></td>
                                    <td><?= $r['nama_lengkap'] ?></td>
                                    <td><?= $r['no_hp'] ?></td>
                                    <td><?= date('d-m-Y H:i', strtotime($r['tgl_naik'])) ?></td>
                                    <td><?= date('d-m-Y H:i', strtotime($r['tgl_turun'])) ?></td>
                                    <td><?= $r['jumlah_anggota'] ?></td>
                                    <td>
                                        <?php if (!empty($r['foto_tim'])): ?>
                                            <img src="<?= htmlspecialchars($r['foto_tim']) ?>"
                                                alt="Foto Tim"
                                                class="reg-foto-tim">
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($r['status'] == "Selesai"): ?>
                                            <span class="badge-status badge-selesai">Selesai</span>
                                        <?php elseif ($r['status'] == "Disetujui"): ?>
                                            <span class="badge-status badge-proses">Sedang Mendaki</span>
                                        <?php else: ?>
                                            <span class="badge-status badge-belum">Belum</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="reg-actions">
                                            <a href="dashboard.php?p=regis&view=detail&id=<?= $r['id_regis'] ?>"
                                                class="reg-btn reg-btn-view">
                                                👁 Lihat
                                            </a>

                                            <a href="dashboard.php?p=regis&view=edit&id=<?= $r['id_regis'] ?>"
                                                class="reg-btn reg-btn-edit">
                                                ✏️ Edit
                                            </a>

                                            <form action="admin/proses.php" method="POST"
                                                onsubmit="return confirm('Yakin ingin menghapus registrasi #<?= $r['id_regis'] ?>?');">
                                                <input type="hidden" name="aksi" value="hapus_registrasi">
                                                <input type="hidden" name="id_regis" value="<?= $r['id_regis'] ?>">
                                                <button type="submit" class="reg-btn reg-btn-delete">
                                                    🗑 Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    <?php
        break;

    // ===================================================================
    // 2) FORM EDIT REGISTRASI
    // ===================================================================
    case 'edit':

        $id = $_GET['id'] ?? 0;
        $q  = $koneksi->query("SELECT r.*, p.nama_lengkap, p.nik 
                               FROM sania_registrasi r 
                               LEFT JOIN sania_pendaki p ON r.id_pendaki = p.id 
                               WHERE r.id_regis = $id");
        $data = $q->fetch_assoc();
    ?>
        <section class="dash-table-card">

            <div class="dash-section-header">
                <div>
                    <div class="dash-section-title">Edit Registrasi</div>
                    <div class="dash-section-sub">Ubah data registrasi pendakian.</div>
                </div>
            </div>

            <form action="admin/proses.php" method="POST" class="dash-form dash-form-regis">
                <input type="hidden" name="aksi" value="update_registrasi">
                <input type="hidden" name="id_regis" value="<?= $data['id_regis'] ?>">

                <div class="dash-form-group dash-form-full">
                    <label>Pendaki</label>
                    <input type="text" class="form-control"
                        value="<?= htmlspecialchars($data['nama_lengkap'] . ' (' . $data['nik'] . ')') ?>"
                        readonly>
                    <input type="hidden" name="id_regis" value="<?= $data['id_regis'] ?>">
                </div>

                <div class="dash-form-group">
                    <label for="tgl_naik">Tanggal Naik</label>
                    <input id="tgl_naik" type="datetime-local" name="tgl_naik"
                        value="<?= date('Y-m-d\TH:i', strtotime($data['tgl_naik'])) ?>">
                </div>

                <div class="dash-form-group">
                    <label for="tgl_turun">Tanggal Turun</label>
                    <input id="tgl_turun" type="datetime-local" name="tgl_turun"
                        value="<?= date('Y-m-d\TH:i', strtotime($data['tgl_turun'])) ?>">
                </div>

                <div class="dash-form-group">
                    <label for="jumlah_anggota">Jumlah Anggota</label>
                    <input id="jumlah_anggota" type="number" name="jumlah_anggota"
                        value="<?= $data['jumlah_anggota'] ?>">
                </div>

                <div class="dash-form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <?php
                        $stat = ['Pending', 'Disetujui', 'Ditolak', 'Selesai'];
                        foreach ($stat as $s):
                            $sel = ($s == $data['status']) ? 'selected' : '';
                        ?>
                            <option value="<?= $s ?>" <?= $sel ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if (!empty($data['foto_tim'])): ?>
                    <div class="dash-form-group dash-form-full">
                        <label>Foto Tim</label>
                        <img src="<?= htmlspecialchars($data['foto_tim']) ?>"
                            alt="Foto Tim"
                            style="max-width:250px;border-radius:12px;">
                    </div>
                <?php endif; ?>

                <div class="dash-form-actions dash-form-full">
                    <button type="submit" class="btn-primary">Update</button>
                    <a href="dashboard.php?p=regis" class="btn-ghost">Batal</a>
                </div>

            </form>

        </section>
    <?php
        break;

    // ===================================================================
    // 1.5) DETAIL REGISTRASI
    // ===================================================================
    case 'detail':

        $id = (int)($_GET['id'] ?? 0);

        $q = $koneksi->query("
            SELECT r.*, 
                   p.nik, p.nama_lengkap, p.usia, p.no_hp AS hp_pendaki, p.alamat
            FROM sania_registrasi r
            LEFT JOIN sania_pendaki p ON r.id_pendaki = p.id
            WHERE r.id_regis = $id
        ");
        $data = $q->fetch_assoc();
        if (!$data) {
            echo "<p>Data tidak ditemukan.</p>";
            break;
        }

        $status      = $data['status'];
        $statusClass = 'reg-status-pending';
        if ($status === 'Disetujui')  $statusClass = 'reg-status-disetujui';
        elseif ($status === 'Ditolak')  $statusClass = 'reg-status-ditolak';
        elseif ($status === 'Selesai')  $statusClass = 'reg-status-selesai';
    ?>
        <section class="dash-table-card">
            <div class="dash-section-header">
                <div>
                    <div class="dash-section-title">Detail Registrasi Pendakian</div>
                    <div class="dash-section-sub">
                        Informasi lengkap pendaki dan data registrasi.
                    </div>
                </div>
                <a href="dashboard.php?p=regis" class="btn-ghost">
                    Kembali ke Data Registrasi
                </a>
            </div>

            <div class="reg-detail-grid">
                <!-- DATA PENDAKI -->
                <div class="reg-detail-card">
                    <div class="reg-detail-title">Data Pendaki</div>

                    <div class="reg-detail-row">
                        <span class="reg-detail-label">Nama</span>
                        <span class="reg-detail-value"><?= htmlspecialchars($data['nama_lengkap']) ?></span>
                    </div>

                    <div class="reg-detail-row">
                        <span class="reg-detail-label">NIK</span>
                        <span class="reg-detail-value"><?= htmlspecialchars($data['nik']) ?></span>
                    </div>

                    <div class="reg-detail-row">
                        <span class="reg-detail-label">Umur</span>
                        <span class="reg-detail-value"><?= (int)$data['usia'] ?> tahun</span>
                    </div>

                    <div class="reg-detail-row">
                        <span class="reg-detail-label">No HP</span>
                        <span class="reg-detail-value"><?= htmlspecialchars($data['hp_pendaki']) ?></span>
                    </div>

                    <div class="reg-detail-row">
                        <span class="reg-detail-label">Alamat</span>
                        <span class="reg-detail-value">
                            <?= nl2br(htmlspecialchars($data['alamat'])) ?>
                        </span>
                    </div>
                </div>

                <!-- DATA REGISTRASI -->
                <div class="reg-detail-card">
                    <div class="reg-detail-title">Data Registrasi</div>

                    <div class="reg-detail-row">
                        <span class="reg-detail-label">Kode Registrasi</span>
                        <span class="reg-detail-value">#<?= $data['id_regis'] ?></span>
                    </div>

                    <div class="reg-detail-row">
                        <span class="reg-detail-label">Tanggal Naik</span>
                        <span class="reg-detail-value">
                            <?= date('d-m-Y H:i', strtotime($data['tgl_naik'])) ?>
                        </span>
                    </div>

                    <div class="reg-detail-row">
                        <span class="reg-detail-label">Tanggal Turun</span>
                        <span class="reg-detail-value">
                            <?= date('d-m-Y H:i', strtotime($data['tgl_turun'])) ?>
                        </span>
                    </div>

                    <div class="reg-detail-row">
                        <span class="reg-detail-label">Jumlah Anggota</span>
                        <span class="reg-detail-value"><?= (int)$data['jumlah_anggota'] ?></span>
                    </div>

                    <div class="reg-detail-row">
                        <span class="reg-detail-label">Status</span>
                        <span class="reg-detail-value">
                            <span class="reg-detail-status-badge <?= $statusClass ?>">
                                <?= htmlspecialchars($status) ?>
                            </span>
                        </span>
                    </div>
                </div>

                <!-- FOTO TIM : FULL WIDTH -->
                <div class="reg-detail-card reg-detail-card-full">
                    <div class="reg-detail-title">Foto Tim</div>
                    <?php if (!empty($data['foto_tim'])): ?>
                        <img src="<?= htmlspecialchars($data['foto_tim']) ?>"
                            class="reg-foto-full"
                            alt="Foto Tim">
                    <?php else: ?>
                        <p style="font-size:13px;color:#6b7280;">
                            - Tidak ada foto tim diunggah.
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php
        break;

    // ===================================================================
    // 3) FORM INPUT REGISTRASI BARU
    // ===================================================================
    case 'input':
    ?>
        <section class="dash-table-card">

            <div class="dash-section-header">
                <div>
                    <div class="dash-section-title">Input Registrasi Pendaki</div>
                    <div class="dash-section-sub">Tambahkan data registrasi pendakian baru.</div>
                </div>
            </div>

            <form action="admin/proses.php" method="POST" class="dash-form dash-form-regis">
                <input type="hidden" name="aksi" value="insert_registrasi">

                <div class="dash-form-group dash-form-full">
                    <label for="id_pendaki">Pilih Pendaki</label>
                    <select id="id_pendaki" name="id_pendaki">
                        <?php
                        $pendaki = $koneksi->query("SELECT * FROM sania_pendaki ORDER BY nama_lengkap");
                        while ($p = $pendaki->fetch_assoc()):
                        ?>
                            <option value="<?= $p['id'] ?>">
                                <?= $p['nama_lengkap'] ?> (<?= $p['nik'] ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="dash-form-group">
                    <label for="tgl_naik">Tanggal Naik</label>
                    <input id="tgl_naik" type="datetime-local" name="tgl_naik">
                </div>

                <div class="dash-form-group">
                    <label for="tgl_turun">Tanggal Turun</label>
                    <input id="tgl_turun" type="datetime-local" name="tgl_turun">
                </div>

                <div class="dash-form-group">
                    <label for="jumlah_anggota">Jumlah Anggota</label>
                    <input id="jumlah_anggota" type="number" name="jumlah_anggota">
                </div>

                <div class="dash-form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option>Pending</option>
                        <option>Disetujui</option>
                        <option>Ditolak</option>
                        <option>Selesai</option>
                    </select>
                </div>

                <div class="dash-form-actions dash-form-full">
                    <button type="submit" class="btn-primary">Simpan</button>
                    <a href="dashboard.php?p=regis" class="btn-ghost">Batal</a>
                </div>

            </form>

        </section>
<?php
        break;
}
?>