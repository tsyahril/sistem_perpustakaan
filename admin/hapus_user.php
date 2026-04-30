<?php
// admin/hapus_user.php — Hapus Pengguna (handler, tanpa UI)
require_once __DIR__ . '/../middleware/admin.php';

$pdo = getPDO();
$id  = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    setFlash('danger', 'ID pengguna tidak valid.');
    redirect('user.php');
}

// Cegah admin menghapus akunnya sendiri
if ($id === (int) $_SESSION['user_id']) {
    setFlash('danger', 'Anda tidak dapat menghapus akun Anda sendiri.');
    redirect('user.php');
}

// Cek apakah user ada
$stmt = $pdo->prepare('SELECT nama FROM users WHERE id = :id');
$stmt->execute([':id' => $id]);
$user = $stmt->fetch();

if (!$user) {
    setFlash('danger', 'Pengguna tidak ditemukan.');
    redirect('user.php');
}

// Eksekusi hapus
$del = $pdo->prepare('DELETE FROM users WHERE id = :id');
$del->execute([':id' => $id]);

if ($del->rowCount() > 0) {
    setFlash('success', "Pengguna <strong>{$user['nama']}</strong> berhasil dihapus.");
} else {
    setFlash('danger', 'Gagal menghapus pengguna. Silakan coba lagi.');
}

redirect('user.php');
