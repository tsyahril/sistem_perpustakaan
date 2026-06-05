<?php
session_start();

include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../middleware/petugas.php";
include __DIR__ . "/../layout/header.php";

// ==========================
// QUERY PENULIS
// ==========================
$penulis = mysqli_query($conn, "
    SELECT *
    FROM penulis
    ORDER BY nama_penulis ASC
");

// ==========================
// QUERY PENERBIT
// ==========================
$penerbit = mysqli_query($conn, "
    SELECT *
    FROM penerbit
    ORDER BY nama_penerbit ASC
");

// ==========================
// QUERY KATEGORI
// ==========================
$kategori = mysqli_query($conn, "
    SELECT *
    FROM kategori
    ORDER BY nama_kategori ASC
");

// ==========================
// SIMPAN BUKU
// ==========================
if (isset($_POST['submit'])) {

    $judul       = mysqli_real_escape_string($conn, $_POST['judul']);
    $isbn        = mysqli_real_escape_string($conn, $_POST['isbn']);
    $tahun       = (int) $_POST['tahun'];
    $stok        = (int) $_POST['stok'];
    $harga       = (int) $_POST['harga'];
    $deskripsi   = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $id_kategori = (int) $_POST['id_kategori'];
    $id_penulis  = (int) $_POST['id_penulis'];
    $id_penerbit = (int) $_POST['id_penerbit'];

    // ==========================
    // VALIDASI
    // ==========================
    if ($judul == '' || $tahun <= 0 || $stok < 0 || $harga < 0 || $id_kategori <= 0 || $id_penulis <= 0 || $id_penerbit <= 0) {

        echo "
        <script>
            Swal.fire({
                icon: 'warning',
                title: 'Data belum lengkap',
                text: 'Pastikan semua data buku sudah diisi dengan benar.',
                background: '#0f172a',
                color: '#fff',
                confirmButtonColor: '#f59e0b'
            });
        </script>
        ";

    } else {

        // ==========================
        // UPLOAD COVER
        // ==========================
        $cover = 'default.jpg';

        if (!empty($_FILES['cover']['name'])) {

            $allowed = ['jpg', 'jpeg', 'png'];
            $nama_asli = $_FILES['cover']['name'];
            $tmp = $_FILES['cover']['tmp_name'];
            $ext = strtolower(pathinfo($nama_asli, PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed)) {

                echo "
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Format cover salah',
                        text: 'Cover hanya boleh JPG, JPEG, atau PNG.',
                        background: '#0f172a',
                        color: '#fff',
                        confirmButtonColor: '#ef4444'
                    });
                </script>
                ";

            } else {

                $namaFile = time() . '-' . preg_replace('/[^a-zA-Z0-9._-]/', '', $nama_asli);

                move_uploaded_file(
                    $tmp,
                    "../../assets/img/cover/" . $namaFile
                );

                $cover = $namaFile;
            }
        }

        // ==========================
        // INSERT DATABASE
        // ==========================
        $insert = mysqli_query($conn, "
            INSERT INTO buku (
                judul,
                cover,
                isbn,
                tahun,
                stok,
                harga,
                deskripsi,
                id_kategori,
                id_penulis,
                id_penerbit
            ) VALUES (
                '$judul',
                '$cover',
                '$isbn',
                '$tahun',
                '$stok',
                '$harga',
                '$deskripsi',
                '$id_kategori',
                '$id_penulis',
                '$id_penerbit'
            )
        ");

        if ($insert) {

            echo "
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Buku berhasil ditambahkan.',
                    background: '#0f172a',
                    color: '#fff',
                    confirmButtonColor: '#3b82f6'
                }).then(() => {
                    window.location = 'index.php';
                });
            </script>
            ";

        } else {

            echo "
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Buku gagal ditambahkan.',
                    background: '#0f172a',
                    color: '#fff',
                    confirmButtonColor: '#ef4444'
                });
            </script>
            ";
        }
    }
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    select option {
        background: #1e293b !important;
        color: white !important;
    }
</style>

<div class="max-w-5xl mx-auto">

    <!-- HEADER -->
    <div class="flex justify-between items-center mb-8 flex-wrap gap-4">

        <div>

            <h1 class="text-3xl font-bold text-white">
                Tambah Buku
            </h1>

            <p class="text-slate-400 mt-1">
                Tambahkan data buku baru ke katalog perpustakaan.
            </p>

        </div>

        <a href="index.php"
            class="bg-slate-800 hover:bg-slate-700 px-5 py-3 rounded-2xl text-white transition">

            <i class="fa-solid fa-arrow-left mr-2"></i>
            Kembali

        </a>

    </div>

    <!-- FORM -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-8 shadow-xl">

        <form method="POST"
            enctype="multipart/form-data"
            class="space-y-6">

            <!-- GRID 1 -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- JUDUL -->
                <div>

                    <label class="block text-sm text-slate-300 mb-2">
                        Judul Buku
                    </label>

                    <input type="text"
                        name="judul"
                        required
                        placeholder="Masukkan judul buku"
                        class="w-full bg-slate-800 border border-slate-700 rounded-2xl px-5 py-4 text-white outline-none focus:border-blue-500">

                </div>

                <!-- ISBN -->
                <div>

                    <label class="block text-sm text-slate-300 mb-2">
                        ISBN
                    </label>

                    <input type="text"
                        name="isbn"
                        placeholder="Masukkan ISBN"
                        class="w-full bg-slate-800 border border-slate-700 rounded-2xl px-5 py-4 text-white outline-none focus:border-blue-500">

                </div>

            </div>

            <!-- GRID 2 -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <!-- TAHUN -->
                <div>

                    <label class="block text-sm text-slate-300 mb-2">
                        Tahun Terbit
                    </label>

                    <input type="number"
                        name="tahun"
                        required
                        placeholder="Contoh: 2026"
                        class="w-full bg-slate-800 border border-slate-700 rounded-2xl px-5 py-4 text-white outline-none focus:border-blue-500">

                </div>

                <!-- STOK -->
                <div>

                    <label class="block text-sm text-slate-300 mb-2">
                        Stok
                    </label>

                    <input type="number"
                        name="stok"
                        required
                        min="0"
                        placeholder="Jumlah stok"
                        class="w-full bg-slate-800 border border-slate-700 rounded-2xl px-5 py-4 text-white outline-none focus:border-blue-500">

                </div>

                <!-- HARGA -->
                <div>

                    <label class="block text-sm text-slate-300 mb-2">
                        Harga Buku
                    </label>

                    <input type="number"
                        name="harga"
                        required
                        min="0"
                        placeholder="Harga buku"
                        class="w-full bg-slate-800 border border-slate-700 rounded-2xl px-5 py-4 text-white outline-none focus:border-blue-500">

                </div>

            </div>

            <!-- GRID 3 -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <!-- KATEGORI -->
                <div>

                    <label class="block text-sm text-slate-300 mb-2">
                        Kategori
                    </label>

                    <select name="id_kategori"
                        required
                        class="w-full bg-slate-800 border border-slate-700 rounded-2xl px-5 py-4 text-white outline-none focus:border-blue-500">

                        <option value="">
                            -- Pilih Kategori --
                        </option>

                        <?php while ($k = mysqli_fetch_assoc($kategori)): ?>

                            <option value="<?= $k['id_kategori']; ?>">
                                <?= htmlspecialchars($k['nama_kategori']); ?>
                            </option>

                        <?php endwhile; ?>

                    </select>

                </div>

                <!-- PENULIS -->
                <div>

                    <label class="block text-sm text-slate-300 mb-2">
                        Penulis
                    </label>

                    <select name="id_penulis"
                        required
                        class="w-full bg-slate-800 border border-slate-700 rounded-2xl px-5 py-4 text-white outline-none focus:border-blue-500">

                        <option value="">
                            -- Pilih Penulis --
                        </option>

                        <?php while ($p = mysqli_fetch_assoc($penulis)): ?>

                            <option value="<?= $p['id_penulis']; ?>">
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
                        class="w-full bg-slate-800 border border-slate-700 rounded-2xl px-5 py-4 text-white outline-none focus:border-blue-500">

                        <option value="">
                            -- Pilih Penerbit --
                        </option>

                        <?php while ($pb = mysqli_fetch_assoc($penerbit)): ?>

                            <option value="<?= $pb['id_penerbit']; ?>">
                                <?= htmlspecialchars($pb['nama_penerbit']); ?>
                            </option>

                        <?php endwhile; ?>

                    </select>

                </div>

            </div>

            <!-- COVER -->
            <div>

                <label class="block text-sm text-slate-300 mb-2">
                    Cover Buku
                </label>

                <input type="file"
                    name="cover"
                    accept=".jpg,.jpeg,.png"
                    class="w-full bg-slate-800 border border-slate-700 rounded-2xl px-5 py-4 text-white outline-none focus:border-blue-500">

                <p class="text-xs text-slate-500 mt-2">
                    Format cover: JPG, JPEG, PNG. Kalau kosong, sistem memakai default.jpg.
                </p>

            </div>

            <!-- DESKRIPSI -->
            <div>

                <label class="block text-sm text-slate-300 mb-2">
                    Deskripsi
                </label>

                <textarea name="deskripsi"
                    rows="5"
                    placeholder="Masukkan deskripsi buku"
                    class="w-full bg-slate-800 border border-slate-700 rounded-2xl px-5 py-4 text-white outline-none focus:border-blue-500"></textarea>

            </div>

            <!-- BUTTON -->
            <div class="flex justify-end gap-3 pt-4">

                <a href="index.php"
                    class="bg-slate-700 hover:bg-slate-600 px-6 py-3 rounded-2xl text-white font-semibold transition">

                    Batal

                </a>

                <button type="submit"
                    name="submit"
                    class="bg-blue-500 hover:bg-blue-600 px-6 py-3 rounded-2xl text-white font-semibold transition">

                    <i class="fa-solid fa-floppy-disk mr-2"></i>
                    Simpan Buku

                </button>

            </div>

        </form>

    </div>

</div>

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

<?php include __DIR__ . "/../layout/footer.php"; ?>