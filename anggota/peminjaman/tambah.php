<?php
session_start();

include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../middleware/anggota.php";
include __DIR__ . "/../layout/header.php";

$id_user = $_SESSION['id_user'];

$user = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT *
    FROM user
    WHERE id_user = '$id_user'
    LIMIT 1
"));

$queryBuku = mysqli_query($conn, "
    SELECT 
        buku.*,
        kategori.nama_kategori,
        COALESCE(penulis.nama_penulis, '-') AS nama_penulis,
        COALESCE(penerbit.nama_penerbit, '-') AS nama_penerbit

    FROM buku

    LEFT JOIN kategori
        ON buku.id_kategori = kategori.id_kategori

    LEFT JOIN penulis
        ON buku.id_penulis = penulis.id_penulis

    LEFT JOIN penerbit
        ON buku.id_penerbit = penerbit.id_penerbit

    ORDER BY buku.judul ASC
");

if (isset($_POST['pinjam'])) {

    $tanggal_pinjam = date('Y-m-d');
    $tanggal_kembali = date('Y-m-d', strtotime('+7 days'));
    $bukuDipilih = $_POST['buku'] ?? [];

    if (count($bukuDipilih) <= 0) {

        $_SESSION['error'] = "Pilih minimal 1 buku!";

    } else {

        mysqli_begin_transaction($conn);

        try {

            foreach ($bukuDipilih as $id_buku) {

                $id_buku = (int) $id_buku;

                $buku = mysqli_fetch_assoc(mysqli_query($conn, "
                    SELECT *
                    FROM buku
                    WHERE id_buku = '$id_buku'
                    LIMIT 1
                "));

                if (!$buku || $buku['stok'] <= 0) {
                    continue;
                }

                // CEK JANGAN PINJAM BUKU YANG SAMA SAAT MASIH AKTIF
                $cekAktif = mysqli_query($conn, "
                    SELECT id_pinjam
                    FROM peminjaman
                    WHERE id_user = '$id_user'
                    AND id_buku = '$id_buku'
                    AND status = 'dipinjam'
                    LIMIT 1
                ");

                if (mysqli_num_rows($cekAktif) > 0) {
                    continue;
                }

                $insert = mysqli_query($conn, "
                    INSERT INTO peminjaman (
                        id_user,
                        id_buku,
                        tanggal_pinjam,
                        tanggal_kembali,
                        status,
                        kondisi_buku
                    ) VALUES (
                        '$id_user',
                        '$id_buku',
                        '$tanggal_pinjam',
                        '$tanggal_kembali',
                        'dipinjam',
                        'baik'
                    )
                ");

                if (!$insert) {
                    throw new Exception("Gagal insert peminjaman");
                }

                $updateStok = mysqli_query($conn, "
                    UPDATE buku
                    SET stok = stok - 1
                    WHERE id_buku = '$id_buku'
                    AND stok > 0
                ");

                if (!$updateStok) {
                    throw new Exception("Gagal update stok");
                }
            }

            mysqli_commit($conn);

            echo "
            <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Peminjaman berhasil dibuat',
                background: '#0f172a',
                color: '#fff',
                confirmButtonColor: '#3b82f6',
                timer: 1600,
                showConfirmButton: false
            }).then(() => {
                window.location='index.php';
            });
            </script>";
            exit;

        } catch (Exception $e) {

            mysqli_rollback($conn);

            $_SESSION['error'] = "Gagal melakukan peminjaman!";
        }
    }
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="space-y-8">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

        <div>
            <h1 class="text-4xl font-bold text-white">
                Pinjam Banyak Buku
            </h1>

            <p class="text-slate-400 mt-2">
                Pilih beberapa buku untuk dipinjam sekaligus.
            </p>
        </div>

        <a href="index.php"
            class="bg-slate-700 hover:bg-slate-600 transition px-5 py-3 rounded-2xl text-white font-semibold w-fit">

            Kembali

        </a>

    </div>

    <form method="POST">

        <div class="grid lg:grid-cols-3 gap-6">

            <div class="lg:col-span-2">

                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl">

                    <h2 class="text-xl font-bold text-white mb-5">
                        Pilih Buku
                    </h2>

                    <div class="grid md:grid-cols-2 gap-4">

                        <?php while ($buku = mysqli_fetch_assoc($queryBuku)): ?>

                            <?php
                            $disabled = $buku['stok'] <= 0;

                            $cekAktif = mysqli_query($conn, "
                                SELECT id_pinjam
                                FROM peminjaman
                                WHERE id_user = '$id_user'
                                AND id_buku = '{$buku['id_buku']}'
                                AND status = 'dipinjam'
                                LIMIT 1
                            ");

                            $sudahDipinjam = mysqli_num_rows($cekAktif) > 0;

                            if ($sudahDipinjam) {
                                $disabled = true;
                            }
                            ?>

                            <label class="border border-slate-800 <?= $disabled ? 'opacity-50 cursor-not-allowed' : 'hover:border-blue-500 cursor-pointer'; ?> transition rounded-2xl p-4 bg-slate-950">

                                <div class="flex gap-4">

                                    <input type="checkbox"
                                        name="buku[]"
                                        value="<?= $buku['id_buku']; ?>"
                                        <?= $disabled ? 'disabled' : ''; ?>
                                        class="mt-2 w-5 h-5">

                                    <img src="../../assets/img/cover/<?= htmlspecialchars($buku['cover']); ?>"
                                        class="w-20 h-28 object-cover rounded-xl border border-slate-700">

                                    <div class="flex-1">

                                        <h3 class="font-bold text-white line-clamp-2">
                                            <?= htmlspecialchars($buku['judul']); ?>
                                        </h3>

                                        <p class="text-slate-400 text-sm mt-1">
                                            <?= htmlspecialchars($buku['nama_penulis']); ?>
                                        </p>

                                        <p class="text-slate-500 text-xs mt-1">
                                            <?= htmlspecialchars($buku['nama_penerbit']); ?>
                                        </p>

                                        <div class="mt-3 flex items-center justify-between gap-2">

                                            <span class="bg-blue-500/10 text-blue-400 px-3 py-1 rounded-xl text-xs">
                                                <?= htmlspecialchars($buku['nama_kategori'] ?? '-'); ?>
                                            </span>

                                            <?php if ($sudahDipinjam): ?>

                                                <span class="text-yellow-400 font-bold text-sm">
                                                    Sedang Dipinjam
                                                </span>

                                            <?php elseif ($buku['stok'] > 0): ?>

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

            <div>

                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl sticky top-5">

                    <h2 class="text-xl font-bold text-white mb-5">
                        Data Peminjaman
                    </h2>

                    <div class="mb-5">

                        <label class="block text-sm text-slate-300 mb-2">
                            Nama Peminjam
                        </label>

                        <input type="text"
                            readonly
                            value="<?= htmlspecialchars($user['nama']); ?>"
                            class="w-full bg-slate-800 border border-slate-700 rounded-2xl px-5 py-4 text-white">

                    </div>

                    <div class="mb-5">

                        <label class="block text-sm text-slate-300 mb-2">
                            Tanggal Pinjam
                        </label>

                        <input type="text"
                            readonly
                            value="<?= date('d-m-Y'); ?>"
                            class="w-full bg-slate-800 border border-slate-700 rounded-2xl px-5 py-4 text-white">

                    </div>

                    <div class="mb-6">

                        <label class="block text-sm text-slate-300 mb-2">
                            Deadline Pengembalian
                        </label>

                        <input type="text"
                            readonly
                            value="<?= date('d-m-Y', strtotime('+7 days')); ?>"
                            class="w-full bg-slate-800 border border-slate-700 rounded-2xl px-5 py-4 text-white">

                    </div>

                    <button type="submit"
                        name="pinjam"
                        class="w-full bg-blue-500 hover:bg-blue-600 transition py-4 rounded-2xl font-semibold text-lg shadow-lg text-white">

                        Pinjam Buku

                    </button>

                </div>

            </div>

        </div>

    </form>

</div>

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

<?php include __DIR__ . "/../layout/footer.php"; ?>