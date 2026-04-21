<?php
/**
 * =============================================================================
 * FUNGSI BANTUAN (HELPER) — dipakai di banyak halaman
 * =============================================================================
 * - h()     : mencegah XSS (teks dari database ditampilkan aman di HTML)
 * - alert() : menampilkan notifikasi Bootstrap dari parameter URL
 * =============================================================================
 */

// strict_types membantu menjaga tipe parameter fungsi
declare(strict_types=1);

/**
 * Escape output ke HTML (wajib dipakai saat menampilkan data dari user/database).
 *
 * @param string|null $teks Teks yang akan ditampilkan di HTML (boleh null)
 * @return string          Teks yang sudah aman untuk dimasukkan ke HTML
 */
function h(?string $teks): string
{
    // htmlspecialchars mengubah < > & " ' jadi entitas HTML supaya tidak dieksekusi sebagai tag/script
    return htmlspecialchars((string) $teks, ENT_QUOTES, 'UTF-8');
}

/**
 * Tampilkan alert Bootstrap jika ada ?status=... di URL (setelah redirect).
 * Redirect dipakai setelah simpan/hapus agar refresh tidak mengulang POST.
 */
function tampilkan_alert_dari_url(): void
{
    // Ambil parameter GET; kalau tidak ada, pakai string kosong (bukan error)
    $status = $_GET['status'] ?? '';
    // Pesan tambahan opsional dari ?msg=... (untuk error yang perlu detail)
    $pesan = $_GET['msg'] ?? '';

    // Peta: kode status -> [jenis warna Bootstrap, teks yang ditampilkan]
    $map = [
        'simpan_ok'   => ['success', 'Data berhasil disimpan.'],
        'hapus_ok'    => ['success', 'Data berhasil dihapus.'],
        'gagal'       => ['danger', $pesan !== '' ? $pesan : 'Terjadi kesalahan.'],
        'duplikat'    => ['warning', $pesan !== '' ? $pesan : 'Data bentrok dengan aturan database (misalnya NIM/NIDN sudah ada).'],
        'tidak_valid' => ['warning', $pesan !== '' ? $pesan : 'Input tidak valid.'],
    ];

    // Kalau status tidak dikenal, tidak menampilkan apa-apa
    if (!isset($map[$status])) {
        return;
    }

    // Destructuring array: $jenis = success/danger/..., $teks = kalimat user
    [$jenis, $teks] = $map[$status];
    // Cetak div alert Bootstrap; h() dipakai agar $jenis dan $teks aman
    echo '<div class="alert alert-' . h($jenis) . ' alert-dismissible fade show" role="alert">'
        . h($teks)
        . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button></div>';
}
