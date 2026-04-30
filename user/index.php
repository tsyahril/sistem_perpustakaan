<?php include '../middleware/user.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Perpustakaan</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/user.css">
</head>
<body>

    <div class="wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>E-Perpus</h3>
            </div>
            <ul class="sidebar-menu">
                <li><a href="index.php" class="active"> Dashboard</a></li>
                <li><a href="buku.php"> Cari Buku</a></li>
                <li><a href="riwayat.php"> Riwayat Pinjam</a></li>
                <li class="logout-item"><a href="../auth/logout.php"> Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <header class="top-header">
                <h2>Dashboard</h2>
                <div class="user-info">User Aktif</div>
            </header>

            <div class="content-body">
                <div class="welcome-banner">
                    <h1>Halo, Selamat Datang </h1>
                    <p>Sistem Informasi Perpustakaan Digital</p>
                </div>

                <div class="stats-grid">
                    <div class="card">
                        <span>Total Buku</span>
                        <h3>120</h3>
                    </div>
                    <div class="card">
                        <span>Sedang Dipinjam</span>
                        <h3>2</h3>
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>
</html>