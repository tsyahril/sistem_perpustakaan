<?php
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anggota Panel - E-Perpus</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <script src="https://cdn.tailwindcss.com"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        html,
        body {
            overflow-x: hidden;
        }

        ::-webkit-scrollbar {
            width: 0;
            height: 0;
            display: none;
        }

        * {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
    </style>
</head>

<body class="bg-slate-950 text-white">

    <?php
    $current_page   = basename($_SERVER['PHP_SELF']);
    $current_folder = basename(dirname($_SERVER['PHP_SELF']));
    $nama_session   = $_SESSION['nama'] ?? 'Anggota';
    ?>

    <div class="flex min-h-screen">

        <!-- SIDEBAR -->
        <aside class="w-72 bg-slate-900 border-r border-slate-800 flex flex-col shadow-2xl sticky top-0 h-screen">

            <!-- LOGO -->
            <div class="h-20 flex items-center gap-4 px-6 border-b border-slate-800">

                <div class="w-12 h-12 rounded-2xl bg-blue-500/10 text-blue-400 flex items-center justify-center text-2xl">
                    <i class="fa-solid fa-user"></i>
                </div>

                <div>
                    <h1 class="text-lg font-bold text-white">
                        Anggota Panel
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

                        <a href="/library/anggota/dashboard/index.php"
                            class="flex items-center gap-3 px-4 py-3 rounded-2xl transition
                        <?= ($current_folder == 'dashboard')
                            ? 'bg-blue-500 text-white shadow-lg'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">

                            <i class="fa-solid fa-house text-lg"></i>
                            <span class="font-medium">Dashboard</span>

                        </a>

                    </div>
                </div>

                <!-- PEMINJAMAN -->
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 mb-3">
                        Peminjaman
                    </p>

                    <div class="space-y-2">

                        <a href="/library/anggota/peminjaman/index.php"
                            class="flex items-center gap-3 px-4 py-3 rounded-2xl transition
                        <?= ($current_folder == 'peminjaman')
                            ? 'bg-blue-500 text-white shadow-lg'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">

                            <i class="fa-solid fa-book-open-reader text-lg"></i>
                            <span class="font-medium">Peminjaman Saya</span>

                        </a>

                        <a href="/library/anggota/riwayat/index.php"
                            class="flex items-center gap-3 px-4 py-3 rounded-2xl transition
                        <?= ($current_folder == 'riwayat')
                            ? 'bg-blue-500 text-white shadow-lg'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">

                            <i class="fa-solid fa-clock-rotate-left text-lg"></i>
                            <span class="font-medium">Riwayat Saya</span>

                        </a>

                    </div>
                </div>

                <!-- AKTIVITAS -->
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 mb-3">
                        Aktivitas
                    </p>

                    <div class="space-y-2">

                        <a href="/library/anggota/denda/index.php"
                            class="flex items-center gap-3 px-4 py-3 rounded-2xl transition
                        <?= ($current_folder == 'denda')
                            ? 'bg-blue-500 text-white shadow-lg'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">

                            <i class="fa-solid fa-money-bill-wave text-lg"></i>
                            <span class="font-medium">Denda Saya</span>

                        </a>

                    </div>
                </div>

                <a href="/library/anggota/ulasan/index.php"
                    class="flex items-center gap-3 px-4 py-3 rounded-2xl transition
                    <?= ($current_folder == 'ulasan')
                        ? 'bg-blue-500 text-white shadow-lg'
                        : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">

                    <i class="fa-solid fa-comments text-lg"></i>
                    <span class="font-medium">Ulasan Saya</span>

                </a>

            </nav>

        </aside>

        <!-- MAIN -->
        <main class="flex-1 h-screen overflow-y-auto">

            <!-- TOPBAR -->
            <div class="h-20 border-b border-slate-800 bg-slate-900/70 backdrop-blur px-8 flex items-center justify-between">

                <div>
                    <h2 class="text-2xl font-bold text-white">
                        Dashboard Anggota
                    </h2>

                    <p class="text-sm text-slate-400">
                        Sistem Informasi Perpustakaan
                    </p>
                </div>

                <!-- PROFILE -->
                <div class="relative">

                    <button id="profileButton"
                        class="flex items-center gap-3 bg-slate-800 hover:bg-slate-700 transition px-4 py-2 rounded-2xl">

                        <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center font-bold text-white">
                            <?= strtoupper(substr($nama_session, 0, 1)); ?>
                        </div>

                        <div class="text-left">
                            <h4 class="text-sm font-semibold text-white">
                                <?= htmlspecialchars($nama_session); ?>
                            </h4>

                            <p class="text-xs text-slate-400">
                                Anggota
                            </p>
                        </div>

                        <i class="fa-solid fa-chevron-down text-sm text-slate-400"></i>

                    </button>

                    <!-- DROPDOWN -->
                    <div id="profileDropdown"
                        class="hidden absolute right-0 mt-3 w-56 bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl overflow-hidden z-50">

                        <div class="px-4 py-4 border-b border-slate-800">
                            <h4 class="text-sm font-semibold text-white">
                                <?= htmlspecialchars($nama_session); ?>
                            </h4>

                            <p class="text-xs text-slate-400">
                                Anggota Perpustakaan
                            </p>
                        </div>

                        <div class="p-2">

                            <a href="/library/anggota/layout/profile/index.php"
                                class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-800 hover:text-white transition">

                                <i class="fa-solid fa-user text-sm"></i>
                                Profile

                            </a>

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

                <script>
                    const profileButton = document.getElementById('profileButton');
                    const profileDropdown = document.getElementById('profileDropdown');

                    if (profileButton) {
                        profileButton.addEventListener('click', function() {
                            profileDropdown.classList.toggle('hidden');
                        });
                    }

                    function confirmLogout(event, url) {
                        event.preventDefault();

                        Swal.fire({
                            title: 'Logout?',
                            text: 'Kamu akan keluar dari akun.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#ef4444',
                            cancelButtonColor: '#64748b',
                            confirmButtonText: 'Ya, Logout',
                            cancelButtonText: 'Batal',
                            background: '#0f172a',
                            color: '#fff'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = url;
                            }
                        });
                    }
                </script>