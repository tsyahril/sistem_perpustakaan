<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - E-Perpus</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <div class="app-container">
        <aside class="sidebar">
            <div class="sidebar-logo">
                <i class='bx bxs-shield-quarter'></i>
                <span>Admin Panel</span>
            </div>
            <nav class="sidebar-nav">
                <a href="index.php" class="nav-item active"><i class='bx bxs-dashboard'></i> <span>Dashboard</span></a>
                <a href="../admin/user.php" class="nav-item"><i class='bx bxs-user-account'></i> <span>Kelola Akun</span></a>
                <a href="admin.peminjaman.php" class="nav-item"><i class='bx bxs-book-bookmark'></i> <span>Peminjaman</span></a>
                <a href="../auth/logout.php" class="nav-item logout"><i class='bx bx-log-out'></i> <span>Keluar</span></a>
            </nav>
        </aside>

        <main class="main-content">
            <nav class="navbar">
                <div class="nav-title">Sistem Informasi Perpustakaan</div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=Admin&background=1e3a8a&color=fff" alt="Profile">
                </div>
            </nav>
            <div class="content-wrapper">