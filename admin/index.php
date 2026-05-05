<?php
session_start();
include '../config/koneksi.php';
include '../middleware/admin.php';
include '../admin/layout/header.php';

// Cek login admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Hitung jumlah user berdasarkan role
$totalAdmin = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM user WHERE role='admin'"))[0];
$totalPetugas = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM user WHERE role='petugas'"))[0];
$totalUser = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM user WHERE role='user'"))[0];

?>

<div class="welcome-banner">
    <h1>Selamat Datang, <?= htmlspecialchars($_SESSION['nama'] ?? 'Admin') ?> 👋</h1>
    <p>Kelola sistem perpustakaan dengan mudah dan cepat.</p>
</div>

<h3 style="margin-bottom: 20px;">Statistik Sistem</h3>
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-blue"><i class='bx bxs-user-badge'></i></div>
        <div class="stat-info">
            <span>Total Admin</span>
            <h3><?= $totalAdmin ?></h3>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-light-blue"><i class='bx bxs-briefcase'></i></div>
        <div class="stat-info">
            <span>Total Petugas</span>
            <h3><?= $totalPetugas ?></h3>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-green"><i class='bx bxs-user-rectangle'></i></div>
        <div class="stat-info">
            <span>Total User/Siswa</span>
            <h3><?= $totalUser ?></h3>
        </div>
    </div>
</div>

<?php include '../admin/layout/footer.php'; ?>