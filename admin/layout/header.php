<?php
ob_start();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Perpus Dashboard</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        ::-webkit-scrollbar {
            width: 7px;
        }

        ::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 20px;
        }
    </style>
</head>

<body class="bg-slate-950 text-white">

    <?php
    $current_page   = basename($_SERVER['PHP_SELF']);
    $current_folder = basename(dirname($_SERVER['PHP_SELF']));
    ?>

    <div class="flex min-h-screen">

        <!-- SIDEBAR -->
        <aside class="w-72 bg-slate-900 border-r border-slate-800 flex flex-col shadow-2xl sticky top-0 h-screen">
            <!-- LOGO -->
            <div class="h-20 flex items-center gap-3 px-6 border-b border-slate-800">

                <div class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center text-3xl">
                    <i class='bx bxs-shield-quarter'></i>
                </div>

                <div>
                    <h1 class="text-lg font-bold text-white">
                        Admin Panel
                    </h1>

                    <p class="text-xs text-slate-400">
                        E-Perpus System
                    </p>
                </div>

            </div>

            <!-- NAVIGATION -->
            <nav class="flex-1 overflow-y-auto p-4 space-y-6 scrollbar-thin scrollbar-thumb-slate-700">

                <!-- MENU UTAMA -->
                <div>

                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 mb-3">
                        Menu Utama
                    </p>

                    <div class="space-y-2">

                        <!-- DASHBOARD -->
                        <a href="/library/admin/dashboard/index.php"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                        <?= ($current_folder == 'dashboard')
                            ? 'bg-blue-500 text-white shadow-lg'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">

                            <i class='bx bxs-dashboard text-2xl'></i>

                            <span class="font-medium">
                                Dashboard
                            </span>

                        </a>

                    </div>

                </div>

                <!-- MANAJEMEN -->
                <div>

                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 mb-3">
                        Manajemen
                    </p>

                    <div class="space-y-2">

                        <!-- USER -->
                        <a href="/library/admin/akun/user.php"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                        <?= ($current_page == 'user.php')
                            ? 'bg-blue-500 text-white shadow-lg'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">

                            <i class='bx bxs-user-account text-2xl'></i>

                            <span class="font-medium">
                                Kelola Akun
                            </span>

                        </a>

                        <!-- KATEGORI -->
                        <a href="/library/admin/kategori/kategori.php"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                        <?= ($current_page == 'kategori.php')
                            ? 'bg-blue-500 text-white shadow-lg'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">

                            <i class='bx bxs-category text-2xl'></i>

                            <span class="font-medium">
                                Kategori
                            </span>

                        </a>

                        <!-- PENULIS -->
                        <a href="/library/admin/penulis/index.php"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                        <?= ($current_folder == 'penulis')
                            ? 'bg-blue-500 text-white shadow-lg'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">

                            <i class='bx bx-edit-alt text-2xl'></i>

                            <span class="font-medium">
                                Penulis
                            </span>

                        </a>

                        <!-- PENERBIT -->
                        <a href="/library/admin/penerbit/index.php"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                        <?= ($current_folder == 'penerbit')
                            ? 'bg-blue-500 text-white shadow-lg'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">

                            <i class='bx bx-buildings text-2xl'></i>

                            <span class="font-medium">
                                Penerbit
                            </span>

                        </a>

                        <!-- BUKU -->
                        <a href="/library/admin/buku/buku.php"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                        <?= ($current_page == 'buku.php')
                            ? 'bg-blue-500 text-white shadow-lg'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">

                            <i class='bx bxs-book-bookmark text-2xl'></i>

                            <span class="font-medium">
                                Buku
                            </span>

                        </a>

                        <!-- LAPORAN -->
                        <a href="/library/admin/laporan/index.php"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                        <?= ($current_folder == 'laporan')
                            ? 'bg-blue-500 text-white shadow-lg'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">

                            <i class='bx bxs-report text-2xl'></i>

                            <span class="font-medium">
                                Laporan
                            </span>

                        </a>

                        <a href="/library/admin/ulasan/index.php"
                            class="flex items-center gap-3 px-4 py-3 rounded-2xl transition
                            <?= ($current_folder == 'ulasan')
                                ? 'bg-blue-500 text-white shadow-lg'
                                : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">

                            <i class="fa-solid fa-comments text-lg"></i>
                            <span class="font-medium">Ulasan Saya</span>

                        </a>

                    </div>

                </div>

                <!-- TRANSAKSI -->
                <div>

                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 mb-3">
                        Transaksi
                    </p>

                    <div class="space-y-2">

                        <!-- PEMINJAMAN -->
                        <a href="/library/admin/peminjaman/peminjaman.php"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                        <?= ($current_folder == 'peminjaman')
                            ? 'bg-blue-500 text-white shadow-lg'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">

                            <i class='bx bxs-spreadsheet text-2xl'></i>

                            <span class="font-medium">
                                Peminjaman
                            </span>

                        </a>

                        <!-- RIWAYAT -->
                        <a href="/library/admin/riwayat/riwayat.php"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                        <?= ($current_folder == 'riwayat')
                            ? 'bg-blue-500 text-white shadow-lg'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">

                            <i class='bx bx-history text-2xl'></i>

                            <span class="font-medium">
                                Riwayat Pinjam
                            </span>

                        </a>

                        <!-- TOTAL & DETAIL DENDA -->
                        <a href="/library/admin/denda/total.php"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                        <?= ($current_folder == 'transaksi')
                            ? 'bg-blue-500 text-white shadow-lg'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">

                            <i class='bx bx-receipt text-2xl'></i>

                            <span class="font-medium">
                                Total & Detail Denda
                            </span>

                        </a>

                    </div>

                </div>

            </nav>

        </aside>

        <!-- MAIN -->
        <main class="flex-1 h-screen overflow-y-auto">

            <!-- TOPBAR -->
            <div class="h-20 border-b border-slate-800 bg-slate-900/70 backdrop-blur px-8 flex items-center justify-between">

                <!-- TITLE -->
                <div>

                    <h2 class="text-2xl font-bold text-white">
                        Dashboard Admin
                    </h2>

                    <p class="text-sm text-slate-400">
                        Sistem Informasi Perpustakaan
                    </p>

                </div>

                <!-- PROFILE -->
                <div class="relative">

                    <button id="profileButton"
                        class="flex items-center gap-3 bg-slate-800 hover:bg-slate-700 transition px-4 py-2 rounded-xl">

                        <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center font-bold text-white">
                            <?= strtoupper(substr($_SESSION['nama'], 0, 1)); ?>
                        </div>

                        <div class="text-left">

                            <h4 class="text-sm font-semibold text-white">
                                <?= htmlspecialchars($_SESSION['nama']); ?>
                            </h4>

                            <p class="text-xs text-slate-400">
                                Administrator
                            </p>

                        </div>

                        <i class='bx bx-chevron-down text-xl text-slate-400'></i>

                    </button>

                    <!-- DROPDOWN -->
                    <div id="profileDropdown"
                        class="hidden absolute right-0 mt-3 w-56 bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl overflow-hidden z-50">

                        <!-- HEADER -->
                        <div class="px-4 py-4 border-b border-slate-800">

                            <h4 class="text-sm font-semibold text-white">
                                <?= htmlspecialchars($_SESSION['nama']); ?>
                            </h4>

                            <p class="text-xs text-slate-400">
                                Administrator
                            </p>

                        </div>

                        <!-- MENU -->
                        <div class="p-2">

                            <!-- PROFIL -->
                            <a href="/library/admin/layout/profil/index.php"
                                class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-800 hover:text-white transition">

                                <i class='bx bx-user text-xl'></i>

                                Profil

                            </a>

                            <!-- SETTING DENDA -->
                            <a href="/library/admin/denda/index.php"
                                class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-800 hover:text-white transition">

                                <i class='bx bx-money text-xl'></i>

                                Setting Denda

                            </a>

                            <!-- LOGOUT -->
                            <a href="/library/auth/logout.php"
                                onclick="confirmLogout(event, this.href)"
                                class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-400 hover:bg-red-500/10 transition">

                                <i class='bx bx-log-out text-xl'></i>

                                Keluar

                            </a>

                        </div>

                    </div>

                </div>

            </div>

            <!-- CONTENT -->
            <div class="p-8">