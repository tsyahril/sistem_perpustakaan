<?php
session_start();

include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../middleware/anggota.php";

if (!isset($_GET['id'])) {
    header("Location: ../dashboard/index.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$id_buku = (int) $_GET['id'];

$buku = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT *
    FROM buku
    WHERE id_buku = '$id_buku'
    LIMIT 1
"));

if (!$buku) {
    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: 'Buku tidak ditemukan',
            background: '#0f172a',
            color: '#fff',
            confirmButtonColor: '#ef4444'
        }).then(() => {
            window.location='../dashboard/index.php';
        });
    });
    </script>";
    exit;
}

if ($buku['stok'] <= 0) {
    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'warning',
            title: 'Stok Habis',
            text: 'Buku ini tidak tersedia',
            background: '#0f172a',
            color: '#fff',
            confirmButtonColor: '#f59e0b'
        }).then(() => {
            window.location='../dashboard/index.php';
        });
    });
    </script>";
    exit;
}

// CEK JANGAN PINJAM BUKU YANG SAMA SAAT MASIH AKTIF
$cek = mysqli_query($conn, "
    SELECT id_pinjam
    FROM peminjaman
    WHERE id_user = '$id_user'
    AND id_buku = '$id_buku'
    AND status = 'dipinjam'
    LIMIT 1
");

if (mysqli_num_rows($cek) > 0) {
    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'warning',
            title: 'Sudah Dipinjam',
            text: 'Kamu sedang meminjam buku ini',
            background: '#0f172a',
            color: '#fff',
            confirmButtonColor: '#f59e0b'
        }).then(() => {
            window.location='index.php';
        });
    });
    </script>";
    exit;
}

$tanggal_pinjam = date('Y-m-d');
$tanggal_kembali = date('Y-m-d', strtotime('+7 days'));

mysqli_begin_transaction($conn);

try {

    $insert = mysqli_query($conn, "
        INSERT INTO peminjaman (
            id_user,
            id_buku,
            tanggal_pinjam,
            tanggal_kembali,
            status,
            kondisi_buku
        ) VALUES (
            '$id_user',
            '$id_buku',
            '$tanggal_pinjam',
            '$tanggal_kembali',
            'dipinjam',
            'baik'
        )
    ");

    if (!$insert) {
        throw new Exception("Gagal insert peminjaman");
    }

    $updateStok = mysqli_query($conn, "
        UPDATE buku
        SET stok = stok - 1
        WHERE id_buku = '$id_buku'
        AND stok > 0
    ");

    if (!$updateStok) {
        throw new Exception("Gagal update stok");
    }

    mysqli_commit($conn);

    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: 'Buku berhasil dipinjam',
            background: '#0f172a',
            color: '#fff',
            confirmButtonColor: '#3b82f6',
            timer: 1600,
            showConfirmButton: false
        }).then(() => {
            window.location='index.php';
        });
    });
    </script>";
    exit;

} catch (Exception $e) {

    mysqli_rollback($conn);

    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: 'Gagal meminjam buku',
            background: '#0f172a',
            color: '#fff',
            confirmButtonColor: '#ef4444'
        }).then(() => {
            window.location='../dashboard/index.php';
        });
    });
    </script>";
    exit;
}