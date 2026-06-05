<?php
session_start();
include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../middleware/admin.php";

if (isset($_POST['simpan'])) {

    $judul       = mysqli_real_escape_string($conn, $_POST['judul']);
    $id_penulis  = $_POST['id_penulis'];
    $isbn        = mysqli_real_escape_string($conn, $_POST['isbn']);
    $id_penerbit = $_POST['id_penerbit'];
    $deskripsi   = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $tahun       = $_POST['tahun'];
    $stok        = $_POST['stok'];
    $harga       = $_POST['harga'];
    $id_kategori = $_POST['id_kategori'];

     // =========================
    // PENULIS AUTO INSERT/GET ID
    // =========================
    $nama_penulis = mysqli_real_escape_string($conn, $_POST['penulis'] ?? '');

    if ($nama_penulis == '') {
        die("Penulis wajib diisi");
    }

    $cek = mysqli_query($conn, "SELECT id_penulis FROM penulis WHERE nama_penulis='$nama_penulis'");
    $row = mysqli_fetch_assoc($cek);

    if ($row) {
        $id_penulis = $row['id_penulis'];
    } else {
        mysqli_query($conn, "INSERT INTO penulis (nama_penulis) VALUES ('$nama_penulis')");
        $id_penulis = mysqli_insert_id($conn);
    }

    // =========================
    // PENERBIT AUTO INSERT/GET ID
    // =========================
    $nama_penerbit = mysqli_real_escape_string($conn, $_POST['penerbit'] ?? '');

    if ($nama_penerbit == '') {
        die("Penerbit wajib diisi");
    }

    $cek2 = mysqli_query($conn, "SELECT id_penerbit FROM penerbit WHERE nama_penerbit='$nama_penerbit'");
    $row2 = mysqli_fetch_assoc($cek2);

    if ($row2) {
        $id_penerbit = $row2['id_penerbit'];
    } else {
        mysqli_query($conn, "INSERT INTO penerbit (nama_penerbit) VALUES ('$nama_penerbit')");
        $id_penerbit = mysqli_insert_id($conn);
    }


    // Upload Cover
    $nama_file = $_FILES['cover']['name'];
    $cover_db = "default.jpg";

    if ($nama_file != "") {

        $ekstensi_izin = ['jpg', 'jpeg', 'png'];

        $pecah = explode('.', $nama_file);

        $ekstensi = strtolower(end($pecah));

        $tmp_file = $_FILES['cover']['tmp_name'];

        $nama_baru = time() . "-" . $nama_file;

        if (in_array($ekstensi, $ekstensi_izin)) {

            move_uploaded_file(
                $tmp_file,
                "../../assets/img/cover/" . $nama_baru
            );

            $cover_db = $nama_baru;
        }
    }

    $query = "INSERT INTO buku (
            judul,
            cover,
            id_penulis,
            isbn,
            id_penerbit,
            deskripsi,
            tahun,
            stok,
            harga,
            id_kategori
        ) VALUES (
            '$judul',
            '$cover_db',
            '$id_penulis',
            '$isbn',
            '$id_penerbit',
            '$deskripsi',
            '$tahun',
            '$stok',
            '$harga',
            '$id_kategori'
        )";

    if (mysqli_query($conn, $query)) {

        $_SESSION['success'] = "Buku berhasil ditambahkan!";

        header("Location: buku.php");
        exit();
    }
}

$list_kategori = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
$list_penulis  = mysqli_query($conn, "SELECT * FROM penulis ORDER BY nama_penulis ASC");
$list_penerbit = mysqli_query($conn, "SELECT * FROM penerbit ORDER BY nama_penerbit ASC");

include __DIR__ .  "/../../admin/layout/header.php";

?>

<!-- Tailwind -->
<script src="https://cdn.tailwindcss.com"></script>

<div class="max-w-6xl mx-auto">

    <!-- Header -->
    <div class="flex items-center justify-between mb-8">

        <div>

            <h2 class="text-3xl font-bold text-white flex items-center gap-3">

                <i class='bx bx-book-add text-blue-400'></i>

                Tambah Buku

            </h2>

            <p class="text-slate-400 mt-1">
                Tambahkan koleksi buku baru ke perpustakaan.
            </p>

        </div>

    </div>

    <!-- Card -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl p-8">

        <form method="POST"
            enctype="multipart/form-data"
            class="space-y-6">

            <!-- Judul + ISBN -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                <div class="lg:col-span-2">

                    <label class="block text-sm text-slate-300 mb-2">
                        Judul Buku
                    </label>

                    <input type="text"
                        name="judul"
                        required
                        placeholder="Masukkan judul buku..."
                        class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-blue-500">

                </div>

                <div>

                    <label class="block text-sm text-slate-300 mb-2">
                        ISBN
                    </label>

                    <input type="text"
                        name="isbn"
                        placeholder="978-602..."
                        class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-blue-500">

                </div>

            </div>

            <!-- PENULIS -->
            <input type="text" name="penulis" placeholder="Ketik penulis"
                class="w-full p-3 bg-slate-800 text-white rounded-xl" list="list_penulis" required>

            <datalist id="list_penulis">
                <?php
                $p = mysqli_query($conn, "SELECT * FROM penulis");
                while ($r = mysqli_fetch_assoc($p)):
                ?>
                    <option value="<?= $r['nama_penulis']; ?>">
                <?php endwhile; ?>
            </datalist>

            <!-- PENERBIT -->
            <input type="text" name="penerbit" placeholder="Ketik penerbit"
                class="w-full p-3 bg-slate-800 text-white rounded-xl" list="list_penerbit" required>

            <datalist id="list_penerbit">
                <?php
                $p2 = mysqli_query($conn, "SELECT * FROM penerbit");
                while ($r2 = mysqli_fetch_assoc($p2)):
                ?>
                    <option value="<?= $r2['nama_penerbit']; ?>">
                <?php endwhile; ?>
            </datalist>

            <!-- Deskripsi -->
            <div>

                <label class="block text-sm text-slate-300 mb-2">
                    Deskripsi Buku
                </label>

                <textarea name="deskripsi"
                    rows="5"
                    placeholder="Tulis sinopsis atau deskripsi buku..."
                    class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none resize-none focus:border-blue-500"></textarea>

            </div>

            <!-- Detail Buku -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">

                <!-- Kategori -->
                <div>

                    <label class="block text-sm text-slate-300 mb-2">
                        Kategori
                    </label>

                    <select name="id_kategori"
                        required
                        class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-blue-500">

                        <option value="">
                            -- Pilih Kategori --
                        </option>

                        <?php while ($kat = mysqli_fetch_assoc($list_kategori)): ?>

                            <option value="<?= $kat['id_kategori']; ?>">

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
                        value="<?= date('Y') ?>"
                        class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-blue-500">

                </div>

                <!-- Harga -->
                <div>

                    <label class="block text-sm text-slate-300 mb-2">
                        Harga
                    </label>

                    <input type="number"
                        name="harga"
                        required
                        min="0"
                        placeholder="50000"
                        class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-blue-500">

                </div>

                <!-- Stok -->
                <div>

                    <label class="block text-sm text-slate-300 mb-2">
                        Stok
                    </label>

                    <div class="flex items-center bg-slate-800 border border-slate-700 rounded-xl overflow-hidden">

                        <button type="button"
                            onclick="kurangStok()"
                            class="px-4 py-3 bg-slate-700 hover:bg-slate-600 text-white transition">

                            <i class='bx bx-minus'></i>

                        </button>

                        <input type="number"
                            id="stokInput"
                            name="stok"
                            value="0"
                            min="0"
                            class="w-full bg-transparent text-center text-white outline-none">

                        <button type="button"
                            onclick="tambahStok()"
                            class="px-4 py-3 bg-slate-700 hover:bg-slate-600 text-white transition">

                            <i class='bx bx-plus'></i>

                        </button>

                    </div>

                </div>


            <!-- Upload Cover -->
            <div>

                <label class="block text-sm text-slate-300 mb-2">
                    Cover Buku
                </label>

                <div class="border-2 border-dashed border-slate-700 rounded-2xl p-6 bg-slate-800/50">

                    <input type="file"
                        name="cover"
                        accept=".jpg,.jpeg,.png"
                        class="w-full text-slate-300">

                    <p class="text-xs text-slate-500 mt-2">
                        Format: JPG, JPEG, PNG
                    </p>

                </div>

            </div>

            <!-- Action -->
            <div class="flex items-center gap-4 pt-5 border-t border-slate-800">

                <button type="submit"
                    name="simpan"
                    class="bg-blue-500 hover:bg-blue-600 transition px-6 py-3 rounded-xl text-white font-medium flex items-center gap-2 shadow-lg">

                    <i class='bx bx-save'></i>

                    Simpan Buku

                </button>

                <a href="buku.php"
                    class="bg-slate-700 hover:bg-slate-600 transition px-6 py-3 rounded-xl text-white">

                    Batal

                </a>

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

<style>
/* Hilangkan scrollbar seluruh halaman */
body {
    overflow-x: hidden;
}

/* Scrollbar custom (atau hidden total) */
::-webkit-scrollbar {
    width: 0px;
    height: 0px;
}

/* Firefox */
* {
    scrollbar-width: none;
    -ms-overflow-style: none;
}

/* Hilangkan spinner number input */
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

<?php include __DIR__ .  "/../../admin/layout/footer.php"; ?>