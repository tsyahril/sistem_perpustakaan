<?php
session_start();

include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../middleware/admin.php";

// ==========================
// CEK ID
// ==========================
if (!isset($_GET['id'])) {
    header("Location: kategori.php");
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// ==========================
// CEK KATEGORI ADA ATAU TIDAK
// ==========================
$cek_kategori = mysqli_query($conn, "
    SELECT id_kategori 
    FROM kategori 
    WHERE id_kategori = '$id'
    LIMIT 1
");

if (!$cek_kategori || mysqli_num_rows($cek_kategori) == 0) {
    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Kategori tidak ditemukan.',
                background: '#0f172a',
                color: '#fff',
                confirmButtonColor: '#ef4444'
            }).then(() => {
                window.location = 'kategori.php';
            });
        });
    </script>";
    exit;
}

// ==========================
// CEK KATEGORI MASIH DIGUNAKAN BUKU
// ==========================
$cek_buku = mysqli_query($conn, "
    SELECT id_buku 
    FROM buku 
    WHERE id_kategori = '$id'
    LIMIT 1
");

if ($cek_buku && mysqli_num_rows($cek_buku) > 0) {
    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Kategori ini masih digunakan oleh beberapa buku.',
                background: '#0f172a',
                color: '#fff',
                confirmButtonColor: '#ef4444'
            }).then(() => {
                window.location = 'kategori.php';
            });
        });
    </script>";
    exit;
}

// ==========================
// HAPUS KATEGORI
// ==========================
$hapus = mysqli_query($conn, "
    DELETE FROM kategori 
    WHERE id_kategori = '$id'
");

if ($hapus) {
    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Data kategori berhasil dihapus.',
                background: '#0f172a',
                color: '#fff',
                confirmButtonColor: '#10b981'
            }).then(() => {
                window.location = 'kategori.php';
            });
        });
    </script>";
    exit;
} else {
    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Data kategori gagal dihapus.',
                background: '#0f172a',
                color: '#fff',
                confirmButtonColor: '#ef4444'
            }).then(() => {
                window.location = 'kategori.php';
            });
        });
    </script>";
    exit;
}
?>