<?php
// admin/index.php — Dashboard Admin
require_once __DIR__ . '/../middleware/admin.php';

$pageTitle  = 'Dashboard';
$activePage = 'dashboard';

$pdo = getPDO();

// ── Statistik ──
$totalAdmin    = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
$totalPetugas  = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'petugas'")->fetchColumn();
$totalUser     = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$totalBukuAktif = (int) $pdo->query("SELECT COUNT(*) FROM buku WHERE status_approval = 'approved'")->fetchColumn();
$totalPending  = (int) $pdo->query("SELECT COUNT(*) FROM buku WHERE status_approval = 'pending'")->fetchColumn();

// ── Buku pending terbaru (5 baris) ──
$stmtPending = $pdo->query("
    SELECT b.judul, b.pengarang, b.created_at, u.nama AS nama_petugas
    FROM   buku b
    LEFT JOIN users u ON b.id_petugas_pengaju = u.id
    WHERE  b.status_approval = 'pending'
    ORDER  BY b.created_at DESC
    LIMIT  5
");
$bukuPending = $stmtPending->fetchAll();

require_once __DIR__ . '/layout/header.php';
?>

<!-- ── Stat Cards ── -->
<div class="row g-3 mb-4">

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#ede9fe">
                <i class="bi bi-shield-fill-check" style="color:#6d28d9"></i>
            </div>
            <div>
                <div class="stat-value"><?= $totalAdmin ?></div>
                <div class="stat-label">Total Admin</div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#dbeafe">
                <i class="bi bi-person-badge-fill" style="color:#1d4ed8"></i>
            </div>
            <div>
                <div class="stat-value"><?= $totalPetugas ?></div>
                <div class="stat-label">Total Petugas</div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7">
                <i class="bi bi-book-fill" style="color:#15803d"></i>
            </div>
            <div>
                <div class="stat-value"><?= $totalBukuAktif ?></div>
                <div class="stat-label">Buku Aktif (Approved)</div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef9c3">
                <i class="bi bi-hourglass-split" style="color:#b45309"></i>
            </div>
            <div>
                <div class="stat-value"><?= $totalPending ?></div>
                <div class="stat-label">Menunggu Persetujuan</div>
            </div>
        </div>
    </div>

</div>

<!-- ── Ringkasan User + Pengajuan ── -->
<div class="row g-3">

    <!-- Ringkasan Pengguna -->
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6><i class="bi bi-people me-2 text-primary"></i>Ringkasan Pengguna</h6>
                <a href="user.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="card-body">
                <?php
                $roleData = [
                    ['label' => 'Admin',   'count' => $totalAdmin,   'color' => '#6d28d9', 'icon' => 'bi-shield-fill-check'],
                    ['label' => 'Petugas', 'count' => $totalPetugas, 'color' => '#1d4ed8', 'icon' => 'bi-person-badge-fill'],
                    ['label' => 'User',    'count' => $totalUser,    'color' => '#15803d', 'icon' => 'bi-person-fill'],
                ];
                $totalAllUser = ($totalAdmin + $totalPetugas + $totalUser) ?: 1;
                foreach ($roleData as $r):
                    $pct = round(($r['count'] / $totalAllUser) * 100);
                ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-semibold d-flex align-items-center gap-2" style="font-size:.875rem">
                            <i class="bi <?= $r['icon'] ?>" style="color:<?= $r['color'] ?>"></i>
                            <?= $r['label'] ?>
                        </span>
                        <span class="fw-bold"><?= $r['count'] ?></span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar"
                             style="width:<?= $pct ?>%;background:<?= $r['color'] ?>">
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Pengajuan Buku Terbaru -->
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6><i class="bi bi-clock-history me-2 text-warning"></i>Pengajuan Buku Terbaru</h6>
                <a href="persetujuan.php" class="btn btn-sm btn-outline-warning">Kelola</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($bukuPending)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-check-circle display-6 text-success"></i>
                        <p class="mt-2 mb-0">Tidak ada pengajuan yang menunggu.</p>
                    </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Judul Buku</th>
                                <th>Pengaju</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($bukuPending as $buku): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= e($buku['judul']) ?></div>
                                    <small class="text-muted"><?= e($buku['pengarang']) ?></small>
                                </td>
                                <td><?= e($buku['nama_petugas'] ?? '-') ?></td>
                                <td>
                                    <small><?= date('d M Y', strtotime($buku['created_at'])) ?></small>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/layout/footer.php'; ?>
