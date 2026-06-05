<?php
session_start();
include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../middleware/petugas.php";
include __DIR__ . "/../layout/header.php";

// PROCESS: HAPUS ULASAN
if (isset($_POST['hapus_ulasan'])) {
    $id_ulasan = (int)$_POST['id_ulasan'];
    $keterangan_petugas = trim($_POST['keterangan_petugas']);

    if ($id_ulasan <= 0 || empty($keterangan_petugas)) {
        $_SESSION['error'] = "Keterangan penghapusan wajib diisi.";
        header("Location: index.php");
        exit;
    }

    $cekUlasan = mysqli_query($conn, "SELECT id_ulasan FROM ulasan WHERE id_ulasan = '$id_ulasan' LIMIT 1");

    if (!$cekUlasan || mysqli_num_rows($cekUlasan) == 0) {
        $_SESSION['error'] = "Ulasan tidak ditemukan.";
        header("Location: index.php");
        exit;
    }

    $hapus = mysqli_query($conn, "DELETE FROM ulasan WHERE id_ulasan = '$id_ulasan'");
    if ($hapus) {
        $_SESSION['success'] = "Ulasan berhasil dihapus oleh petugas.";
    } else {
        $_SESSION['error'] = "Gagal menghapus ulasan.";
    }

    header("Location: index.php");
    exit;
}

// SEARCH & FILTER SANITIZATION
$search_judul    = isset($_GET['search_judul']) ? mysqli_real_escape_string($conn, trim($_GET['search_judul'])) : '';
$filter_kategori = isset($_GET['filter_kategori']) ? mysqli_real_escape_string($conn, $_GET['filter_kategori']) : '';
$filter_stok     = isset($_GET['filter_stok']) ? mysqli_real_escape_string($conn, $_GET['filter_stok']) : '';
$filter_rating   = isset($_GET['filter_rating']) ? mysqli_real_escape_string($conn, $_GET['filter_rating']) : '';

$listKategori = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori ASC");

// MAIN QUERY BUKU + RATINGS
$sql = "SELECT buku.*, kategori.nama_kategori, penulis.nama_penulis, penerbit.nama_penerbit,
               COALESCE(rating_data.rata_rating, 0) AS rata_rating,
               COALESCE(rating_data.jumlah_ulasan, 0) AS jumlah_ulasan
        FROM buku
        LEFT JOIN kategori ON buku.id_kategori = kategori.id_kategori
        LEFT JOIN penulis ON buku.id_penulis = penulis.id_penulis
        LEFT JOIN penerbit ON buku.id_penerbit = penerbit.id_penerbit
        LEFT JOIN (
            SELECT id_buku, AVG(rating) AS rata_rating, COUNT(id_ulasan) AS jumlah_ulasan
            FROM ulasan
            GROUP BY id_buku
        ) AS rating_data ON buku.id_buku = rating_data.id_buku";

$where = [];
if (!empty($search_judul))    $where[] = "buku.judul LIKE '%$search_judul%'";
if (!empty($filter_kategori)) $where[] = "buku.id_kategori = '$filter_kategori'";
if ($filter_stok == 'tersedia') $where[] = "buku.stok > 0";
if ($filter_stok == 'habis')    $where[] = "buku.stok <= 0";

if (count($where) > 0) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

if ($filter_rating == 'tertinggi') {
    $sql .= " ORDER BY rata_rating DESC, buku.id_buku DESC";
} elseif ($filter_rating == 'terendah') {
    $sql .= " ORDER BY rata_rating ASC, buku.id_buku DESC";
} else {
    $sql .= " ORDER BY buku.id_buku DESC";
}

$query = mysqli_query($conn, $sql);
if (!$query) {
    die("Query Error: " . mysqli_error($conn));
}

// QUERY GLOBAL ULASAN FOR MODALS
$queryUlasan = mysqli_query($conn, "SELECT ulasan.*, user.nama FROM ulasan JOIN user ON ulasan.id_user = user.id_user ORDER BY ulasan.id_ulasan DESC");
$ulasanByBuku = [];
if ($queryUlasan) {
    while ($ulasan = mysqli_fetch_assoc($queryUlasan)) {
        $ulasanByBuku[$ulasan['id_buku']][] = $ulasan;
    }
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
    select option { background: #1e293b !important; color: white !important; }
</style>

<?php if (isset($_SESSION['success'])): ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({ icon: 'success', title: 'Berhasil', text: '<?= $_SESSION['success']; ?>', background: '#0f172a', color: '#fff', confirmButtonColor: '#3b82f6' });
    });
</script>
<?php unset($_SESSION['success']); endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({ icon: 'error', title: 'Gagal', text: '<?= $_SESSION['error']; ?>', background: '#0f172a', color: '#fff', confirmButtonColor: '#ef4444' });
    });
</script>
<?php unset($_SESSION['error']); endif; ?>

<div class="space-y-6">
    <div class="flex justify-between items-center flex-wrap gap-4">
        <div>
            <h1 class="text-3xl font-bold text-white">Data Buku</h1>
            <p class="text-slate-400 mt-1">Kelola data buku, rating, dan ulasan anggota</p>
        </div>
        <a href="tambah.php" class="bg-blue-500 hover:bg-blue-600 transition px-5 py-3 rounded-2xl text-white font-semibold flex items-center gap-2 shadow-lg">
            <i class="fa-solid fa-plus"></i> Tambah Buku
        </a>
    </div>

    <div class="bg-slate-900 p-4 rounded-2xl border border-slate-800">
        <form method="GET" class="grid lg:grid-cols-5 gap-3">
            <div class="flex items-center bg-slate-800 rounded-xl px-4 py-3">
                <i class="fa-solid fa-book text-slate-400 mr-3"></i>
                <input type="text" name="search_judul" placeholder="Cari judul buku..." value="<?= htmlspecialchars($search_judul); ?>" class="bg-transparent outline-none text-white w-full placeholder:text-slate-500">
            </div>

            <div class="flex items-center bg-slate-800 rounded-xl px-4 py-3">
                <i class="fa-solid fa-layer-group text-slate-400 mr-3"></i>
                <select name="filter_kategori" class="bg-transparent outline-none text-white w-full">
                    <option value="">Semua Kategori</option>
                    <?php while ($kategori = mysqli_fetch_assoc($listKategori)): ?>
                        <option value="<?= $kategori['id_kategori']; ?>" <?= ($filter_kategori == $kategori['id_kategori']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($kategori['nama_kategori']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <select name="filter_stok" class="bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none">
                <option value="">Semua Stok</option>
                <option value="tersedia" <?= ($filter_stok == 'tersedia') ? 'selected' : ''; ?>>Stok Tersedia</option>
                <option value="habis" <?= ($filter_stok == 'habis') ? 'selected' : ''; ?>>Stok Habis</option>
            </select>

            <select name="filter_rating" class="bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none">
                <option value="">Urutkan Rating</option>
                <option value="tertinggi" <?= ($filter_rating == 'tertinggi') ? 'selected' : ''; ?>>Rating Tertinggi</option>
                <option value="terendah" <?= ($filter_rating == 'terendah') ? 'selected' : ''; ?>>Rating Terendah</option>
            </select>

            <div class="flex gap-3">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 transition px-5 py-3 rounded-xl text-white font-semibold flex-1">Cari</button>
                <a href="index.php" class="bg-red-500 hover:bg-red-600 transition px-5 py-3 rounded-xl text-white flex items-center justify-center">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto bg-slate-900 rounded-2xl border border-slate-800 shadow-xl">
        <table class="w-full text-sm text-left text-slate-300">
            <thead class="bg-slate-800 text-slate-200 uppercase text-xs">
                <tr>
                    <th class="px-6 py-4">Cover</th>
                    <th class="px-6 py-4">Informasi Buku</th>
                    <th class="px-6 py-4">Kategori</th>
                    <th class="px-6 py-4">Harga</th>
                    <th class="px-6 py-4">Rating</th>
                    <th class="px-6 py-4">Stok</th>
                    <th class="px-6 py-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($query) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($query)): 
                        $id_buku = $row['id_buku'];
                        $rata_rating = round($row['rata_rating'], 1);
                        $jumlah_ulasan = (int)$row['jumlah_ulasan'];
                    ?>
                        <tr class="border-b border-slate-800 hover:bg-slate-800/50 transition">
                            <td class="px-6 py-4">
                                <img src="../../assets/img/cover/<?= htmlspecialchars($row['cover']); ?>" class="w-16 h-24 object-cover rounded-xl border border-slate-700 shadow-lg">
                            </td>
                            <td class="px-6 py-4">
                                <h2 class="text-white font-semibold text-base"><?= htmlspecialchars($row['judul']); ?></h2>
                                <div class="space-y-1 mt-2 text-xs">
                                    <p class="text-slate-400"><span class="text-slate-500">Penulis :</span> <?= htmlspecialchars($row['nama_penulis'] ?? '-'); ?></p>
                                    <p class="text-slate-400"><span class="text-slate-500">Penerbit :</span> <?= htmlspecialchars($row['nama_penerbit'] ?? '-'); ?></p>
                                    <p class="text-slate-500">Tahun : <?= htmlspecialchars($row['tahun']); ?></p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="bg-blue-500/10 text-blue-400 px-3 py-1 rounded-full text-xs font-semibold">
                                    <?= htmlspecialchars($row['nama_kategori'] ?? 'Tanpa Kategori'); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-emerald-400 font-bold text-lg">Rp <?= number_format($row['harga'], 0, ',', '.'); ?></span>
                                    <span class="text-xs text-slate-500">Harga Buku</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <button type="button" onclick="openUlasan('modal-ulasan-<?= $id_buku; ?>')" class="bg-yellow-500/10 hover:bg-yellow-500/20 transition text-yellow-400 px-3 py-2 rounded-xl text-sm font-semibold">
                                    ⭐ <?= $rata_rating > 0 ? $rata_rating : '0.0'; ?> <span class="text-slate-500">(<?= $jumlah_ulasan; ?>)</span>
                                </button>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($row['stok'] > 0): ?>
                                    <span class="bg-emerald-500/10 text-emerald-400 px-3 py-1 rounded-full text-xs font-semibold"><?= $row['stok']; ?> Buku</span>
                                <?php else: ?>
                                    <span class="bg-red-500/10 text-red-400 px-3 py-1 rounded-full text-xs font-semibold">Stok Habis</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-5">
                                    <a href="edit.php?id=<?= $row['id_buku']; ?>" class="text-blue-400 hover:text-blue-300 text-xl transition" title="Edit Buku">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <button type="button" onclick="hapusBuku(<?= $row['id_buku']; ?>)" class="text-red-400 hover:text-red-300 text-xl transition" title="Hapus Buku">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <div id="modal-ulasan-<?= $id_buku; ?>" class="fixed inset-0 bg-black/70 hidden z-50 items-center justify-center p-4">
                            <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-3xl max-h-[85vh] overflow-hidden">
                                <div class="p-6 border-b border-slate-800 flex justify-between items-center">
                                    <div>
                                        <h2 class="text-xl font-bold text-white">Ulasan Anggota</h2>
                                        <p class="text-slate-400 text-sm mt-1"><?= htmlspecialchars($row['judul']); ?></p>
                                    </div>
                                    <button type="button" onclick="closeUlasan('modal-ulasan-<?= $id_buku; ?>')" class="text-slate-400 hover:text-white text-2xl">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                                <div class="p-6 overflow-y-auto max-h-[65vh] space-y-4">
                                    <?php if (!empty($ulasanByBuku[$id_buku])): ?>
                                        <?php foreach ($ulasanByBuku[$id_buku] as $ulasan): ?>
                                            <div class="bg-slate-800 border border-slate-700 rounded-2xl p-5">
                                                <div class="flex justify-between items-start gap-4">
                                                    <div>
                                                        <h3 class="text-white font-semibold"><?= htmlspecialchars($ulasan['nama']); ?></h3>
                                                        <p class="text-yellow-400 text-sm mt-1">
                                                            <?= str_repeat('⭐', (int)$ulasan['rating']); ?>
                                                            <span class="text-slate-400"><?= $ulasan['rating']; ?>/5</span>
                                                        </p>
                                                        <p class="text-slate-500 text-xs mt-1"><?= date('d-m-Y H:i', strtotime($ulasan['tanggal_ulasan'])); ?></p>
                                                    </div>
                                                    <button type="button" onclick="hapusUlasan(<?= $ulasan['id_ulasan']; ?>)" class="bg-red-500/10 hover:bg-red-500/20 transition text-red-400 px-3 py-2 rounded-xl text-xs font-semibold">Hapus</button>
                                                </div>
                                                <p class="text-slate-300 text-sm mt-4 leading-relaxed"><?= nl2br(htmlspecialchars($ulasan['ulasan'])); ?></p>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-center py-10 text-slate-500">Belum ada ulasan dari anggota.</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center py-12 text-slate-400">Tidak ada data buku</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<form method="POST" id="formHapusUlasan">
    <input type="hidden" name="hapus_ulasan" value="1">
    <input type="hidden" name="id_ulasan" id="id_ulasan">
    <input type="hidden" name="keterangan_petugas" id="keterangan_petugas">
</form>

<script>
function openUlasan(id) {
    const modal = document.getElementById(id);
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeUlasan(id) {
    const modal = document.getElementById(id);
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function hapusBuku(id) {
    Swal.fire({
        title: 'Hapus Buku?',
        text: 'Cover dan data buku akan dihapus permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        background: '#0f172a',
        color: '#fff'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location = 'hapus.php?id=' + id;
        }
    });
}

function hapusUlasan(id) {
    Swal.fire({
        title: 'Hapus Ulasan?',
        input: 'textarea',
        inputLabel: 'Keterangan Petugas',
        inputPlaceholder: 'Contoh: ulasan spam / tidak sesuai / tidak sopan...',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Hapus Ulasan',
        cancelButtonText: 'Batal',
        background: '#0f172a',
        color: '#fff',
        inputValidator: (value) => {
            if (!value || value.trim() === '') {
                return 'Keterangan wajib diisi.';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('id_ulasan').value = id;
            document.getElementById('keterangan_petugas').value = result.value;
            document.getElementById('formHapusUlasan').submit();
        }
    });
}
</script>

<?php include __DIR__ . "/../layout/footer.php"; ?>