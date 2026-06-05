<?php
session_start();
include __DIR__ . "/../../config/koneksi.php";

// cek login
if (!isset($_SESSION['login'])) {
    header("Location: ../../auth/login.php");
    exit;
}

// ambil id buku
if (!isset($_GET['id_buku'])) {
    header("Location: ../buku/buku.php");
    exit;
}

$id_buku = mysqli_real_escape_string($conn, $_GET['id_buku']);
$id_user = $_SESSION['id_user'];

// ambil data user
$queryUser = mysqli_query($conn, "
    SELECT * FROM user 
    WHERE id_user = '$id_user'
");

$user = mysqli_fetch_assoc($queryUser);

// ambil data buku
$queryBuku = mysqli_query($conn, "
    SELECT buku.*, kategori.nama_kategori
    FROM buku
    LEFT JOIN kategori 
    ON buku.id_kategori = kategori.id_kategori
    WHERE buku.id_buku = '$id_buku'
");

$buku = mysqli_fetch_assoc($queryBuku);

// proses pinjam
if (isset($_POST['pinjam'])) {

    $tanggal_pinjam   = $_POST['tanggal_pinjam'];
    $tanggal_kembali  = $_POST['tanggal_kembali'];

    // validasi
    if (empty($tanggal_pinjam) || empty($tanggal_kembali)) {

        $_SESSION['error'] = "Tanggal wajib diisi!";

    } elseif ($tanggal_kembali < $tanggal_pinjam) {

        $_SESSION['error'] = "Tanggal kembali tidak valid!";

    } elseif ($buku['stok'] <= 0) {

        $_SESSION['error'] = "Stok buku habis!";

    } else {

        // insert peminjaman
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

            // kurangi stok
            mysqli_query($conn, "
                UPDATE buku 
                SET stok = stok - 1
                WHERE id_buku = '$id_buku'
            ");

            echo "
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>

            <script>
                document.addEventListener('DOMContentLoaded', function() {

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Buku berhasil dipinjam!',
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
    <title>Tambah Peminjaman</title>

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

    <div class="max-w-5xl mx-auto">

        <!-- HEADER -->
        <div class="flex items-center justify-between mb-6">

            <div>
                <h1 class="text-3xl font-bold">
                    Tambah Peminjaman
                </h1>

                <p class="text-slate-400 text-sm mt-1">
                    Persiapkan data peminjaman buku
                </p>
            </div>

            <a href="peminjaman.php"
                class="bg-slate-800 hover:bg-slate-700 transition px-4 py-3 rounded-xl flex items-center gap-2">

                <i class='bx bx-arrow-back'></i>

                Kembali

            </a>

        </div>

        <div class="grid lg:grid-cols-3 gap-6">

            <!-- DETAIL BUKU -->
            <div class="lg:col-span-1">

                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl">

                    <img src="../../assets/img/cover/<?= $buku['cover']; ?>"
                        class="w-full h-96 object-cover rounded-2xl shadow-lg">

                    <div class="mt-5">

                        <h2 class="text-2xl font-bold">
                            <?= htmlspecialchars($buku['judul']); ?>
                        </h2>

                        <p class="text-slate-400 mt-1">
                            <?= htmlspecialchars($buku['penulis']); ?>
                        </p>

                        <div class="mt-4 space-y-3">

                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">Kategori</span>

                                <span class="bg-blue-500/10 text-blue-400 px-3 py-1 rounded-xl text-sm">
                                    <?= $buku['nama_kategori']; ?>
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">ISBN</span>

                                <span class="font-medium">
                                    <?= $buku['isbn']; ?>
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">Stok</span>

                                <span class="font-bold text-emerald-400">
                                    <?= $buku['stok']; ?>
                                </span>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <!-- FORM -->
            <div class="lg:col-span-2">

                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-8 shadow-2xl">

                    <form method="POST" class="space-y-6">

                        <!-- Nama -->
                        <div>

                            <label class="block text-sm text-slate-300 mb-2">
                                Nama Peminjam
                            </label>

                            <input type="text"
                                value="<?= htmlspecialchars($user['nama']); ?>"
                                readonly
                                class="w-full bg-slate-800 border border-slate-700 rounded-2xl px-5 py-4 text-white">

                        </div>

                        <!-- Tanggal Pinjam -->
                        <div>

                            <label class="block text-sm text-slate-300 mb-2">
                                Tanggal Pinjam
                            </label>

                            <input type="date"
                                name="tanggal_pinjam"
                                required
                                value="<?= date('Y-m-d'); ?>"
                                class="w-full bg-slate-800 border border-slate-700 rounded-2xl px-5 py-4 outline-none focus:border-blue-500 transition text-white">

                        </div>

                        <!-- Tanggal Kembali -->
                        <div>

                            <label class="block text-sm text-slate-300 mb-2">
                                Tanggal Kembali
                            </label>

                            <input type="date"
                                name="tanggal_kembali"
                                required
                                class="w-full bg-slate-800 border border-slate-700 rounded-2xl px-5 py-4 outline-none focus:border-blue-500 transition text-white">

                        </div>

                        <!-- BUTTON -->
                        <button type="submit"
                            name="pinjam"
                            class="w-full bg-blue-500 hover:bg-blue-600 transition py-4 rounded-2xl font-semibold text-lg shadow-lg">

                            Pinjam Buku

                        </button>

                    </form>

                </div>

            </div>

        </div>

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