<?php
session_start();
include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../middleware/admin.php";

if (isset($_POST['tambah'])) {

    $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $no_hp    = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = $_POST['role'];

    // Cek user
    $cek_user = mysqli_query($conn, 
        "SELECT * FROM user 
         WHERE nama = '$nama' 
         OR email = '$email'"
    );

    if (mysqli_num_rows($cek_user) > 0) {

        $_SESSION['error'] = "Nama atau Email sudah digunakan.";
        header("Location: tambah.php");
        exit;

    } else {

        $query = "INSERT INTO user 
                 (nama, email, password, role, no_hp)
                 VALUES
                 ('$nama', '$email', '$password', '$role', '$no_hp')";

        if (mysqli_query($conn, $query)) {

            $_SESSION['success'] = "User baru berhasil ditambahkan!";
            header("Location: user.php");
            exit;

        } else {

            $_SESSION['error'] = "Error SQL: " . mysqli_error($conn);
            header("Location: tambah.php");
            exit;
        }
    }
}

include '../../admin/layout/header.php';
?>

<script src="https://cdn.tailwindcss.com"></script>

<div class="mb-8">
    <h2 class="text-3xl font-bold text-white mb-2">Tambah Pengguna Baru</h2>
    <p class="text-slate-400">Tambahkan akun baru untuk admin, petugas, atau user.</p>
</div>

<div class="max-w-2xl bg-slate-900 border border-slate-800 rounded-3xl p-8 shadow-2xl">

    <form action="" method="POST" class="space-y-6">

        <input type="text" name="nama" placeholder="Nama Lengkap" required class="w-full bg-slate-800 p-3 rounded-xl text-white">

        <input type="email" name="email" placeholder="Email Aktif" required class="w-full bg-slate-800 p-3 rounded-xl text-white">

        <input type="text" name="no_hp" placeholder="Nomor HP" required class="w-full bg-slate-800 p-3 rounded-xl text-white">

        <input type="password" name="password" placeholder="Password" required class="w-full bg-slate-800 p-3 rounded-xl text-white">

        <select name="role" required class="w-full bg-slate-800 p-3 rounded-xl text-white">
            <option value="" disabled selected>-- Pilih Role --</option>
            <option value="admin">Admin</option>
            <option value="petugas">Petugas</option>
            <option value="anggota">Anggota</option>
        </select>

        <div class="flex gap-4">
            <button type="submit" name="tambah" class="bg-blue-500 px-6 py-3 rounded-xl text-white">
                Tambah Akun
            </button>

            <a href="user.php" class="border px-6 py-3 rounded-xl text-slate-300">
                Batal
            </a>
        </div>

    </form>

</div>

<?php include '../../admin/layout/footer.php'; ?>