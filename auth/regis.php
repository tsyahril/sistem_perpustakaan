<?php
include __DIR__ . "/../config/koneksi.php";

$error = "";

if (isset($_POST['register'])) {

    $nama     = $_POST['nama'];
    $email    = $_POST['email'];
    $no_hp    = $_POST['no_hp'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // cek email
    $cek = mysqli_query($conn, "SELECT * FROM user WHERE email='$email'");

    if (mysqli_num_rows($cek) > 0) {
        $error = "Email sudah terdaftar!";
    } else {

        $query = "INSERT INTO user (nama, email, password, role, no_hp)
                  VALUES ('$nama','$email','$password','user','$no_hp')";

        if (mysqli_query($conn, $query)) {
            header("Location: login.php");
            exit;
        } else {
            $error = "ERROR: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar Akun</title>

<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- Boxicons -->
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

</head>
<body class="bg-slate-950 min-h-screen flex items-center justify-center px-4 py-10">

<div class="w-full max-w-md">

    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-8 shadow-2xl">

        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-white mb-2">
                Buat Akun
            </h1>

            <p class="text-slate-400 text-sm">
                Silakan isi data diri untuk membuat akun
            </p>
        </div>

        <!-- Alert -->
        <?php if ($error): ?>
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 text-sm rounded-xl p-3 mb-5">
                <?= $error; ?>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form action="" method="POST" class="space-y-5">

            <!-- Nama -->
            <div>
                <label class="block text-sm text-slate-300 mb-2">
                    Nama Lengkap
                </label>

                <div class="relative">
                    <i class='bx bx-user absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 text-lg'></i>

                    <input 
                        type="text"
                        name="nama"
                        placeholder="Masukkan nama lengkap"
                        required
                        class="w-full bg-slate-800 border border-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none text-white rounded-xl py-3 pl-12 pr-4 transition"
                    >
                </div>
            </div>

            <!-- Email -->
            <div>
                <label class="block text-sm text-slate-300 mb-2">
                    Email
                </label>

                <div class="relative">
                    <i class='bx bx-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 text-lg'></i>

                    <input 
                        type="email"
                        name="email"
                        placeholder="Masukkan email"
                        required
                        class="w-full bg-slate-800 border border-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none text-white rounded-xl py-3 pl-12 pr-4 transition"
                    >
                </div>
            </div>

            <!-- No HP -->
            <div>
                <label class="block text-sm text-slate-300 mb-2">
                    Nomor HP / WA
                </label>

                <div class="relative">
                    <i class='bx bx-phone absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 text-lg'></i>

                    <input 
                        type="text"
                        name="no_hp"
                        placeholder="Masukkan nomor HP"
                        required
                        class="w-full bg-slate-800 border border-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none text-white rounded-xl py-3 pl-12 pr-4 transition"
                    >
                </div>
            </div>

            <!-- Username -->
            <div>
                <label class="block text-sm text-slate-300 mb-2">
                    Username
                </label>

                <div class="relative">
                    <i class='bx bx-at absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 text-lg'></i>

                    <input 
                        type="text"
                        name="username"
                        placeholder="Masukkan username"
                        required
                        class="w-full bg-slate-800 border border-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none text-white rounded-xl py-3 pl-12 pr-4 transition"
                    >
                </div>
            </div>

            <!-- Password -->
            <div>
                <label class="block text-sm text-slate-300 mb-2">
                    Password
                </label>

                <div class="relative">
                    <i class='bx bx-lock-alt absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 text-lg'></i>

                    <input 
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Masukkan password"
                        required
                        class="w-full bg-slate-800 border border-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none text-white rounded-xl py-3 pl-12 pr-12 transition"
                    >

                    <button 
                        type="button"
                        onclick="togglePassword()"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white"
                    >
                        <i class='bx bx-show' id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <!-- Button -->
            <button 
                type="submit"
                name="register"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition duration-200 shadow-lg shadow-blue-600/20"
            >
                Daftar Sekarang
            </button>

        </form>

        <!-- Footer -->
        <p class="text-center text-slate-400 text-sm mt-6">
            Sudah punya akun?
            <a href="login.php" class="text-blue-400 hover:text-blue-300 font-medium">
                Login
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
        eye.classList.remove("bx-show");
        eye.classList.add("bx-hide");
    } else {
        pass.type = "password";
        eye.classList.remove("bx-hide");
        eye.classList.add("bx-show");
    }
}
</script>

</body>
</html>