<?php
session_start();

include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../middleware/admin.php";

if (!isset($_POST['id_pinjam'])) {

    header("Location: riwayat.php");
    exit;
}

$id_pinjam = $_POST['id_pinjam'];

if (is_array($id_pinjam)) {

    $ids = implode(",", array_map('intval', $id_pinjam));

} else {

    $ids = intval($id_pinjam);
}

$delete = mysqli_query($conn, "
    DELETE FROM peminjaman 
    WHERE id_pinjam IN ($ids)
");

if ($delete) {

    $_SESSION['success'] = "Riwayat berhasil dihapus";

} else {

    $_SESSION['error'] = "Riwayat gagal dihapus";
}

header("Location: riwayat.php");
exit;
?>