<?php
include 'koneksi.php';

// Validasi koneksi
if (!isset($koneksi) || !($koneksi instanceof mysqli)) {
    die('<strong>Error:</strong> Koneksi database ($koneksi) tidak ditemukan. Periksa koneksi.php');
}
?>

<style>
    /* SECTION DENGAN BACKGROUND FOTO (FORM & LIST PENDAKI) */
    .talang-section {
        min-height: calc(100vh - 140px);
        padding: 70px 0px 0px;
        background: url('img/img2.jpg') center/cover no-repeat fixed;
        /* ganti sesuai foto kamu */
        display: flex;
        justify-content: center;
        align-items: flex-start;
        padding-bottom: 70px;
    }

    /* CARD UTAMA (FORM & TABEL PENDaki) */
    .talang-card {
        width: 100%;
        max-width: 80%;
        background: rgba(255, 255, 255, 0.40);
        /* sedikit transparan */
        border-radius: 26px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.25);
        padding: 15px 10px;
    }

    .h-title {
        text-align: center;
        font-size: 32px;
        font-weight: 700;
        color: #ffffff;
        margin-bottom: 4px;
    }

    .h-sub {
        text-align: center;
        font-size: 16px;
        color: #000000ff;
        margin-bottom: 20px;
    }

    .pendaki-card {
        background: #ffffff;
        border-radius: 22px;
        padding: 22px 24px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.15);
        border: 1px solid #e5e7eb;
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    .pendaki-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
        min-width: 700px;
    }

    .pendaki-table thead th {
        text-align: left;
        padding: 12px 14px;
        background: linear-gradient(90deg, #22c55e, #4ade80);
        color: #f9fafb;
        font-weight: 600;
        border: none;
        white-space: nowrap;
    }

    .pendaki-table thead th:first-child,
    .pendaki-table tbody td:first-child {
        text-align: center;
    }

    .pendaki-table tbody td {
        padding: 10px 14px;
        border-bottom: 1px solid #e5e7eb;
        color: #111827;
    }

    .pendaki-table tbody tr:nth-child(even) {
        background: #f9fafb;
    }

    .pendaki-table tbody tr:hover {
        background: #ecfdf3;
    }

    .pendaki-table tbody td:last-child {
        font-weight: 500;
        color: #166534;
    }
</style>

<div class="talang-section">
    <div class="talang-card">
        <div class="text-center mt-4 mb-3">
            <h1 class="h-title">List Pendaki</h1>
            <p class="h-sub">Daftar pendaki yang terdaftar</p>
        </div>

        <div class="pendaki-wrapper">
            <div class="pendaki-card">
                <div class="table-responsive">
                    <table class="pendaki-table">
                        <thead>
                            <tr>
                                <th style="width:60px;">No</th>
                                <th>Nama Lengkap</th>
                                <th>Umur</th>
                                <th>Alamat</th>
                                <th>Tanggal Naik</th>
                                <th>Tanggal Turun</th>
                                <th>Foto Tim</th>
                                <th>Status Pendakian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT p.id, p.nama_lengkap, p.usia, p.alamat, r.tgl_naik,r.tgl_turun,r.foto_tim, r.status
                                FROM sania_pendaki p LEFT JOIN (
                                    SELECT r1.*
                                    FROM sania_registrasi r1
                                    JOIN (
                                      SELECT id_pendaki, MAX(id_regis) AS max_regis
                                      FROM sania_registrasi
                                      GROUP BY id_pendaki
                                    ) r2
                                      ON r1.id_pendaki = r2.id_pendaki
                                     AND r1.id_regis   = r2.max_regis
                                ) r ON p.id = r.id_pendaki
                                ORDER BY p.id ASC";

                            $res = $koneksi->query($sql);

                            $no = 1;
                            if ($res === false) {
                                echo '<tr><td colspan="7" class="text-danger">Query error: ' . htmlspecialchars($koneksi->error) . '</td></tr>';
                            } else {
                                if ($res->num_rows === 0) {
                                    echo '<tr><td colspan="7" class="text-center">Belum ada data pendaki</td></tr>';
                                } else {
                                    while ($r = $res->fetch_assoc()) {
                                        $rawStatus = $r['status'] ?? null;
                                        if ($rawStatus === 'Disetujui') {
                                            $statusPendakian = 'Sedang dalam pendakian';
                                        } elseif ($rawStatus === 'Selesai') {
                                            $statusPendakian = 'Selesai';
                                        } elseif ($rawStatus) {
                                            $statusPendakian = $rawStatus;
                                        } else {
                                            $statusPendakian = 'Belum registrasi';
                                        }

                                        $tgl_naik = '-';
                                        if (!empty($r['tgl_naik']) && $r['tgl_naik'] != '0000-00-00 00:00:00') {
                                            $tsn = strtotime($r['tgl_naik']);
                                            if ($tsn !== false) $tgl_naik = date('d-m-Y H:i', $tsn);
                                        }

                                        $tgl_turun = '-';
                                        if (!empty($r['tgl_turun']) && $r['tgl_turun'] != '0000-00-00 00:00:00') {
                                            $ts = strtotime($r['tgl_turun']);
                                            if ($ts !== false) $tgl_turun = date('d-m-Y H:i', $ts);
                                        }
                                        $foto_tim_html = '-';
                                        if (!empty($r['foto_tim'])) {
                                            $foto_url = htmlspecialchars($r['foto_tim']);
                                            $foto_tim_html = "<img src='{$foto_url}'style='width:55px;height:55px;border-radius:8px;object-fit:cover;'>";
                                        }


                                        $nama  = htmlspecialchars($r['nama_lengkap'] ?? '-');
                                        $usia  = htmlspecialchars($r['usia'] ?? '');
                                        $alamat = htmlspecialchars($r['alamat'] ?? '-');
                                        $statusPendakianEsc = htmlspecialchars($statusPendakian);

                                        echo "<tr>
                                            <td>{$no}</td>
                                            <td>{$nama}</td>
                                            <td>{$usia}</td>
                                            <td>{$alamat}</td>
                                            <td>{$tgl_naik}</td>
                                            <td>{$tgl_turun}</td>
                                            <td>{$foto_tim_html}</td>
                                            <td>{$statusPendakianEsc}</td>
                                          </tr>";
                                        $no++;
                                    }
                                    $res->free();
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>