<?php
/**
 * =============================================================================
 * STUDI KASUS — HALAMAN LOGIN
 * =============================================================================
 * Alur: form POST → cek username di tabel pengguna → password_verify → sesi.
 * Contoh akun (setelah impor database/perkuliahan.sql):
 * - admin / admin123  (peran admin)
 * - operator / operator123 (peran operator)
 * =============================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/fungsi.php';
require_once __DIR__ . '/includes/auth.php';

pastikan_sesi();

if (pengguna_sudah_login()) {
    header('Location: ' . redirect_setelah_login_amai());
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        header('Location: login.php?status=tidak_valid&msg=' . rawurlencode('Username dan kata sandi wajib diisi.'));
        exit;
    }

    try {
        $stmt = $pdo->prepare(
            'SELECT id_pengguna, username, password_hash, nama_tampilan, peran
             FROM pengguna WHERE username = ? LIMIT 1'
        );
        $stmt->execute([$username]);
        $baris = $stmt->fetch();
        if (!$baris || !password_verify($password, (string) $baris['password_hash'])) {
            header('Location: login.php?status=login_gagal');
            exit;
        }
        masukkan_pengguna_ke_sesi([
            'id_pengguna'   => (int) $baris['id_pengguna'],
            'username'      => (string) $baris['username'],
            'nama_tampilan' => (string) $baris['nama_tampilan'],
            'peran'         => (string) $baris['peran'],
        ]);
        $tujuan = isset($_POST['redirect']) ? trim((string) $_POST['redirect']) : '';
        if ($tujuan !== '' && preg_match('/^[a-zA-Z0-9_-]+\.php$/', $tujuan) === 1) {
            header('Location: ' . $tujuan);
        } else {
            header('Location: index.php?status=login_ok');
        }
        exit;
    } catch (PDOException $e) {
        header('Location: login.php?status=gagal&msg=' . rawurlencode($e->getMessage()));
        exit;
    }
}

$judulHalaman = 'Login (studi kasus)';
$redirectHidden = isset($_GET['redirect']) ? trim((string) $_GET['redirect']) : '';
if ($redirectHidden !== '' && preg_match('/^[a-zA-Z0-9_-]+\.php$/', $redirectHidden) !== 1) {
    $redirectHidden = '';
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h2 class="h5 text-secondary mb-3">Masuk ke panel contoh</h2>
                <p class="small text-muted mb-4">
                    Ini modul pembelajaran terpisah dari CRUD akademik: fokus ke <strong>sesi PHP</strong>,
                    <strong>password_hash</strong>, dan <strong>password_verify</strong>.
                </p>
                <form method="post" action="login.php" class="needs-validation" novalidate>
                    <?php if ($redirectHidden !== '') { ?>
                        <input type="hidden" name="redirect" value="<?= h($redirectHidden) ?>">
                    <?php } ?>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required autocomplete="username"
                               value="<?= h(trim((string) ($_GET['u'] ?? ''))) ?>">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Kata sandi</label>
                        <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-box-arrow-in-right me-1"></i>Login</button>
                </form>
                <hr class="my-4">
                <p class="small text-secondary mb-0">
                    <strong>Akun demo</strong> (impor ulang <code>database/perkuliahan.sql</code> jika tabel
                    <code>pengguna</code> belum ada):<br>
                    <code>admin</code> / <code>admin123</code> ·
                    <code>operator</code> / <code>operator123</code>
                </p>
            </div>
        </div>
        <p class="text-center small text-muted mt-3 mb-0">
            <a href="index.php">← Kembali ke beranda</a>
        </p>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
