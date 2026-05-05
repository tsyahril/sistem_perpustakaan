<?php
require_once "../config/koneksi.php";

if (isset($_POST['register'])) {
    // Sanitasi input
    $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $no_hp    = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']); 

    // 1. Perbaikan: Cek apakah username atau email sudah terdaftar
    // Pastikan tanda petik berpasangan '$variable' dan ada kata OR
    $cek_user = mysqli_query($conn, "SELECT * FROM user WHERE username = '$username' OR email = '$email'");
    
    if (mysqli_num_rows($cek_user) > 0) {
        echo "<script>alert('Username atau Email sudah terdaftar!'); window.location='regis.php';</script>";
    } else {
        // 2. Insert data
        $query = "INSERT INTO user (nama, email, password, role, no_hp, username) 
                  VALUES ('$nama', '$email', '$password', 'user', '$no_hp', '$username')";
        
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Registrasi Berhasil! Silakan Login'); window.location='login.php';</script>";
        } else {
            echo "Error: " . mysqli_error($conn);
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