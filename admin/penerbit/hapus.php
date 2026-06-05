<?php
session_start();

include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../middleware/admin.php";

$id = $_GET['id'];

$hapus = mysqli_query($conn, "
    DELETE FROM penerbit
    WHERE id_penerbit = '$id'
");

if ($hapus) {
    $_SESSION['success'] = "Penerbit berhasil dihapus!";
} else {
    $_SESSION['error'] = "Gagal menghapus penerbit!";
}

header("Location: index.php");
exit;
?>