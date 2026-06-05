<?php
session_start();

include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../middleware/anggota.php";

$id_user_login = $_SESSION['id_user'];

$id = $_GET['id'] ?? null;

if (!$id) {
    echo "
    <script>
        alert('ID peminjaman tidak ditemukan!');
        window.location='index.php';
    </script>";
    exit;
}

$id = mysqli_real_escape_string($conn, $id);

// ==========================
// AMBIL DATA PEMINJAMAN AWAL
// HARUS MILIK ANGGOTA YANG LOGIN
// ==========================
$getFirst = mysqli_query($conn, "
    SELECT *
    FROM peminjaman
    WHERE id_pinjam = '$id'
    AND id_user = '$id_user_login'
    LIMIT 1
");

$first = mysqli_fetch_assoc($getFirst);

if (!$first) {
    echo "
    <script>
        alert('Data peminjaman tidak ditemukan atau bukan milik kamu!');
        window.location='index.php';
    </script>";
    exit;
}

if ($first['status'] != 'dipinjam') {
    echo "
    <script>
        alert('Peminjaman ini sudah dikembalikan!');
        window.location='index.php';
    </script>";
    exit;
}

// ==========================
// AMBIL SEMUA DATA DALAM GROUP
// ==========================
$id_user = mysqli_real_escape_string($conn, $first['id_user']);
$tanggal_pinjam = mysqli_real_escape_string($conn, $first['tanggal_pinjam']);
$tanggal_kembali = mysqli_real_escape_string($conn, $first['tanggal_kembali']);
$status = mysqli_real_escape_string($conn, $first['status']);

$query = mysqli_query($conn, "
    SELECT 
        p.*,
        b.judul,
        b.cover,
        b.id_buku,
        u.nama,

        COALESCE(penulis.nama_penulis, '-') AS nama_penulis,
        COALESCE(penerbit.nama_penerbit, '-') AS nama_penerbit

    FROM peminjaman p

    JOIN buku b 
        ON p.id_buku = b.id_buku

    JOIN user u 
        ON p.id_user = u.id_user

    LEFT JOIN penulis
        ON b.id_penulis = penulis.id_penulis

    LEFT JOIN penerbit
        ON b.id_penerbit = penerbit.id_penerbit

    WHERE 
        p.id_user = '$id_user_login'
        AND p.tanggal_pinjam = '$tanggal_pinjam'
        AND p.tanggal_kembali = '$tanggal_kembali'
        AND p.status = '$status'
");

$dataPeminjaman = [];

while ($row = mysqli_fetch_assoc($query)) {
    $dataPeminjaman[] = $row;
}

if (count($dataPeminjaman) == 0) {
    echo "
    <script>
        alert('Data peminjaman tidak ditemukan!');
        window.location='index.php';
    </script>";
    exit;
}

// ==========================
// SUBMIT PENGEMBALIAN
// ==========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proses_pengembalian'])) {

    mysqli_begin_transaction($conn);

    try {

        foreach ($dataPeminjaman as $index => $item) {

            $kondisi = $_POST['kondisi_buku'][$index] ?? 'baik';
            $rating = $_POST['rating'][$index] ?? '';
            $ulasan = $_POST['ulasan'][$index] ?? '';

            $kondisi_diizinkan = ['baik', 'rusak', 'hilang'];

            if (!in_array($kondisi, $kondisi_diizinkan)) {
                $kondisi = 'baik';
            }

            if ($rating === '' || !is_numeric($rating) || $rating < 1 || $rating > 5) {
                throw new Exception("Rating wajib diisi antara 1 sampai 5");
            }

            if (trim($ulasan) == '') {
                throw new Exception("Ulasan wajib diisi");
            }

            $kondisi = mysqli_real_escape_string($conn, $kondisi);
            $rating = mysqli_real_escape_string($conn, $rating);
            $ulasan = mysqli_real_escape_string($conn, trim($ulasan));

            $id_pinjam_item = mysqli_real_escape_string($conn, $item['id_pinjam']);
            $id_buku_item = mysqli_real_escape_string($conn, $item['id_buku']);

            // UPDATE PEMINJAMAN
            $updatePeminjaman = mysqli_query($conn, "
                UPDATE peminjaman
                SET 
                    status = 'kembali',
                    kondisi_buku = '$kondisi',
                    tanggal_dikembalikan = CURDATE()
                WHERE id_pinjam = '$id_pinjam_item'
                AND id_user = '$id_user_login'
            ");

            if (!$updatePeminjaman) {
                throw new Exception("Gagal update peminjaman");
            }

            // TAMBAH STOK JIKA BUKU TIDAK HILANG
            if ($kondisi != 'hilang') {

                $updateStok = mysqli_query($conn, "
                    UPDATE buku
                    SET stok = stok + 1
                    WHERE id_buku = '$id_buku_item'
                ");

                if (!$updateStok) {
                    throw new Exception("Gagal update stok buku");
                }
            }

            // ==========================
            // SIMPAN ULASAN
            // ==========================
            $cekUlasan = mysqli_query($conn, "
                SELECT id_ulasan
                FROM ulasan
                WHERE id_user = '$id_user_login'
                AND id_buku = '$id_buku_item'
                LIMIT 1
            ");

            if (mysqli_num_rows($cekUlasan) > 0) {

                $dataUlasan = mysqli_fetch_assoc($cekUlasan);
                $id_ulasan = $dataUlasan['id_ulasan'];

                $updateUlasan = mysqli_query($conn, "
                    UPDATE ulasan
                    SET 
                        rating = '$rating',
                        ulasan = '$ulasan',
                        tanggal_ulasan = NOW()
                    WHERE id_ulasan = '$id_ulasan'
                ");

                if (!$updateUlasan) {
                    throw new Exception("Gagal update ulasan");
                }

            } else {

                $insertUlasan = mysqli_query($conn, "
                    INSERT INTO ulasan (
                        id_user,
                        id_buku,
                        rating,
                        ulasan
                    ) VALUES (
                        '$id_user_login',
                        '$id_buku_item',
                        '$rating',
                        '$ulasan'
                    )
                ");

                if (!$insertUlasan) {
                    throw new Exception("Gagal tambah ulasan");
                }
            }
        }

        mysqli_commit($conn);

        $_SESSION['success'] = "Pengembalian dan ulasan berhasil diproses";
        header("Location: index.php");
        exit;

    } catch (Exception $e) {

        mysqli_rollback($conn);

        $_SESSION['error'] = $e->getMessage();
        header("Location: index.php");
        exit;
    }
}

include __DIR__ . "/../layout/header.php";
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="max-w-6xl mx-auto space-y-8">

    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

        <div>
            <h1 class="text-4xl font-bold text-white">
                Detail Pengembalian Buku
            </h1>

            <p class="text-slate-400 mt-2">
                Periksa kondisi buku, beri rating, dan tulis ulasan sebelum menyelesaikan pengembalian.
            </p>
        </div>

        <a href="index.php"
            class="bg-slate-800 hover:bg-slate-700 transition px-5 py-3 rounded-2xl text-white font-semibold w-fit flex items-center gap-2">

            <i class='bx bx-arrow-back'></i>
            Kembali

        </a>

    </div>

    <!-- INFO CARD -->
    <div class="grid md:grid-cols-3 gap-5">

        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl">

            <p class="text-slate-400 text-sm">
                Total Buku
            </p>

            <h2 class="text-4xl font-bold text-white mt-2">
                <?= count($dataPeminjaman); ?>
            </h2>

            <p class="text-slate-500 text-sm mt-1">
                Buku yang akan dikembalikan
            </p>

        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl">

            <p class="text-slate-400 text-sm">
                Tanggal Pinjam
            </p>

            <h2 class="text-2xl font-bold text-blue-400 mt-2">
                <?= date('d M Y', strtotime($tanggal_pinjam)); ?>
            </h2>

            <p class="text-slate-500 text-sm mt-1">
                Tanggal awal peminjaman
            </p>

        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl">

            <p class="text-slate-400 text-sm">
                Deadline
            </p>

            <h2 class="text-2xl font-bold text-red-400 mt-2">
                <?= date('d M Y', strtotime($tanggal_kembali)); ?>
            </h2>

            <p class="text-slate-500 text-sm mt-1">
                Batas pengembalian buku
            </p>

        </div>

    </div>

    <!-- FORM -->
    <form method="POST" id="formPengembalian">
        <input type="hidden" name="proses_pengembalian" value="1">

        <div class="space-y-5">

            <?php foreach ($dataPeminjaman as $index => $data): ?>

                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl">

                    <div class="grid lg:grid-cols-[120px_1fr] gap-6">

                        <!-- COVER -->
                        <div>
                            <img
                                src="../../assets/img/cover/<?= htmlspecialchars($data['cover']); ?>"
                                class="w-28 h-40 object-cover rounded-2xl border border-slate-700 shadow-lg">

                        </div>

                        <!-- CONTENT -->
                        <div class="space-y-6">

                            <!-- BOOK INFO -->
                            <div>

                                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">

                                    <div>
                                        <h2 class="text-2xl font-bold text-white">
                                            <?= htmlspecialchars($data['judul']); ?>
                                        </h2>

                                        <p class="text-slate-400 text-sm mt-2">
                                            <?= htmlspecialchars($data['nama_penulis']); ?> • <?= htmlspecialchars($data['nama_penerbit']); ?>
                                        </p>
                                    </div>

                                    <span class="bg-blue-500/10 text-blue-400 border border-blue-500/20 px-4 py-2 rounded-full text-xs font-semibold w-fit">
                                        Buku #<?= $index + 1; ?>
                                    </span>

                                </div>

                                <div class="grid md:grid-cols-3 gap-4 mt-5">

                                    <div class="bg-slate-800/60 border border-slate-700 rounded-2xl p-4">

                                        <p class="text-slate-400 text-xs mb-1">
                                            Peminjam
                                        </p>

                                        <h3 class="text-white font-semibold">
                                            <?= htmlspecialchars($data['nama']); ?>
                                        </h3>

                                    </div>

                                    <div class="bg-slate-800/60 border border-slate-700 rounded-2xl p-4">

                                        <p class="text-slate-400 text-xs mb-1">
                                            Tanggal Pinjam
                                        </p>

                                        <h3 class="text-white font-semibold">
                                            <?= date('d M Y', strtotime($data['tanggal_pinjam'])); ?>
                                        </h3>

                                    </div>

                                    <div class="bg-slate-800/60 border border-slate-700 rounded-2xl p-4">

                                        <p class="text-slate-400 text-xs mb-1">
                                            Deadline
                                        </p>

                                        <h3 class="text-white font-semibold">
                                            <?= date('d M Y', strtotime($data['tanggal_kembali'])); ?>
                                        </h3>

                                    </div>

                                </div>

                            </div>

                            <!-- INPUT AREA -->
                            <div class="grid lg:grid-cols-3 gap-5">

                                <!-- KONDISI -->
                                <div>

                                    <label class="block text-sm text-slate-300 mb-2">
                                        Kondisi Saat Kembali
                                    </label>

                                    <select
                                        name="kondisi_buku[]"
                                        required
                                        class="w-full bg-slate-800 border border-slate-700 rounded-2xl px-5 py-4 text-white outline-none focus:border-emerald-500">

                                        <option value="baik">Baik</option>
                                        <option value="rusak">Rusak</option>
                                        <option value="hilang">Hilang</option>

                                    </select>

                                </div>

                                <!-- RATING -->
                                <div>

                                    <label class="block text-sm text-slate-300 mb-2">
                                        Rating Buku
                                    </label>

                                    <select
                                        name="rating[]"
                                        required
                                        class="w-full bg-slate-800 border border-slate-700 rounded-2xl px-5 py-4 text-white outline-none focus:border-yellow-500">

                                        <option value="">Pilih Rating</option>
                                        <option value="5">★★★★★ - Sangat Bagus</option>
                                        <option value="4">★★★★☆ - Bagus</option>
                                        <option value="3">★★★☆☆ - Cukup</option>
                                        <option value="2">★★☆☆☆ - Kurang</option>
                                        <option value="1">★☆☆☆☆ - Buruk</option>

                                    </select>

                                </div>

                                <!-- STATUS -->
                                <div>

                                    <label class="block text-sm text-slate-300 mb-2">
                                        Status
                                    </label>

                                    <div class="w-full bg-emerald-500/10 border border-emerald-500/20 rounded-2xl px-5 py-4 text-emerald-400 font-semibold">
                                        Siap Dikembalikan
                                    </div>

                                </div>

                            </div>

                            <!-- ULASAN -->
                            <div>

                                <label class="block text-sm text-slate-300 mb-2">
                                    Ulasan Buku
                                </label>

                                <textarea
                                    name="ulasan[]"
                                    required
                                    rows="4"
                                    placeholder="Tulis pendapat kamu tentang buku ini..."
                                    class="w-full bg-slate-800 border border-slate-700 rounded-2xl px-5 py-4 text-white outline-none focus:border-blue-500 resize-none"></textarea>

                                <p class="text-slate-500 text-xs mt-2">
                                    Ulasan ini akan tersimpan di halaman Ulasan Saya.
                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

        <!-- ACTION BAR -->
        <div class="sticky bottom-4 mt-8 bg-slate-900/95 backdrop-blur border border-slate-800 rounded-3xl p-5 shadow-2xl">

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                <div>
                    <h3 class="text-white font-bold">
                        Selesaikan Pengembalian?
                    </h3>

                    <p class="text-slate-400 text-sm">
                        Pastikan kondisi buku, rating, dan ulasan sudah benar.
                    </p>
                </div>

                <button
                    type="button"
                    onclick="confirmSubmit()"
                    class="bg-emerald-500 hover:bg-emerald-600 transition px-8 py-4 rounded-2xl font-semibold text-white flex items-center justify-center gap-2">

                    <i class='bx bx-check-circle text-xl'></i>
                    Simpan Pengembalian & Ulasan

                </button>

            </div>

        </div>

    </form>

</div>

<script>
    function confirmSubmit() {
        Swal.fire({
            title: 'Selesaikan Pengembalian?',
            text: 'Pastikan kondisi buku, rating, dan ulasan sudah benar.',
            icon: 'question',
            background: '#0f172a',
            color: '#fff',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Simpan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('formPengembalian').submit();
            }
        });
    }
</script>

<?php include __DIR__ . "/../layout/footer.php"; ?>