<?php
/**
 * =============================================================================
 * MODUL CRUD — TABEL PRODI (Program Studi)
 * =============================================================================
 * Tabel master: dosen, mahasiswa, dan mata kuliah merujuk ke program studi ini.
 * Isi prodi terlebih dahulu sebelum dosen/mahasiswa/mata kuliah (alur data).
 * =============================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/fungsi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    try {
        if ($aksi === 'simpan_tambah') {
            $kode = trim((string) ($_POST['kode_prodi'] ?? ''));
            $nama = trim((string) ($_POST['nama_prodi'] ?? ''));

            if ($kode === '' || $nama === '') {
                header('Location: prodi.php?status=tidak_valid&msg=' . rawurlencode('Kode dan nama program studi wajib diisi.'));
                exit;
            }

            $stmt = $pdo->prepare('INSERT INTO prodi (kode_prodi, nama_prodi) VALUES (?, ?)');
            $stmt->execute([$kode, $nama]);
            header('Location: prodi.php?status=simpan_ok');
            exit;
        }

        if ($aksi === 'simpan_ubah') {
            $id = (int) ($_POST['id_prodi'] ?? 0);
            $kode = trim((string) ($_POST['kode_prodi'] ?? ''));
            $nama = trim((string) ($_POST['nama_prodi'] ?? ''));

            if ($id < 1 || $kode === '' || $nama === '') {
                header('Location: prodi.php?status=tidak_valid&msg=' . rawurlencode('Data tidak lengkap.'));
                exit;
            }

            $stmt = $pdo->prepare('UPDATE prodi SET kode_prodi = ?, nama_prodi = ? WHERE id_prodi = ?');
            $stmt->execute([$kode, $nama, $id]);
            header('Location: prodi.php?status=simpan_ok');
            exit;
        }

        if ($aksi === 'hapus') {
            $id = (int) ($_POST['id_prodi'] ?? 0);
            if ($id < 1) {
                header('Location: prodi.php?status=tidak_valid');
                exit;
            }
            $stmt = $pdo->prepare('DELETE FROM prodi WHERE id_prodi = ?');
            $stmt->execute([$id]);
            header('Location: prodi.php?status=hapus_ok');
            exit;
        }
    } catch (PDOException $e) {
        $code = (int) ($e->errorInfo[1] ?? 0);
        if ($code === 23000 || $code === 1062) {
            header('Location: prodi.php?status=duplikat&msg=' . rawurlencode('Kode sudah ada atau prodi masih dipakai data lain.'));
            exit;
        }
        header('Location: prodi.php?status=gagal&msg=' . rawurlencode($e->getMessage()));
        exit;
    }
}

$aksiGet = $_GET['aksi'] ?? 'daftar';
$idEdit = (int) ($_GET['id'] ?? 0);

$barisEdit = null;
if ($aksiGet === 'ubah' && $idEdit > 0) {
    $stmt = $pdo->prepare('SELECT * FROM prodi WHERE id_prodi = ?');
    $stmt->execute([$idEdit]);
    $barisEdit = $stmt->fetch();
    if (!$barisEdit) {
        $aksiGet = 'daftar';
    }
}

$daftar = $pdo->query('SELECT * FROM prodi ORDER BY kode_prodi ASC')->fetchAll(PDO::FETCH_ASSOC);

$judulHalaman = 'Data Program Studi';
require_once __DIR__ . '/includes/header.php';
?>

<?php if ($aksiGet === 'tambah' || ($aksiGet === 'ubah' && $barisEdit)) : ?>
    <div class="alert alert-info small" role="note">
        <strong>Petunjuk:</strong> Kode prodi singkat (mis. TI, SI); nama lengkap program studi.
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="post" class="row g-3" action="prodi.php">
                <input type="hidden" name="aksi" value="<?= $aksiGet === 'tambah' ? 'simpan_tambah' : 'simpan_ubah' ?>">
                <?php if ($aksiGet === 'ubah' && $barisEdit) : ?>
                    <input type="hidden" name="id_prodi" value="<?= (int) $barisEdit['id_prodi'] ?>">
                <?php endif; ?>

                <div class="col-md-4">
                    <label class="form-label" for="kode_prodi">Kode prodi</label>
                    <input class="form-control" id="kode_prodi" name="kode_prodi" required maxlength="20"
                           value="<?= $barisEdit ? h((string) $barisEdit['kode_prodi']) : '' ?>">
                </div>
                <div class="col-md-8">
                    <label class="form-label" for="nama_prodi">Nama program studi</label>
                    <input class="form-control" id="nama_prodi" name="nama_prodi" required maxlength="160"
                           value="<?= $barisEdit ? h((string) $barisEdit['nama_prodi']) : '' ?>">
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a class="btn btn-outline-secondary" href="prodi.php">Batal</a>
                </div>
            </form>
        </div>
    </div>
<?php else : ?>
    <div class="alert alert-secondary small" role="note">
        <strong>Daftar prodi:</strong> hapus dapat gagal jika masih dipakai dosen, mahasiswa, atau mata kuliah.
    </div>
    <p><a class="btn btn-primary" href="prodi.php?aksi=tambah"><i class="bi bi-plus-lg"></i> Tambah program studi</a></p>
    <div class="table-responsive card border-0 shadow-sm">
        <table class="table table-striped table-hover mb-0">
            <thead class="table-primary">
            <tr>
                <th scope="col">#</th>
                <th scope="col">Kode</th>
                <th scope="col">Nama program studi</th>
                <th scope="col" style="width: 9rem">Aksi</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($daftar === []) : ?>
                <tr><td colspan="4" class="text-center text-muted py-4">Belum ada data prodi.</td></tr>
            <?php else : ?>
                <?php foreach ($daftar as $i => $r) : ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= h((string) $r['kode_prodi']) ?></td>
                        <td><?= h((string) $r['nama_prodi']) ?></td>
                        <td class="d-flex flex-wrap gap-1">
                            <a class="btn btn-sm btn-outline-primary" href="prodi.php?aksi=ubah&id=<?= (int) $r['id_prodi'] ?>">Ubah</a>
                            <form method="post" action="prodi.php" class="d-inline" onsubmit="return confirm('Yakin menghapus prodi ini?');">
                                <input type="hidden" name="aksi" value="hapus">
                                <input type="hidden" name="id_prodi" value="<?= (int) $r['id_prodi'] ?>">
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
