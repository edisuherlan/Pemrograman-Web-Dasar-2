<?php
/**
 * =============================================================================
 * MODUL CRUD — TABEL MAHASISWA
 * =============================================================================
 * Yang Anda kelola:
 * - NIM (unik per mahasiswa), nama, email opsional, dan angkatan (tahun masuk).
 *
 * Hubungan dengan tabel lain:
 * - Mahasiswa akan dipilih saat membuat KRS (kartu rencana studi).
 * - Jika mahasiswa dihapus, KRS miliknya ikut terhapus (lihat definisi foreign key
 *   ON DELETE CASCADE di database — artinya data turunan ikut bersih).
 *
 * Tips membaca kode:
 * - Pola file ini sama seperti dosen.php: POST dulu, baru tampilkan HTML.
 * - Field tahun (angkatan) di HTML memakai input type="number" agar mudah dicek.
 * =============================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/fungsi.php';

/* --- Bagian 1: proses form (POST) — harus sebelum mencetak HTML --- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Jenis aksi dari input hidden di form
    $aksi = $_POST['aksi'] ?? '';

    try {
        /* --- Simpan data mahasiswa BARU --- */
        if ($aksi === 'simpan_tambah') {
            $nim = trim((string) ($_POST['nim'] ?? ''));
            $nama = trim((string) ($_POST['nama'] ?? ''));
            $email = trim((string) ($_POST['email'] ?? ''));
            // (int) mengubah string angka dari form menjadi integer (tahun angkatan)
            $angkatan = (int) ($_POST['angkatan'] ?? 0);
            $idProdi = (int) ($_POST['id_prodi'] ?? 0);

            // Batas angkatan 2000–2100: sesuaikan rentang jika aturan kampus beda
            if ($nim === '' || $nama === '' || $angkatan < 2000 || $angkatan > 2100 || $idProdi < 1) {
                header('Location: mahasiswa.php?status=tidak_valid&msg=' . rawurlencode('NIM, nama, angkatan (2000–2100), dan prodi wajib valid.'));
                exit;
            }

            $stmt = $pdo->prepare('INSERT INTO mahasiswa (nim, nama, email, angkatan, id_prodi) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$nim, $nama, $email !== '' ? $email : null, $angkatan, $idProdi]);
            header('Location: mahasiswa.php?status=simpan_ok');
            exit;
        }

        /* --- Update mahasiswa yang sudah ada (punya id_mahasiswa) --- */
        if ($aksi === 'simpan_ubah') {
            $id = (int) ($_POST['id_mahasiswa'] ?? 0);
            $nim = trim((string) ($_POST['nim'] ?? ''));
            $nama = trim((string) ($_POST['nama'] ?? ''));
            $email = trim((string) ($_POST['email'] ?? ''));
            $angkatan = (int) ($_POST['angkatan'] ?? 0);
            $idProdi = (int) ($_POST['id_prodi'] ?? 0);

            if ($id < 1 || $nim === '' || $nama === '' || $angkatan < 2000 || $angkatan > 2100 || $idProdi < 1) {
                header('Location: mahasiswa.php?status=tidak_valid&msg=' . rawurlencode('Data tidak lengkap.'));
                exit;
            }

            $stmt = $pdo->prepare('UPDATE mahasiswa SET nim = ?, nama = ?, email = ?, angkatan = ?, id_prodi = ? WHERE id_mahasiswa = ?');
            $stmt->execute([$nim, $nama, $email !== '' ? $email : null, $angkatan, $idProdi, $id]);
            header('Location: mahasiswa.php?status=simpan_ok');
            exit;
        }

        /* --- Hapus satu mahasiswa berdasarkan id --- */
        if ($aksi === 'hapus') {
            $id = (int) ($_POST['id_mahasiswa'] ?? 0);
            if ($id < 1) {
                header('Location: mahasiswa.php?status=tidak_valid');
                exit;
            }
            $stmt = $pdo->prepare('DELETE FROM mahasiswa WHERE id_mahasiswa = ?');
            $stmt->execute([$id]);
            header('Location: mahasiswa.php?status=hapus_ok');
            exit;
        }
    } catch (PDOException $e) {
        // Duplikat NIM atau pelanggaran foreign key
        $code = (int) ($e->errorInfo[1] ?? 0);
        if ($code === 23000 || $code === 1062) {
            header('Location: mahasiswa.php?status=duplikat&msg=' . rawurlencode('NIM sudah terdaftar.'));
            exit;
        }
        header('Location: mahasiswa.php?status=gagal&msg=' . rawurlencode($e->getMessage()));
        exit;
    }
}

/* --- Bagian 2: siapkan data untuk tampilan (GET) --- */
$aksiGet = $_GET['aksi'] ?? 'daftar';
$idEdit = (int) ($_GET['id'] ?? 0);

$barisEdit = null;
if ($aksiGet === 'ubah' && $idEdit > 0) {
    $stmt = $pdo->prepare('SELECT * FROM mahasiswa WHERE id_mahasiswa = ?');
    $stmt->execute([$idEdit]);
    $barisEdit = $stmt->fetch();
    if (!$barisEdit) {
        $aksiGet = 'daftar';
    }
}

$prodiPilihan = $pdo->query('SELECT id_prodi, kode_prodi, nama_prodi FROM prodi ORDER BY kode_prodi ASC')->fetchAll(PDO::FETCH_ASSOC);

// Mahasiswa beserta nama prodi
$daftar = $pdo->query(
    'SELECT m.*, p.kode_prodi, p.nama_prodi
     FROM mahasiswa m
     INNER JOIN prodi p ON p.id_prodi = m.id_prodi
     ORDER BY m.nim ASC'
)->fetchAll(PDO::FETCH_ASSOC);

$judulHalaman = 'Data Mahasiswa';
require_once __DIR__ . '/includes/header.php';
?>

<?php if ($aksiGet === 'tambah' || ($aksiGet === 'ubah' && $barisEdit)) : ?>
    <div class="alert alert-info small" role="note">
        <strong>Petunjuk:</strong> NIM harus unik. Pilih program studi. Angkatan diisi tahun empat digit (contoh: 2024). Email boleh dikosongkan.
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="post" class="row g-3" action="mahasiswa.php">
                <input type="hidden" name="aksi" value="<?= $aksiGet === 'tambah' ? 'simpan_tambah' : 'simpan_ubah' ?>">
                <?php if ($aksiGet === 'ubah' && $barisEdit) : ?>
                    <input type="hidden" name="id_mahasiswa" value="<?= (int) $barisEdit['id_mahasiswa'] ?>">
                <?php endif; ?>

                <div class="col-md-4">
                    <label class="form-label" for="nim">NIM</label>
                    <input class="form-control" id="nim" name="nim" required maxlength="20"
                           value="<?= $barisEdit ? h((string) $barisEdit['nim']) : '' ?>">
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
                <div class="col-md-6">
                    <label class="form-label" for="email">Email (opsional)</label>
                    <input class="form-control" id="email" name="email" type="email" maxlength="120"
                           value="<?= $barisEdit ? h((string) ($barisEdit['email'] ?? '')) : '' ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="angkatan">Angkatan (tahun)</label>
                    <!-- min/max membantu browser memvalidasi rentang tahun -->
                    <input class="form-control" id="angkatan" name="angkatan" type="number" required min="2000" max="2100"
                           value="<?= $barisEdit ? (int) $barisEdit['angkatan'] : 2024 ?>">
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary" <?= $prodiPilihan === [] ? 'disabled' : '' ?>>Simpan</button>
                    <a class="btn btn-outline-secondary" href="mahasiswa.php">Batal</a>
                </div>
            </form>
        </div>
    </div>
<?php else : ?>
    <div class="alert alert-secondary small" role="note">
        <strong>Daftar mahasiswa:</strong> dari sini Anda bisa menambah, mengubah, atau menghapus. Menghapus mahasiswa akan menghapus
        juga KRS dan nilai yang terkait (cascade) sesuai skema database.
    </div>
    <p><a class="btn btn-primary" href="mahasiswa.php?aksi=tambah"><i class="bi bi-plus-lg"></i> Tambah mahasiswa</a></p>
    <div class="table-responsive card border-0 shadow-sm">
        <table class="table table-striped table-hover mb-0">
            <thead class="table-primary">
            <tr>
                <th scope="col">#</th>
                <th scope="col">NIM</th>
                <th scope="col">Nama</th>
                <th scope="col">Prodi</th>
                <th scope="col">Email</th>
                <th scope="col">Angkatan</th>
                <th scope="col" style="width: 9rem">Aksi</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($daftar === []) : ?>
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada data mahasiswa.</td></tr>
            <?php else : ?>
                <?php foreach ($daftar as $i => $r) : ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= h((string) $r['nim']) ?></td>
                        <td><?= h((string) $r['nama']) ?></td>
                        <td><?= h((string) $r['kode_prodi']) ?> — <?= h((string) $r['nama_prodi']) ?></td>
                        <td><?= h((string) ($r['email'] ?? '')) ?></td>
                        <td><?= (int) $r['angkatan'] ?></td>
                        <td class="d-flex flex-wrap gap-1">
                            <a class="btn btn-sm btn-outline-primary" href="mahasiswa.php?aksi=ubah&id=<?= (int) $r['id_mahasiswa'] ?>">Ubah</a>
                            <form method="post" action="mahasiswa.php" class="d-inline" onsubmit="return confirm('Yakin menghapus mahasiswa ini beserta KRS/nilai terkait?');">
                                <input type="hidden" name="aksi" value="hapus">
                                <input type="hidden" name="id_mahasiswa" value="<?= (int) $r['id_mahasiswa'] ?>">
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
