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
            $idDosen = (int) ($_POST['id_dosen'] ?? 0);
            $idProdi = (int) ($_POST['id_prodi'] ?? 0);

            if ($kode === '' || $nama === '' || $sks < 1 || $sks > 24 || $idDosen < 1 || $idProdi < 1) {
                header('Location: matakuliah.php?status=tidak_valid&msg=' . rawurlencode('Kode, nama, SKS (1–24), prodi, dan dosen wajib diisi benar.'));
                exit;
            }

            // Dosen pengampu harus dari program studi yang sama dengan MK
            $cekDos = $pdo->prepare('SELECT id_prodi FROM dosen WHERE id_dosen = ?');
            $cekDos->execute([$idDosen]);
            $rowDos = $cekDos->fetch();
            if (!$rowDos || (int) $rowDos['id_prodi'] !== $idProdi) {
                header('Location: matakuliah.php?status=tidak_valid&msg=' . rawurlencode('Dosen pengampu harus berasal dari program studi yang sama dengan mata kuliah.'));
                exit;
            }

            $stmt = $pdo->prepare('INSERT INTO matakuliah (kode_mk, nama_mk, sks, id_dosen, id_prodi) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$kode, $nama, $sks, $idDosen, $idProdi]);
            header('Location: matakuliah.php?status=simpan_ok');
            exit;
        }

        if ($aksi === 'simpan_ubah') {
            $id = (int) ($_POST['id_mk'] ?? 0);
            $kode = trim((string) ($_POST['kode_mk'] ?? ''));
            $nama = trim((string) ($_POST['nama_mk'] ?? ''));
            $sks = (int) ($_POST['sks'] ?? 0);
            $idDosen = (int) ($_POST['id_dosen'] ?? 0);
            $idProdi = (int) ($_POST['id_prodi'] ?? 0);

            if ($id < 1 || $kode === '' || $nama === '' || $sks < 1 || $sks > 24 || $idDosen < 1 || $idProdi < 1) {
                header('Location: matakuliah.php?status=tidak_valid&msg=' . rawurlencode('Data tidak valid.'));
                exit;
            }

            $cekDos = $pdo->prepare('SELECT id_prodi FROM dosen WHERE id_dosen = ?');
            $cekDos->execute([$idDosen]);
            $rowDos = $cekDos->fetch();
            if (!$rowDos || (int) $rowDos['id_prodi'] !== $idProdi) {
                header('Location: matakuliah.php?status=tidak_valid&msg=' . rawurlencode('Dosen pengampu harus berasal dari program studi yang sama dengan mata kuliah.'));
                exit;
            }

            $stmt = $pdo->prepare('UPDATE matakuliah SET kode_mk = ?, nama_mk = ?, sks = ?, id_dosen = ?, id_prodi = ? WHERE id_mk = ?');
            $stmt->execute([$kode, $nama, $sks, $idDosen, $idProdi, $id]);
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

$prodiPilihan = $pdo->query('SELECT id_prodi, kode_prodi, nama_prodi FROM prodi ORDER BY kode_prodi ASC')->fetchAll(PDO::FETCH_ASSOC);

// Dosen beserta id_prodi (untuk dropdown & konsistensi dengan MK)
$dosenPilihan = $pdo->query(
    'SELECT d.id_dosen, d.nidn, d.nama, d.id_prodi, p.kode_prodi
     FROM dosen d
     INNER JOIN prodi p ON p.id_prodi = d.id_prodi
     ORDER BY d.nama ASC'
)->fetchAll(PDO::FETCH_ASSOC);

// MK + dosen + prodi
$daftar = $pdo->query(
    'SELECT mk.*, d.nama AS nama_dosen, d.nidn, p.kode_prodi, p.nama_prodi
     FROM matakuliah mk
     INNER JOIN dosen d ON d.id_dosen = mk.id_dosen
     INNER JOIN prodi p ON p.id_prodi = mk.id_prodi
     ORDER BY mk.kode_mk ASC'
)->fetchAll(PDO::FETCH_ASSOC);

$judulHalaman = 'Data Mata Kuliah';
require_once __DIR__ . '/includes/header.php';
?>

<?php if ($aksiGet === 'tambah' || ($aksiGet === 'ubah' && $barisEdit)) : ?>
    <div class="alert alert-info small" role="note">
        <strong>Petunjuk:</strong> Pilih program studi, lalu dosen pengampu <em>dari prodi yang sama</em>. SKS biasanya 2–4 per mata kuliah.
    </div>
    <?php if ($prodiPilihan === []) : ?>
        <div class="alert alert-warning">Belum ada data prodi. Tambah di menu <a href="prodi.php">Program studi</a>.</div>
    <?php endif; ?>
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
                <div class="col-md-6">
                    <label class="form-label" for="id_prodi">Program studi (MK)</label>
                    <select class="form-select" id="id_prodi" name="id_prodi" required <?= ($prodiPilihan === []) ? 'disabled' : '' ?>>
                        <option value="">— pilih prodi —</option>
                        <?php foreach ($prodiPilihan as $p) : ?>
                            <?php $sel = $barisEdit && (int) $barisEdit['id_prodi'] === (int) $p['id_prodi'] ? ' selected' : ''; ?>
                            <option value="<?= (int) $p['id_prodi'] ?>"<?= $sel ?>><?= h((string) $p['kode_prodi']) ?> — <?= h((string) $p['nama_prodi']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="id_dosen">Dosen pengampu (se-prodi)</label>
                    <select class="form-select" id="id_dosen" name="id_dosen" required <?= $dosenPilihan === [] ? 'disabled' : '' ?>>
                        <option value="">— pilih dosen —</option>
                        <?php foreach ($dosenPilihan as $d) : ?>
                            <?php $sel = $barisEdit && (int) $barisEdit['id_dosen'] === (int) $d['id_dosen'] ? ' selected' : ''; ?>
                            <option value="<?= (int) $d['id_dosen'] ?>"<?= $sel ?>><?= h((string) $d['nidn']) ?> — <?= h((string) $d['nama']) ?> [<?= h((string) $d['kode_prodi']) ?>]</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary" <?= ($prodiPilihan === [] || $dosenPilihan === []) ? 'disabled' : '' ?>>Simpan</button>
                    <a class="btn btn-outline-secondary" href="matakuliah.php">Batal</a>
                </div>
            </form>
        </div>
    </div>
<?php else : ?>
    <div class="alert alert-secondary small" role="note">
        <strong>Daftar mata kuliah:</strong> kolom prodi & dosen dari relasi. Hapus dapat gagal jika MK masih dipakai di KRS.
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
                <th scope="col">Prodi</th>
                <th scope="col">Dosen pengampu</th>
                <th scope="col" style="width: 9rem">Aksi</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($daftar === []) : ?>
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada data mata kuliah.</td></tr>
            <?php else : ?>
                <?php foreach ($daftar as $i => $r) : ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= h((string) $r['kode_mk']) ?></td>
                        <td><?= h((string) $r['nama_mk']) ?></td>
                        <td><?= (int) $r['sks'] ?></td>
                        <td><?= h((string) $r['kode_prodi']) ?></td>
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
