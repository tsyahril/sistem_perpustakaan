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

    ORDER BY buku.judul ASC
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
    } elseif ($tanggal_kembali < $tanggal_pinjam) {

        $_SESSION['error'] = "Tanggal kembali tidak valid!";
    } elseif (count($bukuDipilih) <= 0) {

        $_SESSION['error'] = "Pilih minimal 1 buku!";
    } else {

        $berhasil = true;

        foreach ($bukuDipilih as $id_buku) {

            $id_buku = mysqli_real_escape_string($conn, $id_buku);

            // AMBIL DATA BUKU

            // ==========================
            // AMBIL SEMUA BUKU
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

                ORDER BY buku.judul ASC
            ");
            $cekBuku = mysqli_query($conn, "
                SELECT * 
                FROM buku
                WHERE id_buku = '$id_buku'
            ");

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

            if ($insert) {

                // KURANGI STOK
                mysqli_query($conn, "
                    UPDATE buku
                    SET stok = stok - 1
                    WHERE id_buku = '$id_buku'
                ");
            } else {

                $berhasil = false;
            }
        }

        // ==========================
        // HASIL
        // ==========================
        if ($berhasil) {

            echo "
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>

            <script>
                document.addEventListener('DOMContentLoaded', function() {

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Peminjaman berhasil dibuat!',
                        background: '#0f172a',
                        color: '#fff',
                        confirmButtonColor: '#3b82f6',
                        timer: 1800,
                        showConfirmButton: false
                    }).then(() => {

                        window.location.href = 'peminjaman.php';

                    });

                });
            </script>
            ";
        } else {

            $_SESSION['error'] = "Gagal melakukan peminjaman!";
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

            <a href="peminjaman.php"
                class="bg-slate-800 hover:bg-slate-700 transition px-4 py-3 rounded-xl flex items-center gap-2">

                <i class='bx bx-arrow-back'></i>
                Kembali

            </a>

        </div>

        <form method="POST">

            <div class="grid lg:grid-cols-3 gap-6">

                <!-- PILIH BUKU -->
                <div class="lg:col-span-2">

                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl">

                        <h2 class="text-xl font-bold mb-5">
                            Pilih Buku
                        </h2>

                        <div class="grid md:grid-cols-2 gap-4">

                            <?php while ($buku = mysqli_fetch_assoc($queryBuku)): ?>

                                <label class="border border-slate-800 hover:border-blue-500 transition rounded-2xl p-4 cursor-pointer bg-slate-950">

                                    <div class="flex gap-4">

                                        <input type="checkbox"
                                            name="buku[]"
                                            value="<?= $buku['id_buku']; ?>"
                                            class="mt-2 w-5 h-5">

                                        <img src="../../assets/img/cover/<?= $buku['cover']; ?>"
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

                                            <div class="mt-3 flex items-center justify-between">

                                                <span class="bg-blue-500/10 text-blue-400 px-3 py-1 rounded-xl text-xs">
                                                    <?= $buku['nama_kategori']; ?>
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

                        </div>

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