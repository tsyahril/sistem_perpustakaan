<?php
session_start();

include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../middleware/anggota.php";
include __DIR__ . "/../layout/header.php";

$id_user = $_SESSION['id_user'];

$search = isset($_GET['search'])
    ? mysqli_real_escape_string($conn, trim($_GET['search']))
    : '';

$where = "";

if ($search != '') {
    $where = "
        WHERE 
            buku.judul LIKE '%$search%'
            OR kategori.nama_kategori LIKE '%$search%'
            OR penulis.nama_penulis LIKE '%$search%'
            OR penerbit.nama_penerbit LIKE '%$search%'
    ";
}

$query = mysqli_query($conn, "
    SELECT
        buku.*,
        kategori.nama_kategori,
        penulis.nama_penulis,
        penerbit.nama_penerbit,
        COALESCE(AVG(ulasan.rating), 0) AS rating,
        COUNT(ulasan.id_ulasan) AS jumlah_ulasan

    FROM buku

    LEFT JOIN kategori 
        ON buku.id_kategori = kategori.id_kategori

    LEFT JOIN penulis 
        ON buku.id_penulis = penulis.id_penulis

    LEFT JOIN penerbit 
        ON buku.id_penerbit = penerbit.id_penerbit

    LEFT JOIN ulasan 
        ON buku.id_buku = ulasan.id_buku

    $where

    GROUP BY buku.id_buku

    ORDER BY buku.id_buku DESC
");

if (!$query) {
    die("Query Error: " . mysqli_error($conn));
}
?>

<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    html,
    body {
        overflow-x: hidden;
    }

    ::-webkit-scrollbar {
        width: 0;
        height: 0;
        display: none;
    }

    * {
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
</style>

<div class="space-y-8">

    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

        <div>
            <h1 class="text-4xl font-bold text-white">
                Dashboard Anggota
            </h1>

            <p class="text-slate-400 mt-2">
                Cari buku, lihat detail, rating, ulasan, dan pinjam buku.
            </p>
        </div>

        <a href="../peminjaman/index.php"
            class="bg-blue-500 hover:bg-blue-600 transition px-5 py-3 rounded-2xl text-white font-semibold w-fit">
            Peminjaman Saya
        </a>

    </div>

    <!-- SEARCH BAR -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-xl">

        <form method="GET" class="flex flex-col md:flex-row gap-3">

            <div class="flex-1 flex items-center bg-slate-800 border border-slate-700 rounded-2xl px-4 py-3">

                <i class="fa-solid fa-magnifying-glass text-slate-400 mr-3"></i>

                <input type="text"
                    name="search"
                    value="<?= htmlspecialchars($search); ?>"
                    placeholder="Cari judul, kategori, penulis, atau penerbit..."
                    class="w-full bg-transparent outline-none text-white placeholder:text-slate-500">

            </div>

            <button type="submit"
                class="bg-blue-500 hover:bg-blue-600 transition px-6 py-3 rounded-2xl text-white font-semibold">
                Cari
            </button>

            <?php if ($search != ''): ?>

                <a href="index.php"
                    class="bg-slate-700 hover:bg-slate-600 transition px-6 py-3 rounded-2xl text-white font-semibold text-center">
                    Reset
                </a>

            <?php endif; ?>

        </form>

    </div>

    <!-- BOOK CARD -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

        <?php if (mysqli_num_rows($query) > 0): ?>

            <?php while ($row = mysqli_fetch_assoc($query)): ?>

                <?php
                $rating = round($row['rating'], 1);
                $id_buku = $row['id_buku'];

                $cek_pinjam = mysqli_query($conn, "
                    SELECT id_pinjam
                    FROM peminjaman
                    WHERE id_user = '$id_user'
                    AND id_buku = '$id_buku'
                    AND status = 'dipinjam'
                    LIMIT 1
                ");

                $sedang_dipinjam = mysqli_num_rows($cek_pinjam) > 0;

                $queryUlasan = mysqli_query($conn, "
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

                <!-- CARD -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-xl hover:-translate-y-1 hover:border-blue-500 transition">

                    <div class="h-64 bg-slate-800 overflow-hidden">
                        <img src="../../assets/img/cover/<?= htmlspecialchars($row['cover']); ?>"
                            class="w-full h-full object-cover">
                    </div>

                    <div class="p-5">

                        <h2 class="line-clamp-2">
                            <?= htmlspecialchars($row['judul']); ?>
                        </h2>

                        <p class="text-slate-400 text-sm mt-1">
                            <?= htmlspecialchars($row['nama_kategori'] ?? 'Tanpa Kategori'); ?>
                        </p>

                        <div class="flex items-center justify-between mt-4 mb-4">

                            <div class="text-yellow-400 font-semibold text-sm">
                                ⭐ <?= $rating > 0 ? $rating : '0.0'; ?>/5
                            </div>

                            <div class="text-slate-500 text-xs">
                                <?= $row['jumlah_ulasan']; ?> ulasan
                            </div>

                        </div>

                        <div class="mb-5">

                            <?php if ($row['stok'] > 0): ?>

                                <span class="bg-emerald-500/10 text-emerald-400 px-3 py-1 rounded-full text-xs font-semibold">
                                    Stok <?= $row['stok']; ?>
                                </span>

                            <?php else: ?>

                                <span class="bg-red-500/10 text-red-400 px-3 py-1 rounded-full text-xs font-semibold">
                                    Stok Habis
                                </span>

                            <?php endif; ?>

                        </div>

                        <div class="flex gap-2">

                            <button type="button"
                                onclick="openDetail('modal-<?= $id_buku; ?>')"
                                class="flex-1 bg-slate-800 hover:bg-slate-700 text-white py-2 rounded-xl text-sm font-semibold transition">
                                Detail
                            </button>

                            <?php if ($sedang_dipinjam): ?>

                                <button type="button"
                                    disabled
                                    class="flex-1 bg-slate-700 text-slate-400 py-2 rounded-xl text-sm font-semibold cursor-not-allowed">
                                    Dipinjam
                                </button>

                            <?php elseif ($row['stok'] <= 0): ?>

                                <button type="button"
                                    disabled
                                    class="flex-1 bg-red-500/10 text-red-400 py-2 rounded-xl text-sm font-semibold cursor-not-allowed">
                                    Habis
                                </button>

                            <?php else: ?>

                                <button type="button"
                                    onclick="pinjamBuku(<?= $id_buku; ?>)"
                                    class="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-2 rounded-xl text-sm font-semibold transition">
                                    Pinjam
                                </button>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

                <!-- MODAL DETAIL -->
                <div id="modal-<?= $id_buku; ?>"
                    class="fixed inset-0 bg-black/80 hidden z-50 items-center justify-center p-4">

                    <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-5xl max-h-[90vh] overflow-y-auto shadow-2xl">

                        <div class="sticky top-0 bg-slate-900 p-6 border-b border-slate-800 flex justify-between items-center z-10">

                            <h2 class="text-2xl font-bold text-white">
                                Detail Buku
                            </h2>

                            <button type="button"
                                onclick="closeDetail('modal-<?= $id_buku; ?>')"
                                class="text-slate-400 hover:text-white text-3xl">
                                &times;
                            </button>

                        </div>

                        <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">

                            <img src="../../assets/img/cover/<?= htmlspecialchars($row['cover']); ?>"
                                class="w-full h-96 object-cover rounded-2xl border border-slate-700">

                            <div class="md:col-span-2 space-y-4">

                                <h3 class="text-3xl font-bold text-white">
                                    <?= htmlspecialchars($row['judul']); ?>
                                </h3>

                                <p class="text-slate-400">
                                    <?= htmlspecialchars($row['nama_kategori'] ?? 'Tanpa Kategori'); ?>
                                </p>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">

                                    <div class="bg-slate-800 rounded-xl p-4">
                                        <p class="text-slate-400">Penulis</p>
                                        <p class="text-white font-semibold mt-1">
                                            <?= htmlspecialchars($row['nama_penulis'] ?? '-'); ?>
                                        </p>
                                    </div>

                                    <div class="bg-slate-800 rounded-xl p-4">
                                        <p class="text-slate-400">Penerbit</p>
                                        <p class="text-white font-semibold mt-1">
                                            <?= htmlspecialchars($row['nama_penerbit'] ?? '-'); ?>
                                        </p>
                                    </div>

                                    <div class="bg-slate-800 rounded-xl p-4">
                                        <p class="text-slate-400">Tahun</p>
                                        <p class="text-white font-semibold mt-1">
                                            <?= htmlspecialchars($row['tahun']); ?>
                                        </p>
                                    </div>

                                    <div class="bg-slate-800 rounded-xl p-4">
                                        <p class="text-slate-400">ISBN</p>
                                        <p class="text-white font-semibold mt-1">
                                            <?= htmlspecialchars($row['isbn'] ?? '-'); ?>
                                        </p>
                                    </div>

                                    <div class="bg-slate-800 rounded-xl p-4">
                                        <p class="text-slate-400">Rating</p>
                                        <p class="text-yellow-400 font-semibold mt-1">
                                            ⭐ <?= $rating > 0 ? $rating : '0.0'; ?>/5
                                            <span class="text-slate-400">
                                                (<?= $row['jumlah_ulasan']; ?> ulasan)
                                            </span>
                                        </p>
                                    </div>

                                    <div class="bg-slate-800 rounded-xl p-4">
                                        <p class="text-slate-400">Stok</p>

                                        <?php if ($row['stok'] > 0): ?>
                                            <p class="text-emerald-400 font-semibold mt-1">
                                                <?= $row['stok']; ?> Buku
                                            </p>
                                        <?php else: ?>
                                            <p class="text-red-400 font-semibold mt-1">
                                                Stok Habis
                                            </p>
                                        <?php endif; ?>
                                    </div>

                                </div>

                                <div class="bg-slate-800 rounded-xl p-4">
                                    <p class="text-slate-400 mb-2">Deskripsi</p>
                                    <p class="text-slate-300 leading-relaxed">
                                        <?= nl2br(htmlspecialchars($row['deskripsi'] ?? 'Tidak ada deskripsi.')); ?>
                                    </p>
                                </div>

                                <div class="flex flex-wrap gap-3">

                                    <?php if ($sedang_dipinjam): ?>

                                        <button type="button"
                                            disabled
                                            class="bg-slate-700 text-slate-400 px-5 py-3 rounded-xl font-semibold cursor-not-allowed">
                                            Sedang Dipinjam
                                        </button>

                                    <?php elseif ($row['stok'] <= 0): ?>

                                        <button type="button"
                                            disabled
                                            class="bg-red-500/10 text-red-400 px-5 py-3 rounded-xl font-semibold cursor-not-allowed">
                                            Stok Habis
                                        </button>

                                    <?php else: ?>

                                        <button type="button"
                                            onclick="pinjamBuku(<?= $id_buku; ?>)"
                                            class="bg-blue-500 hover:bg-blue-600 text-white px-5 py-3 rounded-xl font-semibold">
                                            Pinjam Buku
                                        </button>

                                    <?php endif; ?>

                                    <button type="button"
                                        onclick="closeDetail('modal-<?= $id_buku; ?>')"
                                        class="bg-slate-700 hover:bg-slate-600 text-white px-5 py-3 rounded-xl font-semibold">
                                        Tutup
                                    </button>

                                </div>

                                <!-- ULASAN DENGAN PAGINATION -->
                                <div class="bg-slate-800 rounded-xl p-4">

                                    <div class="flex items-center justify-between mb-4">

                                        <h4 class="text-white font-bold">
                                            Ulasan Anggota
                                        </h4>

                                        <span class="text-xs text-slate-400">
                                            <?= $row['jumlah_ulasan']; ?> ulasan
                                        </span>

                                    </div>

                                    <?php if ($queryUlasan && mysqli_num_rows($queryUlasan) > 0): ?>

                                        <?php
                                        $ulasan_no = 0;
                                        $per_page_ulasan = 5;
                                        $total_ulasan_modal = mysqli_num_rows($queryUlasan);
                                        $total_page_ulasan = ceil($total_ulasan_modal / $per_page_ulasan);
                                        ?>

                                        <div class="space-y-4" id="ulasan-list-<?= $id_buku; ?>">

                                            <?php while ($u = mysqli_fetch_assoc($queryUlasan)): ?>

                                                <?php
                                                $ulasan_no++;
                                                $page_ulasan = ceil($ulasan_no / $per_page_ulasan);
                                                ?>

                                                <div class="ulasan-item-<?= $id_buku; ?> border-b border-slate-700 pb-4 last:border-b-0 last:pb-0 <?= $page_ulasan == 1 ? '' : 'hidden'; ?>"
                                                    data-page="<?= $page_ulasan; ?>">

                                                    <h5 class="text-white font-semibold">
                                                        <?= htmlspecialchars($u['nama']); ?>
                                                    </h5>

                                                    <p class="text-yellow-400 text-sm mt-1">
                                                        <?= str_repeat('⭐', (int) $u['rating']); ?>
                                                        <span class="text-slate-400">
                                                            <?= $u['rating']; ?>/5
                                                        </span>
                                                    </p>

                                                    <p class="text-slate-300 mt-2">
                                                        <?= nl2br(htmlspecialchars($u['ulasan'])); ?>
                                                    </p>

                                                </div>

                                            <?php endwhile; ?>

                                        </div>

                                        <?php if ($total_page_ulasan > 1): ?>

                                            <div class="flex flex-wrap gap-2 mt-5">

                                                <?php for ($i = 1; $i <= $total_page_ulasan; $i++): ?>

                                                    <button type="button"
                                                        onclick="showUlasanPage(<?= $id_buku; ?>, <?= $i; ?>)"
                                                        id="btn-ulasan-<?= $id_buku; ?>-<?= $i; ?>"
                                                        class="ulasan-btn-<?= $id_buku; ?> px-3 py-2 rounded-lg text-xs font-semibold transition
                                                        <?= $i == 1
                                                            ? 'bg-blue-500 text-white'
                                                            : 'bg-slate-700 text-slate-300 hover:bg-slate-600'; ?>">
                                                        <?= $i; ?>
                                                    </button>

                                                <?php endfor; ?>

                                            </div>

                                        <?php endif; ?>

                                    <?php else: ?>

                                        <p class="text-slate-500">
                                            Belum ada ulasan.
                                        </p>

                                    <?php endif; ?>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <div class="col-span-full text-center py-20 text-slate-500">
                Buku tidak ditemukan.
            </div>

        <?php endif; ?>

    </div>

</div>

<script>
    function openDetail(id) {
        const modal = document.getElementById(id);
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeDetail(id) {
        const modal = document.getElementById(id);
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function showUlasanPage(idBuku, page) {
        const items = document.querySelectorAll('.ulasan-item-' + idBuku);
        const buttons = document.querySelectorAll('.ulasan-btn-' + idBuku);

        items.forEach(item => {
            if (item.dataset.page == page) {
                item.classList.remove('hidden');
            } else {
                item.classList.add('hidden');
            }
        });

        buttons.forEach(btn => {
            btn.classList.remove('bg-blue-500', 'text-white');
            btn.classList.add('bg-slate-700', 'text-slate-300');
        });

        const activeBtn = document.getElementById('btn-ulasan-' + idBuku + '-' + page);

        if (activeBtn) {
            activeBtn.classList.remove('bg-slate-700', 'text-slate-300');
            activeBtn.classList.add('bg-blue-500', 'text-white');
        }
    }

    function pinjamBuku(id) {
        Swal.fire({
            title: 'Pinjam Buku?',
            text: 'Buku akan masuk ke daftar peminjaman kamu.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3b82f6',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Pinjam',
            cancelButtonText: 'Batal',
            background: '#0f172a',
            color: '#fff',
            customClass: {
                popup: 'rounded-3xl border border-slate-800 shadow-2xl',
                title: 'text-white font-bold',
                htmlContainer: 'text-slate-300',
                confirmButton: 'rounded-xl px-5 py-2 font-semibold',
                cancelButton: 'rounded-xl px-5 py-2 font-semibold'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location = '../peminjaman/pinjam.php?id=' + id;
            }
        });
    }
</script>

<?php include __DIR__ . "/../layout/footer.php"; ?>