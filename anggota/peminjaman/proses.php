<?php
session_start();

include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../middleware/anggota.php";

if (!isset($_GET['action']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$action = $_GET['action'];
$id_pinjam = mysqli_real_escape_string($conn, $_GET['id']);
$id_user = $_SESSION['id_user'];

if ($action == 'kembali') {

    $q = mysqli_query($conn, "
        SELECT *
        FROM peminjaman
        WHERE id_pinjam = '$id_pinjam'
        AND id_user = '$id_user'
        LIMIT 1
    ");

    $data = mysqli_fetch_assoc($q);

    if (!$data) {
        echo "
        <script>
            alert('Data peminjaman tidak ditemukan atau bukan milik kamu!');
            window.location='index.php';
        </script>";
        exit;
    }

    if ($data['status'] != 'dipinjam') {
        echo "
        <script>
            alert('Peminjaman ini sudah selesai atau tidak aktif!');
            window.location='index.php';
        </script>";
        exit;
    }

    header("Location: detail_pengembalian.php?id=" . $id_pinjam);
    exit;
}

header("Location: index.php");
exit;