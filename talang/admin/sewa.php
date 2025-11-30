<?php
// File: admin/sewa.php
// dipanggil dari dashboard.php (case 'penyewa')

include 'koneksi.php';

$view     = $_GET['view'] ?? 'rekap';
$regis_id = (int)($_GET['regis_id'] ?? 0);
?>

<style>
    /* styling kecil khusus halaman sewa */
    .sewa-badge {
        padding: 3px 8px;
        border-radius: 999px;
        font-size: 11px;
        display: inline-block;
        white-space: nowrap;
    }

    .sewa-registrasi-menunggu {
        background: #fef3c7;
        color: #92400e;
    }

    .sewa-registrasi-disetujui {
        background: #dcfce7;
        color: #166534;
    }

    .sewa-registrasi-ditolak {
        background: #fee2e2;
        color: #b91c1c;
    }

    .sewa-bayar-belum_bayar {
        background: #e5e7eb;
        color: #374151;
    }

    .sewa-bayar-menunggu_verifikasi {
        background: #fef3c7;
        color: #92400e;
    }

    .sewa-bayar-lunas {
        background: #dcfce7;
        color: #166534;
    }

    .sewa-bayar-ditolak {
        background: #fee2e2;
        color: #b91c1c;
    }

    .sewa-btn {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
    }

    .sewa-btn-lunas {
        background: #16a34a;
        color: #fff;
        margin-right: 4px;
    }

    .sewa-btn-lunas:hover {
        background: #15803d;
    }

    .sewa-btn-tolak {
        background: #ef4444;
        color: #fff;
    }

    .sewa-btn-tolak:hover {
        background: #dc2626;
    }

    .sewa-btn-bukti {
        background: #3b82f6;
        color: #fff;
    }

    .sewa-btn-bukti:hover {
        background: #2563eb;
    }

    .sewa-btn-back {
        background: #e5e7eb;
        color: #111827;
    }

    .sewa-btn-back:hover {
        background: #d1d5db;
    }
</style>

<?php
/* ==========================================================
   VIEW 1: REKAP PER REGISTRASI
   ========================================================== */
if ($view === 'rekap') {

    $sql_group = "
        SELECT 
            p.nama_lengkap,
            r.id_regis,
            r.status AS status_registrasi,
            COUNT(sa.sewa_id)        AS jml_item,
            SUM(sa.jumlah)           AS total_barang,
            SUM(sa.harga_total)      AS total_tagihan,
            SUM(CASE WHEN sa.status_pembayaran = 'lunas' THEN 1 ELSE 0 END) AS cnt_lunas,
            SUM(CASE WHEN sa.status_pembayaran IN ('belum_bayar','menunggu_verifikasi') THEN 1 ELSE 0 END) AS cnt_belum,
            SUM(CASE WHEN sa.status_pembayaran = 'ditolak' THEN 1 ELSE 0 END) AS cnt_tolak
        FROM sewa_alat sa
        JOIN sania_registrasi r ON sa.registrasi_id = r.id_regis
        JOIN sania_pendaki   p ON r.id_pendaki     = p.id
        GROUP BY r.id_regis, p.nama_lengkap, r.status
        ORDER BY r.id_regis DESC
    ";
    $groupData = $koneksi->query($sql_group);
?>

    <section class="dash-table-card">
        <div class="dash-section-header">
            <div>
                <div class="dash-section-title">Rekap Sewa per Registrasi</div>
                <div class="dash-section-sub">
                    Ringkasan total sewa per pendakian (per kode registrasi).
                </div>
            </div>
        </div>

        <div class="dash-table-wrap">
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>Regis</th>
                        <th>Nama Pendaki</th>
                        <th>Status Registrasi</th>
                        <th>Jumlah Item Sewa</th>
                        <th>Total Barang</th>
                        <th>Total Tagihan</th>
                        <th>Status Pembayaran</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$groupData || $groupData->num_rows == 0): ?>
                        <tr>
                            <td colspan="8" style="text-align:center;padding:14px 0;color:#6b7280;">
                                Belum ada data sewa per registrasi.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php while ($g = $groupData->fetch_assoc()): ?>
                            <?php
                            // status registrasi
                            $status_reg = $g['status_registrasi'];
                            if ($status_reg === 'Disetujui') {
                                $regClass = 'sewa-registrasi-disetujui';
                            } elseif ($status_reg === 'Ditolak') {
                                $regClass = 'sewa-registrasi-ditolak';
                            } else {
                                $regClass = 'sewa-registrasi-menunggu';
                            }

                            // status pembayaran gabungan
                            $cnt_lunas = (int)$g['cnt_lunas'];
                            $cnt_belum = (int)$g['cnt_belum'];
                            $cnt_tolak = (int)$g['cnt_tolak'];

                            if ($cnt_belum > 0) {
                                $payStatus = 'Belum Lunas';
                                $payClass  = 'sewa-bayar-belum_bayar';
                            } elseif ($cnt_lunas > 0 && $cnt_belum == 0 && $cnt_tolak == 0) {
                                $payStatus = 'Lunas';
                                $payClass  = 'sewa-bayar-lunas';
                            } elseif ($cnt_tolak > 0 && $cnt_lunas == 0 && $cnt_belum == 0) {
                                $payStatus = 'Ditolak';
                                $payClass  = 'sewa-bayar-ditolak';
                            } else {
                                $payStatus = '-';
                                $payClass  = '';
                            }
                            ?>
                            <tr>
                                <td>#<?= $g['id_regis']; ?></td>
                                <td><?= htmlspecialchars($g['nama_lengkap']); ?></td>
                                <td>
                                    <span class="sewa-badge <?= $regClass; ?>">
                                        <?= htmlspecialchars($status_reg); ?>
                                    </span>
                                </td>
                                <td><?= (int)$g['jml_item']; ?></td>
                                <td><?= (int)$g['total_barang']; ?></td>
                                <td>Rp <?= number_format($g['total_tagihan'], 0, ',', '.'); ?></td>
                                <td>
                                    <?php if ($payClass): ?>
                                        <span class="sewa-badge <?= $payClass; ?>">
                                            <?= htmlspecialchars($payStatus); ?>
                                        </span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="dashboard.php?p=penyewa&view=detail&regis_id=<?= $g['id_regis']; ?>"
                                        class="sewa-btn sewa-btn-bukti">
                                        Lihat Detail
                                    </a>
                                    <a href="admin/tiket.php?&regis_id=<?= $g['id_regis']; ?>"
                                        target="_blank"
                                        class="sewa-btn sewa-btn-lunas">
                                        Cetak Tiket
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

<?php
    /* ==========================================================
   VIEW 2: DETAIL SEWA UNTUK 1 REGISTRASI
   ========================================================== */
} elseif ($view === 'detail' && $regis_id > 0) {

    $sqlDetail = "
        SELECT 
            sa.sewa_id,
            sa.jumlah,
            sa.hari,
            sa.harga_total,
            sa.status_pembayaran,
            sa.bukti_pembayaran,
            r.id_regis,
            r.status AS status_registrasi,
            p.nama_lengkap,
            a.nama AS nama_alat
        FROM sewa_alat sa
        JOIN sania_registrasi r ON sa.registrasi_id = r.id_regis
        JOIN sania_pendaki   p ON r.id_pendaki   = p.id
        JOIN alat            a ON sa.alat_id     = a.alat_id
        WHERE r.id_regis = $regis_id
        ORDER BY sa.sewa_id DESC
    ";
    $detailData = $koneksi->query($sqlDetail);

    // ambil satu baris untuk header
    $header = $detailData && $detailData->num_rows > 0 ? $detailData->fetch_assoc() : null;

    // kalau kita ambil satu baris, reset pointer supaya tetap ke-loop
    if ($header) {
        $detailData->data_seek(0);
    }
?>

    <section class="dash-table-card">
        <div class="dash-section-header">
            <div>
                <div class="dash-section-title">
                    Detail Sewa Peralatan – Regis #<?= $regis_id; ?>
                </div>
                <div class="dash-section-sub">
                    <?php if ($header): ?>
                        Pendaki: <?= htmlspecialchars($header['nama_lengkap']); ?>,
                        Status Registrasi: <?= htmlspecialchars($header['status_registrasi']); ?>
                    <?php else: ?>
                        Data sewa tidak ditemukan.
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <a href="dashboard.php?p=penyewa"
                    class="sewa-btn sewa-btn-back">
                    ← Kembali ke Rekap
                </a>
            </div>
        </div>

        <div class="dash-table-wrap" style="width:100%; overflow-x:auto;">
            <table class="dash-table" style="min-width: 900px;">
                <thead>
                    <tr>
                        <th>ID Sewa</th>
                        <th>Alat</th>
                        <th>Jumlah</th>
                        <th>Hari</th>
                        <th>Total</th>
                        <th>Status Pembayaran</th>
                        <th>Bukti</th>
                        <th style="width:150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$detailData || $detailData->num_rows == 0): ?>
                        <tr>
                            <td colspan="8" style="text-align:center; padding:16px 0; color:#6b7280;">
                                Belum ada data sewa untuk registrasi ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php while ($row = $detailData->fetch_assoc()): ?>
                            <?php
                            $sp       = $row['status_pembayaran'];
                            $payClass = 'sewa-bayar-' . $sp;
                            ?>
                            <tr>
                                <td>#<?= $row['sewa_id']; ?></td>
                                <td><?= htmlspecialchars($row['nama_alat']); ?></td>
                                <td><?= (int)$row['jumlah']; ?></td>
                                <td><?= (int)$row['hari']; ?></td>
                                <td>Rp <?= number_format($row['harga_total'], 0, ',', '.'); ?></td>
                                <td>
                                    <span class="sewa-badge <?= $payClass; ?>">
                                        <?= htmlspecialchars($sp); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($row['bukti_pembayaran'])): ?>
                                        <a class="sewa-btn sewa-btn-bukti"
                                            href="../bukti/<?= htmlspecialchars($row['bukti_pembayaran']); ?>"
                                            target="_blank">
                                            Lihat
                                        </a>
                                    <?php else: ?>
                                        <span style="font-size:11px; color:#6b7280;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($sp !== 'lunas'): ?>
                                        <a class="sewa-btn sewa-btn-lunas"
                                            href="dashboard.php?p=pembayaran&sewa_id=<?= $row['sewa_id']; ?>&aksi=lunas"
                                            onclick="return confirm('Set pembayaran sewa #<?= $row['sewa_id']; ?> menjadi LUNAS?');">
                                            Set Lunas
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($sp !== 'ditolak'): ?>
                                        <a class="sewa-btn sewa-btn-tolak"
                                            href="dashboard.php?p=pembayaran&sewa_id=<?= $row['sewa_id']; ?>&aksi=tolak"
                                            onclick="return confirm('Tolak pembayaran sewa #<?= $row['sewa_id']; ?>?');">
                                            Tolak
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

<?php
    // view tidak dikenal
} else {
    echo "<p style='padding:20px;'>View tidak dikenal.</p>";
}
?>