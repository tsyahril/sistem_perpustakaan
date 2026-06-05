<?php
session_start();

include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../middleware/admin.php";
include __DIR__ .  "/../../admin/layout/header.php";
?>

<!-- SWEET ALERT -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- FONT AWESOME -->
<link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

<?php

// ==========================
// VALIDASI ID BUKU
// ==========================
if (!isset($_GET['id_buku'])) {

    echo "
    <script>
        alert('ID Buku tidak ditemukan!');
        window.location='../buku/buku.php';
    </script>
    ";

    exit;
}

$id_buku = mysqli_real_escape_string($conn, $_GET['id_buku']);

// ==========================
// HAPUS ULASAN
// ==========================
if (isset($_GET['hapus_ulasan'])) {

    $id_ulasan = mysqli_real_escape_string($conn, $_GET['hapus_ulasan']);

    mysqli_query($conn, "
        DELETE FROM ulasan
        WHERE id_ulasan = '$id_ulasan'
    ");

    header("Location: index.php?id_buku=$id_buku&hapus=1");
    exit;
}

// ==========================
// TAMBAH ULASAN
// ==========================
if (isset($_POST['tambah_ulasan'])) {

    $rating = mysqli_real_escape_string($conn, $_POST['rating']);
    $ulasan = mysqli_real_escape_string($conn, $_POST['ulasan']);

    $id_user = $_SESSION['id_user'];

    mysqli_query($conn, "
        INSERT INTO ulasan (
            id_user,
            id_buku,
            rating,
            ulasan
        ) VALUES (
            '$id_user',
            '$id_buku',
            '$rating',
            '$ulasan'
        )
    ");

    header("Location: index.php?id_buku=$id_buku&success=1");
    exit;
}

// ==========================
// DATA BUKU
// ==========================
$getBuku = mysqli_query($conn, "
    SELECT 
        buku.*,
        kategori.nama_kategori,
        penulis.nama_penulis,
        penerbit.nama_penerbit,

        AVG(ulasan.rating) as rata_rating,
        COUNT(ulasan.id_ulasan) as total_ulasan

    FROM buku

    LEFT JOIN kategori
        ON buku.id_kategori = kategori.id_kategori

    LEFT JOIN penulis
        ON buku.id_penulis = penulis.id_penulis

    LEFT JOIN penerbit
        ON buku.id_penerbit = penerbit.id_penerbit

    LEFT JOIN ulasan
        ON buku.id_buku = ulasan.id_buku

    WHERE buku.id_buku = '$id_buku'

    GROUP BY buku.id_buku
");

$buku = mysqli_fetch_assoc($getBuku);

// ==========================
// QUERY ULASAN
// ==========================
$query = mysqli_query($conn, "
    SELECT 
        ulasan.*,
        user.nama

    FROM ulasan

    JOIN user
        ON ulasan.id_user = user.id_user

    WHERE ulasan.id_buku = '$id_buku'

    ORDER BY ulasan.id_ulasan DESC
");
?>

<div class="max-w-6xl mx-auto">

    <!-- HEADER -->
    <div class="flex justify-between items-center mb-8">

        <div>

            <h1 class="text-3xl font-bold text-white">
                Ulasan Buku
            </h1>

            <p class="text-slate-400 mt-2">
                Detail rating dan ulasan pembaca.
            </p>

        </div>

        <a href="../buku/buku.php"
            class="bg-slate-800 hover:bg-slate-700 transition px-5 py-3 rounded-xl text-white flex items-center gap-2">

            <i class='bx bx-arrow-back'></i>

            Kembali

        </a>

    </div>

    <!-- CARD BUKU -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-2xl mb-8">

        <div class="grid lg:grid-cols-3 gap-8 p-8">

            <!-- COVER -->
            <div class="flex justify-center">

                <img src="../../assets/img/cover/<?= htmlspecialchars($buku['cover']); ?>"
                    class="w-64 h-96 object-cover rounded-2xl shadow-xl">

            </div>

            <!-- DETAIL -->
            <div class="lg:col-span-2 space-y-6">

                <div>

                    <h2 class="text-4xl font-bold text-white leading-tight">
                        <?= htmlspecialchars($buku['judul']); ?>
                    </h2>

                    <div class="flex flex-wrap gap-3 mt-4">

                        <span class="bg-blue-500/10 text-blue-400 px-4 py-2 rounded-xl text-sm font-semibold">
                            <?= htmlspecialchars($buku['nama_kategori'] ?? 'Tanpa Kategori'); ?>
                        </span>

                        <span class="bg-yellow-500/10 text-yellow-400 px-4 py-2 rounded-xl text-sm font-semibold">
                            ⭐ <?= number_format($buku['rata_rating'] ?: 0, 1); ?>
                        </span>

                        <span class="bg-emerald-500/10 text-emerald-400 px-4 py-2 rounded-xl text-sm font-semibold">
                            <?= $buku['total_ulasan']; ?> Ulasan
                        </span>

                    </div>

                </div>

                <!-- INFO -->
                <div class="grid md:grid-cols-2 gap-4">

                    <div class="bg-slate-800/60 rounded-2xl p-5">

                        <p class="text-slate-400 text-sm mb-2">
                            Penulis
                        </p>

                        <p class="text-white font-semibold text-lg">
                            <?= htmlspecialchars($buku['nama_penulis'] ?? '-'); ?>
                        </p>

                    </div>

                    <div class="bg-slate-800/60 rounded-2xl p-5">

                        <p class="text-slate-400 text-sm mb-2">
                            Penerbit
                        </p>

                        <p class="text-white font-semibold text-lg">
                            <?= htmlspecialchars($buku['nama_penerbit'] ?? '-'); ?>
                        </p>

                    </div>

                    <div class="bg-slate-800/60 rounded-2xl p-5">

                        <p class="text-slate-400 text-sm mb-2">
                            ISBN
                        </p>

                        <p class="text-white font-semibold text-lg">
                            <?= $buku['isbn'] ?: '-'; ?>
                        </p>

                    </div>

                    <div class="bg-slate-800/60 rounded-2xl p-5">

                        <p class="text-slate-400 text-sm mb-2">
                            Harga
                        </p>

                        <p class="text-emerald-400 font-bold text-lg">
                            Rp <?= number_format($buku['harga'], 0, ',', '.'); ?>
                        </p>

                    </div>

                </div>

                <!-- DESKRIPSI -->
                <div class="bg-slate-800/40 rounded-2xl p-6">

                    <p class="text-slate-400 text-sm mb-3">
                        Deskripsi Buku
                    </p>

                    <p class="text-slate-300 leading-relaxed">
                        <?= nl2br(htmlspecialchars($buku['deskripsi'] ?? 'Tidak ada deskripsi buku.')); ?>
                    </p>

                </div>

            </div>

        </div>

    </div>

    <!-- LIST ULASAN -->
    <div class="space-y-5">

        <div class="flex items-center justify-between">

            <h2 class="text-2xl font-bold text-white">
                Semua Ulasan
            </h2>

            <div class="bg-slate-900 border border-slate-800 px-4 py-2 rounded-xl text-slate-300 text-sm">
                Total :
                <span class="text-white font-semibold">
                    <?= $buku['total_ulasan']; ?>
                </span>
            </div>

        </div>

        <!-- FORM TAMBAH ULASAN -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 mb-8">

            <div class="flex items-center gap-3 mb-6">

                <div class="w-12 h-12 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-400 text-2xl">
                    <i class='bx bxs-star'></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold text-white">
                        Tambah Ulasan
                    </h2>

                    <p class="text-slate-400 text-sm">
                        Berikan rating dan review untuk buku ini.
                    </p>
                </div>

            </div>

            <form method="POST" class="space-y-5">

                <!-- RATING -->
                <div>

                    <label class="block text-slate-300 mb-4 font-medium">
                        Rating
                    </label>

                    <div class="rating flex flex-row-reverse justify-end gap-2">

                        <?php for ($i = 5; $i >= 1; $i--): ?>

                            <input type="radio"
                                id="star<?= $i; ?>"
                                name="rating"
                                value="<?= $i; ?>"
                                class="hidden"
                                required>

                            <label for="star<?= $i; ?>"
                                class="cursor-pointer text-4xl text-slate-600 transition duration-200">

                                <i class="fa-solid fa-star"></i>

                            </label>

                        <?php endfor; ?>

                    </div>

                </div>

                <!-- ULASAN -->
                <div>

                    <label class="block text-slate-300 mb-2 font-medium">
                        Ulasan
                    </label>

                    <textarea
                        name="ulasan"
                        rows="5"
                        required
                        placeholder="Tulis ulasan buku..."
                        class="w-full bg-slate-800 border border-slate-700 rounded-2xl p-4 text-white outline-none focus:border-blue-500 resize-none"></textarea>

                </div>

                <!-- BUTTON -->
                <button type="submit"
                    name="tambah_ulasan"
                    class="bg-blue-500 hover:bg-blue-600 transition px-6 py-3 rounded-2xl text-white font-semibold flex items-center gap-2 shadow-lg">

                    <i class='bx bx-send'></i>

                    Kirim Ulasan

                </button>

            </form>

        </div>

        <?php if (mysqli_num_rows($query) > 0): ?>

            <?php while ($row = mysqli_fetch_assoc($query)): ?>

                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 hover:border-slate-700 transition">

                    <!-- TOP -->
                    <div class="flex justify-between items-start gap-4">

                        <div class="flex items-center gap-4">

                            <!-- AVATAR -->
                            <div class="w-14 h-14 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold text-xl shadow-lg">

                                <?= strtoupper(substr($row['nama'], 0, 1)); ?>

                            </div>

                            <!-- USER -->
                            <div>

                                <h3 class="text-lg font-semibold text-white">
                                    <?= htmlspecialchars($row['nama']); ?>
                                </h3>

                                <p class="text-slate-500 text-sm">
                                    Member Perpustakaan
                                </p>

                            </div>

                        </div>

                        <!-- RATING -->
                        <div class="bg-yellow-500/10 border border-yellow-500/20 px-4 py-2 rounded-xl text-yellow-400 font-semibold">

                            ⭐ <?= $row['rating']; ?>/5

                        </div>

                    </div>

                    <!-- ULASAN -->
                    <div class="mt-5">

                        <?php
                        $fullText = nl2br(htmlspecialchars($row['ulasan']));
                        $shortText = substr(strip_tags($row['ulasan']), 0, 180);
                        $isLong = strlen($row['ulasan']) > 180;
                        ?>

                        <p class="text-slate-300 leading-relaxed text-[15px]">

                            <span id="short-<?= $row['id_ulasan']; ?>">

                                <?= nl2br(htmlspecialchars($shortText)); ?>

                                <?php if ($isLong): ?>
                                    ...
                                <?php endif; ?>

                            </span>

                            <?php if ($isLong): ?>

                                <span id="full-<?= $row['id_ulasan']; ?>"
                                    class="hidden">

                                    <?= $fullText; ?>

                                </span>

                                <button
                                    type="button"
                                    onclick="toggleText(<?= $row['id_ulasan']; ?>)"
                                    id="btn-<?= $row['id_ulasan']; ?>"
                                    class="text-blue-400 hover:text-blue-300 mt-3 text-sm font-semibold block">

                                    Baca Selengkapnya

                                </button>

                            <?php endif; ?>

                        </p>
                    </div>

                    <!-- ACTION -->
                    <div class="mt-6 flex justify-end">

                        <a href="javascript:void(0)"
                            onclick="hapusUlasan('?hapus_ulasan=<?= $row['id_ulasan']; ?>&id_buku=<?= $id_buku; ?>')"
                            class="bg-red-500/10 hover:bg-red-500/20 border border-red-500/20 transition px-4 py-2 rounded-xl text-red-400 flex items-center gap-2">

                            <i class='bx bx-trash'></i>

                            Hapus Ulasan

                        </a>

                    </div>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <!-- EMPTY -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-16 text-center">

                <div class="w-24 h-24 mx-auto rounded-full bg-slate-800 flex items-center justify-center mb-6">

                    <i class='bx bx-message-square-x text-5xl text-slate-500'></i>

                </div>

                <h3 class="text-2xl font-bold text-white mb-2">
                    Belum Ada Ulasan
                </h3>

                <p class="text-slate-400">
                    Buku ini belum memiliki review dari pengguna.
                </p>

            </div>

        <?php endif; ?>

    </div>

</div>

<style>
    .rating label:hover,
    .rating label:hover~label {
        color: #facc15;
        transform: scale(1.1);
    }

    .rating input:checked~label {
        color: #facc15;
    }
</style>

<script>
    // ==========================
    // TOGGLE ULASAN
    // ==========================
    function toggleText(id) {

        const shortText = document.getElementById('short-' + id);
        const fullText = document.getElementById('full-' + id);
        const button = document.getElementById('btn-' + id);

        if (fullText.classList.contains('hidden')) {

            fullText.classList.remove('hidden');
            shortText.classList.add('hidden');

            button.innerText = 'Tampilkan Sedikit';

        } else {

            fullText.classList.add('hidden');
            shortText.classList.remove('hidden');

            button.innerText = 'Baca Selengkapnya';

        }

    }

    // ==========================
    // HAPUS ULASAN
    // ==========================
    function hapusUlasan(url) {

        Swal.fire({
            title: 'Hapus Ulasan?',
            text: 'Ulasan akan dihapus permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#475569',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            background: '#0f172a',
            color: '#fff'
        }).then((result) => {

            if (result.isConfirmed) {
                window.location.href = url;
            }

        });

    }
</script>

<!-- SWEET ALERT SUCCESS -->
<?php if (isset($_GET['success'])): ?>

    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Ulasan berhasil ditambahkan.',
            background: '#0f172a',
            color: '#fff',
            confirmButtonColor: '#3b82f6'
        });
    </script>

<?php endif; ?>

<!-- SWEET ALERT HAPUS -->
<?php if (isset($_GET['hapus'])): ?>

    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Ulasan berhasil dihapus.',
            background: '#0f172a',
            color: '#fff',
            confirmButtonColor: '#ef4444'
        });
    </script>

<?php endif; ?>

<?php include __DIR__ .  "/../../admin/layout/footer.php"; ?>