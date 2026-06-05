<?php
session_start();

include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../middleware/admin.php";

// ==========================
// CEK ID
// ==========================
if (!isset($_GET['id'])) {
    header("Location: user.php");
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);
$admin_sekarang = $_SESSION['id_user'];

// ==========================
// CEK JANGAN HAPUS AKUN SENDIRI
// ==========================
if ($id == $admin_sekarang) {
    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Anda tidak bisa menghapus akun sendiri yang sedang aktif.',
                background: '#0f172a',
                color: '#fff',
                confirmButtonColor: '#ef4444'
            }).then(() => {
                window.location = 'user.php';
            });
        });
    </script>";
    exit;
}

// ==========================
// CEK DATA USER ADA ATAU TIDAK
// ==========================
$cekUser = mysqli_query($conn, "
    SELECT id_user 
    FROM user 
    WHERE id_user = '$id'
    LIMIT 1
");

if (!$cekUser || mysqli_num_rows($cekUser) == 0) {
    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Data user tidak ditemukan.',
                background: '#0f172a',
                color: '#fff',
                confirmButtonColor: '#ef4444'
            }).then(() => {
                window.location = 'user.php';
            });
        });
    </script>";
    exit;
}

// ==========================
// HAPUS USER
// ==========================
$query = mysqli_query($conn, "
    DELETE FROM user 
    WHERE id_user = '$id'
");

if ($query) {
    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Data user berhasil dihapus.',
                background: '#0f172a',
                color: '#fff',
                confirmButtonColor: '#10b981'
            }).then(() => {
                window.location = 'user.php';
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
                text: 'Data tidak bisa dihapus karena masih terhubung dengan data lain.',
                background: '#0f172a',
                color: '#fff',
                confirmButtonColor: '#ef4444'
            }).then(() => {
                window.location = 'user.php';
            });
        });
    </script>";
    exit;
}
?>