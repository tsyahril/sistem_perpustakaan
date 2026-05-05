<?php
require_once "../config/koneksi.php";

if (isset($_POST['register'])) {

    $nama  = $_POST['nama'];
    $email = $_POST['email'];
    $no_hp = $_POST['no_hp'];
    $password = $_POST['password'];

    // cek email saja
    $cek = mysqli_query($conn, "SELECT * FROM user WHERE email='$email'");

    if (mysqli_num_rows($cek) > 0) {
        echo "Email sudah terdaftar";
    } else {

        $query = "INSERT INTO user (nama, email, password, role, no_hp)
                  VALUES ('$nama','$email','$password','user','$no_hp')";

        if (mysqli_query($conn, $query)) {
            header("Location: login.php");
            exit;
        } else {
            echo "ERROR: " . mysqli_error($conn);
        }   
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Akun - E-Perpus</title>
    <link rel="stylesheet" href="../assets/css/regis.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body class="auth-page">
    <div class="auth-card">
        <div class="auth-header">
            <h2>Buat Akun</h2>
            <p>Silakan isi data diri Anda untuk meminjam buku.</p>
        </div>

        <form action="" method="POST">
            <div class="input-group">
                <i class='bx bx-user'></i>
                <input type="text" name="nama" placeholder="Nama Lengkap" required>
            </div>
            <div class="input-group">
                <i class='bx bx-envelope'></i>
                <input type="email" name="email" placeholder="Email Aktif" required>
            </div>
            <div class="input-group">
                <i class='bx bx-phone'></i>
                <input type="text" name="no_hp" placeholder="Nomor HP/WA" required>
            </div>
            <div class="input-group">
                <i class='bx bx-at'></i>
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="input-group">
                <i class='bx bx-lock-alt'></i>
                <input type="password" name="password" placeholder="Password" required>
            </div>

            <button type="submit" name="register" class="btn-auth">Daftar Sekarang</button>
        </form>

        <div class="auth-footer">
            Sudah punya akun? <a href="login.php">Login</a>
        </div>
    </div>
</body>
</html>