<?php
session_start();

include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../middleware/admin.php";

if (!isset($_GET['action']) || !isset($_GET['id'])) {
    header("Location: peminjaman.php");
    exit;
}

$action = $_GET['action'];
$id_pinjam = mysqli_real_escape_string($conn, $_GET['id']);

if ($action == 'kembali') {

    $q = mysqli_query($conn, "
        SELECT *
        FROM peminjaman
        WHERE id_pinjam = '$id_pinjam'
        LIMIT 1
    ");

    $data = mysqli_fetch_assoc($q);

    if (!$data) {
        echo "
        <script>
            alert('Data peminjaman tidak ditemukan!');
            window.location='peminjaman.php';
        </script>";
        exit;
    }

    if ($data['status'] != 'dipinjam') {
        echo "
        <script>
            alert('Peminjaman ini sudah selesai atau tidak aktif!');
            window.location='peminjaman.php';
        </script>";
        exit;
    }

    header("Location: detail_pengembalian.php?id=" . $id_pinjam);
    exit;
}

header("Location: peminjaman.php");
exit;