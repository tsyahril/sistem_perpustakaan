<?php
session_start();

include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../middleware/admin.php";
include __DIR__ .  "/../../admin/layout/header.php";

if (isset($_POST['submit'])) {

    $nama_penerbit = mysqli_real_escape_string($conn, $_POST['nama_penerbit']);

    mysqli_query($conn, "
        INSERT INTO penerbit (nama_penerbit)
        VALUES ('$nama_penerbit')
    ");

    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>

    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: 'Penerbit berhasil ditambahkan',
            background: '#0f172a',
            color: '#fff'
        }).then(() => {
            window.location='index.php';
        });
    </script>";
}
?>

<div class="max-w-xl mx-auto">

    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">

        <h1 class="text-2xl font-bold text-white mb-6">
            Tambah Penerbit
        </h1>

        <form method="POST" class="space-y-5">

            <div>

                <label class="block text-slate-300 mb-2">
                    Nama Penerbit
                </label>

                <input
                    type="text"
                    name="nama_penerbit"
                    required
                    class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-blue-500">

            </div>

            <div class="flex gap-3">

                <a href="index.php"
                    class="bg-slate-700 hover:bg-slate-600 transition px-5 py-3 rounded-xl text-white">

                    Kembali

                </a>

                <button
                    type="submit"
                    name="submit"
                    class="bg-blue-500 hover:bg-blue-600 transition px-5 py-3 rounded-xl text-white font-semibold">

                    Simpan

                </button>

            </div>

        </form>

    </div>

</div>

<?php include __DIR__ .  "/../../admin/layout/footer.php"; ?>