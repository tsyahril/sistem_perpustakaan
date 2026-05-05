<?php
session_start();
require_once "../config/koneksi.php";
require_once "../middleware/admin.php";
include '../admin/layout/header.php';

// Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}


?>

<link rel="stylesheet" href="../assets/css/admin_table.css">

<div class="header-action" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <div>
        <h2 style="color: white; font-size: 24px;">Kelola Pengguna</h2>
        <p style="color: #b3b3b3; font-size: 14px;">Tambah, edit, atau hapus akun petugas, penerbit, dan user.</p>
    </div>
    <a href="tambah_user.php" class="btn-add">
        <i class='bx bx-user-plus'></i> Tambah Akun
    </a>
</div>

<div class="table-container">
    <table class="admin-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Lengkap</th>
                <th>Username</th>
                <th>Role</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            // Ambil data semua user dari database
            $query = mysqli_query($conn, "SELECT * FROM user ORDER BY role ASC, nama ASC");
            
            if (mysqli_num_rows($query) > 0) {
                while ($row = mysqli_fetch_assoc($query)) {
            ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td style="font-weight: 600;"><?= htmlspecialchars($row['nama']); ?></td>
                    <td><?= htmlspecialchars($row['nama']); ?></td>
                    <td>
                        <span class="badge <?= strtolower($row['role']); ?>">
                            <?= strtoupper($row['role']); ?>
                        </span>
                    </td>
                    <td>
                        <a href="edit_user.php?id=<?= $row['id_user']; ?>" class="action-link edit">
                            <i class='bx bx-edit-alt'></i> Edit
                        </a>
                        <a href="hapus_user.php?id=<?= $row['id_user']; ?>" 
                           class="action-link delete" 
                           onclick="return confirm('Apakah Anda yakin ingin menghapus akun ini?')">
                            <i class='bx bx-trash'></i> Hapus
                        </a>
                    </td>
                </tr>
            <?php 
                }
            } else {
                echo "<tr><td colspan='5' style='text-align:center; padding: 30px; color: #b3b3b3;'>Belum ada data pengguna.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php include '../admin/layout/footer.php'; ?>