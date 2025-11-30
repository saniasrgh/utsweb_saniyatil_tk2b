<?php
// File: public/riwayat.php
// Dipanggil dari dashboard.php (koneksi & session sudah ada)

if (!isset($koneksi) || !($koneksi instanceof mysqli)) {
    include 'koneksi.php';
}

if (!isset($userId)) {
    // fallback kalau file dipanggil langsung (harusnya tidak)
    session_start();
    $userId = (int)($_SESSION['user_id'] ?? 0);
}

if ($userId <= 0) {
    header('Location: login.php');
    exit;
}

// =====================================================
// 1. Ambil id pendaki berdasarkan user_id
// =====================================================
$pendaki_id = 0;

$stmt = $koneksi->prepare("SELECT id, nama_lengkap FROM sania_pendaki WHERE user_id = ? LIMIT 1");
$stmt->bind_param("i", $userId);
$stmt->execute();
$resPendaki = $stmt->get_result();
$pendakiRow = $resPendaki->fetch_assoc();
$stmt->close();

if ($pendakiRow) {
    $pendaki_id   = (int)$pendakiRow['id'];
    $namaPendaki  = $pendakiRow['nama_lengkap'];
} else {
    $pendaki_id  = 0;
    $namaPendaki = $namaLogin ?? 'Pendaki';
}

// =====================================================
// 2. RIWAYAT PENDAKIAN (registrasi) PUNYA PENGGUNA INI
// =====================================================
$regRows = [];
if ($pendaki_id > 0) {
    $sqlReg = "
        SELECT id_regis, tgl_naik, tgl_turun, status
        FROM sania_registrasi
        WHERE id_pendaki = {$pendaki_id}
        ORDER BY id_regis DESC
    ";
    $qReg = $koneksi->query($sqlReg);
    if ($qReg) {
        while ($row = $qReg->fetch_assoc()) {
            $regRows[] = $row;
        }
    }
}

// =====================================================
// 3. RIWAYAT SEWA ALAT USER (join ke registrasi + alat)
// =====================================================
$sewaRows = [];
if ($pendaki_id > 0) {
    $sqlSewa = "
        SELECT 
            sa.sewa_id,
            sa.jumlah,
            sa.hari,
            sa.harga_total,
            sa.status_pembayaran,
            sa.bukti_pembayaran,
            r.id_regis,
            r.tgl_naik,
            r.tgl_turun,
            r.status AS status_registrasi,
            a.nama   AS nama_alat
        FROM sewa_alat sa
        JOIN sania_registrasi r ON sa.registrasi_id = r.id_regis
        JOIN alat a             ON sa.alat_id       = a.alat_id
        WHERE r.id_pendaki = {$pendaki_id}
        ORDER BY sa.sewa_id DESC
    ";
    $qSewa = $koneksi->query($sqlSewa);
    if ($qSewa) {
        while ($row = $qSewa->fetch_assoc()) {
            $sewaRows[] = $row;
        }
    }
}
?>

<section class="dash-table-card">
    <div class="dash-section-header">
        <div>
            <div class="dash-section-title">Riwayat Pendakian</div>
            <div class="dash-section-sub">
                Semua registrasi pendakian yang pernah kamu lakukan.
            </div>
        </div>
    </div>

    <div class="dash-table-wrap">
        <table class="dash-table">
            <thead>
                <tr>
                    <th style="width:60px;">No</th>
                    <th>Tanggal Naik</th>
                    <th>Tanggal Turun</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($pendaki_id == 0): ?>
                    <tr>
                        <td colspan="4" style="text-align:center;padding:14px 0;color:#6b7280;">
                            Kamu belum mengisi data pendaki. Silakan daftar pendakian terlebih dahulu.
                        </td>
                    </tr>
                <?php elseif (empty($regRows)): ?>
                    <tr>
                        <td colspan="4" style="text-align:center;padding:14px 0;color:#6b7280;">
                            Belum ada riwayat pendakian.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php
                    $no = 1;
                    foreach ($regRows as $row):
                        $tglNaik  = $row['tgl_naik']  ? date('d-m-Y H:i', strtotime($row['tgl_naik']))   : '-';
                        $tglTurun = $row['tgl_turun'] ? date('d-m-Y H:i', strtotime($row['tgl_turun'])) : '-';
                        $status   = $row['status'] ?? '-';

                        $badgeClass = 'dash-badge-lain';
                        if ($status === 'Disetujui') $badgeClass = 'dash-badge-aktif';
                        elseif ($status === 'Selesai') $badgeClass = 'dash-badge-selesai';
                    ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($tglNaik) ?></td>
                            <td><?= htmlspecialchars($tglTurun) ?></td>
                            <td>
                                <span class="dash-badge <?= $badgeClass ?>">
                                    <?= htmlspecialchars($status) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="dash-table-card" style="margin-top:24px;">
    <div class="dash-section-header">
        <div>
            <div class="dash-section-title">Riwayat Sewa Peralatan</div>
            <div class="dash-section-sub">
                Semua transaksi sewa peralatan yang terkait dengan pendakianmu.
            </div>
        </div>
    </div>

    <div class="dash-table-wrap">
        <table class="dash-table">
            <thead>
                <tr>
                    <th style="width:60px;">No</th>
                    <th>ID Sewa</th>
                    <th>Regis</th>
                    <th>Tgl Naik</th>
                    <th>Alat</th>
                    <th>Jumlah</th>
                    <th>Hari</th>
                    <th>Total</th>
                    <th>Status Pembayaran</th>
                    <th>Bukti</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($pendaki_id == 0): ?>
                    <tr>
                        <td colspan="10" style="text-align:center;padding:14px 0;color:#6b7280;">
                            Kamu belum mengisi data pendaki, jadi belum ada data sewa.
                        </td>
                    </tr>
                <?php elseif (empty($sewaRows)): ?>
                    <tr>
                        <td colspan="10" style="text-align:center;padding:14px 0;color:#6b7280;">
                            Belum ada riwayat sewa peralatan.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php
                    $no = 1;
                    foreach ($sewaRows as $s):
                        $tglNaik = $s['tgl_naik'] ? date('d-m-Y H:i', strtotime($s['tgl_naik'])) : '-';
                        $statusPay = $s['status_pembayaran'] ?? '-';
                        $badgePay = 'b-bayar-belum_bayar';
                        if ($statusPay === 'menunggu_verifikasi') $badgePay = 'b-bayar-menunggu_verifikasi';
                        elseif ($statusPay === 'lunas') $badgePay = 'b-bayar-lunas';
                        elseif ($statusPay === 'ditolak') $badgePay = 'b-bayar-ditolak';
                    ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>#<?= $s['sewa_id']; ?></td>
                            <td>#<?= $s['id_regis']; ?></td>
                            <td><?= htmlspecialchars($tglNaik); ?></td>
                            <td><?= htmlspecialchars($s['nama_alat']); ?></td>
                            <td><?= (int)$s['jumlah']; ?></td>
                            <td><?= (int)$s['hari']; ?></td>
                            <td>Rp <?= number_format($s['harga_total'], 0, ',', '.'); ?></td>
                            <td>
                                <span class="badge <?= $badgePay; ?>">
                                    <?= htmlspecialchars($statusPay); ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($s['bukti_pembayaran'])): ?>
                                    <a href="bukti/<?= htmlspecialchars($s['bukti_pembayaran']); ?>" target="_blank">
                                        Lihat
                                    </a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>