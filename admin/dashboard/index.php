<?php
session_start();

include __DIR__ . '/../../config/koneksi.php';
include __DIR__ . '/../../middleware/admin.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit();
}

include '../layout/header.php';

// ==========================
// FUNCTION COUNT
// ==========================
function getCount($conn, $query)
{
    $result = mysqli_query($conn, $query);

    if (!$result) {
        return 0;
    }

    return mysqli_fetch_row($result)[0] ?? 0;
}

// ==========================
// STATISTIK
// ==========================
$totalAdmin = getCount($conn, "
    SELECT COUNT(*) 
    FROM user 
    WHERE role = 'admin'
");

$totalPetugas = getCount($conn, "
    SELECT COUNT(*) 
    FROM user 
    WHERE role = 'petugas'
");

$totalUser = getCount($conn, "
    SELECT COUNT(*) 
    FROM user 
    WHERE role IN ('anggota', 'user')
");

$totalBuku = getCount($conn, "
    SELECT COUNT(*) 
    FROM buku
");

$pinjamAktif = getCount($conn, "
    SELECT COUNT(*) 
    FROM peminjaman 
    WHERE status = 'dipinjam'
");

// RIWAYAT SELESAI / KEMBALI
$riwayatSelesai = getCount($conn, "
    SELECT COUNT(*) 
    FROM peminjaman 
    WHERE 
        status IN ('kembali', 'selesai')
        OR tanggal_dikembalikan IS NOT NULL
");

// BUKU HILANG
$bukuHilang = getCount($conn, "
    SELECT COUNT(DISTINCT id_buku)
    FROM (
        SELECT id_buku 
        FROM peminjaman 
        WHERE kondisi_buku = 'hilang'
    ) AS data_hilang
");

// ==========================
// USER TELAT MENGEMBALIKAN
// ==========================
$userTelat = mysqli_query($conn, "
    SELECT 
        p.id_pinjam,
        p.tanggal_pinjam,
        p.tanggal_kembali,
        u.nama,
        b.judul,
        DATEDIFF(CURDATE(), p.tanggal_kembali) AS telat_hari
    FROM peminjaman p
    JOIN user u 
        ON p.id_user = u.id_user
    JOIN buku b 
        ON p.id_buku = b.id_buku
    WHERE 
        p.status = 'dipinjam'
        AND p.tanggal_kembali < CURDATE()
    ORDER BY p.tanggal_kembali ASC
    LIMIT 5
");
?>

<script src="https://cdn.tailwindcss.com"></script>

<!-- WRAPPER -->
<div class="space-y-7 pb-16">

    <!-- HEADER -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">

        <div class="flex items-center justify-between gap-4 flex-wrap">

            <div>
                <h1 class="text-2xl font-bold text-white">
                    Dashboard
                </h1>

                <p class="text-slate-400 text-sm mt-1">
                    Selamat datang, <?= htmlspecialchars($_SESSION['nama']); ?>
                </p>
            </div>

            <div class="text-sm text-slate-400">
                <?= date('d M Y'); ?>
            </div>

        </div>

    </div>

    <!-- SHORTCUT -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

        <a href="../akun/index.php"
            class="bg-slate-900 border border-slate-800 hover:border-blue-500/50 transition rounded-2xl p-4 text-slate-300 hover:text-white">
            <div class="flex items-center gap-3">
                <i class='bx bxs-user text-xl text-blue-400'></i>
                <span class="text-sm font-medium">Kelola User</span>
            </div>
        </a>

        <a href="../buku/index.php"
            class="bg-slate-900 border border-slate-800 hover:border-emerald-500/50 transition rounded-2xl p-4 text-slate-300 hover:text-white">
            <div class="flex items-center gap-3">
                <i class='bx bxs-book text-xl text-emerald-400'></i>
                <span class="text-sm font-medium">Kelola Buku</span>
            </div>
        </a>

        <a href="../peminjaman/peminjaman.php"
            class="bg-slate-900 border border-slate-800 hover:border-yellow-500/50 transition rounded-2xl p-4 text-slate-300 hover:text-white">
            <div class="flex items-center gap-3">
                <i class='bx bxs-bookmark text-xl text-yellow-400'></i>
                <span class="text-sm font-medium">Peminjaman</span>
            </div>
        </a>

        <a href="../peminjaman/riwayat.php"
            class="bg-slate-900 border border-slate-800 hover:border-purple-500/50 transition rounded-2xl p-4 text-slate-300 hover:text-white">
            <div class="flex items-center gap-3">
                <i class='bx bx-history text-xl text-purple-400'></i>
                <span class="text-sm font-medium">Riwayat</span>
            </div>
        </a>

    </div>

    <!-- STATISTIK -->
    <div>

        <div class="mb-4">
            <h2 class="text-xl font-bold text-white">
                Ringkasan Data
            </h2>

            <p class="text-slate-400 text-sm">
                Statistik utama perpustakaan.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

            <!-- Admin -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-slate-400 text-sm">Admin</p>

                        <h3 class="text-3xl font-bold text-white mt-1">
                            <?= $totalAdmin; ?>
                        </h3>
                    </div>

                    <div class="w-11 h-11 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center text-2xl">
                        <i class='bx bxs-user-badge'></i>
                    </div>

                </div>
            </div>

            <!-- Petugas -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-slate-400 text-sm">Petugas</p>

                        <h3 class="text-3xl font-bold text-white mt-1">
                            <?= $totalPetugas; ?>
                        </h3>
                    </div>

                    <div class="w-11 h-11 rounded-xl bg-cyan-500/10 text-cyan-400 flex items-center justify-center text-2xl">
                        <i class='bx bxs-briefcase'></i>
                    </div>

                </div>
            </div>

            <!-- Anggota -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-slate-400 text-sm">Anggota</p>

                        <h3 class="text-3xl font-bold text-white mt-1">
                            <?= $totalUser; ?>
                        </h3>
                    </div>

                    <div class="w-11 h-11 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-2xl">
                        <i class='bx bxs-user'></i>
                    </div>

                </div>
            </div>

            <!-- Total Buku -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-slate-400 text-sm">Total Buku</p>

                        <h3 class="text-3xl font-bold text-white mt-1">
                            <?= $totalBuku; ?>
                        </h3>
                    </div>

                    <div class="w-11 h-11 rounded-xl bg-yellow-500/10 text-yellow-400 flex items-center justify-center text-2xl">
                        <i class='bx bxs-book'></i>
                    </div>

                </div>
            </div>

            <!-- Sedang Dipinjam -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-slate-400 text-sm">Sedang Dipinjam</p>

                        <h3 class="text-3xl font-bold text-white mt-1">
                            <?= $pinjamAktif; ?>
                        </h3>
                    </div>

                    <div class="w-11 h-11 rounded-xl bg-red-500/10 text-red-400 flex items-center justify-center text-2xl">
                        <i class='bx bxs-bookmark'></i>
                    </div>

                </div>
            </div>

            <!-- Riwayat Selesai -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-slate-400 text-sm">Riwayat Selesai</p>

                        <h3 class="text-3xl font-bold text-white mt-1">
                            <?= $riwayatSelesai; ?>
                        </h3>
                    </div>

                    <div class="w-11 h-11 rounded-xl bg-green-500/10 text-green-400 flex items-center justify-center text-2xl">
                        <i class='bx bx-history'></i>
                    </div>

                </div>
            </div>

            <!-- Buku Hilang -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-slate-400 text-sm">Buku Hilang</p>

                        <h3 class="text-3xl font-bold text-white mt-1">
                            <?= $bukuHilang; ?>
                        </h3>
                    </div>

                    <div class="w-11 h-11 rounded-xl bg-rose-500/10 text-rose-400 flex items-center justify-center text-2xl">
                        <i class='bx bx-error-circle'></i>
                    </div>

                </div>
            </div>

        </div>

    </div>

    <!-- USER TERLAMBAT -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden max-w-4xl">

        <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between gap-3">

            <div>
                <h3 class="text-base font-bold text-white">
                    User Terlambat Mengembalikan
                </h3>

                <p class="text-slate-400 text-xs mt-1">
                    Maksimal 5 data peminjaman yang melewati deadline.
                </p>
            </div>

            <i class='bx bx-time-five text-2xl text-rose-400'></i>

        </div>

        <div class="overflow-x-auto">

            <table class="w-full text-sm text-left">

                <thead class="text-slate-400 border-b border-slate-800">
                    <tr>
                        <th class="px-5 py-3 font-medium">Nama</th>
                        <th class="px-5 py-3 font-medium">Buku</th>
                        <th class="px-5 py-3 font-medium">Deadline</th>
                        <th class="px-5 py-3 font-medium text-right">Telat</th>
                    </tr>
                </thead>

                <tbody class="text-slate-300">

                    <?php if (!$userTelat || mysqli_num_rows($userTelat) == 0): ?>

                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-slate-500">
                                Tidak ada user yang terlambat.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php while ($row = mysqli_fetch_assoc($userTelat)): ?>

                            <tr class="border-b border-slate-800/70 hover:bg-slate-800/40 transition">

                                <td class="px-5 py-4 text-white font-medium">
                                    <?= htmlspecialchars($row['nama']); ?>
                                </td>

                                <td class="px-5 py-4 max-w-[220px] truncate">
                                    <?= htmlspecialchars($row['judul']); ?>
                                </td>

                                <td class="px-5 py-4">
                                    <?= date('d M Y', strtotime($row['tanggal_kembali'])); ?>
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <span class="text-rose-400 font-semibold">
                                        <?= $row['telat_hari']; ?> hari
                                    </span>
                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<?php include '../layout/footer.php'; ?>