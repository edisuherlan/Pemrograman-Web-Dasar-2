<?php
/**
 * =============================================================================
 * FOOTER — penutup layout + JavaScript Bootstrap
 * =============================================================================
 * Bagian ini menutup tag <main> dan memuat JS Bootstrap (untuk komponen seperti
 * modal, alert yang bisa ditutup, dsb).
 * =============================================================================
 */

// Konsisten dengan file lain: cek tipe ketat
declare(strict_types=1);
?>
<!-- Tutup elemen <main> yang dibuka di header.php -->
</main>
<!-- Footer halaman: teks kecil, rata tengah, garis atas -->
<footer class="border-top py-4 mt-auto bg-white">
    <div class="container text-center text-muted small">
        Praktikum Pemrograman Web 2 — pengelolaan database <strong>perkuliahan</strong>
    </div>
</footer>
<!-- bundle = Bootstrap + Popper (untuk dropdown, collapse navbar, dll.) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
<!-- Tutup body dan html -->
</body>
</html>
