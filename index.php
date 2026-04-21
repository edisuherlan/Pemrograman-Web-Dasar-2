<?php
/**
 * =============================================================================
 * BERANDA (HALAMAN UTAMA) — APLIKASI PENGELOLAAN DATABASE PERKULIAHAN
 * =============================================================================
 * Tujuan file ini:
 * - Memberi gambaran besar kepada pengguna (dan Anda sebagai praktikan) tentang
 *   apa saja yang bisa dikelola dalam sistem ini.
 * - Menyediakan pintasan (tautan) menuju halaman CRUD untuk masing-masing tabel.
 *
 * Istilah penting (untuk yang baru belajar):
 * - CRUD = Create (tambah), Read (tampil/lihat), Update (ubah), Delete (hapus).
 * - Tabel = kumpulan data di database yang punya kolom (field) tetap, misalnya
 *   tabel "dosen" punya kolom NIDN, nama, email.
 * - Relasi = hubungan antar tabel. Contoh: mata kuliah "milik" seorang dosen
 *   pengampu, mahasiswa "mengambil" mata kuliah lewat KRS, lalu nilai menyimpan
 *   hasil studi per KRS.
 *
 * Alur belajar yang disarankan:
 * 1) Mulai dari data master: Program studi (prodi), lalu Dosen & Mahasiswa.
 * 2) Lanjut Mata kuliah (perlu memilih dosen pengampu).
 * 3) KRS (memilih mahasiswa + mata kuliah + semester/tahun ajaran).
 * 4) Nilai (mengisi nilai untuk sebuah KRS yang sudah ada).
 *
 * Teknis:
 * - Tampilan memakai Bootstrap 5 lewat CDN (file includes/header.php).
 * - Pastikan database sudah diimpor: database/perkuliahan.sql
 * =============================================================================
 */

// Aktifkan mode tipe ketat untuk mengurangi bug tipe data
declare(strict_types=1);

// Sambungkan ke MySQL; variabel $pdo siap dipakai (di beranda belum dipakai query, tapi OK untuk cek koneksi)
require_once __DIR__ . '/config/database.php';

// Judul ini dipakai di <title> dan heading lewat header.php
$judulHalaman = 'Beranda';

// Sisipkan layout atas: navbar + mulai tag <main>
require_once __DIR__ . '/includes/header.php';
?>

<!-- Baris Bootstrap: g = gutter (jarak antar kolom), g-4 = jarak sedang -->
<div class="row g-4">
    <!-- Kolom lebar di layar besar (8/12), penuh di layar kecil -->
    <div class="col-lg-8">
        <!-- Card = kotak berbayang; border-0 = tanpa border keras; shadow-sm = bayangan halus -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <!-- Judul card -->
                <h2 class="h5 card-title">Selamat datang</h2>
                <!-- text-secondary = abu teks; mb-0 = tanpa margin bawah -->
                <p class="card-text text-secondary mb-0">
                    Gunakan menu di atas untuk mengelola data. Setiap menu mengarah ke satu jenis tabel
                    lengkap dengan form tambah, ubah, hapus, dan daftar data.
                </p>
            </div>
        </div>
    </div>
    <!-- Kolom samping: tips cek sebelum mulai -->
    <div class="col-lg-4">
        <!-- border-primary border-2 = garis biru agak tebal untuk menonjolkan -->
        <div class="card border-primary border-2 shadow-sm">
            <div class="card-body">
                <h2 class="h6 card-title text-primary">Cek sebelum mulai</h2>
                <!-- ps-3 = padding start untuk bullet list -->
                <ul class="small mb-0 text-secondary ps-3">
                    <li>MySQL di Laragon menyala.</li>
                    <li>Database <code>perkuliahan</code> sudah diimpor.</li>
                    <li>Akses lewat browser: <code>http://localhost/mk_web/</code> (sesuaikan folder Anda).</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Baris kartu menu: 1 kolom di HP, 2 di tablet, 3 di layar lebar -->
<div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3 mt-1">
    <?php
    // Array $kartu: setiap elemen = [file tujuan, nama ikon Bootstrap Icons, judul kartu, deskripsi singkat]
    $kartu = [
        ['prodi.php', 'bi-building', 'Program studi', 'Kode & nama prodi. Wajib diisi sebelum dosen/mahasiswa/MK.'],
        ['dosen.php', 'bi-person-badge', 'Dosen', 'Data pengajar & NIDN. Dipakai sebagai pengampu mata kuliah.'],
        ['mahasiswa.php', 'bi-people', 'Mahasiswa', 'Data mahasiswa & NIM. Dipakai saat mengisi KRS.'],
        ['matakuliah.php', 'bi-book', 'Mata kuliah', 'Kode MK, SKS, dan dosen pengampu.'],
        ['krs.php', 'bi-journal-text', 'KRS', 'Mahasiswa mengambil mata kuliah per semester & tahun ajaran.'],
        ['nilai.php', 'bi-clipboard-data', 'Nilai', 'Nilai angka & huruf untuk setiap baris KRS.'],
    ];
    // foreach: ulangi untuk setiap kartu; destructuring ke $url, $ikon, $judul, $isi
    foreach ($kartu as [$url, $ikon, $judul, $isi]) {
        // Keluar dari mode PHP ke HTML untuk markup yang lebih mudah dibaca
        ?>
        <!-- Satu kolom kartu; h-100 = tinggi penuh supaya kartu sejajar rapi -->
        <div class="col">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <!-- Ikon + judul; h() melindungi teks jika suatu saat data dinamis -->
                    <h3 class="h5 card-title"><i class="bi <?= h($ikon) ?> text-primary me-2"></i><?= h($judul) ?></h3>
                    <p class="card-text small text-secondary"><?= h($isi) ?></p>
                    <!-- Tombol outline = tidak penuh warna, tetap jelas sebagai tautan -->
                    <a href="<?= h($url) ?>" class="btn btn-outline-primary btn-sm">Buka halaman</a>
                </div>
            </div>
        </div>
        <?php
    } // akhir foreach kartu
    ?>
</div>

<?php
// Sisipkan layout bawah: tutup main, footer, script Bootstrap
require_once __DIR__ . '/includes/footer.php';
?>
