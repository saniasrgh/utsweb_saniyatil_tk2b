<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<style>
    .gt-info {
        width: 100%;
        margin: 50px 0;
        padding: 0 100px;
        font-family: "Poppins", sans-serif;
    }

    /* HERO JUDUL + DESKRIPSI */
    .gt-hero {
        background-image: url("img/img6.jpg");
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        border-radius: 22px;
        padding: 24px 28px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        border: 1px solid #e5e7eb;
        margin-bottom: 26px;
        text-align: center;
        display: flex;
        flex-direction: column;
        gap: 16px;
        position: relative;
        overflow: hidden;
    }

    /* overlay tipis biar teks lebih kebaca */
    .gt-hero::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(15, 23, 42, 0.2), rgba(15, 23, 42, 0.6));
        z-index: 0;
    }

    .gt-hero>* {
        position: relative;
        z-index: 1;
    }

    .gt-hero h1 {
        text-align: center;
        font-size: 32px;
        font-weight: 700;
        color: #ffffff;
    }

    .gt-hero p {
        font-size: 14px;
        color: #f9fafb;
        line-height: 1.6;
    }

    /* BADGE INFO DI BAWAH JUDUL */
    .gt-badges {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .gt-badges .badge {
        padding: 6px 12px;
        border-radius: 999px;
        background: rgba(240, 253, 250, 0.9);
        font-size: 12px;
        color: #022c22;
        border: 1px solid rgba(45, 212, 191, 0.4);
        backdrop-filter: blur(4px);
    }

    /* CARD LOKASI ATAS */
    .gt-card {
        text-align: center;
        background: linear-gradient(70deg, #f0fef5ff, #7cd6eba7);
        border-radius: 22px;
        padding: 20px 24px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        border: 1px solid #e5e7eb;
        margin-bottom: 26px;
    }

    .gt-card h2 {
        font-size: 20px;
        margin-bottom: 8px;
        color: #2563eb;
    }

    .gt-card p {
        font-size: 14px;
        color: #000000;
        line-height: 1.8;
    }

    /* GRID 2 KOLOM UNTUK KARTU-KARTU ATURAN */
    .gt-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .gt-col {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    /* KARTU ATURAN (Registrasi, Biaya, dll) */
    .gt-rule {
        position: relative;
        overflow: hidden;
        background: radial-gradient(circle at top left, #f9fafb 0, #ffffff 45%);
        border-radius: 22px;
        padding: 18px 20px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 6px 20px rgba(148, 163, 184, 0.25);
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    }

    .gt-rule::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(70deg, #2083ceff, #53c9e4a7);
    }

    .gt-rule:hover {
        transform: translateY(-4px);
        box-shadow: 0 14px 35px rgba(15, 23, 42, 0.16);
        border-color: #16d5dcff;
    }

    .gt-rule h3 {
        font-size: 15px;
        margin-bottom: 8px;
        color: #2083ceff;
    }

    .gt-rule ul {
        margin-left: 18px;
        font-size: 13px;
        color: #374151;
        line-height: 1.6;
    }

    /* LIST BIAYA DI DALAM KARTU BIAYA PENDK. */
    .gt-price {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .gt-price li {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 4px;
    }

    .gt-price span {
        font-size: 13px;
        color: #374151;
    }

    .gt-price strong {
        font-size: 13px;
        color: #111827;
    }

    /* SECTION TITLE UMUM (MAP & KONTAK) */
    .gt-section-title {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 6px;
        color: #1d4ed8;
    }

    .gt-section-sub {
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 14px;
    }

    /* MAP WRAPPER */
    .gt-map-card {
        margin-top: 28px;
        margin-bottom: 24px;
        background: #ffffff;
        border-radius: 22px;
        padding: 18px 20px 22px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 8px 24px rgba(148, 163, 184, 0.35);
    }

    .gt-map-wrap {
        border-radius: 18px;
        overflow: hidden;
        margin-top: 10px;
    }

    .gt-map-wrap iframe {
        display: block;
        width: 100%;
        border: 0;
    }

    /* KONTAK */
    .gt-contact-card {
        background: radial-gradient(circle at top left, #f0f9ff 0, #ecfeff 45%, #f0fdf4 100%);
        border-radius: 22px;
        padding: 18px 20px 22px;
        border: 1px solid #dbeafe;
        box-shadow: 0 8px 24px rgba(129, 140, 248, 0.25);
        margin-bottom: 20px;
    }

    .gt-contact-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
        margin-top: 10px;
        font-size: 14px;
        color: #111827;
    }

    .gt-contact-item strong {
        display: block;
        font-size: 14px;
        margin-bottom: 2px;
    }

    .gt-contact-item span {
        font-size: 13px;
        color: #4b5563;
    }

    /* CATATAN AKHIR */
    .gt-note {
        margin-top: 18px;
        padding: 12px 16px;
        border-radius: 12px;
        background: linear-gradient(70deg, #f0fef5ff, #7cd6eba7);
        font-size: 14px;
        font-weight: bold;
        color: #2083ceff;
        text-align: center;
    }

    /* RESPONSIVE */
    @media (max-width: 900px) {
        .gt-info {
            padding: 0 20px;
        }

        .gt-grid {
            grid-template-columns: minmax(0, 1fr);
        }

        .gt-contact-grid {
            grid-template-columns: minmax(0, 1fr);
        }
    }
</style>

<section class="gt-info" id="info">
    <!-- HERO -->
    <header class="gt-hero">
        <h1>Informasi Pendakian Gunung Talang</h1>
        <p>
            Selamat datang di web resmi registrasi pendakian Gunung Talang.
            Bacalah informasi dan tata tertib berikut sebelum melakukan pendakian.
        </p>

        <div class="gt-badges">
            <span class="badge">2.597 mdpl</span>
            <span class="badge">Lokasi: Kab. Solok</span>
            <span class="badge">Jalur resmi: Bukik Bulek Kampung Batu</span>
        </div>
    </header>

    <!-- LOKASI & JALUR -->
    <div class="gt-card">
        <h2>📍 Lokasi & Jalur</h2>
        <p>Gunung Talang via Jalur Bukik Bulek Kampung Batu — Nagari Kampung Batu Dalam, Kecamatan Danau Kembar, Kabupaten Solok.</p>
        <p>Pendaki wajib melakukan registrasi sebelum naik dan setelah turun di basecamp resmi.</p>
    </div>

    <!-- ATURAN & BIAYA -->
    <div class="gt-grid">

        <div class="gt-col">
            <div class="gt-rule">
                <h3>📝 Registrasi Pendakian</h3>
                <ul>
                    <li>Wajib melapor sebelum dan sesudah pendakian di basecamp.</li>
                    <li>Menyertakan identitas dan nomor HP/WhatsApp yang aktif.</li>
                    <li>Siap dilakukan pemeriksaan barang bawaan oleh petugas.</li>
                    <li>Denda <strong>2x biaya registrasi</strong> apabila tidak melakukan registrasi resmi.</li>
                </ul>
            </div>

            <div class="gt-rule">
                <h3>💰 Biaya Pendakian</h3>
                <ul class="gt-price">
                    <li><span>Registrasi pendaki</span><strong>Rp 25.000</strong></li>
                    <li><span>Parkir motor</span><strong>Rp 15.000</strong></li>
                    <li><span>Parkir mobil</span><strong>Rp 30.000</strong></li>
                    <li><span>Ojek Pos 1–2</span><strong>Rp 35.000</strong></li>
                    <li><span>Ojek WNA</span><strong>Rp 50.000</strong></li>
                    <li><span>Mobil angkut</span><strong>Rp 20.000</strong></li>
                </ul>
            </div>

            <div class="gt-rule">
                <h3>⏰ Waktu Pendakian</h3>
                <ul>
                    <li>Hari Jumat, basecamp dibuka mulai pukul <strong>14.00 WIB</strong>.</li>
                    <li>Di luar itu, mengikuti jam operasional basecamp yang tertera pada informasi kontak.</li>
                </ul>
            </div>
        </div>

        <div class="gt-col">
            <div class="gt-rule">
                <h3>⚠️ Keselamatan</h3>
                <ul>
                    <li>Seluruh aktivitas pendakian menjadi tanggung jawab pribadi pendaki.</li>
                    <li>Wajib mengikuti arahan dan keputusan petugas basecamp.</li>
                    <li>Wanita yang sedang datang bulan <strong>tidak diperbolehkan</strong> mendaki.</li>
                    <li>Pasangan yang bukan suami-istri <strong>tidak diperbolehkan</strong> satu tenda.</li>
                </ul>
            </div>

            <div class="gt-rule">
                <h3>🚫 Larangan</h3>
                <ul>
                    <li>Bersikap takabbur, berteriak-teriak, dan mengeluarkan kata-kata kotor.</li>
                    <li>Perilaku asusila atau tindakan yang tidak sopan.</li>
                    <li>Mencoret-coret fasilitas umum (batu, pohon, shelter, dll).</li>
                    <li>Merusak atau membawa pulang flora dan fauna dari kawasan.</li>
                    <li>Mengkonsumsi miras, narkoba, atau membawa senjata tajam yang tidak sesuai kebutuhan outdoor.</li>
                    <li>Buang air sembarangan di jalur dan area camp.</li>
                </ul>
            </div>

            <div class="gt-rule">
                <h3>🧹 Kebersihan</h3>
                <ul>
                    <li>Setiap pendaki wajib membawa turun kembali sampah pribadi.</li>
                    <li>Gunakan titik pembuangan sampah yang sudah disediakan di basecamp.</li>
                </ul>
            </div>
        </div>

    </div>

    <!-- MAP JALUR -->
    <section id="map" class="gt-map-card">
        <div class="gt-section-title">🧭 Map Jalur Pendakian</div>
        <div class="gt-section-sub">
            Titik awal pendakian berada di Basecamp Bukik Bulek. Ikuti jalur resmi yang telah ditentukan
            dan jangan keluar dari track demi keamanan bersama.
        </div>

        <div class="gt-map-wrap">
            <!-- Ganti src di bawah dengan link embed Google Maps basecamp kamu -->
            <iframe
                src="https://www.google.com/maps/embed?pb="
                height="350"
                allowfullscreen=""
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </section>

    <!-- KONTAK BASECAMP -->
    <section id="kontak" class="gt-contact-card">
        <div class="gt-section-title">☎️ Kontak Basecamp Gunung Talang</div>
        <div class="gt-section-sub">
            Hubungi admin basecamp untuk informasi kuota pendaki, kondisi jalur, cuaca, serta pertanyaan lainnya.
        </div>

        <div class="gt-contact-grid">
            <div class="gt-contact-item">
                <strong>📍 Alamat Basecamp</strong>
                <span>Basecamp Bukik Bulek, Nagari Kampung Batu Dalam, Danau Kembar, Kab. Solok.</span>
            </div>
            <div class="gt-contact-item">
                <strong>📱 WhatsApp Admin</strong>
                <span>+62 812-3456-7890 (reservasi & konfirmasi kedatangan)</span>
            </div>
            <div class="gt-contact-item">
                <strong>✉️ Email Resmi</strong>
                <span>admin@talang.id</span>
            </div>
            <div class="gt-contact-item">
                <strong>🕒 Jam Operasional Basecamp</strong>
                <span>Setiap hari, pukul 07.00 – 22.00 WIB*</span>
            </div>
            <div class="gt-contact-item">
                <strong>⚠️ Catatan Penting</strong>
                <span>Untuk keadaan darurat di gunung, segera hubungi petugas basecamp atau layanan darurat setempat.</span>
            </div>
        </div>
    </section>

    <!-- CATATAN AKHIR -->
    <div class="gt-note">
        Dengan melakukan registrasi, pendaki dianggap telah membaca dan menyetujui seluruh tata tertib pendakian Gunung Talang.
    </div>
</section>