<?php
session_start();

include __DIR__ . "/../../../config/koneksi.php";
include __DIR__ . "/../../../middleware/anggota.php";
include __DIR__ . "/../header.php";

$id_user = $_SESSION['id_user'];

$query = mysqli_query($conn, "
    SELECT *
    FROM user
    WHERE id_user = '$id_user'
    LIMIT 1
");

$user = mysqli_fetch_assoc($query);

if (isset($_POST['update'])) {

    $nama     = mysqli_real_escape_string($conn, trim($_POST['nama']));
    $email    = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = trim($_POST['password']);

    if (empty($nama) || empty($email)) {

        echo "
        <script>
        Swal.fire({
            icon:'error',
            title:'Gagal',
            text:'Nama dan Email wajib diisi'
        });
        </script>";
    } else {

        $cek_email = mysqli_query($conn, "
            SELECT id_user
            FROM user
            WHERE email = '$email'
            AND id_user != '$id_user'
        ");

        if (mysqli_num_rows($cek_email) > 0) {

            echo "
            <script>
            Swal.fire({
                icon:'error',
                title:'Gagal',
                text:'Email sudah digunakan'
            });
            </script>";
        } else {

            if (!empty($password)) {

                $password_hash = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

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

                $_SESSION['nama']  = $nama;
                $_SESSION['email'] = $email;

        echo "
        <script>
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
        </script>";
        exit;

    } else {

                echo "
                <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Profil gagal diperbarui',
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
                </script>";
                exit;
            }
        }
    }
}
?>

<div class="max-w-3xl mx-auto">

    <div class="flex justify-between items-center mb-6">

        <div>
            <h1 class="text-3xl font-bold text-white">
                Edit Profil
            </h1>

            <p class="text-slate-400">
                Ubah nama, email dan password akun
            </p>
        </div>

        <a href="index.php"
            class="bg-slate-800 hover:bg-slate-700 px-4 py-3 rounded-xl text-white">

            Kembali

        </a>

    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-8">

        <form method="POST" class="space-y-5">

            <div>

                <label class="block text-slate-300 mb-2">
                    Nama Lengkap
                </label>

                <input
                    type="text"
                    name="nama"
                    value="<?= htmlspecialchars($user['nama']); ?>"
                    class="w-full bg-slate-800 border border-slate-700 rounded-2xl px-5 py-4 text-white">

            </div>

            <div>

                <label class="block text-slate-300 mb-2">
                    Email
                </label>

                <input
                    type="email"
                    name="email"
                    value="<?= htmlspecialchars($user['email']); ?>"
                    class="w-full bg-slate-800 border border-slate-700 rounded-2xl px-5 py-4 text-white">

            </div>

            <div>

                <label class="block text-slate-300 mb-2">
                    Password Baru
                </label>

                <input
                    type="password"
                    name="password"
                    placeholder="Kosongkan jika tidak ingin mengganti password"
                    class="w-full bg-slate-800 border border-slate-700 rounded-2xl px-5 py-4 text-white">

                <p class="text-slate-500 text-xs mt-2">
                    Isi jika ingin mengganti password.
                </p>

            </div>

            <div class="flex justify-end gap-3">

                <a href="index.php"
                    class="bg-slate-700 hover:bg-slate-600 px-5 py-3 rounded-xl text-white">

                    Batal

                </a>

                <button
                    type="submit"
                    name="update"
                    class="bg-blue-500 hover:bg-blue-600 px-5 py-3 rounded-xl text-white font-semibold">

                    Simpan

                </button>

            </div>

        </form>

    </div>

</div>

<?php include __DIR__ . "/../footer.php"; ?>