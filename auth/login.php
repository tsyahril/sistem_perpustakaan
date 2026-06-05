<?php
include __DIR__ . '/../config/koneksi.php';
session_start();

$error = "";

if (isset($_POST['login'])) {

    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // cek email
    $query = mysqli_query($conn, "
        SELECT * 
        FROM user 
        WHERE email = '$email'
        LIMIT 1
    ");

    $user = mysqli_fetch_assoc($query);

    if ($user && password_verify($password, $user['password'])) {

        $_SESSION['login'] = true;
        $_SESSION['id_user'] = $user['id_user'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['role'] = $user['role'];

        // ==========================
        // UPDATE LAST LOGIN
        // ==========================
        $id_user_login = (int) $user['id_user'];

        mysqli_query($conn, "
            UPDATE user 
            SET last_login = NOW()
            WHERE id_user = $id_user_login
        ");

        if ($user['role'] == 'admin') {
            header("Location: ../admin/dashboard/index.php");
            exit;
        } elseif ($user['role'] == 'petugas') {
            header("Location: ../petugas/dashboard/index.php");
            exit;
        } else {
            header("Location: ../anggota/dashboard/index.php");
            exit;
        }

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

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="bg-slate-950 min-h-screen flex items-center justify-center px-4">

    <div class="w-full max-w-md">

        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-8 shadow-2xl">

            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-white mb-2">
                    Login Perpustakaan
                </h1>

                <p class="text-slate-400 text-sm">
                    Silahkan masuk ke akun anda
                </p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-500/10 border border-red-500/20 text-red-400 text-sm rounded-xl p-3 mb-5">
                    <?= htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5">

                <div>
                    <label class="block text-sm text-slate-300 mb-2">
                        Email
                    </label>

                    <div class="relative">
                        <i class="bi bi-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>

                        <input
                            type="email"
                            name="email"
                            required
                            placeholder="Masukkan email"
                            class="w-full bg-slate-800 border border-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none text-white rounded-xl py-3 pl-11 pr-4 transition">
                    </div>
                </div>

                <div>
                    <label class="block text-sm text-slate-300 mb-2">
                        Password
                    </label>

                    <div class="relative">
                        <i class="bi bi-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            placeholder="Masukkan password"
                            class="w-full bg-slate-800 border border-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none text-white rounded-xl py-3 pl-11 pr-12 transition">

                        <button
                            type="button"
                            onclick="togglePassword()"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white">
                            <i class="bi bi-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <button
                    type="submit"
                    name="login"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition duration-200 shadow-lg shadow-blue-600/20">
                    Login
                </button>

            </form>

            <p class="text-center text-slate-400 text-sm mt-6">
                Belum punya akun?
                <a href="../auth/regis.php" class="text-blue-400 hover:text-blue-300 font-medium">
                    Daftar
                </a>
            </p>

        </div>

    </div>

    <script>
        function togglePassword() {
            const pass = document.getElementById("password");
            const eye = document.getElementById("eyeIcon");

            if (pass.type === "password") {
                pass.type = "text";
                eye.classList.remove("bi-eye");
                eye.classList.add("bi-eye-slash");
            } else {
                pass.type = "password";
                eye.classList.remove("bi-eye-slash");
                eye.classList.add("bi-eye");
            }
        }
    </script>

</body>

</html>