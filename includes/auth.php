<?php
/**
 * =============================================================================
 * AUTENTIKASI (STUDI KASUS LOGIN) — sesi PHP + verifikasi kata sandi
 * =============================================================================
 * Dipakai oleh login.php, panel_aman.php, dan header untuk menampilkan status.
 *
 * Konsep singkat:
 * - Setelah login sukses, ID pengguna disimpan di $_SESSION (bukan menyimpan
 *   kata sandi di sesi).
 * - password_hash / password_verify = cara aman menyimpan & memeriksa sandi.
 * =============================================================================
 */

declare(strict_types=1);

/**
 * Pastikan sesi PHP sudah dimulai (aman dipanggil berulang).
 */
function pastikan_sesi(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * @return array{id_pengguna:int, username:string, nama_tampilan:string, peran:string}|null
 */
function ambil_pengguna_dari_sesi(): ?array
{
    pastikan_sesi();
    $id = $_SESSION['id_pengguna'] ?? null;
    if (!is_int($id) && !is_numeric($id)) {
        return null;
    }
    $id = (int) $id;
    if ($id < 1) {
        return null;
    }
    $username = $_SESSION['username'] ?? '';
    $nama = $_SESSION['nama_tampilan'] ?? '';
    $peran = $_SESSION['peran'] ?? '';
    if ($username === '' || $nama === '' || $peran === '') {
        return null;
    }
    return [
        'id_pengguna'   => $id,
        'username'      => (string) $username,
        'nama_tampilan' => (string) $nama,
        'peran'         => (string) $peran,
    ];
}

function pengguna_sudah_login(): bool
{
    return ambil_pengguna_dari_sesi() !== null;
}

/**
 * Halaman yang boleh diakses tanpa sesi login (misalnya form login).
 *
 * @return list<string>
 */
function daftar_halaman_tanpa_login(): array
{
    // Contoh simulasi: halaman tertentu dibuka tanpa login (tambahkan nama file .php di sini).
    return ['login.php', 'dosen.php'];
}

/**
 * Seluruh aplikasi memakai header: pengunjung tanpa sesi diarahkan ke login.
 * Juga dipanggil dari skrip PHP yang tidak memuat header (contoh file belajar).
 *
 * @param string|null $basename Nilai basename($_SERVER['SCRIPT_NAME']); null = deteksi otomatis
 */
function pastikan_login_atau_redirect(?string $basename = null): void
{
    pastikan_sesi();
    $namaFile = $basename ?? basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    if (in_array($namaFile, daftar_halaman_tanpa_login(), true)) {
        return;
    }
    if (pengguna_sudah_login()) {
        return;
    }
    $redirect = preg_match('/^[a-zA-Z0-9_-]+\.php$/', $namaFile) === 1 ? $namaFile : 'index.php';
    header('Location: login.php?status=login_perlu&redirect=' . rawurlencode($redirect));
    exit;
}

/**
 * Setelah verifikasi database, isi sesi dan perbarui ID sesi (mitigasi fixation).
 *
 * @param array{id_pengguna:int, username:string, nama_tampilan:string, peran:string} $baris
 */
function masukkan_pengguna_ke_sesi(array $baris): void
{
    pastikan_sesi();
    session_regenerate_id(true);
    $_SESSION['id_pengguna'] = (int) $baris['id_pengguna'];
    $_SESSION['username'] = (string) $baris['username'];
    $_SESSION['nama_tampilan'] = (string) $baris['nama_tampilan'];
    $_SESSION['peran'] = (string) $baris['peran'];
}

function keluar_pengguna(): void
{
    pastikan_sesi();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], (bool) $p['secure'], (bool) $p['httponly']);
    }
    session_destroy();
}

/**
 * Halaman yang memanggil ini akan mengarahkan ke login jika belum masuk.
 */
function wajib_sudah_login(): void
{
    if (!pengguna_sudah_login()) {
        $self = basename((string) ($_SERVER['SCRIPT_NAME'] ?? 'panel_aman.php'));
        if (preg_match('/^[a-zA-Z0-9_-]+\.php$/', $self) !== 1) {
            $self = 'panel_aman.php';
        }
        header('Location: login.php?status=login_perlu&redirect=' . rawurlencode($self));
        exit;
    }
}

/**
 * URL aman setelah login: hanya nama file .php sederhana (cegah open redirect).
 */
function redirect_setelah_login_amai(): string
{
    $r = isset($_GET['redirect']) ? trim((string) $_GET['redirect']) : '';
    if ($r !== '' && preg_match('/^[a-zA-Z0-9_-]+\.php$/', $r) === 1) {
        return $r;
    }
    return 'index.php';
}
