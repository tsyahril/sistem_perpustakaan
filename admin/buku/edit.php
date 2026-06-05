<?php
session_start();

include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../middleware/admin.php";

$id = $_GET['id'];

// ==========================
// AMBIL DATA BUKU
// ==========================
$query_buku = mysqli_query(
    $conn,
    "SELECT * FROM buku WHERE id_buku = '$id'"
);

$data = mysqli_fetch_assoc($query_buku);

if (!$data) {

    header("Location: buku.php");
    exit();
}

// ==========================
// UPDATE BUKU
// ==========================
if (isset($_POST['update'])) {

    $judul       = mysqli_real_escape_string($conn, $_POST['judul']);
    $id_penulis  = $_POST['id_penulis'];
    $isbn        = mysqli_real_escape_string($conn, $_POST['isbn']);
    $id_penerbit = $_POST['id_penerbit'];
    $deskripsi   = mysqli_real_escape_string($conn, $_POST['deskripsi']);

    $tahun       = (int) $_POST['tahun'];
    $harga       = (int) $_POST['harga'];
    $stok        = (int) $_POST['stok'];

    $id_kategori = $_POST['id_kategori'];

    // ==========================
    // UPLOAD COVER
    // ==========================
    $nama_file = $_FILES['cover']['name'];

    if ($nama_file != "") {

        $ekstensi_izin = ['jpg', 'jpeg', 'png'];

        $pecah     = explode('.', $nama_file);
        $ekstensi  = strtolower(end($pecah));

        $tmp_file  = $_FILES['cover']['tmp_name'];
        $nama_baru = time() . "-" . $nama_file;

        if (in_array($ekstensi, $ekstensi_izin)) {

            // Hapus cover lama
            if (
                $data['cover'] != 'default.jpg' &&
                file_exists("../../assets/img/cover/" . $data['cover'])
            ) {

                unlink("../../assets/img/cover/" . $data['cover']);
            }

            move_uploaded_file(
                $tmp_file,
                "../../assets/img/cover/" . $nama_baru
            );

            $cover_db = $nama_baru;
        } else {

            $cover_db = $data['cover'];
        }
    } else {

        $cover_db = $data['cover'];
    }

    // ==========================
    // QUERY UPDATE
    // ==========================
    $query_update = "
        UPDATE buku SET

            judul       = '$judul',
            id_penulis  = '$id_penulis',
            isbn        = '$isbn',
            id_penerbit = '$id_penerbit',
            deskripsi   = '$deskripsi',
            tahun       = '$tahun',
            harga       = '$harga',
            stok        = '$stok',
            id_kategori = '$id_kategori',
            cover       = '$cover_db'

        WHERE id_buku = '$id'
    ";

    if (mysqli_query($conn, $query_update)) {

        $_SESSION['success'] = "Data buku berhasil diperbarui!";

        header("Location: buku.php");
        exit();
    }
}

include __DIR__ .  "/../../admin/layout/header.php";
?>

<!-- Tailwind -->
<script src="https://cdn.tailwindcss.com"></script>

<style>
    /* Hilangkan tombol panah bawaan input number */
    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    input[type=number] {
        -moz-appearance: textfield;
        appearance: textfield;
    }
</style>

<div class="max-w-6xl mx-auto">

    <!-- HEADER -->
    <div class="flex items-center justify-between mb-8">

        <div>

            <h2 class="text-3xl font-bold text-white flex items-center gap-3">

                <i class='bx bx-edit-alt text-blue-400'></i>

                Edit Buku

            </h2>

            <p class="text-slate-400 mt-1">
                Perbarui informasi dan detail buku.
            </p>

        </div>

    </div>

    <!-- CARD -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl p-8">

        <form method="POST"
            enctype="multipart/form-data"
            class="space-y-8">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- COVER -->
                <div>

                    <label class="block text-sm text-slate-300 mb-3">
                        Cover Buku
                    </label>

                    <div class="bg-slate-800 border border-slate-700 rounded-2xl p-5">

                        <img src="../../assets/img/cover/<?= $data['cover']; ?>"
                            class="w-full h-[380px] object-cover rounded-xl shadow-lg">

                        <div class="mt-5">

                            <label class="block text-sm text-slate-400 mb-2">
                                Ganti Cover
                            </label>

                            <input type="file"
                                name="cover"
                                accept=".jpg,.jpeg,.png"
                                class="w-full text-slate-300">

                            <p class="text-xs text-slate-500 mt-2">
                                Format: JPG, JPEG, PNG
                            </p>

                        </div>

                    </div>

                </div>

                <!-- FORM -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- JUDUL + ISBN -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                        <!-- Judul -->
                        <div class="lg:col-span-2">

                            <label class="block text-sm text-slate-300 mb-2">
                                Judul Buku
                            </label>

                            <input type="text"
                                name="judul"
                                required
                                value="<?= htmlspecialchars($data['judul']); ?>"
                                class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-blue-500">

                        </div>

                        <!-- ISBN -->
                        <div>

                            <label class="block text-sm text-slate-300 mb-2">
                                ISBN
                            </label>

                            <input type="text"
                                name="isbn"
                                value="<?= htmlspecialchars($data['isbn']); ?>"
                                class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-blue-500">

                        </div>

                    </div>


                    <!-- PENULIS + PENERBIT -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

                        <!-- PENULIS -->
                        <div>

                            <label class="block text-sm text-slate-300 mb-2">
                                Penulis
                            </label>

                            <select name="id_penulis"
                                required
                                class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-blue-500">

                                <option value="">-- Pilih Penulis --</option>

                                <?php
                                $list_penulis = mysqli_query($conn, "SELECT * FROM penulis ORDER BY nama_penulis ASC");

                                while ($p = mysqli_fetch_assoc($list_penulis)):
                                ?>

                                    <option value="<?= $p['id_penulis']; ?>"
                                        <?= ($p['id_penulis'] == $data['id_penulis']) ? 'selected' : ''; ?>>

                                        <?= htmlspecialchars($p['nama_penulis']); ?>

                                    </option>

                                <?php endwhile; ?>

                            </select>

                        </div>

                        <!-- PENERBIT -->
                        <div>

                            <label class="block text-sm text-slate-300 mb-2">
                                Penerbit
                            </label>

                            <select name="id_penerbit"
                                required
                                class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-blue-500">

                                <option value="">-- Pilih Penerbit --</option>

                                <?php
                                $list_penerbit = mysqli_query($conn, "SELECT * FROM penerbit ORDER BY nama_penerbit ASC");

                                while ($pr = mysqli_fetch_assoc($list_penerbit)):
                                ?>

                                    <option value="<?= $pr['id_penerbit']; ?>"
                                        <?= ($pr['id_penerbit'] == $data['id_penerbit']) ? 'selected' : ''; ?>>

                                        <?= htmlspecialchars($pr['nama_penerbit']); ?>

                                    </option>

                                <?php endwhile; ?>

                            </select>

                        </div>

                    </div>

                    <!-- DESKRIPSI -->
                    <div>

                        <label class="block text-sm text-slate-300 mb-2">
                            Deskripsi Buku
                        </label>

                        <textarea name="deskripsi"
                            rows="5"
                            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none resize-none focus:border-blue-500"><?= htmlspecialchars($data['deskripsi']); ?></textarea>

                    </div>

                    <!-- DETAIL -->
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">

                        <!-- Kategori -->
                        <div>

                            <label class="block text-sm text-slate-300 mb-2">
                                Kategori
                            </label>

                            <select name="id_kategori"
                                required
                                class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-blue-500">

                                <?php
                                $list_kat = mysqli_query(
                                    $conn,
                                    "SELECT * FROM kategori ORDER BY nama_kategori ASC"
                                );

                                while ($kat = mysqli_fetch_assoc($list_kat)):
                                ?>

                                    <option value="<?= $kat['id_kategori']; ?>"
                                        <?= ($kat['id_kategori'] == $data['id_kategori']) ? 'selected' : ''; ?>>

                                        <?= htmlspecialchars($kat['nama_kategori']); ?>

                                    </option>

                                <?php endwhile; ?>

                            </select>

                        </div>

                        <!-- Tahun -->
                        <div>

                            <label class="block text-sm text-slate-300 mb-2">
                                Tahun
                            </label>

                            <input type="number"
                                name="tahun"
                                required
                                value="<?= $data['tahun']; ?>"
                                class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-blue-500">

                        </div>

                        <!-- Harga -->
                        <div>

                            <label class="block text-sm text-slate-300 mb-2">
                                Harga Buku
                            </label>

                            <div class="relative">

                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-medium">
                                    Rp
                                </span>

                                <input type="number"
                                    name="harga"
                                    min="0"
                                    required
                                    value="<?= $data['harga']; ?>"
                                    class="w-full bg-slate-800 border border-slate-700 rounded-xl pl-12 pr-4 py-3 text-white outline-none focus:border-emerald-500">

                            </div>

                        </div>

                        <!-- Stok -->
                        <div>

                            <label class="block text-sm text-slate-300 mb-2">
                                Stok
                            </label>

                            <div class="flex items-center bg-slate-800 border border-slate-700 rounded-xl overflow-hidden h-[50px]">

                                <!-- Minus -->
                                <button type="button"
                                    onclick="kurangStok()"
                                    class="w-14 h-full flex items-center justify-center bg-slate-700 hover:bg-slate-600 text-white transition text-xl">

                                    <i class='bx bx-minus'></i>

                                </button>

                                <!-- Input -->
                                <input type="number"
                                    id="stokInput"
                                    name="stok"
                                    value="<?= $data['stok']; ?>"
                                    min="0"
                                    class="w-full h-full bg-transparent text-center text-white outline-none text-lg font-semibold">

                                <!-- Plus -->
                                <button type="button"
                                    onclick="tambahStok()"
                                    class="w-14 h-full flex items-center justify-center bg-slate-700 hover:bg-slate-600 text-white transition text-xl">

                                    <i class='bx bx-plus'></i>

                                </button>

                            </div>

                        </div>

                    <!-- BUTTON -->
                    <div class="flex items-center gap-4 pt-5 border-t border-slate-800">

                        <button type="submit"
                            name="update"
                            class="bg-blue-500 hover:bg-blue-600 transition px-6 py-3 rounded-xl text-white font-medium flex items-center gap-2 shadow-lg">

                            <i class='bx bx-save'></i>

                            Simpan Perubahan

                        </button>

                        <a href="buku.php"
                            class="bg-slate-700 hover:bg-slate-600 transition px-6 py-3 rounded-xl text-white">

                            Batal

                        </a>

                    </div>

                </div>

            </div>

        </form>

    </div>

</div>

<script>
    function tambahStok() {

        let input =
            document.getElementById('stokInput');

        input.stepUp();
    }

    function kurangStok() {

        let input =
            document.getElementById('stokInput');

        input.stepDown();
    }
</script>

<?php include __DIR__ .  "/../../admin/layout/footer.php"; ?>