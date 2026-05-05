<?php
require_once "../config/koneksi.php";
require_once "../middleware/admin.php";

if(isset($_GET['id'])){
    $id = $_GET['id'];
    // Cegah admin menghapus dirinya sendiri (opsional tapi disarankan)
    mysqli_query($conn, "DELETE FROM user WHERE id_user = '$id'");
    header("Location: admin.akun.php?pesan=terhapus");
}
?>