<?php
session_start();

include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../middleware/admin.php";

// ==========================
// HAPUS ULASAN
// ==========================
if (isset($_GET['hapus'])) {

    $id_ulasan = (int) $_GET['hapus'];

    $cek = mysqli_query($conn, "
        SELECT id_ulasan
        FROM ulasan
        WHERE id_ulasan = '$id_ulasan'
        LIMIT 1
    ");

    if (mysqli_num_rows($cek) == 0) {

        $_SESSION['error'] = "Ulasan tidak ditemukan.";

    } else {

        $hapus = mysqli_query($conn, "
            DELETE FROM ulasan
            WHERE id_ulasan = '$id_ulasan'
        ");

        if ($hapus) {
            $_SESSION['success'] = "Ulasan berhasil dihapus.";
        } else {
            $_SESSION['error'] = "Ulasan gagal dihapus.";
        }
    }

    header("Location: index.php");
    exit;
}

include __DIR__ . "/../../admin/layout/header.php";

// ==========================
// AMBIL SEMUA ULASAN
// ==========================
$query = mysqli_query($conn, "
    SELECT 
        uls.id_ulasan,
        uls.id_user,
        uls.id_buku,
        uls.rating,
        uls.ulasan,
        uls.tanggal_ulasan,

        user.nama AS nama_user,
        user.email,

        buku.judul,
        buku.cover,

        COALESCE(penulis.nama_penulis, '-') AS nama_penulis,
        COALESCE(penerbit.nama_penerbit, '-') AS nama_penerbit,
        COALESCE(kategori.nama_kategori, '-') AS nama_kategori,

        (
            SELECT COUNT(*)
            FROM ulasan u2
            WHERE u2.id_buku = uls.id_buku
        ) AS total_ulasan_buku

    FROM ulasan uls

    JOIN user
        ON uls.id_user = user.id_user

    JOIN buku
        ON uls.id_buku = buku.id_buku

    LEFT JOIN penulis
        ON buku.id_penulis = penulis.id_penulis

    LEFT JOIN penerbit
        ON buku.id_penerbit = penerbit.id_penerbit

    LEFT JOIN kategori
        ON buku.id_kategori = kategori.id_kategori

    ORDER BY buku.judul ASC, uls.tanggal_ulasan DESC, uls.id_ulasan DESC
");

if (!$query) {
    die("Query Error: " . mysqli_error($conn));
}

// ==========================
// DATA STATISTIK
// ==========================
$q_total_ulasan = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM ulasan
");

$total_ulasan = mysqli_fetch_assoc($q_total_ulasan)['total'] ?? 0;

$q_total_buku_diulas = mysqli_query($conn, "
    SELECT COUNT(DISTINCT id_buku) AS total
    FROM ulasan
");

$total_buku_diulas = mysqli_fetch_assoc($q_total_buku_diulas)['total'] ?? 0;
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
                Semua Ulasan
            </h1>

            <p class="text-slate-400 mt-2">
                Daftar semua ulasan anggota yang dikelompokkan berdasarkan buku.
            </p>
        </div>

        <a href="../dashboard/index.php"
            class="bg-slate-700 hover:bg-slate-600 transition px-5 py-3 rounded-2xl text-white font-semibold w-fit flex items-center gap-2">

            <i class='bx bx-arrow-back'></i>
            Kembali

        </a>

    </div>

    <!-- CARD INFO -->
    <div class="grid md:grid-cols-2 gap-5">

        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl">
            <p class="text-slate-400 text-sm">
                Total Ulasan
            </p>

            <h2 class="text-4xl font-bold text-white mt-2">
                <?= $total_ulasan; ?>
            </h2>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl">
            <p class="text-slate-400 text-sm">
                Buku Diulas
            </p>

            <h2 class="text-4xl font-bold text-blue-400 mt-2">
                <?= $total_buku_diulas; ?>
            </h2>
        </div>

    </div>

    <!-- ULASAN GROUP BY BUKU -->
    <div class="space-y-6">

        <?php if (mysqli_num_rows($query) > 0): ?>

            <?php
            $current_buku = null;
            $no_group = 0;

            while ($row = mysqli_fetch_assoc($query)):
            ?>

                <?php if ($current_buku != $row['id_buku']): ?>

                    <?php if ($current_buku !== null): ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php
                    $current_buku = $row['id_buku'];
                    $no_group++;

                    $id_dropdown = "dropdown-buku-" . $no_group;
                    $id_icon = "icon-buku-" . $no_group;

                    $cover = !empty($row['cover']) ? $row['cover'] : 'default.jpg';
                    ?>

                    <div class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">

                        <!-- HEADER DROPDOWN -->
                        <button
                            type="button"
                            onclick="toggleDropdown('<?= $id_dropdown; ?>', '<?= $id_icon; ?>')"
                            class="w-full bg-slate-800/70 hover:bg-slate-800 transition border-b border-slate-700 p-6 text-left">

                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">

                                <div class="flex items-start gap-5">

                                    <img 
                                        src="../../assets/img/cover/<?= htmlspecialchars($cover); ?>"
                                        class="w-20 h-28 object-cover rounded-2xl border border-slate-700 shadow"
                                        alt="Cover Buku">

                                    <div>
                                        <h2 class="text-2xl font-bold text-white">
                                            <?= htmlspecialchars($row['judul']); ?>
                                        </h2>

                                        <p class="text-slate-400 text-sm mt-2">
                                            <?= htmlspecialchars($row['nama_penulis']); ?> 
                                            • 
                                            <?= htmlspecialchars($row['nama_penerbit']); ?>
                                        </p>

                                        <span class="inline-block mt-3 bg-blue-500/10 text-blue-400 border border-blue-500/20 px-3 py-1 rounded-full text-xs font-semibold">
                                            <?= htmlspecialchars($row['nama_kategori']); ?>
                                        </span>
                                    </div>

                                </div>

                                <div class="flex items-center gap-3">

                                    <span class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 px-3 py-1 rounded-full text-xs font-semibold">
                                        <?= (int) $row['total_ulasan_buku']; ?> Ulasan
                                    </span>

                                    <div id="<?= $id_icon; ?>"
                                        class="w-10 h-10 rounded-xl bg-slate-700 flex items-center justify-center text-white text-2xl transition">
                                        +
                                    </div>

                                </div>

                            </div>

                        </button>

                        <!-- ISI DROPDOWN -->
                        <div id="<?= $id_dropdown; ?>" class="hidden">

                            <div class="overflow-x-auto">

                                <table class="w-full text-sm text-left text-slate-300">

                                    <thead class="bg-slate-800 text-slate-300 uppercase text-xs">

                                        <tr>
                                            <th class="px-6 py-4">Anggota</th>
                                            <th class="px-6 py-4">Rating</th>
                                            <th class="px-6 py-4">Ulasan</th>
                                            <th class="px-6 py-4">Tanggal</th>
                                            <th class="px-6 py-4 text-center">Aksi</th>
                                        </tr>

                                    </thead>

                                    <tbody class="divide-y divide-slate-800">

                <?php endif; ?>

                                        <tr class="hover:bg-slate-800/50 transition">

                                            <!-- ANGGOTA -->
                                            <td class="px-6 py-4 align-top">

                                                <div>
                                                    <h3 class="font-semibold text-white">
                                                        <?= htmlspecialchars($row['nama_user']); ?>
                                                    </h3>

                                                    <p class="text-xs text-slate-500 mt-1">
                                                        <?= htmlspecialchars($row['email']); ?>
                                                    </p>
                                                </div>

                                            </td>

                                            <!-- RATING -->
                                            <td class="px-6 py-4 whitespace-nowrap align-top">

                                                <span class="bg-yellow-500/10 text-yellow-400 border border-yellow-500/20 px-3 py-1 rounded-full text-xs font-semibold">
                                                    <?= (int) $row['rating']; ?> / 5
                                                </span>

                                            </td>

                                            <!-- ULASAN -->
                                            <td class="px-6 py-4 min-w-[350px] align-top">

                                                <p class="text-slate-300 leading-relaxed">
                                                    <?= nl2br(htmlspecialchars($row['ulasan'])); ?>
                                                </p>

                                            </td>

                                            <!-- TANGGAL -->
                                            <td class="px-6 py-4 whitespace-nowrap align-top">

                                                <?php if (!empty($row['tanggal_ulasan'])): ?>
                                                    <?= date('d M Y', strtotime($row['tanggal_ulasan'])); ?>
                                                <?php else: ?>
                                                    <span class="text-slate-500">-</span>
                                                <?php endif; ?>

                                            </td>

                                            <!-- AKSI -->
                                            <td class="px-6 py-4 text-center align-top">

                                                <button
                                                    type="button"
                                                    onclick="confirmHapus(<?= (int) $row['id_ulasan']; ?>)"
                                                    class="bg-red-500 hover:bg-red-600 transition px-4 py-2 rounded-xl text-white text-sm font-semibold inline-flex items-center gap-2">

                                                    <i class='bx bx-trash'></i>
                                                    Hapus

                                                </button>

                                            </td>

                                        </tr>

            <?php endwhile; ?>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

        <?php else: ?>

            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-12 text-center text-slate-500">
                Belum ada ulasan dari anggota.
            </div>

        <?php endif; ?>

    </div>

</div>

<script>
    function toggleDropdown(dropdownId, iconId) {
        const dropdown = document.getElementById(dropdownId);
        const icon = document.getElementById(iconId);

        if (!dropdown || !icon) {
            return;
        }

        dropdown.classList.toggle('hidden');

        if (dropdown.classList.contains('hidden')) {
            icon.innerHTML = '+';
        } else {
            icon.innerHTML = '−';
        }
    }

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

<?php include __DIR__ . "/../../admin/layout/footer.php"; ?>