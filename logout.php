<?php
/**
 * =============================================================================
 * LOGOUT — hapus sesi lalu kembali ke halaman login
 * =============================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

pastikan_sesi();
keluar_pengguna();

header('Location: login.php?status=logout_ok');
exit;
