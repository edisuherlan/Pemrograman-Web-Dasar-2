<?php
/**
 * =============================================================================
 * MODUL CRUD — TABEL DOSEN
 * =============================================================================
 * Apa yang dikelola di halaman ini?
 * - Menyimpan data dosen: NIDN (nomor unik), nama, dan email (opsional).
 *
 * Kenapa tabel ini penting?
 * - Mata kuliah wajib punya "dosen pengampu". Jadi biasanya Anda isi dosen dulu,
 *   baru mengisi mata kuliah.
 *
 * Alur di kode (supaya Anda bisa membaca file ini):
 * 1) Bagian ATAS file: jika pengiriman form (method POST), kita proses dulu
 *    (tambah / ubah / hapus), lalu redirect dengan pesan. Ini pola umum supaya
 *    tidak terjadi "resubmit" saat refresh halaman (PRG: Post/Redirect/Get).
 * 2) Bagian BAWAH: ambil data dari database untuk ditampilkan (daftar / form).
 * 3) Terakhir: include header & footer agar tampilan konsisten.
 *
 * Parameter URL (GET) yang dipakai:
 * - Tanpa parameter: tampilkan daftar.
 * - ?aksi=tambah : form kosong untuk data baru.
 * - ?aksi=ubah&id=... : form terisi untuk mengedit dosen tertentu.
 *
 * Catatan keamanan pemula:
 * - Input tidak langsung diselipkan ke string SQL. Kita pakai prepare() + execute()
 *   agar aman dari SQL injection.
 * - Tampilan HTML memakai fungsi h() dari includes/fungsi.php untuk mencegah XSS.
 * =============================================================================
 */

// Aktifkan pengecekan tipe (misalnya string vs int) secara ketat
declare(strict_types=1);

// Muat koneksi database ($pdo)
require_once __DIR__ . '/config/database.php';
// Muat fungsi h() dan alert
require_once __DIR__ . '/includes/fungsi.php';

/* --- Proses POST (simpan / hapus) — sebelum output HTML --- */
// Hanya jalankan blok ini jika user mengirim form (bukan sekadar membuka halaman)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Field tersembunyi "aksi" memberi tahu server mau simpan tambah, ubah, atau hapus
    $aksi = $_POST['aksi'] ?? '';

    // try/catch: tangkap error database agar tidak tampil layar putih berbahaya
    try {
        // --- TAMBAH DATA BARU ---
        if ($aksi === 'simpan_tambah') {
            // trim() menghapus spasi di awal/akhir supaya tidak ada NIDN " kosong "
            $nidn = trim((string) ($_POST['nidn'] ?? ''));
            $nama = trim((string) ($_POST['nama'] ?? ''));
            $email = trim((string) ($_POST['email'] ?? ''));
            $idProdi = (int) ($_POST['id_prodi'] ?? 0);

            // Validasi sederhana: field wajib tidak boleh kosong
            if ($nidn === '' || $nama === '' || $idProdi < 1) {
                // Redirect dengan pesan; rawurlencode agar spasi/karakter aman di URL
                header('Location: dosen.php?status=tidak_valid&msg=' . rawurlencode('NIDN, nama, dan program studi wajib diisi.'));
                // exit wajib setelah header Location agar skrip berhenti
                exit;
            }

            // ? = placeholder; nilai asli dikirim terpisah (aman dari SQL injection)
            $stmt = $pdo->prepare('INSERT INTO dosen (nidn, nama, email, id_prodi) VALUES (?, ?, ?, ?)');
            // Email kosong disimpan sebagai NULL di database (bukan string kosong)
            $stmt->execute([$nidn, $nama, $email !== '' ? $email : null, $idProdi]);
            // Redirect sukses; user akan melihat alert hijau
            header('Location: dosen.php?status=simpan_ok');
            exit;
        }

        // --- UBAH DATA YANG SUDAH ADA ---
        if ($aksi === 'simpan_ubah') {
            // id_dosen dari input hidden: menunjuk baris mana yang diupdate
            $id = (int) ($_POST['id_dosen'] ?? 0);
            $nidn = trim((string) ($_POST['nidn'] ?? ''));
            $nama = trim((string) ($_POST['nama'] ?? ''));
            $email = trim((string) ($_POST['email'] ?? ''));
            $idProdi = (int) ($_POST['id_prodi'] ?? 0);

            // id harus positif; NIDN dan nama tidak boleh kosong
            if ($id < 1 || $nidn === '' || $nama === '' || $idProdi < 1) {
                header('Location: dosen.php?status=tidak_valid&msg=' . rawurlencode('Data tidak lengkap.'));
                exit;
            }

            $stmt = $pdo->prepare('UPDATE dosen SET nidn = ?, nama = ?, email = ?, id_prodi = ? WHERE id_dosen = ?');
            // Urutan nilai harus sama dengan urutan ? pada query
            $stmt->execute([$nidn, $nama, $email !== '' ? $email : null, $idProdi, $id]);
            header('Location: dosen.php?status=simpan_ok');
            exit;
        }

        // --- HAPUS SATU BARIS ---
        if ($aksi === 'hapus') {
            $id = (int) ($_POST['id_dosen'] ?? 0);
            if ($id < 1) {
                header('Location: dosen.php?status=tidak_valid');
                exit;
            }
            $stmt = $pdo->prepare('DELETE FROM dosen WHERE id_dosen = ?');
            $stmt->execute([$id]);
            header('Location: dosen.php?status=hapus_ok');
            exit;
        }
    } catch (PDOException $e) {
        // errorInfo[1] = kode error MySQL; 1062 = duplicate entry; 23000 = integrity constraint
        $code = (int) ($e->errorInfo[1] ?? 0);
        if ($code === 23000 || $code === 1062) {
            header('Location: dosen.php?status=duplikat&msg=' . rawurlencode('NIDN sudah dipakai dosen lain, atau data terkait melarang penghapusan.'));
            exit;
        }
        // Error lain: tampilkan pesan teknis (untuk praktikum OK; produksi sebaiknya dicatat di log
        header('Location: dosen.php?status=gagal&msg=' . rawurlencode($e->getMessage()));
        exit;
    }
}

/* --- Tampilan (GET): baca parameter URL lalu ambil data untuk ditampilkan --- */
// Default: tampilkan daftar; bisa 'tambah' atau 'ubah'
$aksiGet = $_GET['aksi'] ?? 'daftar';
// id dari URL untuk mode ubah (integer)
$idEdit = (int) ($_GET['id'] ?? 0);

// Variabel untuk menampung satu baris saat edit; null = tidak sedang edit
$barisEdit = null;
if ($aksiGet === 'ubah' && $idEdit > 0) {
    // SELECT satu baris berdasarkan primary key
    $stmt = $pdo->prepare('SELECT * FROM dosen WHERE id_dosen = ?');
    $stmt->execute([$idEdit]);
    $barisEdit = $stmt->fetch();
    // Jika ID tidak ketemu (URL dimainkan), kembali ke daftar
    if (!$barisEdit) {
        $aksiGet = 'daftar';
    }
}

// Daftar prodi untuk dropdown form
$prodiPilihan = $pdo->query('SELECT id_prodi, kode_prodi, nama_prodi FROM prodi ORDER BY kode_prodi ASC')->fetchAll(PDO::FETCH_ASSOC);

// Ambil semua dosen beserta nama prodi (JOIN) untuk tabel
$daftarDosen = $pdo->query(
    'SELECT d.*, p.kode_prodi, p.nama_prodi
     FROM dosen d
     INNER JOIN prodi p ON p.id_prodi = d.id_prodi
     ORDER BY d.nama ASC'
)->fetchAll(PDO::FETCH_ASSOC);

// Judul halaman untuk header
$judulHalaman = 'Data Dosen';
// Tampilkan navbar + judul + alert
require_once __DIR__ . '/includes/header.php';
?>

<?php
// Jika mode tambah ATAU ubah dengan data valid: tampilkan form
// else: tampilkan tabel daftar
if ($aksiGet === 'tambah' || ($aksiGet === 'ubah' && $barisEdit)) :
    ?>
    <!-- Form mengirim POST ke file ini sendiri -->
    <div class="alert alert-info small" role="note">
        <strong>Petunjuk:</strong> Isi NIDN (unik), nama lengkap, program studi, dan email jika ada. Setelah klik simpan, Anda akan kembali ke daftar
        dengan notifikasi sukses/gagal.
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <!-- action kosong ke dosen.php = kirim ke URL yang sama -->
            <form method="post" class="row g-3" action="dosen.php">
                <!-- Nilai aksi dinamis: server bedakan tambah vs ubah -->
                <input type="hidden" name="aksi" value="<?= $aksiGet === 'tambah' ? 'simpan_tambah' : 'simpan_ubah' ?>">
                <?php if ($aksiGet === 'ubah' && $barisEdit) : ?>
                    <!-- Saat ubah, kirim id agar UPDATE tepat sasaran -->
                    <input type="hidden" name="id_dosen" value="<?= (int) $barisEdit['id_dosen'] ?>">
                <?php endif; ?>

                <div class="col-md-4">
                    <label class="form-label" for="nidn">NIDN</label>
                    <!-- required = browser cegah submit jika kosong; maxlength sesuai kolom SQL -->
                    <input class="form-control" id="nidn" name="nidn" required maxlength="20"
                           value="<?= $barisEdit ? h((string) $barisEdit['nidn']) : '' ?>">
                </div>
                <div class="col-md-8">
                    <label class="form-label" for="nama">Nama lengkap</label>
                    <input class="form-control" id="nama" name="nama" required maxlength="120"
                           value="<?= $barisEdit ? h((string) $barisEdit['nama']) : '' ?>">
                </div>
                <div class="col-12">
                    <label class="form-label" for="id_prodi">Program studi</label>
                    <select class="form-select" id="id_prodi" name="id_prodi" required <?= $prodiPilihan === [] ? 'disabled' : '' ?>>
                        <option value="">— pilih prodi —</option>
                        <?php foreach ($prodiPilihan as $p) : ?>
                            <?php $sel = $barisEdit && (int) $barisEdit['id_prodi'] === (int) $p['id_prodi'] ? ' selected' : ''; ?>
                            <option value="<?= (int) $p['id_prodi'] ?>"<?= $sel ?>><?= h((string) $p['kode_prodi']) ?> — <?= h((string) $p['nama_prodi']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label" for="email">Email (opsional)</label>
                    <!-- type="email" memberi validasi format email ringan di browser -->
                    <input class="form-control" id="email" name="email" type="email" maxlength="120"
                           value="<?= $barisEdit ? h((string) ($barisEdit['email'] ?? '')) : '' ?>">
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary" <?= $prodiPilihan === [] ? 'disabled' : '' ?>>Simpan</button>
                    <a class="btn btn-outline-secondary" href="dosen.php">Batal</a>
                </div>
            </form>
        </div>
    </div>
<?php else : ?>
    <!-- MODE DAFTAR: tabel + tombol tambah -->
    <div class="alert alert-secondary small" role="note">
        <strong>Daftar dosen:</strong> gunakan <em>Tambah</em> untuk data baru. Tombol <em>Ubah</em> / <em>Hapus</em> ada di setiap baris.
        Hapus bisa gagal jika dosen masih dipakai sebagai pengampu mata kuliah. Isi prodi dulu di menu <a href="prodi.php">Prodi</a> jika dropdown kosong.
    </div>
    <p><a class="btn btn-primary" href="dosen.php?aksi=tambah"><i class="bi bi-plus-lg"></i> Tambah dosen</a></p>
    <!-- table-responsive = scroll horizontal di layar sempit -->
    <div class="table-responsive card border-0 shadow-sm">
        <table class="table table-striped table-hover mb-0">
            <thead class="table-primary">
            <tr>
                <th scope="col">#</th>
                <th scope="col">NIDN</th>
                <th scope="col">Nama</th>
                <th scope="col">Prodi</th>
                <th scope="col">Email</th>
                <th scope="col" style="width: 9rem">Aksi</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($daftarDosen === []) : ?>
                <!-- colspan 5 = satu sel melebar 5 kolom -->
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data dosen.</td></tr>
            <?php else : ?>
                <?php
                // Loop: $i indeks 0,1,2...; $r satu array asosiatif per baris
                foreach ($daftarDosen as $i => $r) :
                    ?>
                    <tr>
                        <!-- Nomor urut tampilan (bukan id database) -->
                        <td><?= $i + 1 ?></td>
                        <td><?= h((string) $r['nidn']) ?></td>
                        <td><?= h((string) $r['nama']) ?></td>
                        <td><?= h((string) $r['kode_prodi']) ?> — <?= h((string) $r['nama_prodi']) ?></td>
                        <td><?= h((string) ($r['email'] ?? '')) ?></td>
                        <td class="d-flex flex-wrap gap-1">
                            <!-- Link GET ke mode ubah dengan id -->
                            <a class="btn btn-sm btn-outline-primary" href="dosen.php?aksi=ubah&id=<?= (int) $r['id_dosen'] ?>">Ubah</a>
                            <!-- Form POST untuk hapus: lebih aman daripada GET -->
                            <form method="post" action="dosen.php" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus dosen ini?');">
                                <input type="hidden" name="aksi" value="hapus">
                                <input type="hidden" name="id_dosen" value="<?= (int) $r['id_dosen'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php
// Tutup layout: footer + script
require_once __DIR__ . '/includes/footer.php';
?>
