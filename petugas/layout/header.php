<?php
ob_start();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Petugas Panel - E-Perpus</title>

    <!-- GOOGLE FONT -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- FONT AWESOME -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <!-- TAILWIND -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- SWEETALERT -->
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
            <div class="h-20 flex items-center gap-4 px-6 border-b border-slate-800">

                <div class="w-12 h-12 rounded-2xl bg-blue-500/10 text-blue-400 flex items-center justify-center text-2xl">

                    <i class="fa-solid fa-user-shield"></i>

                </div>

                <div>

                    <h1 class="text-lg font-bold text-white">
                        Petugas Panel
                    </h1>

                    <p class="text-xs text-slate-400">
                        E-Perpus System
                    </p>

                </div>

            </div>

            <!-- MENU -->
            <nav class="flex-1 overflow-y-auto p-4 space-y-6">

                <!-- MENU UTAMA -->
                <div>

                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 mb-3">
                        Menu Utama
                    </p>

                    <div class="space-y-2">

                        <!-- DASHBOARD -->
                        <a href="/library/petugas/dashboard/index.php"
                            class="flex items-center gap-3 px-4 py-3 rounded-2xl transition
                            <?= ($current_folder == 'dashboard')
                                ? 'bg-blue-500 text-white shadow-lg'
                                : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">

                            <i class="fa-solid fa-chart-line text-lg"></i>

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

                        <!-- BUKU -->
                        <a href="/library/petugas/buku/index.php"
                            class="flex items-center gap-3 px-4 py-3 rounded-2xl transition
                            <?= ($current_folder == 'buku')
                                ? 'bg-blue-500 text-white shadow-lg'
                                : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">

                            <i class="fa-solid fa-book text-lg"></i>

                            <span class="font-medium">
                                Buku
                            </span>

                        </a>

                         <a href="/library/petugas/ulasan/index.php"
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

                        <!-- RIWAYAT -->
                        <a href="/library/petugas/riwayat/index.php"
                            class="flex items-center gap-3 px-4 py-3 rounded-2xl transition
                            <?= ($current_folder == 'riwayat')
                                ? 'bg-blue-500 text-white shadow-lg'
                                : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">

                            <i class="fa-solid fa-clock-rotate-left text-lg"></i>

                            <span class="font-medium">
                                Riwayat Pinjam
                            </span>

                        </a>

                        <!-- DENDA -->
                        <a href="/library/petugas/denda/index.php"
                            class="flex items-center gap-3 px-4 py-3 rounded-2xl transition
                            <?= ($current_folder == 'denda')
                                ? 'bg-blue-500 text-white shadow-lg'
                                : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">

                            <i class="fa-solid fa-money-bill-wave text-lg"></i>

                            <span class="font-medium">
                                Total Denda
                            </span>

                        </a>

                    </div>

                </div>

                <!-- LAPORAN -->
                <div>

                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 mb-3">
                        Laporan
                    </p>

                    <div class="space-y-2">

                        <!-- PRINT RIWAYAT -->
                        <a href="/library/petugas/laporan/index.php"
                            class="flex items-center gap-3 px-4 py-3 rounded-2xl transition
                            <?= ($current_page == 'riwayat.php' && $current_folder == 'print')
                                ? 'bg-blue-500 text-white shadow-lg'
                                : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">

                            <i class="fa-solid fa-file-lines text-lg"></i>

                            <span class="font-medium">
                                Laporan
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
                        Dashboard Petugas
                    </h2>

                    <p class="text-sm text-slate-400">
                        Sistem Informasi Perpustakaan
                    </p>

                </div>

                <!-- PROFILE -->
                <div class="relative">

                    <button id="profileButton"
                        class="flex items-center gap-3 bg-slate-800 hover:bg-slate-700 transition px-4 py-2 rounded-2xl">

                        <!-- AVATAR -->
                        <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center font-bold text-white">

                            <?= strtoupper(substr($_SESSION['nama'], 0, 1)); ?>

                        </div>

                        <!-- INFO -->
                        <div class="text-left">

                            <h4 class="text-sm font-semibold text-white">
                                <?= htmlspecialchars($_SESSION['nama']); ?>
                            </h4>

                            <p class="text-xs text-slate-400">
                                Petugas
                            </p>

                        </div>

                        <i class="fa-solid fa-chevron-down text-sm text-slate-400"></i>

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
                                Petugas Perpustakaan
                            </p>

                        </div>

                        <!-- MENU -->
                        <div class="p-2">

                            <!-- PROFILE -->
                            <a href="/library/petugas/layout/profile/index.php"
                                class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-800 hover:text-white transition">

                                <i class="fa-solid fa-user text-sm"></i>

                                Profile

                            </a>

                            <!-- LOGOUT -->
                            <a href="/library/auth/logout.php"
                                onclick="confirmLogout(event, this.href)"
                                class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-400 hover:bg-red-500/10 transition">

                                <i class="fa-solid fa-right-from-bracket text-sm"></i>

                                Logout

                            </a>

                        </div>

                    </div>

                </div>

            </div>

            <!-- CONTENT -->
            <div class="p-8">