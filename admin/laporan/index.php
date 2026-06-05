<?php
session_start();

include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../middleware/admin.php";
include __DIR__ . "/../../admin/layout/header.php";

// ==========================
// STAT CARD
// ==========================
$total_buku = mysqli_num_rows(mysqli_query($conn, "SELECT id_buku FROM buku"));
$total_user = mysqli_num_rows(mysqli_query($conn, "SELECT id_user FROM user"));
$total_pinjam = mysqli_num_rows(mysqli_query($conn, "SELECT id_pinjam FROM peminjaman"));

// ==========================
// LIST KATEGORI
// ==========================
$listKategori = mysqli_query($conn, "
    SELECT *
    FROM kategori
    ORDER BY nama_kategori ASC
");

// ==========================
// LIST SEMUA USER UNTUK FILTER PEMINJAMAN
// ==========================
$listPeminjam = mysqli_query($conn, "
    SELECT 
        id_user,
        nama
    FROM user
    ORDER BY nama ASC
");
?>

<script src="https://cdn.tailwindcss.com"></script>

<!-- TOM SELECT UNTUK SEARCH USER -->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

<style>
    select option {
        background: #1e293b !important;
        color: white !important;
    }

    .ts-control {
        background: #1e293b !important;
        border: 1px solid #334155 !important;
        border-radius: 0.75rem !important;
        padding: 0.75rem 1rem !important;
        color: white !important;
        box-shadow: none !important;
        min-height: 48px !important;
    }

    .ts-control input {
        color: white !important;
    }

    .ts-dropdown {
        background: #1e293b !important;
        border: 1px solid #334155 !important;
        color: white !important;
        border-radius: 0.75rem !important;
        overflow: hidden !important;
        z-index: 9999 !important;
    }

    .ts-dropdown .option {
        color: white !important;
        padding: 10px 14px !important;
    }

    .ts-dropdown .active {
        background: #eab308 !important;
        color: white !important;
    }

    .ts-wrapper.single .ts-control:after {
        border-color: white transparent transparent transparent !important;
    }
</style>

<div class="min-h-screen">

    <!-- HEADER -->
    <div class="mb-10">

        <h1 class="text-4xl font-bold text-white">
            Laporan Sistem
        </h1>

        <p class="text-slate-400 mt-2">
            Filter dan cetak laporan data perpustakaan
        </p>

    </div>

    <!-- STAT CARDS -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">

            <p class="text-slate-400">
                Total Buku
            </p>

            <h2 class="text-3xl font-bold text-white mt-2">
                <?= number_format($total_buku, 0, ',', '.'); ?>
            </h2>

        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">

            <p class="text-slate-400">
                Total User
            </p>

            <h2 class="text-3xl font-bold text-white mt-2">
                <?= number_format($total_user, 0, ',', '.'); ?>
            </h2>

        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">

            <p class="text-slate-400">
                Peminjaman
            </p>

            <h2 class="text-3xl font-bold text-white mt-2">
                <?= number_format($total_pinjam, 0, ',', '.'); ?>
            </h2>

        </div>

    </div>

    <!-- FORM LAPORAN -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- LAPORAN BUKU -->
        <form action="print_buku.php"
            method="GET"
            target="_blank"
            class="bg-slate-900 border border-slate-800 rounded-2xl p-6 hover:border-blue-500 transition">

            <div class="w-16 h-16 rounded-2xl bg-blue-500/10 text-blue-400 flex items-center justify-center text-4xl mb-5">
                <i class='bx bxs-book-bookmark'></i>
            </div>

            <h2 class="text-xl font-bold text-white mb-2">
                Laporan Buku
            </h2>

            <p class="text-slate-400 text-sm mb-5">
                Cetak data buku berdasarkan kategori, rating, dan stok.
            </p>

            <label class="text-slate-400 text-sm">
                Kategori
            </label>

            <select name="id_kategori"
                class="w-full mt-2 mb-4 bg-slate-800 text-white rounded-xl px-4 py-3 border border-slate-700 outline-none">

                <option value="">
                    Semua Kategori
                </option>

                <?php while ($kategori = mysqli_fetch_assoc($listKategori)): ?>

                    <option value="<?= $kategori['id_kategori']; ?>">
                        <?= htmlspecialchars($kategori['nama_kategori']); ?>
                    </option>

                <?php endwhile; ?>

            </select>

            <label class="text-slate-400 text-sm">
                Rating Minimal
            </label>

            <select name="rating"
                class="w-full mt-2 mb-4 bg-slate-800 text-white rounded-xl px-4 py-3 border border-slate-700 outline-none">

                <option value="">
                    Semua Rating
                </option>

                <option value="5">
                    5 Bintang
                </option>

                <option value="4">
                    4 Bintang ke atas
                </option>

                <option value="3">
                    3 Bintang ke atas
                </option>

                <option value="2">
                    2 Bintang ke atas
                </option>

                <option value="1">
                    1 Bintang ke atas
                </option>

            </select>

            <label class="text-slate-400 text-sm">
                Stok
            </label>

            <select name="stok"
                class="w-full mt-2 mb-5 bg-slate-800 text-white rounded-xl px-4 py-3 border border-slate-700 outline-none">

                <option value="">
                    Semua Stok
                </option>

                <option value="tersedia">
                    Stok Tersedia
                </option>

                <option value="habis">
                    Stok Habis
                </option>

            </select>

            <button type="submit"
                class="w-full bg-blue-500 hover:bg-blue-600 transition text-white py-3 rounded-xl font-semibold">

                Print Laporan Buku

            </button>

        </form>

        <!-- LAPORAN AKUN -->
        <form action="print_user.php"
            method="GET"
            target="_blank"
            class="bg-slate-900 border border-slate-800 rounded-2xl p-6 hover:border-emerald-500 transition">

            <div class="w-16 h-16 rounded-2xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-4xl mb-5">
                <i class='bx bxs-user-account'></i>
            </div>

            <h2 class="text-xl font-bold text-white mb-2">
                Laporan Akun
            </h2>

            <p class="text-slate-400 text-sm mb-5">
                Cetak data akun berdasarkan role.
            </p>

            <label class="text-slate-400 text-sm">
                Role
            </label>

            <select name="role"
                class="w-full mt-2 mb-5 bg-slate-800 text-white rounded-xl px-4 py-3 border border-slate-700 outline-none">

                <option value="">
                    Semua Role
                </option>

                <option value="admin">
                    Admin
                </option>

                <option value="petugas">
                    Petugas
                </option>

                <option value="anggota">
                    Anggota
                </option>

                <option value="user">
                    User
                </option>

            </select>

            <button type="submit"
                class="w-full bg-emerald-500 hover:bg-emerald-600 transition text-white py-3 rounded-xl font-semibold">

                Print Laporan Akun

            </button>

        </form>

        <!-- LAPORAN PEMINJAMAN -->
        <form action="print_peminjaman.php"
            method="GET"
            target="_blank"
            class="bg-slate-900 border border-slate-800 rounded-2xl p-6 hover:border-yellow-500 transition">

            <div class="w-16 h-16 rounded-2xl bg-yellow-500/10 text-yellow-400 flex items-center justify-center text-4xl mb-5">
                <i class='bx bx-history'></i>
            </div>

            <h2 class="text-xl font-bold text-white mb-2">
                Laporan Peminjaman
            </h2>

            <p class="text-slate-400 text-sm mb-5">
                Cetak riwayat peminjaman berdasarkan tanggal dan user.
            </p>

            <label class="text-slate-400 text-sm">
                Dari Tanggal Dikembalikan
            </label>

            <input type="date"
                name="tanggal_awal"
                class="w-full mt-2 mb-4 bg-slate-800 text-white rounded-xl px-4 py-3 border border-slate-700 outline-none">

            <label class="text-slate-400 text-sm">
                Sampai Tanggal Dikembalikan
            </label>

            <input type="date"
                name="tanggal_akhir"
                class="w-full mt-2 mb-4 bg-slate-800 text-white rounded-xl px-4 py-3 border border-slate-700 outline-none">

            <label class="text-slate-400 text-sm">
                User Peminjam
            </label>

            <select id="search-user"
                name="id_user"
                class="w-full mt-2 mb-5">

                <option value="">
                    Semua User
                </option>

                <?php if ($listPeminjam && mysqli_num_rows($listPeminjam) > 0): ?>

                    <?php while ($peminjam = mysqli_fetch_assoc($listPeminjam)): ?>

                        <option value="<?= $peminjam['id_user']; ?>">
                            <?= htmlspecialchars($peminjam['nama']); ?>
                        </option>

                    <?php endwhile; ?>

                <?php endif; ?>

            </select>

            <button type="submit"
                class="w-full bg-yellow-500 hover:bg-yellow-600 transition text-white py-3 rounded-xl font-semibold">

                Print Laporan Peminjaman

            </button>

        </form>

    </div>

    <div class="mt-12 bg-slate-900 border border-slate-800 rounded-2xl p-6 text-slate-400 text-sm">

        💡 Pilih filter dulu, lalu klik tombol print. Halaman print akan terbuka di tab baru.

    </div>

</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        new TomSelect("#search-user", {
            create: false,
            allowEmptyOption: true,
            placeholder: "Ketik nama user...",
            sortField: {
                field: "text",
                direction: "asc"
            }
        });
    });
</script>

<?php include __DIR__ . "/../../admin/layout/footer.php"; ?>