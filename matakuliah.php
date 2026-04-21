<?php
/**
 * =============================================================================
 * MODUL CRUD — TABEL MATA KULIAH
 * =============================================================================
 * Yang dikelola:
 * - Kode mata kuliah (unik), nama mata kuliah, SKS, dan dosen pengampu (wajib).
 *
 * Relasi:
 * - Kolom id_dosen menghubungkan ke tabel dosen. Satu dosen bisa mengampu banyak
 *   mata kuliah (hubungan satu-ke-banyak).
 *
 * Tips:
 * - Di form, dosen ditampilkan sebagai <select> (dropdown) yang diisi dari tabel dosen.
 *   Ini lebih aman daripada mengetik ID manual (mengurangi salah input).
 * - Jika daftar dosen kosong, isi dulu halaman Dosen.
 * =============================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/fungsi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    try {
        if ($aksi === 'simpan_tambah') {
            $kode = trim((string) ($_POST['kode_mk'] ?? ''));
            $nama = trim((string) ($_POST['nama_mk'] ?? ''));
            $sks = (int) ($_POST['sks'] ?? 0);
            // id_dosen dari <select>: harus angka ID yang valid di tabel dosen
            $idDosen = (int) ($_POST['id_dosen'] ?? 0);

            if ($kode === '' || $nama === '' || $sks < 1 || $sks > 24 || $idDosen < 1) {
                header('Location: matakuliah.php?status=tidak_valid&msg=' . rawurlencode('Kode, nama, SKS (1–24), dan dosen wajib diisi benar.'));
                exit;
            }

            $stmt = $pdo->prepare('INSERT INTO matakuliah (kode_mk, nama_mk, sks, id_dosen) VALUES (?, ?, ?, ?)');
            $stmt->execute([$kode, $nama, $sks, $idDosen]);
            header('Location: matakuliah.php?status=simpan_ok');
            exit;
        }

        if ($aksi === 'simpan_ubah') {
            $id = (int) ($_POST['id_mk'] ?? 0);
            $kode = trim((string) ($_POST['kode_mk'] ?? ''));
            $nama = trim((string) ($_POST['nama_mk'] ?? ''));
            $sks = (int) ($_POST['sks'] ?? 0);
            $idDosen = (int) ($_POST['id_dosen'] ?? 0);

            if ($id < 1 || $kode === '' || $nama === '' || $sks < 1 || $sks > 24 || $idDosen < 1) {
                header('Location: matakuliah.php?status=tidak_valid&msg=' . rawurlencode('Data tidak valid.'));
                exit;
            }

            $stmt = $pdo->prepare('UPDATE matakuliah SET kode_mk = ?, nama_mk = ?, sks = ?, id_dosen = ? WHERE id_mk = ?');
            $stmt->execute([$kode, $nama, $sks, $idDosen, $id]);
            header('Location: matakuliah.php?status=simpan_ok');
            exit;
        }

        if ($aksi === 'hapus') {
            $id = (int) ($_POST['id_mk'] ?? 0);
            if ($id < 1) {
                header('Location: matakuliah.php?status=tidak_valid');
                exit;
            }
            $stmt = $pdo->prepare('DELETE FROM matakuliah WHERE id_mk = ?');
            $stmt->execute([$id]);
            header('Location: matakuliah.php?status=hapus_ok');
            exit;
        }
    } catch (PDOException $e) {
        $code = (int) ($e->errorInfo[1] ?? 0);
        if ($code === 23000 || $code === 1062) {
            header('Location: matakuliah.php?status=duplikat&msg=' . rawurlencode('Kode MK sudah ada atau MK masih dipakai di KRS.'));
            exit;
        }
        header('Location: matakuliah.php?status=gagal&msg=' . rawurlencode($e->getMessage()));
        exit;
    }
}

$aksiGet = $_GET['aksi'] ?? 'daftar';
$idEdit = (int) ($_GET['id'] ?? 0);

$barisEdit = null;
if ($aksiGet === 'ubah' && $idEdit > 0) {
    $stmt = $pdo->prepare('SELECT * FROM matakuliah WHERE id_mk = ?');
    $stmt->execute([$idEdit]);
    $barisEdit = $stmt->fetch();
    if (!$barisEdit) {
        $aksiGet = 'daftar';
    }
}

// Semua dosen untuk mengisi dropdown (urut nama)
$dosenPilihan = $pdo->query('SELECT id_dosen, nidn, nama FROM dosen ORDER BY nama ASC')->fetchAll(PDO::FETCH_ASSOC);

// JOIN: ambil MK + nama dosen sekaligus untuk tampilan tabel (tanpa perlu query kedua)
$daftar = $pdo->query(
    'SELECT mk.*, d.nama AS nama_dosen, d.nidn
     FROM matakuliah mk
     INNER JOIN dosen d ON d.id_dosen = mk.id_dosen
     ORDER BY mk.kode_mk ASC'
)->fetchAll(PDO::FETCH_ASSOC);

$judulHalaman = 'Data Mata Kuliah';
require_once __DIR__ . '/includes/header.php';
?>

<?php if ($aksiGet === 'tambah' || ($aksiGet === 'ubah' && $barisEdit)) : ?>
    <div class="alert alert-info small" role="note">
        <strong>Petunjuk:</strong> Pilih dosen pengampu dari dropdown. SKS biasanya 2–4 per mata kuliah (sesuaikan aturan kampus Anda).
    </div>
    <?php if ($dosenPilihan === []) : ?>
        <div class="alert alert-warning">Belum ada data dosen. Silakan tambah dulu di menu <a href="dosen.php">Dosen</a>.</div>
    <?php endif; ?>
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="post" class="row g-3" action="matakuliah.php">
                <input type="hidden" name="aksi" value="<?= $aksiGet === 'tambah' ? 'simpan_tambah' : 'simpan_ubah' ?>">
                <?php if ($aksiGet === 'ubah' && $barisEdit) : ?>
                    <input type="hidden" name="id_mk" value="<?= (int) $barisEdit['id_mk'] ?>">
                <?php endif; ?>

                <div class="col-md-3">
                    <label class="form-label" for="kode_mk">Kode MK</label>
                    <input class="form-control" id="kode_mk" name="kode_mk" required maxlength="20"
                           value="<?= $barisEdit ? h((string) $barisEdit['kode_mk']) : '' ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="nama_mk">Nama mata kuliah</label>
                    <input class="form-control" id="nama_mk" name="nama_mk" required maxlength="160"
                           value="<?= $barisEdit ? h((string) $barisEdit['nama_mk']) : '' ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="sks">SKS</label>
                    <input class="form-control" id="sks" name="sks" type="number" required min="1" max="24"
                           value="<?= $barisEdit ? (int) $barisEdit['sks'] : 3 ?>">
                </div>
                <div class="col-12">
                    <label class="form-label" for="id_dosen">Dosen pengampu</label>
                    <!-- disabled jika tidak ada dosen: mencegah submit data tidak konsisten -->
                    <select class="form-select" id="id_dosen" name="id_dosen" required <?= $dosenPilihan === [] ? 'disabled' : '' ?>>
                        <option value="">— pilih dosen —</option>
                        <?php foreach ($dosenPilihan as $d) : ?>
                            <?php
                            // Tandai option yang sama dengan id_dosen baris sedang diubah
                            $sel = $barisEdit && (int) $barisEdit['id_dosen'] === (int) $d['id_dosen'] ? ' selected' : '';
                            ?>
                            <option value="<?= (int) $d['id_dosen'] ?>"<?= $sel ?>><?= h((string) $d['nidn']) ?> — <?= h((string) $d['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary" <?= $dosenPilihan === [] ? 'disabled' : '' ?>>Simpan</button>
                    <a class="btn btn-outline-secondary" href="matakuliah.php">Batal</a>
                </div>
            </form>
        </div>
    </div>
<?php else : ?>
    <div class="alert alert-secondary small" role="note">
        <strong>Daftar mata kuliah:</strong> kolom dosen diisi dari relasi ke tabel dosen. Hapus dapat gagal jika MK masih dipakai di KRS.
    </div>
    <p><a class="btn btn-primary" href="matakuliah.php?aksi=tambah"><i class="bi bi-plus-lg"></i> Tambah mata kuliah</a></p>
    <div class="table-responsive card border-0 shadow-sm">
        <table class="table table-striped table-hover mb-0">
            <thead class="table-primary">
            <tr>
                <th scope="col">#</th>
                <th scope="col">Kode</th>
                <th scope="col">Nama MK</th>
                <th scope="col">SKS</th>
                <th scope="col">Dosen pengampu</th>
                <th scope="col" style="width: 9rem">Aksi</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($daftar === []) : ?>
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data mata kuliah.</td></tr>
            <?php else : ?>
                <?php foreach ($daftar as $i => $r) : ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= h((string) $r['kode_mk']) ?></td>
                        <td><?= h((string) $r['nama_mk']) ?></td>
                        <td><?= (int) $r['sks'] ?></td>
                        <td><?= h((string) $r['nama_dosen']) ?> <span class="text-muted small">(<?= h((string) $r['nidn']) ?>)</span></td>
                        <td class="d-flex flex-wrap gap-1">
                            <a class="btn btn-sm btn-outline-primary" href="matakuliah.php?aksi=ubah&id=<?= (int) $r['id_mk'] ?>">Ubah</a>
                            <form method="post" action="matakuliah.php" class="d-inline" onsubmit="return confirm('Yakin menghapus mata kuliah ini?');">
                                <input type="hidden" name="aksi" value="hapus">
                                <input type="hidden" name="id_mk" value="<?= (int) $r['id_mk'] ?>">
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
