<?php
session_start();
include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../middleware/admin.php";

if (!isset($_GET['id'])) {
    header("Location: user.php");
    exit;
}

$id = $_GET['id'];

// password default baru
$password_baru = password_hash("123456", PASSWORD_DEFAULT);

$query = mysqli_query($conn, "
    UPDATE user 
    SET password='$password_baru'
    WHERE id_user='$id'
");

if ($query) {

    $_SESSION['success'] = "Password berhasil direset menjadi: 123456";

} else {

    $_SESSION['error'] = "Reset password gagal!";
}

header("Location: user.php");
exit;
?>