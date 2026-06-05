<?php
// Mulai session agar PHP tahu session mana yang akan dihapus
session_start();

// Hapus semua variabel session yang tersimpan (id_user, nama, role, dll)
session_unset();

// Hancurkan session secara total dari server
session_destroy();

// Arahkan kembali ke halaman login (keluar dari folder auth/ ke root atau tetap di auth/)

$_SESSION['success'] = "Berhasil logout!";
header("Location: login.php?pesan=logout");
exit();

if(isset($_GET['pesan'])){
    if($_GET['pesan'] == "logout"){
        echo "<div style='color: #3b82f6; margin-bottom: 15px; text-align: center;'>
                Anda telah berhasil keluar.
              </div>";
    }
}

?>