<?php
session_start();
include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../middleware/admin.php";
include __DIR__ . "/../../admin/layout/header.php";

// Pencarian
$search = isset($_GET['search']) 
    ? mysqli_real_escape_string($conn, $_GET['search']) 
    : '';

if (!empty($search)) {

    $sql = "SELECT * FROM user 
            WHERE nama LIKE '%$search%' 
            OR email LIKE '%$search%' 
            ORDER BY role ASC, nama ASC";

} else {

    $sql = "SELECT * FROM user 
            ORDER BY role ASC, nama ASC";
}

$query = mysqli_query($conn, $sql);
?>

<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- Boxicons -->
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- SWEET ALERT -->
<?php if (isset($_GET['success'])): ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        <?php if ($_GET['success'] == 'tambah'): ?>

            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Akun berhasil ditambahkan',
                background: '#0f172a',
                color: '#fff',
                confirmButtonColor: '#3b82f6'
            });

        <?php elseif ($_GET['success'] == 'edit'): ?>

            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Akun berhasil diupdate',
                background: '#0f172a',
                color: '#fff',
                confirmButtonColor: '#3b82f6'
            });

        <?php elseif ($_GET['success'] == 'hapus'): ?>

            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Akun berhasil dihapus',
                background: '#0f172a',
                color: '#fff',
                confirmButtonColor: '#ef4444'
            });

        <?php elseif ($_GET['success'] == 'reset'): ?>

            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Password berhasil direset menjadi 123456',
                background: '#0f172a',
                color: '#fff',
                confirmButtonColor: '#eab308'
            });

        <?php endif; ?>

    });
</script>

<?php endif; ?>

<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-5 mb-8">

    <div>

        <h2 class="text-3xl font-bold text-white">
            Kelola Pengguna
        </h2>

        <p class="text-slate-400 mt-1">
            Total ditemukan:
            <span class="text-blue-400 font-semibold">
                <?= mysqli_num_rows($query); ?>
            </span>
            pengguna.
        </p>

    </div>

    <a href="tambah.php"
       class="bg-blue-500 hover:bg-blue-600 transition px-5 py-3 rounded-xl text-white flex items-center gap-2 shadow-lg w-fit">

        <i class='bx bx-user-plus text-xl'></i>
        Tambah Akun

    </a>

</div>

<!-- Search -->
<div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 mb-8 shadow-lg">

    <form method="GET" class="flex items-center gap-3">

        <div class="flex-1 flex items-center bg-slate-800 rounded-xl px-4 py-3">

            <i class='bx bx-search text-slate-400 text-xl mr-2'></i>

            <input type="text"
                   name="search"
                   placeholder="Cari nama atau email pengguna..."
                   value="<?= htmlspecialchars($search) ?>"
                   class="bg-transparent outline-none text-white w-full placeholder:text-slate-500">

        </div>

        <button type="submit"
                class="bg-blue-500 hover:bg-blue-600 transition px-5 py-3 rounded-xl text-white font-medium">

            Cari

        </button>

        <?php if(!empty($search)): ?>

            <a href="user.php"
               class="text-slate-400 hover:text-red-400 text-2xl transition px-2">

                <i class='bx bx-x'></i>

            </a>

        <?php endif; ?>

    </form>

</div>

<!-- Table -->
<div class="overflow-x-auto bg-slate-900 border border-slate-800 rounded-2xl shadow-xl">

    <table class="w-full text-left text-sm text-slate-300">

        <thead class="bg-slate-800 uppercase text-xs text-slate-300">

            <tr>

                <th class="px-6 py-4">No</th>
                <th class="px-6 py-4">Nama Lengkap</th>
                <th class="px-6 py-4">Email</th>
                <th class="px-6 py-4">Role</th>
                <th class="px-6 py-4">Reset Password</th>
                <th class="px-6 py-4">Aksi</th>

            </tr>

        </thead>

        <tbody>

            <?php
            $no = 1;

            if (mysqli_num_rows($query) > 0) {

                while ($row = mysqli_fetch_assoc($query)) {
            ?>

            <tr class="border-b border-slate-800 hover:bg-slate-800/50 transition">

                <!-- No -->
                <td class="px-6 py-4 text-slate-400">
                    <?= $no++; ?>
                </td>

                <!-- Nama -->
                <td class="px-6 py-4 font-medium text-white">
                    <?= htmlspecialchars($row['nama']); ?>
                </td>

                <!-- Email -->
                <td class="px-6 py-4 text-slate-300">
                    <?= htmlspecialchars($row['email']); ?>
                </td>

                <!-- Role -->
                <td class="px-6 py-4">

                    <?php
                    $role = strtolower($row['role']);

                    $badgeClass = match($role) {
                        'admin'   => 'bg-blue-500/10 text-blue-400',
                        'petugas' => 'bg-cyan-500/10 text-cyan-400',
                        default   => 'bg-emerald-500/10 text-emerald-400'
                    };
                    ?>

                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $badgeClass; ?>">
                        <?= strtoupper($row['role']); ?>
                    </span>

                </td>

                <!-- Reset Password -->
                <td class="px-6 py-4">

                    <a href="reset_password.php?id=<?= $row['id_user']; ?>"
                       onclick="confirmReset(event, this.href)"
                       class="bg-yellow-500/10 hover:bg-yellow-500/20 text-yellow-400 px-4 py-2 rounded-xl text-xs font-semibold transition">

                        Reset Password

                    </a>

                </td>

                <!-- Aksi -->
                <td class="px-6 py-4">

                    <div class="flex items-center gap-4">

                        <!-- Edit -->
                        <a href="edit_user.php?id=<?= $row['id_user']; ?>"
                           class="text-blue-400 hover:text-blue-300 text-2xl transition">

                            <i class='bx bx-edit-alt'></i>

                        </a>

                        <!-- Delete -->
                        <a href="hapus_user.php?id=<?= $row['id_user']; ?>"
                           onclick="confirmDelete(event, this.href)"
                           class="text-red-400 hover:text-red-300 text-2xl transition">

                            <i class='bx bx-trash'></i>

                        </a>

                    </div>

                </td>

            </tr>

            <?php
                }

            } else {
            ?>

            <tr>

                <td colspan="6"
                    class="text-center py-10 text-slate-400">

                    Data tidak ditemukan...

                </td>

            </tr>

            <?php } ?>

        </tbody>

    </table>

</div>

<!-- SWEET ALERT CONFIRM -->
<script>

    // DELETE
    function confirmDelete(event, url) {

        event.preventDefault();

        Swal.fire({
            title: 'Hapus akun?',
            text: 'Data akun akan dihapus permanen',
            icon: 'warning',
            background: '#0f172a',
            color: '#fff',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {

            if (result.isConfirmed) {
                window.location.href = user.php;
            }

        });

    }

    // RESET PASSWORD
    function confirmReset(event, url) {

        event.preventDefault();

        Swal.fire({
            title: 'Reset Password?',
            text: 'Password user akan direset menjadi 123456',
            icon: 'question',
            background: '#0f172a',
            color: '#fff',
            showCancelButton: true,
            confirmButtonColor: '#eab308',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Reset',
            cancelButtonText: 'Batal'
        }).then((result) => {

            if (result.isConfirmed) {
                window.location.href = url;
            }

        });

    }

</script>

<?php include '../../admin/layout/footer.php'; ?>