<?php
session_start();
include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../middleware/admin.php";
include __DIR__ .  "/../../admin/layout/header.php";

// ==========================
// HAPUS ULASAN
// ==========================

if (isset($_GET['hapus_ulasan'])) {

    $id_ulasan = mysqli_real_escape_string($conn, $_GET['hapus_ulasan']);

    mysqli_query($conn, "
        DELETE FROM ulasan 
        WHERE id_ulasan = '$id_ulasan'
    ");

    echo "
    <script>
        alert('Ulasan berhasil dihapus!');
        window.location='../buku/ulasan.php';
    </script>
    ";
}

// ==========================
// SEARCH & FILTER
// ==========================

$search_judul = isset($_GET['search_judul'])
    ? mysqli_real_escape_string($conn, $_GET['search_judul'])
    : '';

$filter_kategori = isset($_GET['filter_kategori'])
    ? mysqli_real_escape_string($conn, $_GET['filter_kategori'])
    : '';

$filter_rating = isset($_GET['filter_rating'])
    ? mysqli_real_escape_string($conn, $_GET['filter_rating'])
    : '';

// ==========================
// QUERY KATEGORI
// ==========================

$listKategori = mysqli_query($conn, "
    SELECT * FROM kategori
    ORDER BY nama_kategori ASC
");

// ==========================
// QUERY BUKU
// ==========================

$sql = "
    SELECT 
        buku.*, 
        kategori.nama_kategori,
        penulis.nama_penulis,
        penerbit.nama_penerbit,
        AVG(ulasan.rating) AS rata_rating,
        COUNT(ulasan.id_ulasan) AS jml_ulasan

    FROM buku

    LEFT JOIN kategori
        ON buku.id_kategori = kategori.id_kategori

    LEFT JOIN penulis
        ON buku.id_penulis = penulis.id_penulis

    LEFT JOIN penerbit
        ON buku.id_penerbit = penerbit.id_penerbit

    LEFT JOIN ulasan
        ON buku.id_buku = ulasan.id_buku
";

$where = [];

// Search Judul
if (!empty($search_judul)) {
    $where[] = "buku.judul LIKE '%$search_judul%'";
}

// Filter Kategori
if (!empty($filter_kategori)) {
    $where[] = "buku.id_kategori = '$filter_kategori'";
}

// Gabung WHERE
if (count($where) > 0) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

// Grouping
$sql .= " GROUP BY buku.id_buku";

// Sorting Rating
if ($filter_rating == 'tertinggi') {

    $sql .= " ORDER BY rata_rating DESC";
} elseif ($filter_rating == 'terendah') {

    $sql .= " ORDER BY rata_rating ASC";
} else {

    $sql .= " ORDER BY buku.id_buku DESC";
}

$query = mysqli_query($conn, $sql);
?>

<!-- Tailwind -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- HEADER -->
<div class="flex justify-between items-center mb-6">

    <div>

        <h2 class="text-3xl font-bold text-white">
            Koleksi Buku
        </h2>

        <p class="text-slate-400 text-sm mt-1">
            Kelola data buku, ISBN, dan ulasan pembaca.
        </p>

    </div>

    <a href="tambah.php"
        class="bg-blue-500 hover:bg-blue-600 transition px-4 py-2 rounded-xl text-white flex items-center gap-2 shadow-lg">

        <i class='bx bx-plus text-xl'></i>

        Tambah Buku

    </a>

</div>

<!-- SEARCH & FILTER -->
<div class="bg-slate-900 p-4 rounded-2xl border border-slate-800 mb-6">

    <form method="GET"
        class="flex flex-col lg:flex-row gap-3">

        <!-- Search Judul -->
        <div class="flex items-center bg-slate-800 rounded-xl px-4 py-3 flex-1">

            <i class='bx bx-book text-slate-400 text-xl mr-2'></i>

            <input type="text"
                name="search_judul"
                placeholder="Cari judul buku..."
                value="<?= htmlspecialchars($search_judul); ?>"
                class="bg-transparent outline-none text-white w-full placeholder:text-slate-500">

        </div>

        <!-- Filter Kategori -->
        <div class="flex items-center bg-slate-800 rounded-xl px-4 py-3 flex-1 gap-2">

            <i class='bx bx-category text-slate-400 text-xl'></i>

            <select name="filter_kategori"
                class="bg-transparent outline-none text-white w-full">

                <option value="" class="text-black">
                    Semua Kategori
                </option>

                <?php while ($kategori = mysqli_fetch_assoc($listKategori)): ?>

                    <option value="<?= $kategori['id_kategori']; ?>"
                        class="text-black"
                        <?= ($filter_kategori == $kategori['id_kategori']) ? 'selected' : ''; ?>>

                        <?= htmlspecialchars($kategori['nama_kategori']); ?>

                    </option>

                <?php endwhile; ?>

            </select>

        </div>

        <!-- Filter Rating -->
        <select name="filter_rating"
            class="bg-slate-800 text-white rounded-xl px-4 py-3 outline-none border border-slate-700">

            <option value="">
                Urutkan Rating
            </option>

            <option value="tertinggi"
                <?= ($filter_rating == 'tertinggi') ? 'selected' : ''; ?>>

                Rating Tertinggi

            </option>

            <option value="terendah"
                <?= ($filter_rating == 'terendah') ? 'selected' : ''; ?>>

                Rating Terendah

            </option>

        </select>

        <!-- Button Cari -->
        <button type="submit"
            class="bg-blue-500 hover:bg-blue-600 transition text-white px-5 py-3 rounded-xl font-medium">

            Cari

        </button>

        <!-- Reset -->
        <?php if (
            !empty($search_judul) ||
            !empty($filter_kategori) ||
            !empty($filter_rating)
        ): ?>

            <a href="buku.php"
                class="bg-red-500 hover:bg-red-600 transition text-white px-4 py-3 rounded-xl flex items-center justify-center">

                <i class='bx bx-x text-xl'></i>

            </a>

        <?php endif; ?>

    </form>

</div>

<!-- TABLE -->
<div class="overflow-x-auto bg-slate-900 rounded-2xl border border-slate-800 shadow-xl">

    <table class="w-full text-sm text-left text-slate-300">

        <thead class="bg-slate-800 text-slate-200 uppercase text-xs">

            <tr>

                <th class="px-6 py-4">No</th>
                <th class="px-6 py-4">Cover</th>
                <th class="px-6 py-4">Informasi Buku</th>
                <th class="px-6 py-4">ISBN</th>
                <th class="px-6 py-4">Kategori</th>
                <th class="px-6 py-4">Harga</th>
                <th class="px-6 py-4">Rating</th>
                <th class="px-6 py-4">Stok</th>
                <th class="px-6 py-4">Aksi</th>

            </tr>

        </thead>

        <tbody>

            <?php
            $no = 1;

            if (mysqli_num_rows($query) > 0):

                while ($row = mysqli_fetch_assoc($query)):
            ?>

                    <tr class="border-b border-slate-800 hover:bg-slate-800/50 transition">

                        <!-- NO -->
                        <td class="px-6 py-4 text-slate-400">
                            <?= $no++; ?>
                        </td>

                        <!-- COVER -->
                        <td class="px-6 py-4">

                            <img src="../../assets/img/cover/<?= $row['cover']; ?>"
                                class="w-16 h-24 object-cover rounded-lg shadow-md">

                        </td>

                        <!-- INFO -->
                        <td class="px-6 py-4">

                            <div class="font-semibold text-white text-base">
                                <?= htmlspecialchars($row['judul']); ?>
                            </div>

                            <div class="text-xs text-slate-400 mt-1">
                                Penulis :
                                <?= htmlspecialchars($row['nama_penulis'] ?? '-'); ?>
                            </div>

                            <div class="text-xs text-slate-500 mt-1">
                                Penerbit :
                                <?= htmlspecialchars($row['nama_penerbit'] ?? '-'); ?>
                            </div>

                        </td>

                        <!-- ISBN -->
                        <td class="px-6 py-4 text-slate-300">
                            <?= $row['isbn'] ?: '-'; ?>
                        </td>

                        <!-- KATEGORI -->
                        <td class="px-6 py-4">

                            <span class="bg-blue-500/20 text-blue-400 px-3 py-1 rounded-full text-xs flex items-center gap-1 w-fit">

                                <i class='bx bx-tag-alt'></i>

                                <?= $row['nama_kategori'] ?? 'Tanpa Kategori'; ?>

                            </span>

                        </td>

                        <!-- HARGA -->
                        <td class="px-6 py-4">

                            <div class="flex flex-col">

                                <span class="text-lg font-bold text-emerald-400">
                                    Rp <?= number_format($row['harga'], 0, ',', '.'); ?>
                                </span>

                                <span class="text-xs text-slate-400">
                                    Harga Buku
                                </span>

                            </div>

                        </td>

                        <!-- RATING -->
                        <td class="px-6 py-4">

                            <a href="../buku/ulasan.php?id_buku=<?= $row['id_buku']; ?>"
                                class="bg-yellow-500/10 hover:bg-yellow-500/20 transition px-3 py-2 rounded-lg text-yellow-400 text-sm w-fit inline-block">

                                ⭐ <?= number_format($row['rata_rating'] ?: 0, 1); ?>

                                <span class="text-slate-500">
                                    (<?= $row['jml_ulasan']; ?>)
                                </span>

                            </a>

                        </td>

                        <!-- STOK -->
                        <td class="px-6 py-4">

                            <div class="flex flex-col">

                                <span class="text-lg font-bold text-white">
                                    <?= $row['stok']; ?>
                                </span>

                                <span class="text-xs text-slate-400">
                                    Unit
                                </span>

                            </div>

                        </td>

                        <!-- AKSI -->
                        <td class="px-6 py-4">

                            <div class="flex items-center gap-4">

                                <a href="../peminjaman/tambah.php?id_buku=<?= $row['id_buku']; ?>"
                                    class="text-emerald-400 hover:text-emerald-300 text-2xl"
                                    title="Pinjam Buku">

                                    <i class='bx bxs-bookmark-plus'></i>

                                </a>

                                <a href="edit.php?id=<?= $row['id_buku']; ?>"
                                    class="text-blue-400 hover:text-blue-300 text-2xl"
                                    title="Edit">

                                    <i class='bx bx-edit-alt'></i>

                                </a>

                                <a href="hapus.php?id=<?= $row['id_buku']; ?>"
                                    onclick="return confirm('Hapus buku ini?')"
                                    class="text-red-400 hover:text-red-300 text-2xl"
                                    title="Hapus">

                                    <i class='bx bx-trash'></i>

                                </a>

                            </div>

                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>

                    <td colspan="9"
                        class="text-center py-10 text-slate-400">

                        Data buku tidak ditemukan.

                    </td>

                </tr>

            <?php endif; ?>

        </tbody>

    </table>

</div>

<?php include __DIR__ .  "/../../admin/layout/footer.php"; ?>