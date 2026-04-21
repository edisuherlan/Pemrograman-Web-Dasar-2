<?php
/**
 * =============================================================================
 * MODUL CRUD — TABEL NILAI
 * =============================================================================
 * Konsep:
 * - Nilai menyimpan hasil studi untuk **satu baris KRS** (relasi 1:1).
 * - Artinya: satu KRS hanya boleh punya satu baris nilai (unik id_krs di tabel nilai).
 *
 * Alur kerja yang masuk akal:
 * 1) Buat mahasiswa, mata kuliah, lalu KRS (mahasiswa mengambil MK).
 * 2) Di halaman ini, pilih KRS yang **belum punya nilai** saat menambah data baru.
 * 3) Isi nilai angka (contoh: 85.50) dan pilih nilai huruf (A, B, C, dst.).
 *
 * Catatan kolom nilai huruf di database bertipe CHAR(2): kita isi satu huruf
 * (misalnya A) — MySQL tetap menyimpannya sesuai definisi tabel.
 *
 * Mengubah nilai:
 * - Tombol ubah hanya mengganti angka & huruf, tidak mengganti KRS (menghindari bentrok unik).
 * =============================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/fungsi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    try {
        if ($aksi === 'simpan_tambah') {
            $idKrs = (int) ($_POST['id_krs'] ?? 0);
            // str_replace: user bisa mengetik koma sebagai desimal; database pakai titik
            $angka = (float) str_replace(',', '.', (string) ($_POST['nilai_angka'] ?? '0'));
            // strtoupper: jaga-jaga user input huruf kecil; disimpan konsisten besar
            $huruf = strtoupper(trim((string) ($_POST['nilai_huruf'] ?? '')));

            if ($idKrs < 1 || $huruf === '') {
                header('Location: nilai.php?status=tidak_valid&msg=' . rawurlencode('Pilih KRS dan isi nilai huruf.'));
                exit;
            }
            if ($angka < 0 || $angka > 100) {
                header('Location: nilai.php?status=tidak_valid&msg=' . rawurlencode('Nilai angka harus antara 0 dan 100.'));
                exit;
            }

            $stmt = $pdo->prepare('INSERT INTO nilai (id_krs, nilai_angka, nilai_huruf) VALUES (?, ?, ?)');
            $stmt->execute([$idKrs, $angka, $huruf]);
            header('Location: nilai.php?status=simpan_ok');
            exit;
        }

        if ($aksi === 'simpan_ubah') {
            $idNilai = (int) ($_POST['id_nilai'] ?? 0);
            $angka = (float) str_replace(',', '.', (string) ($_POST['nilai_angka'] ?? '0'));
            $huruf = strtoupper(trim((string) ($_POST['nilai_huruf'] ?? '')));

            if ($idNilai < 1 || $huruf === '') {
                header('Location: nilai.php?status=tidak_valid&msg=' . rawurlencode('Data tidak lengkap.'));
                exit;
            }
            if ($angka < 0 || $angka > 100) {
                header('Location: nilai.php?status=tidak_valid&msg=' . rawurlencode('Nilai angka harus antara 0 dan 100.'));
                exit;
            }

            $stmt = $pdo->prepare('UPDATE nilai SET nilai_angka = ?, nilai_huruf = ? WHERE id_nilai = ?');
            $stmt->execute([$angka, $huruf, $idNilai]);
            header('Location: nilai.php?status=simpan_ok');
            exit;
        }

        if ($aksi === 'hapus') {
            $id = (int) ($_POST['id_nilai'] ?? 0);
            if ($id < 1) {
                header('Location: nilai.php?status=tidak_valid');
                exit;
            }
            $stmt = $pdo->prepare('DELETE FROM nilai WHERE id_nilai = ?');
            $stmt->execute([$id]);
            header('Location: nilai.php?status=hapus_ok');
            exit;
        }
    } catch (PDOException $e) {
        $code = (int) ($e->errorInfo[1] ?? 0);
        if ($code === 23000 || $code === 1062) {
            header('Location: nilai.php?status=duplikat&msg=' . rawurlencode('KRS ini sudah memiliki nilai (satu KRS satu nilai).'));
            exit;
        }
        header('Location: nilai.php?status=gagal&msg=' . rawurlencode($e->getMessage()));
        exit;
    }
}

$aksiGet = $_GET['aksi'] ?? 'daftar';
$idEdit = (int) ($_GET['id'] ?? 0);

$barisEdit = null;
if ($aksiGet === 'ubah' && $idEdit > 0) {
    // JOIN banyak tabel agar form ubah bisa menampilkan teks NIM & nama MK (bukan cuma id)
    $stmt = $pdo->prepare(
        'SELECT n.*, k.id_mahasiswa, k.id_mk, k.semester, k.tahun_ajaran, m.nim, m.nama AS nama_mhs, mk.kode_mk, mk.nama_mk, p.kode_prodi
         FROM nilai n
         INNER JOIN krs k ON k.id_krs = n.id_krs
         INNER JOIN mahasiswa m ON m.id_mahasiswa = k.id_mahasiswa
         INNER JOIN matakuliah mk ON mk.id_mk = k.id_mk
         INNER JOIN prodi p ON p.id_prodi = m.id_prodi
         WHERE n.id_nilai = ?'
    );
    $stmt->execute([$idEdit]);
    $barisEdit = $stmt->fetch();
    if (!$barisEdit) {
        $aksiGet = 'daftar';
    }
}

// Query KRS yang belum punya baris di tabel nilai (LEFT JOIN ... WHERE n.id_nilai IS NULL)
$krsKosong = $pdo->query(
    'SELECT k.id_krs, m.nim, m.nama AS nama_mhs, mk.kode_mk, mk.nama_mk, k.semester, k.tahun_ajaran, p.kode_prodi
     FROM krs k
     INNER JOIN mahasiswa m ON m.id_mahasiswa = k.id_mahasiswa
     INNER JOIN matakuliah mk ON mk.id_mk = k.id_mk
     INNER JOIN prodi p ON p.id_prodi = m.id_prodi
     LEFT JOIN nilai n ON n.id_krs = k.id_krs
     WHERE n.id_nilai IS NULL
     ORDER BY k.tahun_ajaran DESC, m.nim'
)->fetchAll(PDO::FETCH_ASSOC);

// Semua nilai untuk tabel daftar (dengan JOIN untuk tampilan lengkap)
$daftar = $pdo->query(
    'SELECT n.*, k.semester, k.tahun_ajaran, m.nim, m.nama AS nama_mhs, mk.kode_mk, mk.nama_mk, p.kode_prodi
     FROM nilai n
     INNER JOIN krs k ON k.id_krs = n.id_krs
     INNER JOIN mahasiswa m ON m.id_mahasiswa = k.id_mahasiswa
     INNER JOIN matakuliah mk ON mk.id_mk = k.id_mk
     INNER JOIN prodi p ON p.id_prodi = m.id_prodi
     ORDER BY k.tahun_ajaran DESC, m.nim'
)->fetchAll(PDO::FETCH_ASSOC);

// Pilihan nilai huruf sederhana (bisa Anda perkaya jadi A, B+, B, ... sesuai aturan kampus)
$hurufPilihan = ['A', 'B', 'C', 'D', 'E'];

$judulHalaman = 'Data Nilai';
require_once __DIR__ . '/includes/header.php';
?>

<?php if ($aksiGet === 'tambah') : ?>
    <div class="alert alert-info small" role="note">
        <strong>Petunjuk:</strong> Hanya KRS yang **belum ada nilainya** yang muncul di dropdown. Setelah nilai disimpan, gunakan daftar di bawah untuk mengubah/menghapus.
        Samakan huruf dengan aturan kampus Anda (contoh skala: A ≥ 85, B 75–84, C 65–74).
    </div>
    <?php if ($krsKosong === []) : ?>
        <div class="alert alert-warning mb-0">
            Tidak ada KRS tanpa nilai. Tambah KRS baru di menu <a href="krs.php">KRS</a>, atau ubah/hapus nilai yang sudah ada di tabel di bawah.
        </div>
    <?php else : ?>
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form method="post" class="row g-3" action="nilai.php">
                    <input type="hidden" name="aksi" value="simpan_tambah">
                    <div class="col-12">
                        <label class="form-label" for="id_krs">Pilih KRS</label>
                        <select class="form-select" id="id_krs" name="id_krs" required>
                            <option value="">— pilih KRS —</option>
                            <?php foreach ($krsKosong as $row) : ?>
                                <option value="<?= (int) $row['id_krs'] ?>">
                                    [<?= h((string) $row['kode_prodi']) ?>]
                                    <?= h((string) $row['nim']) ?> — <?= h((string) $row['nama_mhs']) ?>
                                    | <?= h((string) $row['kode_mk']) ?> — <?= h((string) $row['nama_mk']) ?>
                                    | <?= h((string) $row['semester']) ?> <?= h((string) $row['tahun_ajaran']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="nilai_angka">Nilai angka (0–100)</label>
                        <input class="form-control" id="nilai_angka" name="nilai_angka" type="number" step="0.01" min="0" max="100" required value="80">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="nilai_huruf">Nilai huruf</label>
                        <select class="form-select" id="nilai_huruf" name="nilai_huruf" required>
                            <?php
                            // $hurufOpt: hindari nama variabel $h agar tidak tertukar dengan fungsi h()
                            foreach ($hurufPilihan as $hurufOpt) :
                                ?>
                                <option value="<?= h($hurufOpt) ?>"><?= h($hurufOpt) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a class="btn btn-outline-secondary" href="nilai.php">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

<?php elseif ($aksiGet === 'ubah' && $barisEdit) : ?>
    <div class="alert alert-info small" role="note">
        <strong>Mengubah nilai:</strong> KRS tidak diubah (tetap <?= h((string) $barisEdit['nim']) ?> — <?= h((string) $barisEdit['kode_mk']) ?>).
        Anda hanya memperbarui angka dan huruf.
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="post" class="row g-3" action="nilai.php">
                <input type="hidden" name="aksi" value="simpan_ubah">
                <input type="hidden" name="id_nilai" value="<?= (int) $barisEdit['id_nilai'] ?>">
                <div class="col-12">
                    <p class="form-control-plaintext border rounded px-2 bg-white small mb-0">
                        <strong>KRS:</strong>
                        [<?= h((string) $barisEdit['kode_prodi']) ?>]
                        <?= h((string) $barisEdit['nim']) ?> — <?= h((string) $barisEdit['nama_mhs']) ?> |
                        <?= h((string) $barisEdit['kode_mk']) ?> — <?= h((string) $barisEdit['nama_mk']) ?> |
                        <?= h((string) $barisEdit['semester']) ?> <?= h((string) $barisEdit['tahun_ajaran']) ?>
                    </p>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="nilai_angka">Nilai angka (0–100)</label>
                    <input class="form-control" id="nilai_angka" name="nilai_angka" type="number" step="0.01" min="0" max="100" required
                           value="<?= h((string) $barisEdit['nilai_angka']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="nilai_huruf">Nilai huruf</label>
                    <select class="form-select" id="nilai_huruf" name="nilai_huruf" required>
                        <?php
                        // Bandingkan dengan trim karena CHAR(2) bisa berisi spasi padding
                        $nh = trim((string) $barisEdit['nilai_huruf']);
                        foreach ($hurufPilihan as $hurufOpt) {
                            $sel = ($nh === $hurufOpt) ? ' selected' : '';
                            echo '<option value="' . h($hurufOpt) . '"' . $sel . '>' . h($hurufOpt) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Simpan perubahan</button>
                    <a class="btn btn-outline-secondary" href="nilai.php">Batal</a>
                </div>
            </form>
        </div>
    </div>
<?php else : ?>
    <div class="alert alert-secondary small" role="note">
        <strong>Daftar nilai:</strong> setiap baris adalah nilai untuk satu KRS. Menghapus nilai tidak menghapus KRS (mahasiswa tetap terdaftar di MK tersebut).
    </div>
    <p><a class="btn btn-primary" href="nilai.php?aksi=tambah"><i class="bi bi-plus-lg"></i> Tambah nilai</a></p>
    <div class="table-responsive card border-0 shadow-sm">
        <table class="table table-striped table-hover mb-0 small">
            <thead class="table-primary">
            <tr>
                <th scope="col">#</th>
                <th scope="col">Mahasiswa</th>
                <th scope="col">Prodi</th>
                <th scope="col">Mata kuliah</th>
                <th scope="col">Sem / TA</th>
                <th scope="col">Angka</th>
                <th scope="col">Huruf</th>
                <th scope="col" style="width: 9rem">Aksi</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($daftar === []) : ?>
                <tr><td colspan="8" class="text-center text-muted py-4">Belum ada data nilai.</td></tr>
            <?php else : ?>
                <?php foreach ($daftar as $i => $r) : ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= h((string) $r['nim']) ?> — <?= h((string) $r['nama_mhs']) ?></td>
                        <td><?= h((string) $r['kode_prodi']) ?></td>
                        <td><?= h((string) $r['kode_mk']) ?> — <?= h((string) $r['nama_mk']) ?></td>
                        <td><?= h((string) $r['semester']) ?> <?= h((string) $r['tahun_ajaran']) ?></td>
                        <td><?= h((string) $r['nilai_angka']) ?></td>
                        <td><?= h(trim((string) $r['nilai_huruf'])) ?></td>
                        <td class="d-flex flex-wrap gap-1">
                            <a class="btn btn-sm btn-outline-primary" href="nilai.php?aksi=ubah&id=<?= (int) $r['id_nilai'] ?>">Ubah</a>
                            <form method="post" action="nilai.php" class="d-inline" onsubmit="return confirm('Hapus nilai ini?');">
                                <input type="hidden" name="aksi" value="hapus">
                                <input type="hidden" name="id_nilai" value="<?= (int) $r['id_nilai'] ?>">
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
