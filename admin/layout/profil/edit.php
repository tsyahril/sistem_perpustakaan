<?php
session_start();

include __DIR__ . "/../../../config/koneksi.php";
include __DIR__ . "/../../../middleware/admin.php";
include __DIR__ . "/../header.php";

$id_user = $_SESSION['id_user'];

$query = mysqli_query($conn, "
    SELECT *
    FROM user
    WHERE id_user = '$id_user'
    LIMIT 1
");

$user = mysqli_fetch_assoc($query);

if (!$user) {
    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Data user tidak ditemukan!',
                background: '#0f172a',
                color: '#ffffff',
                confirmButtonColor: '#ef4444'
            }).then(() => {
                window.location='index.php';
            });
        });
    </script>";
    exit;
}

if (isset($_POST['update'])) {

    $nama     = mysqli_real_escape_string($conn, trim($_POST['nama']));
    $email    = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = trim($_POST['password']);

    if ($nama == '' || $email == '') {

        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Nama dan email wajib diisi!',
                    background: '#0f172a',
                    color: '#ffffff',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#ef4444',
                    customClass: {
                        popup: 'rounded-3xl border border-slate-800 shadow-2xl',
                        title: 'text-white font-bold',
                        htmlContainer: 'text-slate-300',
                        confirmButton: 'rounded-xl px-6 py-3 font-semibold'
                    }
                }).then(() => {
                    window.location='edit.php';
                });
            });
        </script>";
        exit;
    }

    $cek_email = mysqli_query($conn, "
        SELECT id_user
        FROM user
        WHERE email = '$email'
        AND id_user != '$id_user'
        LIMIT 1
    ");

    if (mysqli_num_rows($cek_email) > 0) {

        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Email sudah digunakan akun lain!',
                    background: '#0f172a',
                    color: '#ffffff',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#ef4444',
                    customClass: {
                        popup: 'rounded-3xl border border-slate-800 shadow-2xl',
                        title: 'text-white font-bold',
                        htmlContainer: 'text-slate-300',
                        confirmButton: 'rounded-xl px-6 py-3 font-semibold'
                    }
                }).then(() => {
                    window.location='edit.php';
                });
            });
        </script>";
        exit;
    }

    if ($password != '') {

        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $password_hash = mysqli_real_escape_string($conn, $password_hash);

        $update = mysqli_query($conn, "
            UPDATE user
            SET 
                nama = '$nama',
                email = '$email',
                password = '$password_hash'
            WHERE id_user = '$id_user'
        ");

    } else {

        $update = mysqli_query($conn, "
            UPDATE user
            SET 
                nama = '$nama',
                email = '$email'
            WHERE id_user = '$id_user'
        ");
    }

    if ($update) {

        $_SESSION['nama'] = $nama;

        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Profil berhasil diperbarui',
                    background: '#0f172a',
                    color: '#ffffff',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#3b82f6',
                    customClass: {
                        popup: 'rounded-3xl border border-slate-800 shadow-2xl',
                        title: 'text-white font-bold',
                        htmlContainer: 'text-slate-300',
                        confirmButton: 'rounded-xl px-6 py-3 font-semibold'
                    }
                }).then(() => {
                    window.location='index.php';
                });
            });
        </script>";
        exit;

    } else {

        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Profil gagal diperbarui. Silakan coba lagi.',
                    background: '#0f172a',
                    color: '#ffffff',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#ef4444',
                    customClass: {
                        popup: 'rounded-3xl border border-slate-800 shadow-2xl',
                        title: 'text-white font-bold',
                        htmlContainer: 'text-slate-300',
                        confirmButton: 'rounded-xl px-6 py-3 font-semibold'
                    }
                }).then(() => {
                    window.location='edit.php';
                });
            });
        </script>";
        exit;
    }
}
?>

<div class="max-w-3xl mx-auto">

    <div class="flex items-center justify-between mb-6">

        <div>
            <h1 class="text-3xl font-bold text-white">
                Edit Profil Admin
            </h1>

            <p class="text-slate-400 text-sm">
                Ubah nama, email, dan password akun admin
            </p>
        </div>

        <a href="index.php"
            class="bg-slate-800 hover:bg-slate-700 transition px-4 py-3 rounded-xl flex items-center gap-2 text-white">

            <i class='bx bx-arrow-back text-xl'></i>
            Kembali

        </a>

    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-8 shadow-2xl">

        <form method="POST" class="space-y-5">

            <div>
                <label class="block text-slate-300 text-sm mb-2">
                    Nama Lengkap
                </label>

                <input type="text"
                    name="nama"
                    value="<?= htmlspecialchars($user['nama']); ?>"
                    required
                    class="w-full bg-slate-800 border border-slate-700 rounded-2xl px-5 py-4 text-white outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="block text-slate-300 text-sm mb-2">
                    Email
                </label>

                <input type="email"
                    name="email"
                    value="<?= htmlspecialchars($user['email']); ?>"
                    required
                    class="w-full bg-slate-800 border border-slate-700 rounded-2xl px-5 py-4 text-white outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="block text-slate-300 text-sm mb-2">
                    Password Baru
                </label>

                <input type="password"
                    name="password"
                    placeholder="Kosongkan jika tidak ingin mengganti password"
                    class="w-full bg-slate-800 border border-slate-700 rounded-2xl px-5 py-4 text-white outline-none focus:border-blue-500">

                <p class="text-slate-500 text-xs mt-2">
                    Isi hanya kalau ingin mengganti password.
                </p>
            </div>

            <div class="flex justify-end gap-3 pt-4">

                <a href="index.php"
                    class="bg-slate-700 hover:bg-slate-600 transition px-5 py-3 rounded-xl text-white font-semibold">
                    Batal
                </a>

                <button type="submit"
                    name="update"
                    class="bg-blue-500 hover:bg-blue-600 transition px-5 py-3 rounded-xl text-white font-semibold">
                    Simpan Perubahan
                </button>

            </div>

        </form>

    </div>

</div>

<?php include __DIR__ . "/../footer.php"; ?>