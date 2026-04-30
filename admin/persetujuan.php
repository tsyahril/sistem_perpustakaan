<?php
// admin/persetujuan.php — Halaman Persetujuan Buku
require_once __DIR__ . '/../middleware/admin.php';

$pageTitle  = 'Persetujuan Buku';
$activePage = 'approval';

$pdo = getPDO();

// ── Tab status ──
$statusFilter = $_GET['status'] ?? 'pending';
if (!in_array($statusFilter, ['pending', 'approved', 'rejected'])) {
    $statusFilter = 'pending';
}

$page    = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$offset  = ($page - 1) * $perPage;

// Hitung jumlah per status untuk badge tab
$counts = $pdo->query(
    "SELECT status_approval, COUNT(*) AS jumlah
     FROM   buku
     GROUP  BY status_approval"
)->fetchAll(PDO::FETCH_KEY_PAIR);

$countPending  = (int) ($counts['pending']  ?? 0);
$countApproved = (int) ($counts['approved'] ?? 0);
$countRejected = (int) ($counts['rejected'] ?? 0);

// Total baris untuk pagination
$totalRows  = (int) $pdo->query(
    "SELECT COUNT(*) FROM buku WHERE status_approval = '$statusFilter'"
)->fetchColumn();
$totalPages = (int) ceil($totalRows / $perPage);

// Fetch buku
$stmt = $pdo->prepare("
    SELECT b.id, b.judul, b.pengarang, b.penerbit, b.tahun_terbit, b.stok,
           b.status_approval, b.catatan_penolakan, b.created_at,
           u.nama AS nama_petugas
    FROM   buku b
    LEFT JOIN users u ON b.id_petugas_pengaju = u.id
    WHERE  b.status_approval = :status
    ORDER  BY b.created_at DESC
    LIMIT  :limit OFFSET :offset
");
$stmt->bindValue(':status', $statusFilter);
$stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
$stmt->execute();
$bukuList = $stmt->fetchAll();

require_once __DIR__ . '/layout/header.php';
?>

<!-- ── Tab Navigasi ── -->
<div class="approval-tabs">
    <?php
    $tabs = [
        'pending'  => ['label' => 'Menunggu',  'icon' => 'bi-hourglass-split',  'color' => 'warning', 'count' => $countPending],
        'approved' => ['label' => 'Disetujui', 'icon' => 'bi-check-circle-fill','color' => 'success', 'count' => $countApproved],
        'rejected' => ['label' => 'Ditolak',   'icon' => 'bi-x-circle-fill',    'color' => 'danger',  'count' => $countRejected],
    ];
    foreach ($tabs as $key => $tab): ?>
    <a href="?status=<?= $key ?>"
       class="approval-tab <?= $statusFilter === $key ? 'active' : '' ?>">
        <i class="bi <?= $tab['icon'] ?> text-<?= $tab['color'] ?>"></i>
        <?= $tab['label'] ?>
        <span class="badge bg-<?= $tab['color'] ?> rounded-pill"
              style="font-size:.7rem">
            <?= $tab['count'] ?>
        </span>
    </a>
    <?php endforeach; ?>
</div>

<!-- ── Tabel Buku ── -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6>
            <i class="bi bi-book me-2"></i>
            Daftar Buku —
            <span class="text-capitalize"><?= $statusFilter ?></span>
        </h6>
        <small class="text-muted"><?= $totalRows ?> buku</small>
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th width="40">#</th>
                    <th>Judul Buku</th>
                    <th>Pengarang</th>
                    <th>Penerbit</th>
                    <th>Pengaju</th>
                    <th>Tanggal Pengajuan</th>
                    <?php if ($statusFilter === 'pending'): ?>
                        <th class="text-center" width="180">Aksi</th>
                    <?php else: ?>
                        <th width="110">Status</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($bukuList)): ?>
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox display-6"></i>
                        <p class="mt-2">Tidak ada buku dengan status ini.</p>
                    </td>
                </tr>
            <?php else: ?>
            <?php foreach ($bukuList as $i => $buku): ?>
                <tr>
                    <td class="text-muted"><?= $offset + $i + 1 ?></td>
                    <td>
                        <div class="fw-semibold"><?= e($buku['judul']) ?></div>
                        <small class="text-muted">
                            Stok: <?= $buku['stok'] ?> &middot; <?= $buku['tahun_terbit'] ?>
                        </small>
                    </td>
                    <td><?= e($buku['pengarang']) ?></td>
                    <td><?= e($buku['penerbit']) ?></td>
                    <td>
                        <span class="badge-status badge-petugas">
                            <?= e($buku['nama_petugas'] ?? 'N/A') ?>
                        </span>
                    </td>
                    <td>
                        <small>
                            <?= date('d M Y, H:i', strtotime($buku['created_at'])) ?>
                        </small>
                    </td>
                    <?php if ($statusFilter === 'pending'): ?>
                    <td class="text-center">
                        <!-- Tombol Setujui -->
                        <button class="btn btn-sm btn-success me-1"
                                data-bs-toggle="modal"
                                data-bs-target="#approveModal"
                                data-id="<?= $buku['id'] ?>"
                                data-judul="<?= e($buku['judul']) ?>">
                            <i class="bi bi-check-lg me-1"></i>Setujui
                        </button>
                        <!-- Tombol Tolak -->
                        <button class="btn btn-sm btn-outline-danger"
                                data-bs-toggle="modal"
                                data-bs-target="#rejectModal"
                                data-id="<?= $buku['id'] ?>"
                                data-judul="<?= e($buku['judul']) ?>">
                            <i class="bi bi-x-lg me-1"></i>Tolak
                        </button>
                    </td>
                    <?php else: ?>
                    <td>
                        <?php if ($buku['status_approval'] === 'approved'): ?>
                            <span class="badge-status badge-approved">Disetujui</span>
                        <?php else: ?>
                            <span class="badge-status badge-rejected"
                                  title="<?= e($buku['catatan_penolakan'] ?? '') ?>">
                                Ditolak
                            </span>
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="card-body pt-2 pb-3 border-top
                d-flex justify-content-between align-items-center">
        <small class="text-muted">
            Halaman <?= $page ?> dari <?= $totalPages ?>
        </small>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                    <a class="page-link"
                       href="?status=<?= $statusFilter ?>&page=<?= $p ?>">
                        <?= $p ?>
                    </a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<!-- ── Modal Setujui ── -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">
                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                    Konfirmasi Persetujuan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Setujui buku <strong id="approveBukuJudul"></strong>?
                Buku ini akan segera tampil di katalog dan dapat diakses oleh pengguna.
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Batal</button>
                <form method="POST" action="proses_persetujuan.php" class="d-inline">
                    <input type="hidden" name="id"              id="approveId">
                    <input type="hidden" name="aksi"            value="approved">
                    <input type="hidden" name="redirect_status" value="pending">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg me-1"></i> Ya, Setujui
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ── Modal Tolak ── -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">
                    <i class="bi bi-x-circle-fill text-danger me-2"></i>
                    Konfirmasi Penolakan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">
                    Tolak buku <strong id="rejectBukuJudul"></strong>?
                </p>
                <form method="POST" action="proses_persetujuan.php" id="rejectForm">
                    <input type="hidden" name="id"              id="rejectId">
                    <input type="hidden" name="aksi"            value="rejected">
                    <input type="hidden" name="redirect_status" value="pending">
                    <div>
                        <label class="form-label fw-semibold">
                            Catatan Penolakan
                            <span class="text-muted fw-normal">(opsional)</span>
                        </label>
                        <textarea name="catatan" class="form-control" rows="3"
                                  placeholder="Jelaskan alasan penolakan kepada petugas...">
                        </textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Batal</button>
                <button type="submit" form="rejectForm" class="btn btn-danger">
                    <i class="bi bi-x-lg me-1"></i> Ya, Tolak
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    /* Approve modal — isi id & judul */
    const approveModal = document.getElementById('approveModal');
    approveModal.addEventListener('show.bs.modal', function (e) {
        const btn = e.relatedTarget;
        document.getElementById('approveId').value              = btn.dataset.id;
        document.getElementById('approveBukuJudul').textContent = btn.dataset.judul;
    });

    /* Reject modal — isi id & judul */
    const rejectModal = document.getElementById('rejectModal');
    rejectModal.addEventListener('show.bs.modal', function (e) {
        const btn = e.relatedTarget;
        document.getElementById('rejectId').value              = btn.dataset.id;
        document.getElementById('rejectBukuJudul').textContent = btn.dataset.judul;
    });
</script>

<?php require_once __DIR__ . '/layout/footer.php'; ?>
