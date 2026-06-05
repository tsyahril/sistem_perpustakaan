<?php
session_start();

include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../middleware/admin.php";

$id = $_GET['id'];

$hapus = mysqli_query($conn, "
    DELETE FROM penulis
    WHERE id_penulis = '$id'
");

if ($hapus) {
    $_SESSION['success'] = "Penulis berhasil dihapus!";
} else {
    $_SESSION['error'] = "Gagal menghapus penulis!";
}

header("Location: index.php");
exit;
?>