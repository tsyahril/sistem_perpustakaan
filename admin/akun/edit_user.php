<?php
session_start();

include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../middleware/admin.php";

// ==========================
// CEK ID
// ==========================
if (!isset($_GET['id'])) {
    header("Location: user.php");
    exit();
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// ==========================
// AMBIL DATA USER
// ==========================
$query = mysqli_query($conn, "SELECT * FROM user WHERE id_user = '$id' LIMIT 1");
$data  = mysqli_fetch_assoc($query);

if (!$data) {
    $_SESSION['error'] = "Data tidak ditemukan!";
    header("Location: user.php");
    exit();
}

$alert = null;

// ==========================
// PROSES UPDATE
// ==========================
if (isset($_POST['update'])) {

    $nama  = mysqli_real_escape_string($conn, trim($_POST['nama']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $no_hp = mysqli_real_escape_string($conn, trim($_POST['no_hp']));
    $role  = mysqli_real_escape_string($conn, $_POST['role']);

    $role_diizinkan = ['admin', 'petugas', 'anggota'];

    if ($nama == '' || $email == '' || $no_hp == '' || $role == '') {

        $alert = [
            'icon' => 'error',
            'title' => 'Gagal!',
            'text' => 'Semua field wajib diisi.'
        ];

    } elseif (!in_array($role, $role_diizinkan)) {

        $alert = [
            'icon' => 'error',
            'title' => 'Gagal!',
            'text' => 'Role tidak valid.'
        ];

    } else {

        $cek_email = mysqli_query($conn, "
            SELECT id_user 
            FROM user 
            WHERE email = '$email' 
            AND id_user != '$id'
            LIMIT 1
        ");

        if (mysqli_num_rows($cek_email) > 0) {

            $alert = [
                'icon' => 'error',
                'title' => 'Gagal!',
                'text' => 'Email sudah digunakan oleh pengguna lain.'
            ];

        } else {

            if (!empty($_POST['password'])) {

                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

                $sql = "UPDATE user SET 
                        nama = '$nama',
                        email = '$email',
                        no_hp = '$no_hp',
                        role = '$role',
                        password = '$password'
                        WHERE id_user = '$id'";

            } else {

                $sql = "UPDATE user SET 
                        nama = '$nama',
                        email = '$email',
                        no_hp = '$no_hp',
                        role = '$role'
                        WHERE id_user = '$id'";
            }

            if (mysqli_query($conn, $sql)) {

                $alert = [
                    'icon' => 'success',
                    'title' => 'Berhasil!',
                    'text' => 'Data pengguna berhasil diperbarui.',
                    'redirect' => 'user.php'
                ];

            } else {

                $alert = [
                    'icon' => 'error',
                    'title' => 'Gagal!',
                    'text' => 'Terjadi kesalahan: ' . mysqli_error($conn)
                ];
            }
        }
    }
}

include '../../admin/layout/header.php';
?>

<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="min-h-screen bg-[#020617] text-slate-100 px-6 py-8">

    <div class="mb-8">
        <div class="inline-flex items-center gap-2 bg-blue-500/10 border border-blue-500/20 text-blue-400 px-4 py-2 rounded-full text-sm font-semibold mb-4">
            Edit Data Pengguna
        </div>

        <h2 class="text-3xl font-bold text-white mb-2">
            Edit Pengguna
        </h2>

        <p class="text-slate-400">
            Ubah informasi akun
            <span class="text-blue-400 font-semibold">
                <?= htmlspecialchars($data['nama']); ?>
            </span>
        </p>
    </div>

    <div class="max-w-2xl bg-[#0f172a] border border-slate-800 rounded-3xl p-8 shadow-2xl shadow-blue-950/20">

        <form action="" method="POST" class="space-y-6" id="formUpdate">

            <input type="hidden" name="update" value="1">

            <div>
                <label class="block mb-2 text-sm font-semibold text-slate-300">
                    Nama Lengkap
                </label>
                <input 
                    type="text" 
                    name="nama" 
                    value="<?= htmlspecialchars($data['nama']); ?>" 
                    required
                    class="w-full bg-[#1e293b] border border-slate-700 p-3 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                >
            </div>

            <div>
                <label class="block mb-2 text-sm font-semibold text-slate-300">
                    Email
                </label>
                <input 
                    type="email" 
                    name="email" 
                    value="<?= htmlspecialchars($data['email']); ?>" 
                    required
                    class="w-full bg-[#1e293b] border border-slate-700 p-3 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                >
            </div>

            <div>
                <label class="block mb-2 text-sm font-semibold text-slate-300">
                    No HP
                </label>
                <input 
                    type="text" 
                    name="no_hp" 
                    value="<?= htmlspecialchars($data['no_hp']); ?>" 
                    required
                    class="w-full bg-[#1e293b] border border-slate-700 p-3 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                >
            </div>

            <div>
                <label class="block mb-2 text-sm font-semibold text-slate-300">
                    Password Baru
                </label>
                <input 
                    type="password" 
                    name="password" 
                    placeholder="Kosongkan jika tidak ingin mengganti password"
                    class="w-full bg-[#1e293b] border border-slate-700 p-3 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                >
                <p class="text-xs text-slate-500 mt-2">
                    Password hanya berubah jika kolom ini diisi.
                </p>
            </div>

            <div>
                <label class="block mb-2 text-sm font-semibold text-slate-300">
                    Role
                </label>
                <select 
                    name="role" 
                    required
                    class="w-full bg-[#1e293b] border border-slate-700 p-3 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                >
                    <option value="admin" <?= ($data['role'] == 'admin') ? 'selected' : ''; ?>>
                        Admin
                    </option>

                    <option value="petugas" <?= ($data['role'] == 'petugas') ? 'selected' : ''; ?>>
                        Petugas
                    </option>

                    <option value="anggota" <?= ($data['role'] == 'anggota') ? 'selected' : ''; ?>>
                        Anggota
                    </option>
                </select>
            </div>

            <div class="flex gap-4 pt-4">

                <button 
                    type="submit"
                    class="bg-blue-600 hover:bg-blue-700 px-6 py-3 rounded-xl text-white font-semibold transition shadow-lg shadow-blue-900/30"
                >
                    Simpan
                </button>

                <a 
                    href="user.php"
                    class="bg-slate-800 hover:bg-slate-700 border border-slate-700 px-6 py-3 rounded-xl text-slate-300 font-semibold transition"
                >
                    Batal
                </a>

            </div>

        </form>

    </div>

</div>

<script>
    const formUpdate = document.getElementById('formUpdate');

    formUpdate.addEventListener('submit', function(e) {
        e.preventDefault();

        Swal.fire({
            title: 'Simpan Perubahan?',
            text: 'Data pengguna akan diperbarui.',
            icon: 'question',
            background: '#0f172a',
            color: '#e2e8f0',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#334155',
            confirmButtonText: 'Ya, Simpan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                formUpdate.submit();
            }
        });
    });
</script>

<?php if ($alert) { ?>
<script>
    Swal.fire({
        icon: <?= json_encode($alert['icon']); ?>,
        title: <?= json_encode($alert['title']); ?>,
        text: <?= json_encode($alert['text']); ?>,
        background: '#0f172a',
        color: '#e2e8f0',
        confirmButtonColor: '#2563eb'
    }).then(() => {
        <?php if (isset($alert['redirect'])) { ?>
            window.location.href = <?= json_encode($alert['redirect']); ?>;
        <?php } ?>
    });
</script>
<?php } ?>

<?php include '../../admin/layout/footer.php'; ?>