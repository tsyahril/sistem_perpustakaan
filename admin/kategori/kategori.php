<?php
session_start();
include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../middleware/admin.php";
include __DIR__ .  "/../../admin/layout/header.php";

// SweetAlert (flash message)
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<!-- Tailwind -->
<script src="https://cdn.tailwindcss.com"></script>
<?php if (isset($_SESSION['success'])) { ?>
<script>
Swal.fire({
    icon: 'success',
    title: 'Berhasil!',
    text: '<?= $_SESSION['success']; ?>',
    background: '#0f172a',
    color: '#fff'
});
</script>
<?php unset($_SESSION['success']); } ?>

<?php if (isset($_SESSION['error'])) { ?>
<script>
Swal.fire({
    icon: 'error',
    title: 'Gagal!',
    text: '<?= $_SESSION['error']; ?>',
    background: '#0f172a',
    color: '#fff'
});
</script>
<?php unset($_SESSION['error']); } ?>

<?php
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$sql = "SELECT * FROM kategori";
if (!empty($search)) {
    $sql .= " WHERE nama_kategori LIKE '%$search%'";
}
$sql .= " ORDER BY nama_kategori ASC";
$query = mysqli_query($conn, $sql);
?>

<!-- HEADER -->
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-white">Kategori Buku</h1>
        <p class="text-slate-400 text-sm">Kelola klasifikasi genre buku</p>
    </div>

    <a href="tambah.php"
       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow flex items-center gap-2">
        <i class="fa fa-plus"></i> Tambah Kategori
    </a>
</div>

<!-- SEARCH -->
<form method="GET" class="mb-6">
    <div class="flex bg-slate-800 border border-slate-700 rounded-lg overflow-hidden max-w-md">
        <input type="text"
               name="search"
               value="<?= htmlspecialchars($search) ?>"
               placeholder="Cari kategori..."
               class="w-full px-4 py-2 bg-transparent text-white outline-none">

        <button class="bg-blue-600 px-4 text-white">
            <i class="fa fa-search"></i>
        </button>
    </div>
</form>

<!-- TABLE -->
<div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-lg">

    <table class="w-full text-left text-white">
        <thead class="bg-slate-800 text-slate-300">
            <tr>
                <th class="p-4 w-16">No</th>
                <th class="p-4">Nama Kategori</th>
                <th class="p-4 w-32">Aksi</th>
            </tr>
        </thead>

        <tbody>
            <?php
            $no = 1;
            if (mysqli_num_rows($query) > 0) {
                while ($row = mysqli_fetch_assoc($query)):
            ?>
            <tr class="border-t border-slate-800 hover:bg-slate-800/50 transition">
                <td class="p-4 text-slate-400"><?= $no++; ?></td>

                <td class="p-4 font-semibold">
                    <?= htmlspecialchars($row['nama_kategori']); ?>
                </td>

                <td class="p-4 flex gap-3">

                    <a href="edit.php?id=<?= $row['id_kategori']; ?>"
                       class="text-blue-400 hover:text-blue-300">
                        <i class="fa fa-pen-to-square"></i>
                    </a>

                    <a href="#"
                       onclick="hapusKategori(<?= $row['id_kategori']; ?>)"
                       class="text-red-400 hover:text-red-300">
                        <i class="fa fa-trash"></i>
                    </a>

                </td>
            </tr>
            <?php endwhile; } else { ?>
                <tr>
                    <td colspan="3" class="text-center p-6 text-slate-400">
                        Data tidak ditemukan
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

</div>

<!-- SWEETALERT DELETE -->
<script>
function hapusKategori(id) {
    Swal.fire({
        title: 'Hapus kategori?',
        text: "Data tidak bisa dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal',
        background: '#0f172a',
        color: '#fff',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location = 'hapus.php?id=' + id;
        }
    });
}
</script>

<?php include __DIR__ .  "/../../admin/layout/footer.php"; ?>