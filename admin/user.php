<?php
// admin/user.php — Daftar Pengguna
require_once __DIR__ . '/../middleware/admin.php';

$pageTitle  = 'Manajemen Pengguna';
$activePage = 'users';

$pdo = getPDO();

// ── Filter & Search ──
$search     = trim($_GET['q']    ?? '');
$roleFilter = $_GET['role']      ?? '';
$page       = max(1, (int)($_GET['page'] ?? 1));
$perPage    = 10;
$offset     = ($page - 1) * $perPage;

$conditions = [];
$params     = [];

if ($search !== '') {
    $conditions[] = '(nama LIKE :q OR username LIKE :q)';
    $params[':q'] = "%$search%";
}
if (in_array($roleFilter, ['admin', 'petugas', 'user'])) {
    $conditions[] = 'role = :role';
    $params[':role'] = $roleFilter;
}

$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Total rows
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM users $where");
$countStmt->execute($params);
$totalRows  = (int) $countStmt->fetchColumn();
$totalPages = (int) ceil($totalRows / $perPage);

// Fetch data
$stmt = $pdo->prepare("
    SELECT id, nama, username, role, created_at
    FROM   users
    $where
    ORDER  BY id DESC
    LIMIT  :limit OFFSET :offset
");
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll();

require_once __DIR__ . '/layout/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <p class="text-muted mb-0" style="font-size:.875rem">
        Menampilkan <strong><?= count($users) ?></strong> dari
        <strong><?= $totalRows ?></strong> pengguna.
    </p>
    <a href="tambah_user.php" class="btn btn-primary">
        <i class="bi bi-person-plus-fill me-1"></i> Tambah Pengguna
    </a>
</div>

<!-- Filter Bar -->
<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-sm-6 col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" name="q" class="form-control"
                           placeholder="Cari nama atau username..."
                           value="<?= e($search) ?>">
                </div>
            </div>
            <div class="col-sm-4 col-md-3">
                <select name="role" class="form-select">
                    <option value="">Semua Role</option>
                    <option value="admin"   <?= $roleFilter === 'admin'   ? 'selected' : '' ?>>Admin</option>
                    <option value="petugas" <?= $roleFilter === 'petugas' ? 'selected' : '' ?>>Petugas</option>
                    <option value="user"    <?= $roleFilter === 'user'    ? 'selected' : '' ?>>User</option>
                </select>
            </div>
            <div class="col-auto d-flex gap-1">
                <button class="btn btn-primary" type="submit">
                    <i class="bi bi-funnel me-1"></i>Filter
                </button>
                <?php if ($search || $roleFilter): ?>
                    <a href="user.php" class="btn btn-outline-secondary">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Pengguna -->
<div class="card">
    <div class="card-header">
        <h6><i class="bi bi-people me-2"></i>Daftar Pengguna</h6>
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th width="50">#</th>
                    <th>Nama Lengkap</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Terdaftar</th>
                    <th width="150" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox display-6"></i>
                        <p class="mt-2">Tidak ada data pengguna ditemukan.</p>
                    </td>
                </tr>
            <?php else: ?>
            <?php foreach ($users as $i => $user): ?>
                <tr>
                    <td class="text-muted"><?= $offset + $i + 1 ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="admin-avatar" style="width:32px;height:32px;font-size:.75rem">
                                <?= strtoupper(substr($user['nama'], 0, 1)) ?>
                            </div>
                            <span class="fw-semibold"><?= e($user['nama']) ?></span>
                        </div>
                    </td>
                    <td><code><?= e($user['username']) ?></code></td>
                    <td>
                        <?php
                        $roleBadge = [
                            'admin'   => 'badge-admin',
                            'petugas' => 'badge-petugas',
                            'user'    => 'badge-user',
                        ];
                        ?>
                        <span class="badge-status <?= $roleBadge[$user['role']] ?? '' ?>">
                            <?= ucfirst($user['role']) ?>
                        </span>
                    </td>
                    <td>
                        <small><?= date('d M Y', strtotime($user['created_at'])) ?></small>
                    </td>
                    <td class="text-center">
                        <a href="edit_user.php?id=<?= $user['id'] ?>"
                           class="btn btn-sm btn-outline-primary me-1"
                           title="Edit">
                            <i class="bi bi-pencil-fill"></i>
                        </a>
                        <?php if ($user['id'] !== (int) $_SESSION['user_id']): ?>
                        <button class="btn btn-sm btn-outline-danger"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteModal"
                                data-id="<?= $user['id'] ?>"
                                data-name="<?= e($user['nama']) ?>"
                                title="Hapus">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                        <?php else: ?>
                        <button class="btn btn-sm btn-outline-secondary" disabled
                                title="Tidak bisa menghapus akun sendiri">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="card-body pt-2 pb-3 border-top d-flex justify-content-between align-items-center">
        <small class="text-muted">Halaman <?= $page ?> dari <?= $totalPages ?></small>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                    <a class="page-link"
                       href="?page=<?= $p ?>&q=<?= urlencode($search) ?>&role=<?= urlencode($roleFilter) ?>">
                        <?= $p ?>
                    </a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>
                    Konfirmasi Hapus
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin menghapus pengguna
                <strong id="deleteUserName"></strong>?
                Tindakan ini tidak dapat dibatalkan.
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Batal</button>
                <a href="#" id="deleteConfirmBtn" class="btn btn-danger">
                    <i class="bi bi-trash-fill me-1"></i> Ya, Hapus
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    /* Isi data modal hapus dari tombol yang diklik */
    const deleteModal = document.getElementById('deleteModal');
    deleteModal.addEventListener('show.bs.modal', function (e) {
        const btn  = e.relatedTarget;
        const id   = btn.getAttribute('data-id');
        const name = btn.getAttribute('data-name');
        document.getElementById('deleteUserName').textContent  = name;
        document.getElementById('deleteConfirmBtn').href       = 'hapus_user.php?id=' + id;
    });
</script>

<?php require_once __DIR__ . '/layout/footer.php'; ?>
