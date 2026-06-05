<?php
session_start();

include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../middleware/admin.php";

// ==========================
// AMBIL ID PEMINJAMAN
// ==========================
$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['error'] = "ID peminjaman tidak ditemukan.";
    header("Location: peminjaman.php");
    exit;
}

$id = (int) $id;

// ==========================
// AMBIL DATA PEMINJAMAN AWAL
// ==========================
$getFirst = mysqli_query($conn, "
    SELECT *
    FROM peminjaman
    WHERE id_pinjam = '$id'
    LIMIT 1
");

if (!$getFirst) {
    die("Query Error: " . mysqli_error($conn));
}

$first = mysqli_fetch_assoc($getFirst);

if (!$first) {
    $_SESSION['error'] = "Data peminjaman tidak ditemukan.";
    header("Location: peminjaman.php");
    exit;
}

if ($first['status'] != 'dipinjam') {
    $_SESSION['error'] = "Peminjaman ini sudah dikembalikan.";
    header("Location: peminjaman.php");
    exit;
}

// ==========================
// DATA GROUP PEMINJAMAN
// ==========================
$id_user = (int) $first['id_user'];
$tanggal_pinjam = mysqli_real_escape_string($conn, $first['tanggal_pinjam']);
$tanggal_kembali = mysqli_real_escape_string($conn, $first['tanggal_kembali']);
$status = mysqli_real_escape_string($conn, $first['status']);

// ==========================
// AMBIL SEMUA BUKU DALAM GROUP
// ==========================
$query = mysqli_query($conn, "
    SELECT 
        p.*,

        b.id_buku,
        b.judul,
        b.cover,

        u.nama AS nama_user,
        u.email,

        COALESCE(penulis.nama_penulis, '-') AS nama_penulis,
        COALESCE(penerbit.nama_penerbit, '-') AS nama_penerbit,
        COALESCE(kategori.nama_kategori, '-') AS nama_kategori

    FROM peminjaman p

    JOIN buku b
        ON p.id_buku = b.id_buku

    JOIN user u
        ON p.id_user = u.id_user

    LEFT JOIN penulis
        ON b.id_penulis = penulis.id_penulis

    LEFT JOIN penerbit
        ON b.id_penerbit = penerbit.id_penerbit

    LEFT JOIN kategori
        ON b.id_kategori = kategori.id_kategori

    WHERE 
        p.id_user = '$id_user'
        AND p.tanggal_pinjam = '$tanggal_pinjam'
        AND p.tanggal_kembali = '$tanggal_kembali'
        AND p.status = '$status'

    ORDER BY p.id_pinjam ASC
");

if (!$query) {
    die("Query Error: " . mysqli_error($conn));
}

$dataPeminjaman = [];

while ($row = mysqli_fetch_assoc($query)) {
    $dataPeminjaman[] = $row;
}

if (count($dataPeminjaman) == 0) {
    $_SESSION['error'] = "Data peminjaman tidak ditemukan.";
    header("Location: peminjaman.php");
    exit;
}

// ==========================
// HITUNG KETERLAMBATAN
// ==========================
$tanggal_hari_ini = date('Y-m-d');
$hari_telat = 0;

if ($tanggal_hari_ini > $tanggal_kembali) {
    $tgl1 = new DateTime($tanggal_kembali);
    $tgl2 = new DateTime($tanggal_hari_ini);
    $hari_telat = $tgl1->diff($tgl2)->days;
}

// ==========================
// SUBMIT PENGEMBALIAN + ULASAN
// ==========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proses_pengembalian'])) {

    mysqli_begin_transaction($conn);

    try {

        foreach ($dataPeminjaman as $index => $item) {

            $kondisi = $_POST['kondisi_buku'][$index] ?? '';
            $rating  = $_POST['rating'][$index] ?? '';
            $ulasan  = $_POST['ulasan'][$index] ?? '';

            // ==========================
            // VALIDASI KONDISI
            // ==========================
            $kondisi_diizinkan = ['baik', 'rusak', 'hilang'];

            if (!in_array($kondisi, $kondisi_diizinkan)) {
                throw new Exception("Kondisi buku wajib dipilih dengan benar.");
            }

            // ==========================
            // VALIDASI RATING
            // ==========================
            if ($rating === '' || !is_numeric($rating) || $rating < 1 || $rating > 5) {
                throw new Exception("Rating wajib diisi antara 1 sampai 5.");
            }

            // ==========================
            // VALIDASI ULASAN
            // ==========================
            if (trim($ulasan) == '') {
                throw new Exception("Ulasan buku wajib diisi.");
            }

            $kondisi = mysqli_real_escape_string($conn, $kondisi);
            $rating  = (int) $rating;
            $ulasan  = mysqli_real_escape_string($conn, trim($ulasan));

            $id_pinjam_item = (int) $item['id_pinjam'];
            $id_buku_item   = (int) $item['id_buku'];
            $id_user_item   = (int) $item['id_user'];

            // ==========================
            // UPDATE PEMINJAMAN
            // ==========================
            $updatePeminjaman = mysqli_query($conn, "
                UPDATE peminjaman
                SET 
                    status = 'selesai',
                    kondisi_buku = '$kondisi',
                    tanggal_dikembalikan = CURDATE()
                WHERE id_pinjam = '$id_pinjam_item'
                AND status = 'dipinjam'
            ");

            if (!$updatePeminjaman) {
                throw new Exception("Gagal update data peminjaman.");
            }

            // ==========================
            // TAMBAH STOK HANYA JIKA BUKU BAIK
            // ==========================
            if ($kondisi == 'baik') {

                $updateStok = mysqli_query($conn, "
                    UPDATE buku
                    SET stok = stok + 1
                    WHERE id_buku = '$id_buku_item'
                ");

                if (!$updateStok) {
                    throw new Exception("Gagal update stok buku.");
                }
            }

            // ==========================
            // SIMPAN ULASAN
            // ULASAN DISIMPAN ATAS NAMA ANGGOTA PEMINJAM
            // ==========================
            $cekUlasan = mysqli_query($conn, "
                SELECT id_ulasan
                FROM ulasan
                WHERE id_user = '$id_user_item'
                AND id_buku = '$id_buku_item'
                LIMIT 1
            ");

            if (!$cekUlasan) {
                throw new Exception("Gagal cek data ulasan.");
            }

            if (mysqli_num_rows($cekUlasan) > 0) {

                $dataUlasan = mysqli_fetch_assoc($cekUlasan);
                $id_ulasan = (int) $dataUlasan['id_ulasan'];

                $updateUlasan = mysqli_query($conn, "
                    UPDATE ulasan
                    SET
                        rating = '$rating',
                        ulasan = '$ulasan',
                        tanggal_ulasan = NOW()
                    WHERE id_ulasan = '$id_ulasan'
                ");

                if (!$updateUlasan) {
                    throw new Exception("Gagal update ulasan.");
                }

            } else {

                $insertUlasan = mysqli_query($conn, "
                    INSERT INTO ulasan (
                        id_user,
                        id_buku,
                        rating,
                        ulasan,
                        tanggal_ulasan
                    ) VALUES (
                        '$id_user_item',
                        '$id_buku_item',
                        '$rating',
                        '$ulasan',
                        NOW()
                    )
                ");

                if (!$insertUlasan) {
                    throw new Exception("Gagal tambah ulasan.");
                }
            }
        }

        mysqli_commit($conn);

        $_SESSION['success'] = "Pengembalian dan ulasan berhasil diproses.";
        header("Location: peminjaman.php");
        exit;

    } catch (Exception $e) {

        mysqli_rollback($conn);

        $_SESSION['error'] = $e->getMessage();
        header("Location: detail.php?id=" . $id);
        exit;
    }
}

include __DIR__ . "/../layout/header.php";
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

<div class="max-w-6xl mx-auto space-y-8">

    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

        <div>
            <h1 class="text-4xl font-bold text-white">
                Detail Pengembalian Buku
            </h1>

            <p class="text-slate-400 mt-2">
                Periksa kondisi buku, beri rating, dan isi ulasan sebelum menyelesaikan pengembalian.
            </p>
        </div>

        <a href="peminjaman.php"
            class="bg-slate-800 hover:bg-slate-700 transition px-5 py-3 rounded-2xl text-white font-semibold w-fit flex items-center gap-2">

            <i class='bx bx-arrow-back'></i>
            Kembali

        </a>

    </div>

    <!-- INFO CARD -->
    <div class="grid md:grid-cols-4 gap-5">

        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl">

            <p class="text-slate-400 text-sm">
                Nama Anggota
            </p>

            <h2 class="text-xl font-bold text-white mt-2">
                <?= htmlspecialchars($dataPeminjaman[0]['nama_user']); ?>
            </h2>

            <p class="text-slate-500 text-sm mt-1">
                <?= htmlspecialchars($dataPeminjaman[0]['email']); ?>
            </p>

        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl">

            <p class="text-slate-400 text-sm">
                Total Buku
            </p>

            <h2 class="text-4xl font-bold text-white mt-2">
                <?= count($dataPeminjaman); ?>
            </h2>

            <p class="text-slate-500 text-sm mt-1">
                Buku dikembalikan
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
                <?php if ($hari_telat > 0): ?>
                    Telat <?= $hari_telat; ?> hari
                <?php else: ?>
                    Tidak terlambat
                <?php endif; ?>
            </p>

        </div>

    </div>

    <!-- FORM -->
    <form method="POST" id="formPengembalian">

        <input type="hidden" name="proses_pengembalian" value="1">

        <div class="space-y-5">

            <?php foreach ($dataPeminjaman as $index => $data): ?>

                <?php
                $cover = !empty($data['cover']) ? $data['cover'] : 'default.jpg';
                ?>

                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl">

                    <div class="grid lg:grid-cols-[120px_1fr] gap-6">

                        <!-- COVER -->
                        <div>
                            <img
                                src="../../assets/img/cover/<?= htmlspecialchars($cover); ?>"
                                class="w-28 h-40 object-cover rounded-2xl border border-slate-700 shadow-lg"
                                alt="Cover Buku">
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
                                            <?= htmlspecialchars($data['nama_penulis']); ?>
                                            •
                                            <?= htmlspecialchars($data['nama_penerbit']); ?>
                                        </p>

                                        <span class="inline-block mt-3 bg-blue-500/10 text-blue-400 border border-blue-500/20 px-3 py-1 rounded-full text-xs font-semibold">
                                            <?= htmlspecialchars($data['nama_kategori']); ?>
                                        </span>
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
                                            <?= htmlspecialchars($data['nama_user']); ?>
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

                                        <option value="">Pilih Kondisi</option>
                                        <option value="baik">Baik</option>
                                        <option value="rusak">Rusak</option>
                                        <option value="hilang">Hilang</option>

                                    </select>

                                    <p class="text-slate-500 text-xs mt-2">
                                        Stok hanya bertambah jika kondisi buku baik.
                                    </p>

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
                                        Status Proses
                                    </label>

                                    <div class="w-full bg-emerald-500/10 border border-emerald-500/20 rounded-2xl px-5 py-4 text-emerald-400 font-semibold">
                                        Siap Diproses
                                    </div>

                                    <p class="text-slate-500 text-xs mt-2">
                                        Setelah disimpan, status menjadi selesai.
                                    </p>

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
                                    placeholder="Tulis ulasan untuk buku ini..."
                                    class="w-full bg-slate-800 border border-slate-700 rounded-2xl px-5 py-4 text-white outline-none focus:border-blue-500 resize-none"></textarea>

                                <p class="text-slate-500 text-xs mt-2">
                                    Ulasan ini akan tersimpan sebagai ulasan milik anggota peminjam.
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
                        Pastikan kondisi buku, rating, dan ulasan sudah benar sebelum disimpan.
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