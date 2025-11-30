<style>
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
</style>
<!-- STATISTIK KECIL -->
<section class="dash-stats">
    <?php if ($role === 'admin'): ?>
        <div class="dash-stat-card">
            <div class="dash-stat-icon">🧗‍♂️</div>
            <div class="dash-stat-label">Total Pendaki Terdaftar</div>
            <div class="dash-stat-value"><?= number_format($totalPendaki) ?></div>
            <div class="dash-stat-desc">Semua pendaki yang sudah mengisi data.</div>
        </div>
    <?php else: ?>
        <div class="dash-stat-card">
            <div class="dash-stat-icon">🧗‍♂️</div>
            <div class="dash-stat-label">Total Pendakian Kamu</div>
            <div class="dash-stat-value"><?= number_format($totalRegistrasi) ?></div>
            <div class="dash-stat-desc">Semua perjalanan yang pernah kamu daftarkan.</div>
        </div>
    <?php endif; ?>

    <div class="dash-stat-card">
        <div class="dash-stat-icon">⛰️</div>
        <div class="dash-stat-label">Pendakian Aktif</div>
        <div class="dash-stat-value"><?= number_format($totalAktif) ?></div>
        <div class="dash-stat-desc">Status registrasi: Disetujui.</div>
    </div>

    <div class="dash-stat-card">
        <div class="dash-stat-icon">✅</div>
        <div class="dash-stat-label">Pendakian Selesai</div>
        <div class="dash-stat-value"><?= number_format($totalSelesai) ?></div>
        <div class="dash-stat-desc">Status registrasi: Selesai.</div>
    </div>

    <div class="dash-stat-card">
        <div class="dash-stat-icon">🗂️</div>
        <div class="dash-stat-label">
            <?= ($role === 'admin') ? 'Total Registrasi' : 'Status Terakhir' ?>
        </div>
        <div class="dash-stat-value">
            <?php
            if ($role === 'admin') {
                echo number_format($totalRegistrasi);
            } else {
                // untuk user: tunjukkan status dari registrasi paling baru
                if (empty($recent)) {
                    echo '-';
                } else {
                    echo htmlspecialchars($recent[0]['status'] ?? '-');
                }
            }
            ?>
        </div>
        <div class="dash-stat-desc">
            <?= ($role === 'admin') ? 'Termasuk aktif, selesai, & lainnya.' : 'Status dari pendaftaran paling baru.' ?>
        </div>
    </div>
</section>
<!-- TABEL REGISTRASI TERBARU -->
<section class="dash-table-card">
    <div class="dash-section-header">
        <div>
            <div class="dash-section-title">
                <?= ($role === 'admin') ? 'Registrasi Terbaru' : 'Riwayat Pendakian Terakhir' ?>
            </div>
            <div class="dash-section-sub">
                <?= ($role === 'admin')
                    ? '5 pendaftaran terakhir yang masuk sistem.'
                    : '5 pendakian terakhir yang kamu daftarkan.' ?>
            </div>
        </div>
        <?php if ($role === 'admin'): ?>
            <a href="dashboard.php?p=pendaki" style="font-size:12px;color:#15803d;text-decoration:none;font-weight:600;">
                Lihat semua pendaki →
            </a>
        <?php endif; ?>
    </div>

    <div class="dash-table-wrap">
        <table class="dash-table">
            <thead>
                <tr>
                    <th style="width:60px;">ID</th>
                    <?php if ($role === 'admin'): ?>
                        <th>Nama Pendaki</th>
                    <?php endif; ?>
                    <th>Tanggal Daftar</th>
                    <th>Status</th>
                    <?php if ($role !== 'admin'): ?>
                        <th>Aksi</th> <!-- kolom aksi hanya untuk user -->
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recent)): ?>
                    <tr>
                        <td colspan="<?= ($role === 'admin') ? 4 : 4 ?>" style="text-align:center;padding:16px 0;color:#6b7280;">
                            Belum ada registrasi pendaki.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recent as $row): ?>
                        <?php
                        $tgl = '-';
                        if (!empty($row['tgl_daftar']) && $row['tgl_daftar'] != '0000-00-00 00:00:00') {
                            $ts = strtotime($row['tgl_daftar']);
                            if ($ts !== false) $tgl = date('d-m-Y H:i', $ts);
                        }

                        $status = $row['status'] ?? '-';
                        $badgeClass = 'dash-badge-lain';
                        if ($status === 'Disetujui') $badgeClass = 'dash-badge-aktif';
                        elseif ($status === 'Selesai') $badgeClass = 'dash-badge-selesai';
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id_regis']) ?></td>
                            <?php if ($role === 'admin'): ?>
                                <td><?= htmlspecialchars($row['nama_lengkap'] ?? $namaLogin) ?></td>
                            <?php endif; ?>
                            <td><?= htmlspecialchars($tgl) ?></td>
                            <td>
                                <span class="dash-badge <?= $badgeClass ?>">
                                    <?= htmlspecialchars($status ?: '-') ?>
                                </span>
                            </td>

                            <?php if ($role !== 'admin'): ?>
                                <td>
                                    <?php if ($status === 'Disetujui'): ?>
                                        <!-- User bisa sewa peralatan utk registrasi ini -->
                                        <a href="dashboard.php?p=camp&regis_id=<?= $row['id_regis'] ?>" class="reg-btn reg-btn-view">
                                            👁 Sewa Peralatan Camp
                                        </a>
                                        <!-- <a href="dashboard.php?p=camp&regis_id=<?= $row['id_regis'] ?>"
                                            style="font-size:12px;color:#15803d;text-decoration:underline;">
                                            Sewa Peralatan Camp
                                        </a> -->
                                    <?php else: ?>
                                        <span style="font-size:12px;color:#9ca3af;">
                                            Sewa tersedia setelah disetujui
                                        </span>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>