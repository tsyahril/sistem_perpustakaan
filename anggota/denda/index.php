<?php
session_start();

include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../middleware/anggota.php";
include __DIR__ .  "/../layout/header.php";
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php
// ==========================
// USER LOGIN
// ==========================
$id_user_login = $_SESSION['id_user'];
$role_login    = $_SESSION['role'];

// ==========================
// PAGINATION
// ==========================
$limit = 15;

$page = isset($_GET['page'])
    ? (int) $_GET['page']
    : 1;

if ($page < 1) {
    $page = 1;
}

$offset = ($page - 1) * $limit;

// ==========================
// FILTER
// ==========================
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';

$where = "";

// ==========================
// SEARCH
// ==========================
if (!empty($search)) {

    $search = mysqli_real_escape_string($conn, $search);

    $where .= "
        AND (
            buku.judul LIKE '%$search%'
            OR user.nama LIKE '%$search%'
        )
    ";
}

// ==========================
// FILTER STATUS DENDA
// ==========================
if ($status == 'dibayar') {

    $where .= "
        AND peminjaman.status_denda = 'sudah_dibayar'
    ";
}

if ($status == 'belum') {

    $where .= "
        AND (
            peminjaman.status_denda IS NULL
            OR peminjaman.status_denda != 'sudah_dibayar'
        )
    ";
}

// ==========================
// BAYAR DENDA
// ==========================
if (isset($_GET['bayar'])) {

    $id_pinjam = (int) $_GET['bayar'];

    $cek = mysqli_query($conn, "
        SELECT *
        FROM peminjaman
        WHERE id_pinjam = '$id_pinjam'
        AND id_user = '$id_user_login'
        LIMIT 1
    ");

    if (mysqli_num_rows($cek) > 0) {

        mysqli_query($conn, "
            UPDATE peminjaman
            SET
                status_denda = 'sudah_dibayar',
                tanggal_bayar_denda = NOW()
            WHERE id_pinjam = '$id_pinjam'
            AND id_user = '$id_user_login'
        ");

        echo "
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Denda berhasil dibayar',
                confirmButtonColor: '#3b82f6',
                background: '#0f172a',
                color: '#fff'
            }).then(() => {
                window.location='index.php';
            });
        </script>
        ";
        exit;
    }
}

// ==========================
// AUTO EXPIRED
// ==========================
mysqli_query($conn, "
    UPDATE peminjaman
    SET status_denda = 'expired'
    WHERE status_denda = 'sudah_dibayar'
    AND tanggal_bayar_denda <= DATE_SUB(NOW(), INTERVAL 7 DAY)
    AND id_user = '$id_user_login'
");

// ==========================
// SETTING DENDA
// ==========================
$getDenda = mysqli_query($conn, "
    SELECT *
    FROM denda
    LIMIT 1
");

$setting = mysqli_fetch_assoc($getDenda);

$denda_per_hari = $setting['jumlah_denda'] ?? 5000;
$rumus_rusak    = $setting['denda_rusak'] ?? 2;
$rumus_hilang   = $setting['denda_hilang'] ?? 2;

if ($rumus_rusak <= 0) {
    $rumus_rusak = 2;
}

if ($rumus_hilang <= 0) {
    $rumus_hilang = 2;
}

// ==========================
// TOTAL DATA
// ==========================
$totalQuery = mysqli_query($conn, "
    SELECT COUNT(*) as total

    FROM peminjaman

    JOIN user
        ON peminjaman.id_user = user.id_user

    JOIN buku
        ON peminjaman.id_buku = buku.id_buku

    WHERE 
        peminjaman.id_user = '$id_user_login'

    AND peminjaman.status NOT IN ('pending', 'dipinjam')

    AND (
        peminjaman.status_denda IS NULL
        OR peminjaman.status_denda != 'expired'
    )

    AND (
        DATE(peminjaman.tanggal_dikembalikan) > DATE(peminjaman.tanggal_kembali)
        OR peminjaman.kondisi_buku = 'rusak'
        OR peminjaman.kondisi_buku = 'hilang'
    )

    $where
");

$totalData = mysqli_fetch_assoc($totalQuery)['total'] ?? 0;

$totalPage = ceil($totalData / $limit);

// ==========================
// QUERY DATA DENDA
// ==========================
$query = mysqli_query($conn, "
    SELECT
        peminjaman.*,
        user.nama,
        buku.judul,
        buku.harga

    FROM peminjaman

    JOIN user
        ON peminjaman.id_user = user.id_user

    JOIN buku
        ON peminjaman.id_buku = buku.id_buku

    WHERE 
        peminjaman.id_user = '$id_user_login'

    AND peminjaman.status NOT IN ('pending', 'dipinjam')

    AND (
        peminjaman.status_denda IS NULL
        OR peminjaman.status_denda != 'expired'
    )

    AND (
        DATE(peminjaman.tanggal_dikembalikan) > DATE(peminjaman.tanggal_kembali)
        OR peminjaman.kondisi_buku = 'rusak'
        OR peminjaman.kondisi_buku = 'hilang'
    )

    $where

    ORDER BY peminjaman.id_pinjam DESC

    LIMIT $offset, $limit
");

// ==========================
// TOTAL DENDA DI HALAMAN INI
// ==========================
$total_semua_denda = 0;
?>

<div class="max-w-7xl mx-auto">

    <!-- HEADER -->
    <div class="flex justify-between items-center mb-6 flex-wrap gap-4">

        <div>
            <h1 class="text-3xl font-bold text-white">
                Denda Saya
            </h1>

            <p class="text-slate-400 mt-1">
                Daftar denda peminjaman buku kamu.
            </p>
        </div>

        <button onclick="window.print()"
            class="bg-blue-500 hover:bg-blue-600 px-5 py-3 rounded-2xl text-white font-semibold inline-flex items-center gap-2">

            <svg xmlns="http://www.w3.org/2000/svg"
                class="w-5 h-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor">

                <path stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z" />

            </svg>

            Print Denda
        </button>

    </div>

    <!-- FILTER -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 mb-6">

        <form method="GET"
            class="grid lg:grid-cols-4 gap-4">

            <input
                type="text"
                name="search"
                value="<?= htmlspecialchars($search); ?>"
                placeholder="Cari buku..."
                class="bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none">

            <select
                name="status"
                class="bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none">

                <option value="">Semua Status</option>

                <option value="belum" <?= $status == 'belum' ? 'selected' : ''; ?>>
                    Belum Dibayar
                </option>

                <option value="dibayar" <?= $status == 'dibayar' ? 'selected' : ''; ?>>
                    Sudah Dibayar
                </option>

            </select>

            <button
                type="submit"
                class="bg-blue-500 hover:bg-blue-600 px-4 py-3 rounded-xl text-white font-semibold">
                Filter
            </button>

            <a href="total.php"
                class="bg-slate-700 hover:bg-slate-600 px-4 py-3 rounded-xl text-white text-center font-semibold">
                Reset
            </a>

        </form>

    </div>

    <!-- TABLE -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden">

        <div class="overflow-x-auto">

            <table class="w-full">

                <thead class="bg-slate-800 text-slate-300">

                    <tr>
                        <th class="p-4 text-left">No</th>
                        <th class="p-4 text-left">Buku</th>
                        <th class="p-4 text-left">Telat</th>
                        <th class="p-4 text-left">Kondisi Buku</th>
                        <th class="p-4 text-left">Total Denda</th>
                        <th class="p-4 text-left">Status</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>

                </thead>

                <tbody class="divide-y divide-slate-800">

                    <?php if ($query && mysqli_num_rows($query) > 0): ?>

                        <?php
                        $no = $offset + 1;

                        while ($row = mysqli_fetch_assoc($query)):

                            $kondisi_buku = strtolower($row['kondisi_buku'] ?? 'baik');

                            if (!in_array($kondisi_buku, ['baik', 'rusak', 'hilang'])) {
                                $kondisi_buku = 'baik';
                            }

                            // HARI TELAT
                            $hari_telat = 0;

                            if (
                                !empty($row['tanggal_dikembalikan'])
                                &&
                                strtotime($row['tanggal_dikembalikan']) > strtotime($row['tanggal_kembali'])
                            ) {

                                $hari_telat = floor(
                                    (
                                        strtotime($row['tanggal_dikembalikan'])
                                        -
                                        strtotime($row['tanggal_kembali'])
                                    ) / (60 * 60 * 24)
                                );
                            }

                            // DENDA
                            $denda_telat  = $hari_telat * $denda_per_hari;
                            $denda_rusak  = 0;
                            $denda_hilang = 0;

                            if ($kondisi_buku == 'rusak') {
                                $denda_rusak = $row['harga'] / $rumus_rusak;
                            }

                            if ($kondisi_buku == 'hilang') {
                                $denda_hilang = $row['harga'] * $rumus_hilang;
                            }

                            $total_denda =
                                $denda_telat +
                                $denda_rusak +
                                $denda_hilang;

                            $total_semua_denda += $total_denda;
                        ?>

                            <tr class="bg-slate-900/40 hover:bg-slate-800/50 transition duration-200 border-b border-slate-800">

                                <td class="p-4 text-white">
                                    <?= $no++; ?>
                                </td>

                                <td class="p-4 text-white">
                                    <?= htmlspecialchars($row['judul']); ?>
                                </td>

                                <td class="p-4 text-yellow-400">
                                    <?= $hari_telat; ?> Hari
                                </td>

                                <td class="p-4">

                                    <?php
                                    $badge = 'emerald';

                                    if ($kondisi_buku == 'rusak') {
                                        $badge = 'yellow';
                                    }

                                    if ($kondisi_buku == 'hilang') {
                                        $badge = 'red';
                                    }
                                    ?>

                                    <span class="bg-<?= $badge; ?>-500/10 text-<?= $badge; ?>-400 px-3 py-1 rounded-full text-xs">
                                        <?= strtoupper($kondisi_buku); ?>
                                    </span>

                                </td>

                                <td class="p-4 text-emerald-400 font-bold">
                                    Rp <?= number_format($total_denda, 0, ',', '.'); ?>
                                </td>

                                <td class="p-4">

                                    <?php if ($row['status_denda'] == 'sudah_dibayar'): ?>

                                        <span class="bg-emerald-500/10 text-emerald-400 px-3 py-1 rounded-full text-xs">
                                            Sudah Dibayar
                                        </span>

                                    <?php else: ?>

                                        <span class="bg-red-500/10 text-red-400 px-3 py-1 rounded-full text-xs">
                                            Belum Dibayar
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td class="p-4 text-center">

                                    <?php if ($row['status_denda'] != 'sudah_dibayar'): ?>

                                        <a href="#"
                                            onclick="bayarDenda(<?= $row['id_pinjam']; ?>)"
                                            class="bg-emerald-500 hover:bg-emerald-600 px-4 py-2 rounded-xl text-white text-sm">
                                            Bayar
                                        </a>

                                    <?php else: ?>

                                        <span class="text-slate-500 text-sm">
                                            Selesai
                                        </span>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="7"
                                class="p-10 text-center text-slate-400">
                                Tidak ada data denda
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

                <tfoot class="bg-slate-800">

                    <tr>
                        <td colspan="4"
                            class="p-5 text-right text-white font-bold text-xl">
                            Total Denda Halaman Ini :
                        </td>

                        <td colspan="3"
                            class="p-5 text-red-400 font-bold text-2xl">
                            Rp <?= number_format($total_semua_denda, 0, ',', '.'); ?>
                        </td>
                    </tr>

                </tfoot>

            </table>

        </div>

    </div>

    <!-- PAGINATION -->
    <?php if ($totalPage > 1): ?>

        <div class="flex justify-center gap-2 mt-6 flex-wrap">

            <?php for ($i = 1; $i <= $totalPage; $i++): ?>

                <a href="?page=<?= $i; ?>&search=<?= urlencode($search); ?>&status=<?= urlencode($status); ?>"
                    class="px-4 py-2 rounded-xl text-sm font-semibold
                    <?= $page == $i
                        ? 'bg-blue-500 text-white'
                        : 'bg-slate-800 text-slate-300 hover:bg-slate-700'; ?>">

                    <?= $i; ?>

                </a>

            <?php endfor; ?>

        </div>

    <?php endif; ?>

</div>

<style>
    @media print {

        body * {
            visibility: hidden;
        }

        table,
        table * {
            visibility: visible;
        }

        table {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            background: white;
            color: black;
        }

        table {
            border-collapse: separate;
            border-spacing: 0;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

    }
</style>

<script>
    function bayarDenda(id) {

        Swal.fire({
            title: 'Bayar Denda?',
            text: 'Pastikan kamu ingin membayar denda ini',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#ef4444',
            confirmButtonText: 'Ya, Bayar!',
            cancelButtonText: 'Batal',
            background: '#0f172a',
            color: '#fff'
        }).then((result) => {

            if (result.isConfirmed) {
                window.location = '?bayar=' + id;
            }

        });

    }
</script>

<?php include __DIR__ .  "/../layout/footer.php"; ?>