<?php
// admin/proses_persetujuan.php — Proses Approve / Reject Buku
// Handler POST saja, tidak menampilkan UI.
require_once __DIR__ . '/../middleware/admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlash('danger', 'Metode request tidak valid.');
    redirect('persetujuan.php');
}

$pdo     = getPDO();
$id      = (int) ($_POST['id']   ?? 0);
$aksi    = trim($_POST['aksi']   ?? '');
$catatan = trim($_POST['catatan'] ?? '');

$redirectStatus = in_array($_POST['redirect_status'] ?? '', ['pending', 'approved', 'rejected'])
    ? $_POST['redirect_status']
    : 'pending';

// ── Validasi dasar ──
if ($id <= 0) {
    setFlash('danger', 'ID buku tidak valid.');
    redirect("persetujuan.php?status=$redirectStatus");
}

if (!in_array($aksi, ['approved', 'rejected'])) {
    setFlash('danger', 'Aksi tidak valid.');
    redirect("persetujuan.php?status=$redirectStatus");
}

// ── Ambil data buku ──
$stmtCek = $pdo->prepare(
    'SELECT id, judul, status_approval FROM buku WHERE id = :id'
);
$stmtCek->execute([':id' => $id]);
$buku = $stmtCek->fetch();

if (!$buku) {
    setFlash('danger', 'Buku tidak ditemukan.');
    redirect("persetujuan.php?status=$redirectStatus");
}

if ($buku['status_approval'] !== 'pending') {
    setFlash('warning', "Buku ini sudah diproses sebelumnya (status: {$buku['status_approval']}).");
    redirect("persetujuan.php?status=$redirectStatus");
}

// ── Update status ──
if ($aksi === 'approved') {
    $stmt = $pdo->prepare(
        'UPDATE buku
         SET    status_approval = :status, catatan_penolakan = NULL
         WHERE  id = :id'
    );
    $stmt->execute([':status' => 'approved', ':id' => $id]);

    setFlash(
        'success',
        "Buku <strong>{$buku['judul']}</strong> berhasil <strong>disetujui</strong> dan kini tampil di katalog."
    );
    redirect('persetujuan.php?status=pending');

} else {
    $stmt = $pdo->prepare(
        'UPDATE buku
         SET    status_approval = :status, catatan_penolakan = :catatan
         WHERE  id = :id'
    );
    $stmt->execute([
        ':status'  => 'rejected',
        ':catatan' => $catatan !== '' ? $catatan : null,
        ':id'      => $id,
    ]);

    setFlash(
        'danger',
        "Buku <strong>{$buku['judul']}</strong> telah <strong>ditolak</strong>."
    );
    redirect('persetujuan.php?status=pending');
}
