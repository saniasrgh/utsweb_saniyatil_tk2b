<?php
include 'koneksi.php';

// ==================================================
// 1. PROSES AKSI VERIFIKASI (LUNAS / TOLAK)
// ==================================================
if (isset($_GET['aksi']) && isset($_GET['sewa_id'])) {
    $sewa_id = (int) $_GET['sewa_id'];
    $aksi    = $_GET['aksi'];

    if ($aksi == 'lunas') {
        $status = 'lunas';
        $msg    = 'Pembayaran berhasil diset LUNAS';
    } elseif ($aksi == 'tolak') {
        $status = 'ditolak';
        $msg    = 'Pembayaran DITOLAK';
    } else {
        $status = '';
        $msg    = 'Aksi tidak dikenal';
    }

    if ($status != '') {
        mysqli_query($koneksi, "
            UPDATE sewa_alat 
            SET status_pembayaran='$status'
            WHERE sewa_id='$sewa_id'
        ");
    }

    echo "<script>
        alert('$msg');
        window.location='dashboard.php?p=pembayaran';
    </script>";
    exit;
}

// ==================================================
// 2. AMBIL DATA SEWA + ALAT UNTUK DITAMPILKAN
// ==================================================
$sql = "SELECT sa.*, a.nama 
        FROM sewa_alat sa
        JOIN alat a ON sa.alat_id = a.alat_id
        ORDER BY sa.sewa_id DESC";

$q = mysqli_query($koneksi, $sql);
?>

<style>
    /* Hanya style lokal, jangan sentuh body/font lagi */

    .pay-badge {
        padding: 3px 8px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 600;
        color: #fff;
        text-transform: lowercase;
    }

    .pay-b-belum_bayar {
        background: #6b7280;
    }

    .pay-b-menunggu_verifikasi {
        background: #facc15;
        color: #111827;
    }

    .pay-b-lunas {
        background: #16a34a;
    }

    .pay-b-ditolak {
        background: #dc2626;
    }

    .pay-btn {
        display: inline-block;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
    }

    .pay-btn-lunas {
        background: #16a34a;
        color: #f9fafb;
    }

    .pay-btn-tolak {
        background: #dc2626;
        color: #f9fafb;
    }

    .pay-btn-bukti {
        background: #2563eb;
        color: #f9fafb;
    }

    .pay-btn+.pay-btn {
        margin-left: 6px;
    }
</style>

<section class="dash-section">
    <div class="dash-table-card">
        <div class="dash-section-header">
            <div>
                <div class="dash-section-title">Kelola Pembayaran Sewa</div>
                <div class="dash-section-sub">
                    Pantau status pembayaran sewa peralatan camp.
                </div>
            </div>
        </div>

        <div class="dash-table-wrap">
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>ID Sewa</th>
                        <th>Nama Alat</th>
                        <th>Jumlah</th>
                        <th>Hari</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Bukti</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($r = mysqli_fetch_assoc($q)) { ?>
                        <tr>
                            <td><?= (int)$r['sewa_id']; ?></td>
                            <td><?= htmlspecialchars($r['nama']); ?></td>
                            <td><?= (int)$r['jumlah']; ?></td>
                            <td><?= (int)$r['hari']; ?></td>
                            <td>Rp <?= number_format($r['harga_total'], 0, ',', '.'); ?></td>
                            <td>
                                <?php
                                $status = $r['status_pembayaran'];
                                $class  = 'pay-b-' . $status;
                                ?>
                                <span class="pay-badge <?= $class; ?>">
                                    <?= htmlspecialchars($status); ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($r['bukti_pembayaran'])) { ?>
                                    <a class="pay-btn pay-btn-bukti"
                                        href="bukti/<?= htmlspecialchars($r['bukti_pembayaran']); ?>"
                                        target="_blank">
                                        Lihat
                                    </a>
                                <?php } else { ?>
                                    -
                                <?php } ?>
                            </td>
                            <td>
                                <?php if ($status != 'lunas') { ?>
                                    <a class="pay-btn pay-btn-lunas"
                                        href="dashboard.php?p=pembayaran&sewa_id=<?= (int)$r['sewa_id']; ?>&aksi=lunas"
                                        onclick="return confirm('Set pembayaran sewa #<?= (int)$r['sewa_id']; ?> menjadi LUNAS?');">
                                        Set Lunas
                                    </a>

                                    <a class="pay-btn pay-btn-tolak"
                                        href="dashboard.php?p=pembayaran&sewa_id=<?= (int)$r['sewa_id']; ?>&aksi=tolak"
                                        onclick="return confirm('Tolak pembayaran sewa #<?= (int)$r['sewa_id']; ?>?');">
                                        Tolak
                                    </a>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</section>