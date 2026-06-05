<?php
session_start();

include __DIR__ . "/../../../config/koneksi.php";
include __DIR__ . "/../../../middleware/petugas.php";
include __DIR__ . "/../header.php";

$id_user = $_SESSION['id_user'];

$query = mysqli_query($conn, "
    SELECT *
    FROM user
    WHERE id_user = '$id_user'
    LIMIT 1
");

$user = mysqli_fetch_assoc($query);

$total_buku = mysqli_num_rows(mysqli_query($conn, "
    SELECT id_buku FROM buku
"));

$total_dipinjam = mysqli_num_rows(mysqli_query($conn, "
    SELECT peminjaman.id_pinjam
    FROM peminjaman
    JOIN user ON peminjaman.id_user = user.id_user
    WHERE peminjaman.status = 'dipinjam'
    AND user.role IN ('anggota', 'user')
"));
?>

<div class="max-w-4xl mx-auto">

    <div class="flex items-center justify-between mb-6">

        <div>
            <h1 class="text-3xl font-bold text-white">
                Profil Petugas
            </h1>

            <p class="text-slate-400 text-sm">
                Informasi akun petugas perpustakaan
            </p>
        </div>

        <a href="../../dashboard/index.php"
            class="bg-slate-800 hover:bg-slate-700 transition px-4 py-3 rounded-xl flex items-center gap-2 text-white">

            <i class='bx bx-arrow-back text-xl'></i>
            Kembali

        </a>

    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-8 shadow-2xl">

        <div class="flex flex-col md:flex-row md:items-center gap-6 mb-8">

            <div class="w-24 h-24 rounded-full bg-blue-600 flex items-center justify-center text-4xl font-bold shadow-lg">
                <?= strtoupper(substr($user['nama'], 0, 1)); ?>
            </div>

            <div>
                <h2 class="text-3xl font-bold text-white">
                    <?= htmlspecialchars($user['nama']); ?>
                </h2>

                <p class="text-slate-400 mt-1">
                    <?= htmlspecialchars($user['email']); ?>
                </p>

                <div class="mt-3 inline-flex items-center gap-2 bg-blue-500/10 text-blue-400 px-4 py-2 rounded-xl text-sm">
                    <i class='bx bx-shield-quarter'></i>
                    <?= ucfirst($user['role']); ?>
                </div>
            </div>

        </div>

        <div class="grid md:grid-cols-2 gap-5 mb-8">

            <div class="bg-slate-800 rounded-2xl p-5 border border-slate-700">
                <p class="text-slate-400 text-sm mb-2">Nama Lengkap</p>
                <h3 class="text-lg font-semibold text-white">
                    <?= htmlspecialchars($user['nama']); ?>
                </h3>
            </div>

            <div class="bg-slate-800 rounded-2xl p-5 border border-slate-700">
                <p class="text-slate-400 text-sm mb-2">Email</p>
                <h3 class="text-lg font-semibold text-white break-all">
                    <?= htmlspecialchars($user['email']); ?>
                </h3>
            </div>

            <div class="bg-slate-800 rounded-2xl p-5 border border-slate-700">
                <p class="text-slate-400 text-sm mb-2">Password</p>

                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-white tracking-widest">
                        ••••••••
                    </h3>
                </div>
            </div>

            <div class="bg-slate-800 rounded-2xl p-5 border border-slate-700">
                <p class="text-slate-400 text-sm mb-2">Role</p>
                <h3 class="text-lg font-semibold text-white">
                    <?= ucfirst($user['role']); ?>
                </h3>
            </div>

        </div>

        <a href="edit.php"
            class="inline-flex items-center gap-2 bg-blue-500 hover:bg-blue-600 transition px-5 py-3 rounded-xl text-white font-semibold">

            <i class='bx bx-edit'></i>
            Edit Profil

        </a>

    </div>

</div>

<?php include __DIR__ . "/../footer.php"; ?>