<?php
session_start();
include __DIR__ . "/../../config/koneksi.php";
$id = $_GET['id'];
mysqli_query($conn, "DELETE FROM buku WHERE id_buku = '$id'");
$_SESSION['success'] = "Buku berhasil dihapus!";
header("Location: buku.php");
?>