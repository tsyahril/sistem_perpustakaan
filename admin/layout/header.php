<?php
// admin/layout/header.php
// ============================================================
// Layout: Sidebar + Topbar untuk semua halaman Admin
// Variabel yang harus di-set sebelum include:
//   $pageTitle  (string) — Judul halaman aktif
//   $activePage (string) — ID menu aktif: 'dashboard' | 'users' | 'approval'
// ============================================================

$adminName = e($_SESSION['nama'] ?? 'Admin');
$flash     = getFlash();

// Hitung pending buku untuk badge sidebar
$pdo         = getPDO();
$pendingCount = (int) $pdo
    ->query("SELECT COUNT(*) FROM buku WHERE status_approval = 'pending'")
    ->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Dashboard') ?> &mdash; <?= APP_NAME ?> Admin</title>

    <!-- Bootstrap 5 -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Admin CSS (file terpisah) -->
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<!-- Sidebar Overlay (mobile) -->
<div id="sidebar-overlay" onclick="toggleSidebar()"></div>

<!-- ── SIDEBAR ── -->
<nav id="sidebar">
    <div class="sidebar-brand">
        <div class="logo-icon">
            <i class="bi bi-book-half"></i>
        </div>
        <div>
            <span class="brand-name"><?= APP_NAME ?></span>
            <span class="brand-sub">Panel Administrator</span>
        </div>
    </div>

    <div class="sidebar-nav">
        <p class="nav-section-title">Menu Utama</p>
        <ul class="nav flex-column gap-1 ps-0 list-unstyled">
            <li class="nav-item">
                <a href="index.php"
                   class="<?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>">
                    <i class="bi bi-grid-1x2-fill"></i> Dashboard
                </a>
            </li>
        </ul>

        <p class="nav-section-title mt-2">Manajemen</p>
        <ul class="nav flex-column gap-1 ps-0 list-unstyled">
            <li class="nav-item">
                <a href="user.php"
                   class="<?= ($activePage ?? '') === 'users' ? 'active' : '' ?>">
                    <i class="bi bi-people-fill"></i> Pengguna
                </a>
            </li>
            <li class="nav-item">
                <a href="persetujuan.php"
                   class="<?= ($activePage ?? '') === 'approval' ? 'active' : '' ?>">
                    <i class="bi bi-patch-check-fill"></i> Persetujuan Buku
                    <?php if ($pendingCount > 0): ?>
                        <span class="nav-badge"><?= $pendingCount ?></span>
                    <?php endif; ?>
                </a>
            </li>
        </ul>
    </div>

    <div class="sidebar-footer">
        <a href="../logout.php">
            <i class="bi bi-box-arrow-left"></i> Keluar
        </a>
    </div>
</nav>

<!-- ── MAIN CONTENT ── -->
<div id="main-content">

    <!-- Topbar -->
    <header id="topbar">
        <!-- Hamburger — mobile only -->
        <button class="btn btn-sm d-lg-none border-0" onclick="toggleSidebar()">
            <i class="bi bi-list fs-5"></i>
        </button>

        <h1 class="topbar-title"><?= e($pageTitle ?? 'Dashboard') ?></h1>

        <div class="topbar-right">
            <div class="admin-avatar"><?= strtoupper(substr($adminName, 0, 1)) ?></div>
            <div class="admin-info d-none d-sm-block">
                <div class="admin-name"><?= $adminName ?></div>
                <div class="admin-role">Administrator</div>
            </div>
        </div>
    </header>

    <!-- Flash message -->
    <?php if ($flash): ?>
    <div class="flash-container">
        <?php
        $alertClass = match ($flash['type']) {
            'success' => 'alert-success',
            'danger'  => 'alert-danger',
            default   => 'alert-warning',
        };
        $iconClass = match ($flash['type']) {
            'success' => 'bi-check-circle-fill',
            'danger'  => 'bi-x-circle-fill',
            default   => 'bi-exclamation-triangle-fill',
        };
        ?>
        <div class="alert <?= $alertClass ?> alert-dismissible d-flex align-items-center gap-2"
             role="alert">
            <i class="bi <?= $iconClass ?> fs-5 flex-shrink-0"></i>
            <span><?= $flash['message'] ?></span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"
                    aria-label="Tutup"></button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Page content -->
    <div class="page-content">
