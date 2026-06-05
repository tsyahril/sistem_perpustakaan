<?php
session_start();

include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../middleware/anggota.php";

// ==========================
// USER LOGIN
// ==========================
$id_user = $_SESSION['id_user'];

// ==========================
// HAPUS ULASAN
// ==========================
if (isset($_GET['hapus'])) {

    $id_ulasan = mysqli_real_escape_string($conn, $_GET['hapus']);

    // Pastikan ulasan milik anggota yang login
    $cek = mysqli_query($conn, "
        SELECT id_ulasan
        FROM ulasan
        WHERE id_ulasan = '$id_ulasan'
        AND id_user = '$id_user'
        LIMIT 1
    ");

    if (mysqli_num_rows($cek) == 0) {

        $_SESSION['error'] = "Ulasan tidak ditemukan atau bukan milik kamu.";
        header("Location: index.php");
        exit;

    } else {

        $hapus = mysqli_query($conn, "
            DELETE FROM ulasan
            WHERE id_ulasan = '$id_ulasan'
            AND id_user = '$id_user'
        ");

        if ($hapus) {
            $_SESSION['success'] = "Ulasan berhasil dihapus.";
        } else {
            $_SESSION['error'] = "Ulasan gagal dihapus.";
        }

        header("Location: index.php");
        exit;
    }
}

include __DIR__ . "/../layout/header.php";

// ==========================
// AMBIL ULASAN MILIK ANGGOTA
// ==========================
$query = mysqli_query($conn, "
    SELECT 
        ulasan.id_ulasan,
        ulasan.rating,
        ulasan.ulasan,
        ulasan.tanggal_ulasan,

        buku.judul,
        buku.cover,

        COALESCE(penulis.nama_penulis, '-') AS nama_penulis,
        COALESCE(penerbit.nama_penerbit, '-') AS nama_penerbit,
        COALESCE(kategori.nama_kategori, '-') AS nama_kategori

    FROM ulasan

    JOIN buku
        ON ulasan.id_buku = buku.id_buku

    LEFT JOIN penulis
        ON buku.id_penulis = penulis.id_penulis

    LEFT JOIN penerbit
        ON buku.id_penerbit = penerbit.id_penerbit

    LEFT JOIN kategori
        ON buku.id_kategori = kategori.id_kategori

    WHERE ulasan.id_user = '$id_user'

    ORDER BY ulasan.tanggal_ulasan DESC, ulasan.id_ulasan DESC
");

if (!$query) {
    die("Query Error: " . mysqli_error($conn));
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (isset($_SESSION['success'])): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: <?= json_encode($_SESSION['success']); ?>,
        background: '#0f172a',
        color: '#fff',
        confirmButtonColor: '#10b981'
    });
</script>
<?php unset($_SESSION['success']); endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<script>
    Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: <?= json_encode($_SESSION['error']); ?>,
        background: '#0f172a',
        color: '#fff',
        confirmButtonColor: '#ef4444'
    });
</script>
<?php unset($_SESSION['error']); endif; ?>

<div class="space-y-8">

    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

        <div>
            <h1 class="text-4xl font-bold text-white">
                Ulasan Saya
            </h1>

            <p class="text-slate-400 mt-2">
                Daftar semua ulasan buku yang pernah kamu berikan.
            </p>
        </div>

        <a href="../dashboard/index.php"
            class="bg-slate-700 hover:bg-slate-600 transition px-5 py-3 rounded-2xl text-white font-semibold w-fit flex items-center gap-2">

            <i class='bx bx-arrow-back'></i>
            Kembali

        </a>

    </div>

    <!-- TABLE -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">

        <div class="overflow-x-auto">

            <table class="w-full text-sm text-left text-slate-300">

                <thead class="bg-slate-800 text-slate-300 uppercase text-xs">

                    <tr>
                        <th class="px-6 py-4">Buku</th>
                        <th class="px-6 py-4">Kategori</th>
                        <th class="px-6 py-4">Rating</th>
                        <th class="px-6 py-4">Ulasan</th>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>

                </thead>

                <tbody class="divide-y divide-slate-800">

                    <?php if (mysqli_num_rows($query) > 0): ?>

                        <?php while ($row = mysqli_fetch_assoc($query)): ?>

                            <tr class="hover:bg-slate-800/50 transition">

                                <!-- BUKU -->
                                <td class="px-6 py-4">

                                    <div class="flex items-center gap-4 min-w-[280px]">

                                        <img 
                                            src="../../assets/img/cover/<?= htmlspecialchars($row['cover']); ?>"
                                            class="w-14 h-20 object-cover rounded-xl border border-slate-700 shadow">

                                        <div>
                                            <h3 class="font-semibold text-white">
                                                <?= htmlspecialchars($row['judul']); ?>
                                            </h3>

                                            <p class="text-xs text-slate-500 mt-1">
                                                <?= htmlspecialchars($row['nama_penulis']); ?> • <?= htmlspecialchars($row['nama_penerbit']); ?>
                                            </p>
                                        </div>

                                    </div>

                                </td>

                                <!-- KATEGORI -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?= htmlspecialchars($row['nama_kategori']); ?>
                                </td>

                                <!-- RATING -->
                                <td class="px-6 py-4 whitespace-nowrap">

                                    <span class="bg-yellow-500/10 text-yellow-400 border border-yellow-500/20 px-3 py-1 rounded-full text-xs font-semibold">
                                        <?= (int) $row['rating']; ?> / 5
                                    </span>

                                </td>

                                <!-- ULASAN -->
                                <td class="px-6 py-4 min-w-[300px]">

                                    <p class="text-slate-300 leading-relaxed">
                                        <?= nl2br(htmlspecialchars($row['ulasan'])); ?>
                                    </p>

                                </td>

                                <!-- TANGGAL -->
                                <td class="px-6 py-4 whitespace-nowrap">

                                    <?php if (!empty($row['tanggal_ulasan'])): ?>
                                        <?= date('d M Y', strtotime($row['tanggal_ulasan'])); ?>
                                    <?php else: ?>
                                        <span class="text-slate-500">-</span>
                                    <?php endif; ?>

                                </td>

                                <!-- AKSI -->
                                <td class="px-6 py-4 text-center">

                                    <button
                                        type="button"
                                        onclick="confirmHapus(<?= $row['id_ulasan']; ?>)"
                                        class="bg-red-500 hover:bg-red-600 transition px-4 py-2 rounded-xl text-white text-sm font-semibold inline-flex items-center gap-2">

                                        <i class='bx bx-trash'></i>
                                        Hapus

                                    </button>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                Kamu belum memberikan ulasan.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<script>
    function confirmHapus(id) {
        Swal.fire({
            title: 'Hapus Ulasan?',
            text: 'Ulasan yang dihapus tidak bisa dikembalikan.',
            icon: 'warning',
            background: '#0f172a',
            color: '#fff',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location = 'index.php?hapus=' + id;
            }
        });
    }
</script>

<?php include __DIR__ . "/../layout/footer.php"; ?>