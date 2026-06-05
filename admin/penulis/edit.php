<?php
session_start();

include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../middleware/admin.php";

include __DIR__ .  "/../../admin/layout/header.php";

$id = $_GET['id'];

$query = mysqli_query($conn, "
    SELECT * FROM penulis
    WHERE id_penulis = '$id'
");

$data = mysqli_fetch_assoc($query);

if (isset($_POST['submit'])) {

    $nama_penulis = mysqli_real_escape_string($conn, $_POST['nama_penulis']);

    mysqli_query($conn, "
        UPDATE penulis
        SET nama_penulis = '$nama_penulis'
        WHERE id_penulis = '$id'
    ");

    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>

    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Penulis berhasil diupdate',
            background: '#0f172a',
            color: '#fff'
        }).then(() => {
            window.location='index.php';
        });
    </script>";
}
?>

<!-- Tailwind (AMAN kalau belum ada di header) -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- SweetAlert (AMAN kalau belum ada di header) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="max-w-xl mx-auto mt-10">

    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-lg">

        <h1 class="text-2xl font-bold text-white mb-6">
            Edit Penulis
        </h1>

        <form method="POST" class="space-y-5">

            <div>

                <label class="block text-slate-300 mb-2">
                    Nama Penulis
                </label>

                <input
                    type="text"
                    name="nama_penulis"
                    required
                    value="<?= htmlspecialchars($data['nama_penulis']); ?>"
                    class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-yellow-500">

            </div>

            <div class="flex gap-3">

                <a href="index.php"
                    class="bg-slate-700 hover:bg-slate-600 transition px-5 py-3 rounded-xl text-white">

                    Kembali

                </a>

                <button
                    type="submit"
                    name="submit"
                    class="bg-yellow-500 hover:bg-yellow-600 transition px-5 py-3 rounded-xl text-white font-semibold">

                    Update

                </button>

            </div>

        </form>

    </div>

</div>

<?php include __DIR__ .  "/../../admin/layout/footer.php"; ?>