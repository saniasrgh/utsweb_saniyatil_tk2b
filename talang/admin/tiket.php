<?php
// admin/tiket.php
include '../koneksi.php';

$regis_id = (int)($_GET['regis_id'] ?? 0);
if ($regis_id <= 0) {
  echo "ID registrasi tidak valid.";
  exit;
}

// ==================================================
// 1. AMBIL DATA REGISTRASI + PENDAKI
// ==================================================
$sqlReg = "
    SELECT 
        r.*,
        p.nama_lengkap,
        p.nik,
        p.no_hp,
        p.alamat
    FROM sania_registrasi r
    JOIN sania_pendaki p ON r.id_pendaki = p.id
    WHERE r.id_regis = $regis_id
    LIMIT 1
";
$qReg = $koneksi->query($sqlReg);
$reg  = $qReg ? $qReg->fetch_assoc() : null;

if (!$reg) {
  echo "Data registrasi tidak ditemukan.";
  exit;
}

// biaya registrasi 25.000 / orang
$jumlah_anggota = (int)($reg['jumlah_anggota'] ?? 0);
$biaya_registrasi = $jumlah_anggota * 25000;

// ==================================================
// 2. AMBIL DATA SEWA UNTUK REGIS INI
// ==================================================
$sqlSewa = "
    SELECT 
        sa.*,
        a.nama AS nama_alat
    FROM sewa_alat sa
    JOIN alat a ON sa.alat_id = a.alat_id
    WHERE sa.registrasi_id = $regis_id
";
$qSewa = $koneksi->query($sqlSewa);

$items = [];
$total_sewa = 0;
$cnt_lunas = $cnt_belum = $cnt_tolak = 0;

if ($qSewa) {
  while ($row = $qSewa->fetch_assoc()) {
    $items[] = $row;
    $total_sewa += (int)$row['harga_total'];

    if ($row['status_pembayaran'] === 'lunas') {
      $cnt_lunas++;
    } elseif (in_array($row['status_pembayaran'], ['belum_bayar', 'menunggu_verifikasi'])) {
      $cnt_belum++;
    } elseif ($row['status_pembayaran'] === 'ditolak') {
      $cnt_tolak++;
    }
  }
}

// total akhir (registrasi + semua sewa)
$total_tagihan = $biaya_registrasi + $total_sewa;

// status pembayaran keseluruhan
if ($cnt_belum > 0) {
  $status_bayar = 'Belum Lunas';
} elseif ($cnt_lunas > 0 && $cnt_belum == 0 && $cnt_tolak == 0) {
  $status_bayar = 'Lunas';
} elseif ($cnt_tolak > 0 && $cnt_lunas == 0 && $cnt_belum == 0) {
  $status_bayar = 'Ditolak';
} else {
  $status_bayar = '-';
}

$metode_bayar = $reg['metode_bayar'] ?? '-';
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Tiket Pembayaran #<?= $regis_id; ?></title>
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      background: #e5e7eb;
      margin: 0;
      padding: 30px 0;
    }

    .ticket-wrapper {
      width: 900px;
      margin: 0 auto;
      background: #f9fafb;
      border-radius: 18px;
      box-shadow: 0 18px 40px rgba(15, 23, 42, 0.22);
      padding: 26px 32px 30px;
    }

    .ticket-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 18px;
    }

    .ticket-title {
      font-size: 22px;
      font-weight: 800;
      color: #111827;
      margin-bottom: 6px;
    }

    .ticket-sub {
      font-size: 13px;
      color: #6b7280;
    }

    .ticket-meta {
      text-align: right;
      font-size: 12px;
    }

    .status-chip {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 999px;
      font-size: 11px;
      font-weight: 600;
      background: #dcfce7;
      color: #166534;
      margin-bottom: 4px;
    }

    .ticket-section-title {
      font-size: 14px;
      font-weight: 700;
      margin-bottom: 6px;
      color: #111827;
    }

    .ticket-grid {
      display: grid;
      grid-template-columns: 1.2fr 1.2fr;
      gap: 24px;
      font-size: 13px;
      margin-bottom: 16px;
    }

    .ticket-label {
      font-weight: 600;
      color: #374151;
    }

    .ticket-value {
      color: #111827;
    }

    table {
      border-collapse: collapse;
      width: 100%;
      margin-top: 6px;
      font-size: 13px;
    }

    th,
    td {
      border-bottom: 1px solid #e5e7eb;
      padding: 6px 8px;
      text-align: left;
    }

    th {
      background: #f3f4f6;
      font-weight: 600;
      color: #374151;
    }

    tfoot td {
      font-weight: 700;
    }

    .ticket-footer {
      font-size: 11px;
      color: #6b7280;
      margin-top: 14px;
    }

    .btn-print {
      margin-top: 18px;
      padding: 9px 20px;
      border-radius: 999px;
      border: none;
      background: #16a34a;
      color: white;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
    }

    .btn-print:hover {
      background: #15803d;
    }

    @media print {
      body {
        background: white;
        padding: 0;
      }

      .ticket-wrapper {
        box-shadow: none;
        border-radius: 0;
        width: 100%;
      }

      .btn-print {
        display: none;
      }
    }
  </style>
</head>

<body>
  <div class="ticket-wrapper">
    <div class="ticket-header">
      <div>
        <div class="ticket-title">Tiket Pembayaran Pendakian</div>
        <div class="ticket-sub">
          Mt. Talang • Jalur Bukik Bulek Kampung Batu<br>
          Kode Registrasi: #<?= $regis_id; ?>
        </div>
      </div>
      <div class="ticket-meta">
        <div class="status-chip">Status Pembayaran: <?= htmlspecialchars($status_bayar); ?></div><br>
        <span style="font-size:11px;color:#6b7280;">
          Metode: <?= htmlspecialchars($metode_bayar); ?>
        </span>
      </div>
    </div>

    <!-- DATA PENDAKI & REGISTRASI -->
    <div class="ticket-section-title">Data Pendaki & Registrasi</div>
    <div class="ticket-grid">
      <div>
        <div><span class="ticket-label">Nama Pendaki</span><br>
          <span class="ticket-value"><?= htmlspecialchars($reg['nama_lengkap']); ?></span>
        </div>
        <div style="margin-top:6px;"><span class="ticket-label">No HP</span><br>
          <span class="ticket-value"><?= htmlspecialchars($reg['no_hp']); ?></span>
        </div>
        <div style="margin-top:6px;"><span class="ticket-label">Alamat</span><br>
          <span class="ticket-value"><?= nl2br(htmlspecialchars($reg['alamat'])); ?></span>
        </div>
      </div>
      <div>
        <div><span class="ticket-label">NIK</span><br>
          <span class="ticket-value"><?= htmlspecialchars($reg['nik']); ?></span>
        </div>
        <div style="margin-top:6px;"><span class="ticket-label">Tanggal Naik</span><br>
          <span class="ticket-value"><?= htmlspecialchars($reg['tgl_naik']); ?></span>
        </div>
        <div style="margin-top:6px;"><span class="ticket-label">Tanggal Turun</span><br>
          <span class="ticket-value"><?= htmlspecialchars($reg['tgl_turun']); ?></span>
        </div>
      </div>
    </div>

    <!-- RINCIAN SEWA + REGISTRASI -->
    <div class="ticket-section-title" style="margin-top:10px;">Rincian Sewa & Biaya Registrasi</div>

    <table>
      <thead>
        <tr>
          <th>Alat / Keterangan</th>
          <th style="width:70px;">Jumlah</th>
          <th style="width:60px;">Hari</th>
          <th style="width:110px;">Subtotal</th>
          <th style="width:90px;">Status</th>
        </tr>
      </thead>
      <tbody>
        <!-- BARIS BIAYA REGISTRASI -->
        <tr>
          <td>Biaya Registrasi Pendakian<br>
            <span style="font-size:11px;color:#6b7280;">
              25.000 / orang
            </span>
          </td>
          <td><?= $jumlah_anggota; ?> org</td>
          <td>1</td>
          <td>Rp <?= number_format($biaya_registrasi, 0, ',', '.'); ?></td>
          <td>lunas</td>
        </tr>

        <?php if (empty($items)): ?>
          <tr>
            <td colspan="5" style="text-align:center;color:#6b7280;padding:10px 0;">
              Tidak ada data sewa peralatan.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($items as $row): ?>
            <tr>
              <td><?= htmlspecialchars($row['nama_alat']); ?></td>
              <td><?= (int)$row['jumlah']; ?></td>
              <td><?= (int)$row['hari']; ?></td>
              <td>Rp <?= number_format($row['harga_total'], 0, ',', '.'); ?></td>
              <td><?= htmlspecialchars($row['status_pembayaran']); ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="3" style="text-align:right;">Total Tagihan</td>
          <td colspan="2">Rp <?= number_format($total_tagihan, 0, ',', '.'); ?></td>
        </tr>
      </tfoot>
    </table>

    <div class="ticket-footer">
      Tunjukkan tiket ini kepada petugas basecamp saat melakukan konfirmasi pembayaran dan sebelum memulai pendakian.
    </div>

    <button class="btn-print" onclick="window.print()">Cetak Tiket</button>
  </div>
</body>

</html>