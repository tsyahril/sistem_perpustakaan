<?php
session_start();
include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../middleware/petugas.php";

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "ID buku tidak ditemukan.";
    header("Location: index.php");
    exit;
}

$id = (int)$_GET['id'];

$queryData = mysqli_query($conn, "SELECT cover FROM buku WHERE id_buku = '$id' LIMIT 1");
$data = mysqli_fetch_assoc($queryData);

if (!$data) {
    $_SESSION['error'] = "Data buku tidak ditemukan.";
    header("Location: index.php");
    exit;
}

// Integrity Constraint Validasi: Cegah hapus jika sedang dipinjam
$cekPinjam = mysqli_query($conn, "SELECT id_pinjam FROM peminjaman WHERE id_buku = '$id' LIMIT 1");

if ($cekPinjam && mysqli_num_rows($cekPinjam) > 0) {
    $_SESSION['error'] = "Buku tidak bisa dihapus karena sudah memiliki data peminjaman.";
    header("Location: index.php");
    exit;
}

// Transaction System Block
mysqli_begin_transaction($conn);

try {
    // 1. Cascading Delete: Hapus ulasan dari buku terkait terlebih dahulu
    mysqli_query($conn, "DELETE FROM ulasan WHERE id_buku = '$id'");

    // 2. Target Delete: Hapus data buku dari entitas utama
    mysqli_query($conn, "DELETE FROM buku WHERE id_buku = '$id'");

    mysqli_commit($conn);

    // 3. Storage Asset Cleanup: Hapus file cover fisik dari server jika bukan default.jpg
    if ($data['cover'] != 'default.jpg' && file_exists("../../assets/img/cover/" . $data['cover'])) {
        unlink("../../assets/img/cover/" . $data['cover']);
    }

    $_SESSION['success'] = "Buku berhasil dihapus.";
    header("Location: index.php");
    exit;

} catch (Exception $e) {
    mysqli_rollback($conn);
    $_SESSION['error'] = "Gagal menghapus buku.";
    header("Location: index.php");
    exit;
}