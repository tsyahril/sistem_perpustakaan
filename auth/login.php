<?php
include __DIR__ . '/../config/koneksi.php';
session_start();

$error = "";

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM user WHERE email='$email' AND password='$password'");
    $user = mysqli_fetch_array($query);

    if ($user) {
        $_SESSION['login'] = true;
        $_SESSION['id'] = $user['id_user'];
        $_SESSION['role'] = $user['role'];
        if ($user['role'] == 'admin') {
            header("Location: ../admin/index.php");
        } elseif ($user['role'] == 'petugas') {
            header("Location: ../petugas/index.php");
        } else {
            header("Location: ../user/index.php");
        }
        exit;
    } else {
        $error = "Email atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login</title>

<!-- Bootstrap CSS (WAJIB) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<!-- CSS -->
<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>

<div class="login-container">

        <div class="login-card">
            <h3 class="text-center fw-bold mb-3 title-login">Login Perpustakaan</h3>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error; ?></div>
            <?php endif; ?>

            <form method="POST">

                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Password</label>
                    <div class="input-group">
                        <input type="password" id="password" name="password" class="form-control" required>
                        <span class="input-group-text" onclick="togglePassword()">
                            <i class="bi bi-eye" id="eyeIcon"></i>
                        </span>
                    </div>
                </div>

                <button name="login" class="btn btn-primary w-100">Login</button>

                <p class="text-center mt-3">
                    Belum punya akun? <a href="register.php">Daftar</a>
                </p>

            </form>
        </div>
</div>

<script>
function togglePassword() {
    const pass = document.getElementById("password");
    pass.type = pass.type === "password" ? "text" : "password";
}

</script>

</body>
</html>