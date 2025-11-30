<?php
// file: admin/pendaki.php

// harus admin
if ($role !== 'admin') {
  echo "<p>Akses ditolak.</p>";
  return;
}

if (!isset($db) && isset($koneksi)) {
  $db = $koneksi;
}

$view = $_GET['view'] ?? 'list';
?>


<!-- .table-title{
font-size: 22px;
font-weight: 700;
} -->
<style>
  .table-title {
    font-size: 22px;
    font-weight: 700;
    color: #0f172a;
  }

  .pendaki-actions {
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }

  .pendaki-actions form {
    margin: 0;
  }

  .pendaki-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 5px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    text-decoration: none;
    transition: 0.15s ease;
    white-space: nowrap;
  }

  .pendaki-btn-edit {
    background: #e0f2fe;
    /* biru muda */
    color: #0369a1;
  }

  .pendaki-btn-edit:hover {
    background: #bfdbfe;
  }

  .pendaki-btn-delete {
    background: #fee2e2;
    /* merah muda */
    color: #b91c1c;
  }

  .pendaki-btn-delete:hover {
    background: #fecaca;
  }

  /* ===== STYLE KHUSUS TABEL PENDAKI ===== */

  /* kasih sedikit shadow & hover lembut di baris */
  .pendaki-table tbody tr {
    transition: background 0.15s ease, transform 0.1s ease;
  }

  .pendaki-table tbody tr:hover {
    background: #ecfdf3;
    /* hijau sangat muda */
    transform: translateY(-1px);
  }

  /* kolom nama dengan avatar inisial */
  .pendaki-name {
    display: inline-flex;
    align-items: center;
    gap: 8px;
  }

  .pendaki-avatar {
    width: 28px;
    height: 28px;
    border-radius: 999px;
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: #f9fafb;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 600;
    box-shadow: 0 4px 10px rgba(22, 163, 74, 0.35);
  }

  /* chip kecil di umur */
  .pendaki-age-chip {
    display: inline-flex;
    align-items: center;
    padding: 3px 8px;
    border-radius: 999px;
    background: #f1f5f9;
    font-size: 12px;
    font-weight: 500;
    color: #0f172a;
  }

  .pendaki-age-chip span {
    font-size: 11px;
    margin-left: 3px;
    color: #6b7280;
  }

  /* ===== TABEL HEADER YANG LEBIH ESTETIK ===== */

  .pendaki-table thead th {
    background: linear-gradient(90deg, #16a34a, #4ade80);
    color: white;
    font-size: 13px;
    font-weight: 700;
    text-align: center;
    padding: 14px 16px;
    letter-spacing: .3px;
    border-bottom: 0;
    white-space: nowrap;
  }
  .pendaki-table thead {
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}
  /* radius lembut di kiri-kanan */
  .pendaki-table thead th:first-child {
    border-top-left-radius: 12px;
  }

  .pendaki-table thead th:last-child {
    border-top-right-radius: 12px;
  }

  /* border halus antar kolom */
  .pendaki-table thead th+th {
    border-left: 1px solid rgba(255, 255, 255, 0.25);
  }
</style>


<?php

$view = $_GET['view'] ?? 'list';

switch ($view) {

  // ================= LIST ======================
  case 'list':

    $q = mysqli_query($db, "SELECT * FROM sania_pendaki ORDER BY nama_lengkap ASC");
?>

    <section class="dash-table-card">

      <div class="dash-section-header">
        <div>
          <div class="table-title">List Pendaki</div>
          <div class="dash-section-sub">Daftar pendaki yang terdaftar</div>
        </div>
      </div>

      <div class="dash-table-wrap">
        <table class="dash-table pendaki-table">
          <thead>
            <tr>
              <th>No</th>
              <th>NIK</th>
              <th>Nama</th>
              <th>Umur</th>
              <th>No HP</th>
              <th>Alamat</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>

            <?php
            $no = 1;
            while ($r = mysqli_fetch_assoc($q)):
              $nama   = $r['nama_lengkap'];
              $initial = strtoupper(substr($nama, 0, 1));
            ?>
              <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($r['nik']) ?></td>

                <!-- NAMA + AVATAR INISIAL -->
                <td>
                  <div class="pendaki-name">
                    <div class="pendaki-avatar"><?= $initial ?></div>
                    <span><?= htmlspecialchars($nama) ?></span>
                  </div>
                </td>

                <!-- UMUR DALAM CHIP KECIL -->
                <td>
                  <div class="pendaki-age-chip">
                    <?= (int)$r['usia'] ?>
                    <span>tahun</span>
                  </div>
                </td>

                <td><?= htmlspecialchars($r['no_hp']) ?></td>
                <td><?= htmlspecialchars($r['alamat']) ?></td>

                <td>
                  <div class="pendaki-actions">
                    <a href="dashboard.php?p=pendaki&view=edit&id=<?= $r['id'] ?>"
                      class="pendaki-btn pendaki-btn-edit">
                      ✏️ Edit
                    </a>

                    <form action="admin/proses.php" method="post"
                      onsubmit="return confirm('Hapus pendaki <?= htmlspecialchars($r['nama_lengkap'], ENT_QUOTES) ?>?');">
                      <input type="hidden" name="aksi" value="hapus_pendaki">
                      <input type="hidden" name="id" value="<?= $r['id'] ?>">
                      <button type="submit" class="pendaki-btn pendaki-btn-delete">
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

    </section>
  <?php
    break;


  // ================= EDIT ======================
  case 'edit':

    $id = (int)$_GET['id'];
    $data = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM sania_pendaki WHERE id=$id"));
  ?>

    <section class="dash-table-card">

      <div class="dash-section-header">
        <div>
          <div class="dash-section-title">Edit Pendaki</div>
          <div class="dash-section-sub">Perbarui data pendaki yang sudah terdaftar.</div>
        </div>
      </div>

      <form method="POST" action="admin/proses.php" class="dash-form">
        <input type="hidden" name="aksi" value="update_pendaki">
        <input type="hidden" name="id" value="<?= $data['id'] ?>">

        <div class="dash-form-group">
          <label for="nik">NIK</label>
          <input id="nik" type="text" name="nik" required value="<?= htmlspecialchars($data['nik']) ?>">
        </div>

        <div class="dash-form-group">
          <label for="nama_lengkap">Nama Lengkap</label>
          <input id="nama_lengkap" type="text" name="nama_lengkap" required value="<?= htmlspecialchars($data['nama_lengkap']) ?>">
        </div>

        <div class="dash-form-group">
          <label for="usia">Umur</label>
          <input id="usia" type="number" name="usia" required value="<?= htmlspecialchars($data['usia']) ?>">
        </div>

        <div class="dash-form-group">
          <label for="no_hp">No HP</label>
          <input id="no_hp" type="text" name="no_hp" required value="<?= htmlspecialchars($data['no_hp']) ?>">
        </div>

        <div class="dash-form-group dash-form-full">
          <label for="alamat">Alamat</label>
          <textarea id="alamat" name="alamat" rows="2" required><?= htmlspecialchars($data['alamat']) ?></textarea>
        </div>

        <div class="dash-form-actions dash-form-full">
          <button type="submit" name="submit_edit" class="btn-primary">Simpan</button>
          <a href="dashboard.php?p=pendaki" class="btn-ghost">Batal</a>
        </div>
      </form>

    </section>

<?php
    break;
}
