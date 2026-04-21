<?php
/**
 * =============================================================================
 * MODUL CRUD — TABEL KRS (Kartu Rencana Studi)
 * =============================================================================
 * Konsep:
 * - Satu baris KRS menyatakan: seorang mahasiswa mengambil sebuah mata kuliah
 *   pada semester dan tahun ajaran tertentu.
 * - Tidak boleh ada duplikasi kombinasi (mahasiswa + MK + semester + tahun ajaran)
 *   karena ada UNIQUE di database — jika dobel, simpan akan ditolak.
 *
 * Form:
 * - Mahasiswa dan mata kuliah dipilih lewat <select> agar ID-nya konsisten.
 * - Semester memakai nilai enum di MySQL: gasal atau genap.
 * - Tahun ajaran contoh format: 2025/2026 (teks 9 karakter sesuai kolom VARCHAR).
 *
 * Relasi ke nilai:
 * - Setelah KRS ada, Anda bisa mengisi nilai di menu Nilai (satu nilai per KRS).
 * =============================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/fungsi.php';

/**
 * Pastikan mahasiswa dan mata kuliah satu program studi (atur bisnis KRS).
 */
function krs_prodi_cocok(PDO $pdo, int $idMahasiswa, int $idMk): bool
{
    $st = $pdo->prepare(
        'SELECT m.id_prodi AS p_mhs, mk.id_prodi AS p_mk
         FROM mahasiswa m, matakuliah mk
         WHERE m.id_mahasiswa = ? AND mk.id_mk = ?'
    );
    $st->execute([$idMahasiswa, $idMk]);
    $row = $st->fetch(PDO::FETCH_ASSOC);

    return $row !== false && (int) $row['p_mhs'] === (int) $row['p_mk'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    try {
        if ($aksi === 'simpan_tambah') {
            $idMhs = (int) ($_POST['id_mahasiswa'] ?? 0);
            $idMk = (int) ($_POST['id_mk'] ?? 0);
            $semester = trim((string) ($_POST['semester'] ?? ''));
            $thn = trim((string) ($_POST['tahun_ajaran'] ?? ''));

            // Pastikan semester persis 'gasal' atau 'genap' (sesuai ENUM di MySQL)
            if ($idMhs < 1 || $idMk < 1 || ($semester !== 'gasal' && $semester !== 'genap') || $thn === '') {
                header('Location: krs.php?status=tidak_valid&msg=' . rawurlencode('Pilih mahasiswa & MK, semester, dan tahun ajaran.'));
                exit;
            }

            if (!krs_prodi_cocok($pdo, $idMhs, $idMk)) {
                header('Location: krs.php?status=tidak_valid&msg=' . rawurlencode('Mahasiswa dan mata kuliah harus dari program studi yang sama.'));
                exit;
            }

            $stmt = $pdo->prepare(
                'INSERT INTO krs (id_mahasiswa, id_mk, semester, tahun_ajaran) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([$idMhs, $idMk, $semester, $thn]);
            header('Location: krs.php?status=simpan_ok');
            exit;
        }

        if ($aksi === 'simpan_ubah') {
            $id = (int) ($_POST['id_krs'] ?? 0);
            $idMhs = (int) ($_POST['id_mahasiswa'] ?? 0);
            $idMk = (int) ($_POST['id_mk'] ?? 0);
            $semester = trim((string) ($_POST['semester'] ?? ''));
            $thn = trim((string) ($_POST['tahun_ajaran'] ?? ''));

            if ($id < 1 || $idMhs < 1 || $idMk < 1 || ($semester !== 'gasal' && $semester !== 'genap') || $thn === '') {
                header('Location: krs.php?status=tidak_valid&msg=' . rawurlencode('Data tidak valid.'));
                exit;
            }

            if (!krs_prodi_cocok($pdo, $idMhs, $idMk)) {
                header('Location: krs.php?status=tidak_valid&msg=' . rawurlencode('Mahasiswa dan mata kuliah harus dari program studi yang sama.'));
                exit;
            }

            $stmt = $pdo->prepare(
                'UPDATE krs SET id_mahasiswa = ?, id_mk = ?, semester = ?, tahun_ajaran = ? WHERE id_krs = ?'
            );
            $stmt->execute([$idMhs, $idMk, $semester, $thn, $id]);
            header('Location: krs.php?status=simpan_ok');
            exit;
        }

        if ($aksi === 'hapus') {
            $id = (int) ($_POST['id_krs'] ?? 0);
            if ($id < 1) {
                header('Location: krs.php?status=tidak_valid');
                exit;
            }
            $stmt = $pdo->prepare('DELETE FROM krs WHERE id_krs = ?');
            $stmt->execute([$id]);
            header('Location: krs.php?status=hapus_ok');
            exit;
        }
    } catch (PDOException $e) {
        $code = (int) ($e->errorInfo[1] ?? 0);
        if ($code === 23000 || $code === 1062) {
            header('Location: krs.php?status=duplikat&msg=' . rawurlencode('KRS bentrok: mahasiswa sudah mengambil MK yang sama di semester & tahun itu.'));
            exit;
        }
        header('Location: krs.php?status=gagal&msg=' . rawurlencode($e->getMessage()));
        exit;
    }
}

$aksiGet = $_GET['aksi'] ?? 'daftar';
$idEdit = (int) ($_GET['id'] ?? 0);

$barisEdit = null;
if ($aksiGet === 'ubah' && $idEdit > 0) {
    $stmt = $pdo->prepare('SELECT * FROM krs WHERE id_krs = ?');
    $stmt->execute([$idEdit]);
    $barisEdit = $stmt->fetch();
    if (!$barisEdit) {
        $aksiGet = 'daftar';
    }
}

// Dropdown mahasiswa: id + teks NIM — nama
$mahasiswaList = $pdo->query('SELECT id_mahasiswa, nim, nama FROM mahasiswa ORDER BY nim ASC')->fetchAll(PDO::FETCH_ASSOC);
// Dropdown MK: id + kode — nama
$mkList = $pdo->query('SELECT id_mk, kode_mk, nama_mk FROM matakuliah ORDER BY kode_mk ASC')->fetchAll(PDO::FETCH_ASSOC);

// Daftar KRS dengan JOIN supaya tampilan ramah (bukan cuma angka ID)
$daftar = $pdo->query(
    'SELECT k.*, m.nim, m.nama AS nama_mhs, mk.kode_mk, mk.nama_mk, p.kode_prodi
     FROM krs k
     INNER JOIN mahasiswa m ON m.id_mahasiswa = k.id_mahasiswa
     INNER JOIN matakuliah mk ON mk.id_mk = k.id_mk
     INNER JOIN prodi p ON p.id_prodi = m.id_prodi
     ORDER BY k.tahun_ajaran DESC, k.semester, m.nim'
)->fetchAll(PDO::FETCH_ASSOC);

$judulHalaman = 'Data KRS';
require_once __DIR__ . '/includes/header.php';
?>

<?php if ($aksiGet === 'tambah' || ($aksiGet === 'ubah' && $barisEdit)) : ?>
    <div class="alert alert-info small" role="note">
        <strong>Petunjuk:</strong> Satu mahasiswa tidak boleh mengambil kombinasi MK + semester + tahun ajaran yang sama dua kali.
        Format tahun ajaran disarankan: <code>2025/2026</code>. Mahasiswa hanya boleh mengambil MK dari <strong>prodi yang sama</strong>.
    </div>
    <?php if ($mahasiswaList === [] || $mkList === []) : ?>
        <div class="alert alert-warning">Lengkapi dulu data <a href="mahasiswa.php">Mahasiswa</a> dan <a href="matakuliah.php">Mata kuliah</a>.</div>
    <?php endif; ?>
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="post" class="row g-3" action="krs.php">
                <input type="hidden" name="aksi" value="<?= $aksiGet === 'tambah' ? 'simpan_tambah' : 'simpan_ubah' ?>">
                <?php if ($aksiGet === 'ubah' && $barisEdit) : ?>
                    <input type="hidden" name="id_krs" value="<?= (int) $barisEdit['id_krs'] ?>">
                <?php endif; ?>

                <div class="col-md-6">
                    <label class="form-label" for="id_mahasiswa">Mahasiswa</label>
                    <select class="form-select" id="id_mahasiswa" name="id_mahasiswa" required <?= $mahasiswaList === [] ? 'disabled' : '' ?>>
                        <option value="">— pilih —</option>
                        <?php foreach ($mahasiswaList as $m) : ?>
                            <?php $sel = $barisEdit && (int) $barisEdit['id_mahasiswa'] === (int) $m['id_mahasiswa'] ? ' selected' : ''; ?>
                            <option value="<?= (int) $m['id_mahasiswa'] ?>"<?= $sel ?>><?= h((string) $m['nim']) ?> — <?= h((string) $m['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="id_mk">Mata kuliah</label>
                    <select class="form-select" id="id_mk" name="id_mk" required <?= $mkList === [] ? 'disabled' : '' ?>>
                        <option value="">— pilih —</option>
                        <?php foreach ($mkList as $mk) : ?>
                            <?php $sel = $barisEdit && (int) $barisEdit['id_mk'] === (int) $mk['id_mk'] ? ' selected' : ''; ?>
                            <option value="<?= (int) $mk['id_mk'] ?>"<?= $sel ?>><?= h((string) $mk['kode_mk']) ?> — <?= h((string) $mk['nama_mk']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="semester">Semester</label>
                    <select class="form-select" id="semester" name="semester" required>
                        <?php
                        // Nilai semester saat ini: dari database jika edit, atau default gasal jika tambah
                        $semNow = $barisEdit ? (string) $barisEdit['semester'] : 'gasal';
                        // Array asosiatif: nilai ENUM => label tampilan user
                        foreach (['gasal' => 'Gasal', 'genap' => 'Genap'] as $val => $label) {
                            $sel = $semNow === $val ? ' selected' : '';
                            echo '<option value="' . h($val) . '"' . $sel . '>' . h($label) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-8">
                    <label class="form-label" for="tahun_ajaran">Tahun ajaran</label>
                    <input class="form-control" id="tahun_ajaran" name="tahun_ajaran" required maxlength="9"
                           placeholder="contoh: 2025/2026"
                           value="<?= $barisEdit ? h((string) $barisEdit['tahun_ajaran']) : '2025/2026' ?>">
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary" <?= ($mahasiswaList === [] || $mkList === []) ? 'disabled' : '' ?>>Simpan</button>
                    <a class="btn btn-outline-secondary" href="krs.php">Batal</a>
                </div>
            </form>
        </div>
    </div>
<?php else : ?>
    <div class="alert alert-secondary small" role="note">
        <strong>Daftar KRS:</strong> menampilkan gabungan nama mahasiswa dan mata kuliah (JOIN). Menghapus KRS akan menghapus nilai terkait (cascade).
    </div>
    <p><a class="btn btn-primary" href="krs.php?aksi=tambah"><i class="bi bi-plus-lg"></i> Tambah KRS</a></p>
    <div class="table-responsive card border-0 shadow-sm">
        <table class="table table-striped table-hover mb-0 small">
            <thead class="table-primary">
            <tr>
                <th scope="col">#</th>
                <th scope="col">Mahasiswa</th>
                <th scope="col">Prodi</th>
                <th scope="col">Mata kuliah</th>
                <th scope="col">Semester</th>
                <th scope="col">Tahun ajaran</th>
                <th scope="col" style="width: 9rem">Aksi</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($daftar === []) : ?>
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada data KRS.</td></tr>
            <?php else : ?>
                <?php foreach ($daftar as $i => $r) : ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= h((string) $r['nim']) ?> — <?= h((string) $r['nama_mhs']) ?></td>
                        <td><?= h((string) $r['kode_prodi']) ?></td>
                        <td><?= h((string) $r['kode_mk']) ?> — <?= h((string) $r['nama_mk']) ?></td>
                        <td><?= h((string) $r['semester']) ?></td>
                        <td><?= h((string) $r['tahun_ajaran']) ?></td>
                        <td class="d-flex flex-wrap gap-1">
                            <a class="btn btn-sm btn-outline-primary" href="krs.php?aksi=ubah&id=<?= (int) $r['id_krs'] ?>">Ubah</a>
                            <form method="post" action="krs.php" class="d-inline" onsubmit="return confirm('Yakin menghapus KRS ini (dan nilai jika ada)?');">
                                <input type="hidden" name="aksi" value="hapus">
                                <input type="hidden" name="id_krs" value="<?= (int) $r['id_krs'] ?>">
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
