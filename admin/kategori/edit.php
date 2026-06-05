<?php
session_start();
include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../middleware/admin.php";

// Ambil ID
$id = $_GET['id'];

// Ambil data kategori
$data = mysqli_fetch_assoc(
    mysqli_query($conn, 
    "SELECT * FROM kategori 
     WHERE id_kategori = '$id'")
);

// Update kategori
if (isset($_POST['update'])) {

    $nama = mysqli_real_escape_string($conn, $_POST['nama_kategori']);

    if (mysqli_query($conn, 
        "UPDATE kategori 
         SET nama_kategori = '$nama' 
         WHERE id_kategori = '$id'")) {

        $_SESSION['success'] = "Kategori berhasil diperbarui!";
        header("Location: kategori.php");
        exit();

    } else {

        $_SESSION['error'] = "Gagal update kategori!";
        header("Location: kategori.php");
        exit();
    }
}

include __DIR__ .  "/../../admin/layout/header.php";
?>

<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (isset($_SESSION['success'])) { ?>
<script>
Swal.fire({
    icon: 'success',
    title: 'Berhasil!',
    text: '<?= $_SESSION['success']; ?>'
});
</script>
<?php unset($_SESSION['success']); } ?>

<?php if (isset($_SESSION['error'])) { ?>
<script>
Swal.fire({
    icon: 'error',
    title: 'Gagal!',
    text: '<?= $_SESSION['error']; ?>'
});
</script>
<?php unset($_SESSION['error']); } ?>

<!-- Tailwind -->
<script src="https://cdn.tailwindcss.com"></script>

<div class="mb-8">
    <h2 class="text-3xl font-bold text-white mb-2">
        Edit Kategori
    </h2>

    <p class="text-slate-400">
        Ubah nama kategori buku.
    </p>
</div>

<div class="max-w-xl bg-slate-900 border border-slate-800 rounded-3xl p-8 shadow-2xl">

    <form method="POST" class="space-y-6">

        <div>
            <label class="block text-slate-300 mb-2 font-medium">
                Nama Kategori
            </label>

            <input type="text"
                   name="nama_kategori"
                   value="<?= htmlspecialchars($data['nama_kategori']); ?>"
                   required
                   class="w-full bg-slate-800 border border-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none rounded-xl px-4 py-3 text-white">
        </div>

        <div class="flex gap-4">
            <button type="submit"
                    name="update"
                    class="bg-blue-500 hover:bg-blue-600 px-6 py-3 rounded-xl text-white font-semibold">
                Update
            </button>

            <a href="kategori.php"
               class="border border-slate-700 px-6 py-3 rounded-xl text-slate-300">
                Batal
            </a>
        </div>

    </form>

</div>

<?php include __DIR__ .  "/../../admin/layout/footer.php"; ?>