<?php
include __DIR__ . "/config/koneksi.php";

// ambil semua user
$query = mysqli_query($conn, "SELECT id_user, password FROM user");

while ($user = mysqli_fetch_assoc($query)) {

    $id = $user['id_user'];
    $password_lama = $user['password'];

    // cek biar password yang sudah hash tidak dihash ulang
    if (!password_get_info($password_lama)['algo']) {

        // hash password lama
        $password_baru = password_hash($password_lama, PASSWORD_DEFAULT);

        // update ke database
        mysqli_query($conn, "UPDATE user 
                             SET password='$password_baru' 
                             WHERE id_user='$id'");

        echo "Password user ID $id berhasil dihash <br>";
    }
}

echo "Selesai!";
?>