<?php
session_start();
include __DIR__ . "/../../config/koneksi.php";

// ==========================
// CEK LOGIN
// ==========================
if (!isset($_SESSION['login'])) {
    header("Location: ../../auth/login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

// ==========================
// AMBIL DATA USER
// ==========================
$queryUser = mysqli_query($conn, "
    SELECT * FROM user 
    WHERE id_user = '$id_user'
");

$user = mysqli_fetch_assoc($queryUser);

// ==========================
// SEARCH & PAGINATION BUKU
// ==========================
$search = $_GET['search'] ?? '';

$limit = 6;

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

if ($page < 1) {
    $page = 1;
}

$offset = ($page - 1) * $limit;

$whereBuku = "";

if (!empty($search)) {
    $search_safe = mysqli_real_escape_string($conn, $search);

    $whereBuku .= "
        AND (
            buku.judul LIKE '%$search_safe%'
            OR penulis.nama_penulis LIKE '%$search_safe%'
            OR penerbit.nama_penerbit LIKE '%$search_safe%'
            OR kategori.nama_kategori LIKE '%$search_safe%'
        )
    ";
}

// ==========================
// TOTAL BUKU UNTUK PAGINATION
// ==========================
$totalBukuQuery = mysqli_query($conn, "
    SELECT COUNT(*) AS total

    FROM buku

    LEFT JOIN kategori
        ON buku.id_kategori = kategori.id_kategori

    LEFT JOIN penulis
        ON buku.id_penulis = penulis.id_penulis

    LEFT JOIN penerbit
        ON buku.id_penerbit = penerbit.id_penerbit

    WHERE 1=1
    $whereBuku
");

$totalBukuData = mysqli_fetch_assoc($totalBukuQuery);
$totalBuku = $totalBukuData['total'] ?? 0;
$totalPage = ceil($totalBuku / $limit);

// ==========================
// AMBIL DATA BUKU
// ==========================
$queryBuku = mysqli_query($conn, "
    SELECT 
        buku.*,
        kategori.nama_kategori,
        penulis.nama_penulis,
        penerbit.nama_penerbit

    FROM buku

    LEFT JOIN kategori
        ON buku.id_kategori = kategori.id_kategori

    LEFT JOIN penulis
        ON buku.id_penulis = penulis.id_penulis

    LEFT JOIN penerbit
        ON buku.id_penerbit = penerbit.id_penerbit

    WHERE 1=1
    $whereBuku

    ORDER BY buku.judul ASC

    LIMIT $offset, $limit
");

// ==========================
// PROSES PINJAM
// ==========================
if (isset($_POST['pinjam'])) {

    $tanggal_pinjam  = $_POST['tanggal_pinjam'];
    $tanggal_kembali = $_POST['tanggal_kembali'];

    $bukuDipilih = $_POST['buku'] ?? [];

    // VALIDASI
    if (empty($tanggal_pinjam) || empty($tanggal_kembali)) {

        $_SESSION['error'] = "Tanggal wajib diisi!";
        header("Location: tambah.php");
        exit;

    } elseif ($tanggal_kembali < $tanggal_pinjam) {

        $_SESSION['error'] = "Tanggal kembali tidak valid!";
        header("Location: tambah.php");
        exit;

    } elseif (count($bukuDipilih) <= 0) {

        $_SESSION['error'] = "Pilih minimal 1 buku!";
        header("Location: tambah.php");
        exit;

    } else {

        mysqli_begin_transaction($conn);

        try {

            $berhasil_pinjam = 0;

            foreach ($bukuDipilih as $id_buku) {

                $id_buku = mysqli_real_escape_string($conn, $id_buku);

                // AMBIL DATA BUKU
                $cekBuku = mysqli_query($conn, "
                    SELECT * 
                    FROM buku
                    WHERE id_buku = '$id_buku'
                    LIMIT 1
                ");

                if (!$cekBuku || mysqli_num_rows($cekBuku) == 0) {
                    continue;
                }

                $buku = mysqli_fetch_assoc($cekBuku);

                // CEK STOK
                if ($buku['stok'] <= 0) {
                    continue;
                }

                // INSERT PEMINJAMAN
                $insert = mysqli_query($conn, "
                    INSERT INTO peminjaman (
                        id_user,
                        id_buku,
                        tanggal_pinjam,
                        tanggal_kembali,
                        status
                    ) VALUES (
                        '$id_user',
                        '$id_buku',
                        '$tanggal_pinjam',
                        '$tanggal_kembali',
                        'dipinjam'
                    )
                ");

                if (!$insert) {
                    throw new Exception("Gagal insert peminjaman");
                }

                // KURANGI STOK
                $updateStok = mysqli_query($conn, "
                    UPDATE buku
                    SET stok = stok - 1
                    WHERE id_buku = '$id_buku'
                ");

                if (!$updateStok) {
                    throw new Exception("Gagal update stok");
                }

                $berhasil_pinjam++;
            }

            if ($berhasil_pinjam <= 0) {
                throw new Exception("Tidak ada buku yang berhasil dipinjam");
            }

            mysqli_commit($conn);

            $_SESSION['success'] = "$berhasil_pinjam buku berhasil dipinjam!";
            header("Location: index.php");
            exit;

        } catch (Exception $e) {

            mysqli_rollback($conn);

            $_SESSION['error'] = "Gagal melakukan peminjaman!";
            header("Location: tambah.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Banyak Peminjaman</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-950 min-h-screen p-6 text-white">

    <div class="max-w-7xl mx-auto">

        <!-- HEADER -->
        <div class="flex items-center justify-between mb-6">

            <div>

                <h1 class="text-3xl font-bold">
                    Tambah Banyak Peminjaman
                </h1>

                <p class="text-slate-400 text-sm mt-1">
                    Pilih beberapa buku sekaligus
                </p>

            </div>

            <a href="index.php"
                class="bg-slate-800 hover:bg-slate-700 transition px-4 py-3 rounded-xl flex items-center gap-2">

                <i class='bx bx-arrow-back'></i>
                Kembali

            </a>

        </div>

        <!-- SEARCH -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 mb-6 shadow-xl">

            <form method="GET" class="grid md:grid-cols-4 gap-4">

                <input
                    type="text"
                    name="search"
                    value="<?= htmlspecialchars($search); ?>"
                    placeholder="Cari nama buku, penulis, penerbit, kategori..."
                    class="md:col-span-3 w-full bg-slate-800 border border-slate-700 rounded-2xl px-5 py-4 text-white outline-none focus:border-blue-500">

                <div class="flex gap-3">

                    <button
                        type="submit"
                        class="flex-1 bg-blue-500 hover:bg-blue-600 transition rounded-2xl font-semibold">
                        Cari
                    </button>

                    <a href="tambah.php"
                        class="bg-slate-700 hover:bg-slate-600 transition px-5 py-4 rounded-2xl font-semibold">
                        Reset
                    </a>

                </div>

            </form>

        </div>

        <form method="POST">

            <div class="grid lg:grid-cols-3 gap-6">

                <!-- PILIH BUKU -->
                <div class="lg:col-span-2">

                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl">

                        <div class="flex items-center justify-between mb-5">

                            <h2 class="text-xl font-bold">
                                Pilih Buku
                            </h2>

                            <p class="text-slate-400 text-sm">
                                Total: <?= $totalBuku; ?> buku
                            </p>

                        </div>

                        <div class="grid md:grid-cols-2 gap-4">

                            <?php if ($queryBuku && mysqli_num_rows($queryBuku) > 0): ?>

                                <?php while ($buku = mysqli_fetch_assoc($queryBuku)): ?>

                                    <label class="border border-slate-800 hover:border-blue-500 transition rounded-2xl p-4 cursor-pointer bg-slate-950 <?= $buku['stok'] <= 0 ? 'opacity-50 cursor-not-allowed' : ''; ?>">

                                        <div class="flex gap-4">

                                            <input type="checkbox"
                                                name="buku[]"
                                                value="<?= $buku['id_buku']; ?>"
                                                class="mt-2 w-5 h-5"
                                                <?= $buku['stok'] <= 0 ? 'disabled' : ''; ?>>

                                            <img src="../../assets/img/cover/<?= htmlspecialchars($buku['cover']); ?>"
                                                class="w-20 h-28 object-cover rounded-xl">

                                            <div class="flex-1">

                                                <h3 class="font-bold text-white line-clamp-2">
                                                    <?= htmlspecialchars($buku['judul']); ?>
                                                </h3>

                                                <p class="text-slate-400 text-sm mt-1">
                                                    <?= htmlspecialchars($buku['nama_penulis'] ?? '-'); ?>
                                                </p>

                                                <p class="text-slate-400 text-sm mt-1">
                                                    <?= htmlspecialchars($buku['nama_penerbit'] ?? '-'); ?>
                                                </p>

                                                <div class="mt-3 flex items-center justify-between gap-3">

                                                    <span class="bg-blue-500/10 text-blue-400 px-3 py-1 rounded-xl text-xs">
                                                        <?= htmlspecialchars($buku['nama_kategori'] ?? '-'); ?>
                                                    </span>

                                                    <?php if ($buku['stok'] > 0): ?>

                                                        <span class="text-emerald-400 font-bold text-sm">
                                                            Stok <?= $buku['stok']; ?>
                                                        </span>

                                                    <?php else: ?>

                                                        <span class="text-red-400 font-bold text-sm">
                                                            Habis
                                                        </span>

                                                    <?php endif; ?>

                                                </div>

                                            </div>

                                        </div>

                                    </label>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <div class="md:col-span-2 text-center py-12 text-slate-500">
                                    Buku tidak ditemukan.
                                </div>

                            <?php endif; ?>

                        </div>

                        <!-- PAGINATION -->
                        <?php if ($totalPage > 1): ?>

                            <div class="flex justify-center gap-2 mt-8 flex-wrap">

                                <?php if ($page > 1): ?>
                                    <a href="?page=<?= $page - 1; ?>&search=<?= urlencode($search); ?>"
                                        class="px-4 py-2 rounded-xl text-sm font-semibold bg-slate-800 text-slate-300 hover:bg-slate-700">
                                        Prev
                                    </a>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $totalPage; $i++): ?>

                                    <a href="?page=<?= $i; ?>&search=<?= urlencode($search); ?>"
                                        class="px-4 py-2 rounded-xl text-sm font-semibold
                                        <?= $page == $i
                                            ? 'bg-blue-500 text-white'
                                            : 'bg-slate-800 text-slate-300 hover:bg-slate-700'; ?>">
                                        <?= $i; ?>
                                    </a>

                                <?php endfor; ?>

                                <?php if ($page < $totalPage): ?>
                                    <a href="?page=<?= $page + 1; ?>&search=<?= urlencode($search); ?>"
                                        class="px-4 py-2 rounded-xl text-sm font-semibold bg-slate-800 text-slate-300 hover:bg-slate-700">
                                        Next
                                    </a>
                                <?php endif; ?>

                            </div>

                        <?php endif; ?>

                    </div>

                </div>

                <!-- FORM -->
                <div>

                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl sticky top-5">

                        <h2 class="text-xl font-bold mb-5">
                            Data Peminjaman
                        </h2>

                        <!-- USER -->
                        <div class="mb-5">

                            <label class="block text-sm text-slate-300 mb-2">
                                Nama Peminjam
                            </label>

                            <input type="text"
                                readonly
                                value="<?= htmlspecialchars($user['nama']); ?>"
                                class="w-full bg-slate-800 border border-slate-700 rounded-2xl px-5 py-4 text-white">

                        </div>

                        <!-- TANGGAL PINJAM -->
                        <div class="mb-5">

                            <label class="block text-sm text-slate-300 mb-2">
                                Tanggal Pinjam
                            </label>

                            <input type="date"
                                name="tanggal_pinjam"
                                required
                                value="<?= date('Y-m-d'); ?>"
                                class="w-full bg-slate-800 border border-slate-700 rounded-2xl px-5 py-4 text-white">

                        </div>

                        <!-- TANGGAL KEMBALI -->
                        <div class="mb-6">

                            <label class="block text-sm text-slate-300 mb-2">
                                Deadline Pengembalian
                            </label>

                            <input type="date"
                                name="tanggal_kembali"
                                required
                                class="w-full bg-slate-800 border border-slate-700 rounded-2xl px-5 py-4 text-white">

                        </div>

                        <!-- BUTTON -->
                        <button type="submit"
                            name="pinjam"
                            class="w-full bg-blue-500 hover:bg-blue-600 transition py-4 rounded-2xl font-semibold text-lg shadow-lg">

                            Pinjam Buku

                        </button>

                    </div>

                </div>

            </div>

        </form>

    </div>

    <!-- SWEET ALERT ERROR -->
    <?php if (isset($_SESSION['error'])): ?>

        <script>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '<?= $_SESSION['error']; ?>',
                background: '#0f172a',
                color: '#fff',
                confirmButtonColor: '#ef4444'
            });
        </script>

        <?php unset($_SESSION['error']); ?>

    <?php endif; ?>

</body>

</html>