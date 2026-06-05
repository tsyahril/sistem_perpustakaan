<?php
session_start();

include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../middleware/admin.php";
include __DIR__ .  "/../../admin/layout/header.php";
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- HEADER -->
<div class="flex justify-between items-center mb-6">

    <div>

        <h1 class="text-3xl font-bold text-white">
            Data Penulis
        </h1>

        <p class="text-slate-400 mt-1">
            Kelola data penulis buku
        </p>

    </div>

    <a href="tambah.php"
        class="bg-blue-500 hover:bg-blue-600 transition px-5 py-3 rounded-xl text-white font-semibold flex items-center gap-2 shadow-lg">

        <i class='bx bx-plus'></i>
        Tambah Penulis

    </a>

</div>

<!-- TABLE -->
<div class="overflow-x-auto bg-slate-900 border border-slate-800 rounded-2xl shadow-xl">

    <table class="w-full text-sm text-left text-slate-300">

        <thead class="bg-slate-800 text-slate-200 uppercase text-xs">

            <tr>

                <th class="px-6 py-4">No</th>
                <th class="px-6 py-4">Nama Penulis</th>
                <th class="px-6 py-4 text-center">Aksi</th>

            </tr>

        </thead>

        <tbody>

            <?php
            $query = mysqli_query($conn, "
                SELECT * FROM penulis
                ORDER BY id_penulis DESC
            ");

            if (mysqli_num_rows($query) == 0):
            ?>

                <tr>

                    <td colspan="3"
                        class="text-center py-10 text-slate-500">

                        Belum ada data penulis

                    </td>

                </tr>

            <?php else: ?>

                <?php
                $no = 1;

                while ($row = mysqli_fetch_assoc($query)):
                ?>

                    <tr class="border-b border-slate-800 hover:bg-slate-800/50 transition">

                        <td class="px-6 py-4">
                            <?= $no++; ?>
                        </td>

                        <td class="px-6 py-4 font-semibold text-white">
                            <?= htmlspecialchars($row['nama_penulis']); ?>
                        </td>

                        <td class="px-6 py-4">

                            <div class="flex justify-center gap-3">

                                <!-- EDIT -->
                                <a href="edit.php?id=<?= $row['id_penulis']; ?>"
                                    class="text-blue-400 hover:text-blue-300 text-2xl"
                                    title="Edit">

                                    <i class='bx bx-edit-alt'></i>
                                    

                                </a>

                                <!-- HAPUS -->
                                <a href="hapus.php?id=<?= $row['id_penulis']; ?>"
                                    onclick="confirmDelete(event, this.href)"
                                    class="text-red-400 hover:text-red-300 text-2xl"
                                    title="Hapus">

                                    <i class='bx bx-trash'></i>
                                    

                                </a>

                            </div>

                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php endif; ?>

        </tbody>

    </table>

</div>

<script>
    function confirmDelete(event, url) {

        event.preventDefault();

        Swal.fire({
            title: 'Hapus penulis?',
            text: 'Data tidak bisa dikembalikan',
            icon: 'warning',
            background: '#0f172a',
            color: '#fff',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus'
        }).then((result) => {

            if (result.isConfirmed) {
                window.location.href = url;
            }

        });

    }
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

<?php include __DIR__ .  "/../../admin/layout/footer.php"; ?>