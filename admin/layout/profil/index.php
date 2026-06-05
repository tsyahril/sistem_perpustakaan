<?php
session_start();

include __DIR__ . "/../../../config/koneksi.php";
include __DIR__ . "/../../../middleware/admin.php";
include __DIR__ . "/../header.php";

// Pastikan id_user aman dari SQL Injection
$id_user = mysqli_real_escape_string($conn, $_SESSION['id_user']);

$query = mysqli_query($conn, "
    SELECT *
    FROM user
    WHERE id_user = '$id_user'
    LIMIT 1
");

// ==========================
// BUKU SEDANG DIPINJAM
// ==========================
$total_dipinjam = mysqli_num_rows(mysqli_query($conn, "
    SELECT id_pinjam
    FROM peminjaman
    WHERE id_user = '$id_user'
    AND status = 'dipinjam'
"));

// ==========================
// TOTAL RIWAYAT
// ==========================
$total_riwayat = mysqli_num_rows(mysqli_query($conn, "
    SELECT id_pinjam
    FROM peminjaman
    WHERE id_user = '$id_user'
    AND (
        status IN ('selesai', 'kembali')
        OR tanggal_dikembalikan IS NOT NULL
    )
"));

// ==========================
// TOTAL ULASAN / RATING
// ==========================
$total_rating = mysqli_num_rows(mysqli_query($conn, "
    SELECT id_ulasan
    FROM ulasan
    WHERE id_user = '$id_user'
"));

$user = mysqli_fetch_assoc($query);
?>

<div class="max-w-4xl mx-auto px-4 py-6">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-white">Profil Admin</h1>
            <p class="text-slate-400 text-sm mt-1">Informasi akun administrator</p>
        </div>

        <a href="../../dashboard/index.php" class="bg-slate-800 hover:bg-slate-700 transition px-4 py-3 rounded-xl flex items-center gap-2 text-white">
            <i class='bx bx-arrow-back text-xl'></i>
            <span>Kembali</span>
        </a>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 md:p-8 shadow-2xl">

        <div class="flex flex-col md:flex-row md:items-center gap-6 mb-8">
            <div class="w-24 h-24 rounded-full bg-blue-600 flex items-center justify-center text-4xl font-bold shadow-lg text-white select-none">
                <?= strtoupper(substr($user['nama'] ?? 'A', 0, 1)); ?>
            </div>

            <div>
                <h2 class="text-3xl font-bold text-white">
                    <?= htmlspecialchars($user['nama'] ?? '-'); ?>
                </h2>
                <p class="text-slate-400 mt-1">
                    <?= htmlspecialchars($user['email'] ?? '-'); ?>
                </p>
                <div class="mt-3 inline-flex items-center gap-2 bg-blue-500/10 text-blue-400 px-4 py-2 rounded-xl text-sm border border-blue-500/20">
                    <i class='bx bx-shield-quarter'></i>
                    <?= ucfirst($user['role'] ?? 'Admin'); ?>
                </div>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-5 mb-8">
            <div class="bg-slate-800/50 rounded-2xl p-5 border border-slate-700/60">
                <p class="text-slate-400 text-sm mb-2">Nama Lengkap</p>
                <h3 class="text-lg font-semibold text-white">
                    <?= htmlspecialchars($user['nama'] ?? '-'); ?>
                </h3>
            </div>

            <div class="bg-slate-800/50 rounded-2xl p-5 border border-slate-700/60">
                <p class="text-slate-400 text-sm mb-2">Email</p>
                <h3 class="text-lg font-semibold text-white break-all">
                    <?= htmlspecialchars($user['email'] ?? '-'); ?>
                </h3>
            </div>

            <div class="bg-slate-800/50 rounded-2xl p-5 border border-slate-700/60">
                <p class="text-slate-400 text-sm mb-2">Password</p>
                <h3 class="text-lg font-semibold text-white tracking-widest">
                    ••••••••
                </h3>
            </div>

            <div class="bg-slate-800/50 rounded-2xl p-5 border border-slate-700/60">
                <p class="text-slate-400 text-sm mb-2">Role</p>
                <h3 class="text-lg font-semibold text-white">
                    <?= ucfirst($user['role'] ?? 'Admin'); ?>
                </h3>
            </div>
        </div>

        <div class="grid sm:grid-cols-3 gap-5 mb-8">
            <div class="bg-blue-500/10 border border-blue-500/20 rounded-2xl p-5 transition hover:border-blue-500/40">
                <p class="text-blue-300 text-sm mb-2">Sedang Dipinjam</p>
                <h2 class="text-3xl font-bold text-blue-400">
                    <?= $total_dipinjam; ?>
                </h2>
            </div>

            <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-2xl p-5 transition hover:border-emerald-500/40">
                <p class="text-emerald-300 text-sm mb-2">Riwayat Peminjaman</p>
                <h2 class="text-3xl font-bold text-emerald-400">
                    <?= $total_riwayat; ?>
                </h2>
            </div>

            <div class="bg-yellow-500/10 border border-yellow-500/20 rounded-2xl p-5 transition hover:border-yellow-500/40">
                <p class="text-yellow-300 text-sm mb-2">Rating Diberikan</p>
                <h2 class="text-3xl font-bold text-yellow-400">
                    <?= $total_rating; ?>
                </h2>
            </div>
        </div>

        <div class="flex">
            <a href="edit.php" class="inline-flex items-center gap-2 bg-blue-500 hover:bg-blue-600 transition px-5 py-3 rounded-xl text-white font-semibold shadow-lg shadow-blue-500/20">
                <i class='bx bx-edit'></i>
                Edit Profil
            </a>
        </div>

    </div>
</div>

<?php include __DIR__ . "/../footer.php"; ?>