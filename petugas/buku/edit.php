<?php
session_start();
include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../middleware/petugas.php";
include __DIR__ . "/../layout/header.php";

// 1. Validasi ID Buku
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "ID buku tidak ditemukan.";
    header("Location: index.php");
    exit;
}

$id_buku = (int)$_GET['id'];

// 2. Ambil Data Buku yang Akan Diedit
$query_buku = mysqli_query($conn, "SELECT * FROM buku WHERE id_buku = '$id_buku' LIMIT 1");
$buku = mysqli_fetch_assoc($query_buku);

if (!$buku) {
    $_SESSION['error'] = "Data buku tidak ditemukan.";
    header("Location: index.php");
    exit;
}

// 3. Ambil Data Master untuk Dropdown
$list_kategori = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
$list_penulis  = mysqli_query($conn, "SELECT * FROM penulis ORDER BY nama_penulis ASC");
$list_penerbit = mysqli_query($conn, "SELECT * FROM penerbit ORDER BY nama_penerbit ASC");

// 4. Proses Update Data
if (isset($_POST['update_buku'])) {
    $judul       = mysqli_real_escape_string($conn, trim($_POST['judul']));
    $id_kategori = (int)$_POST['id_kategori'];
    $id_penulis  = (int)$_POST['id_penulis'];
    $id_penerbit = (int)$_POST['id_penerbit'];
    $tahun       = (int)$_POST['tahun'];
    $harga       = (int)$_POST['harga'];
    $stok        = (int)$_POST['stok'];
    
    $cover_lama  = $buku['cover'];

    if (empty($judul) || $id_kategori <= 0 || $id_penulis <= 0 || $id_penerbit <= 0 || $tahun <= 0 || $harga < 0 || $stok < 0) {
        $_SESSION['error'] = "Semua fields wajib diisi dengan benar.";
    } else {
        // Logika Upload Cover Baru jika ada
        $nama_file = $_FILES['cover']['name'];
        $tmp_file  = $_FILES['cover']['tmp_name'];
        $error_file = $_FILES['cover']['error'];
        
        if ($error_file === 0) {
            $ekstensi_valid = ['jpg', 'jpeg', 'png', 'webp'];
            $ekstensi_file  = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
            
            if (in_array($ekstensi_file, $ekstensi_valid)) {
                $cover_baru = uniqid() . '.' . $ekstensi_file;
                $tujuan_upload = "../../assets/img/cover/" . $cover_baru;
                
                if (move_uploaded_file($tmp_file, $tujuan_upload)) {
                    // Hapus cover lama fisik jika bukan default.jpg
                    if ($cover_lama != 'default.jpg' && file_exists("../../assets/img/cover/" . $cover_lama)) {
                        unlink("../../assets/img/cover/" . $cover_lama);
                    }
                    $cover_lama = $cover_baru; // Pakai nama baru untuk query
                }
            }
        }

        // Query Update ke Database
        $update = mysqli_query($conn, "UPDATE buku SET 
            judul       = '$judul',
            id_kategori = '$id_kategori',
            id_penulis  = '$id_penulis',
            id_penerbit = '$id_penerbit',
            tahun       = '$tahun',
            harga       = '$harga',
            stok        = '$stok',
            cover       = '$cover_lama'
            WHERE id_buku = '$id_buku'");

        if ($update) {
            $_SESSION['success'] = "Data buku berhasil diperbarui.";
            header("Location: index.php");
            exit;
        } else {
            $_SESSION['error'] = "Gagal memperbarui data buku.";
        }
    }
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
    select option { background: #1e293b !important; color: white !important; }
</style>

<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="index.php" class="bg-slate-800 hover:bg-slate-700 text-slate-300 p-3 rounded-xl transition">
            <i class="fa-solid fa-arrow-left text-lg"></i>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-white">Edit Buku</h1>
            <p class="text-slate-400 mt-1">Perbarui informasi, inventaris stok, atau berkas media cover buku.</p>
        </div>
    </div>

    <div class="bg-slate-900 p-8 rounded-2xl border border-slate-800 shadow-xl">
        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            
            <div>
                <label class="block text-sm font-semibold text-slate-300 mb-2">Judul Buku :</label>
                <input type="text" name="judul" value="<?= htmlspecialchars($buku['judul']); ?>" required 
                       class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white outline-none focus:border-blue-500 transition">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Kategori :</label>
                    <select name="id_kategori" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white outline-none focus:border-blue-500 transition">
                        <option value="">Pilih Kategori</option>
                        <?php while ($kat = mysqli_fetch_assoc($list_kategori)): ?>
                            <option value="<?= $kat['id_kategori']; ?>" <?= ($buku['id_kategori'] == $kat['id_kategori']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($kat['nama_kategori']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Penulis :</label>
                    <select name="id_penulis" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white outline-none focus:border-blue-500 transition">
                        <option value="">Pilih Penulis</option>
                        <?php while ($pen = mysqli_fetch_assoc($list_penulis)): ?>
                            <option value="<?= $pen['id_penulis']; ?>" <?= ($buku['id_penulis'] == $pen['id_penulis']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($pen['nama_penulis']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Penerbit :</label>
                    <select name="id_penerbit" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white outline-none focus:border-blue-500 transition">
                        <option value="">Pilih Penerbit</option>
                        <?php while ($penbit = mysqli_fetch_assoc($list_penerbit)): ?>
                            <option value="<?= $penbit['id_penerbit']; ?>" <?= ($buku['id_penerbit'] == $penbit['id_penerbit']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($penbit['nama_penerbit']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Tahun Terbit :</label>
                    <input type="number" name="tahun" value="<?= $buku['tahun']; ?>" required min="1000" max="2100"
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white outline-none focus:border-blue-500 transition">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Harga Buku (Rp) :</label>
                    <input type="number" name="harga" value="<?= $buku['harga']; ?>" required min="0"
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white outline-none focus:border-blue-500 transition">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Stok Tersedia :</label>
                    <input type="number" name="stok" value="<?= $buku['stok']; ?>" required min="0"
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white outline-none focus:border-blue-500 transition">
                </div>
            </div>

            <div class="bg-slate-950 p-6 rounded-xl border border-slate-800 grid grid-cols-1 md:flex gap-6 items-center">
                <div class="flex-shrink-0">
                    <p class="text-xs text-slate-500 mb-2 text-center md:text-left">Cover Saat Ini:</p>
                    <img src="../../assets/img/cover/<?= htmlspecialchars($buku['cover']); ?>" 
                         class="w-24 h-36 object-cover rounded-xl border border-slate-800 shadow-lg mx-auto">
                </div>
                <div class="flex-1 space-y-2">
                    <label class="block text-sm font-semibold text-slate-300">Ganti File Cover :</label>
                    <input type="file" name="cover" accept="image/*"
                           class="w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-500/10 file:text-blue-400 hover:file:bg-blue-500/20 file:cursor-pointer cursor-pointer">
                    <p class="text-xs text-slate-500">Ekstensi file yang diperbolehkan: .jpg, .jpeg, .png, .webp (Kosongkan jika tidak ingin mengubah cover).</p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-4 pt-4 border-t border-slate-800">
                <a href="index.php" class="bg-slate-800 hover:bg-slate-700 transition px-6 py-3 rounded-xl text-slate-300 font-semibold text-sm">
                    Batal
                </a>
                <button type="submit" name="update_buku" class="bg-blue-500 hover:bg-blue-600 active:scale-[0.99] transition px-6 py-3 rounded-xl text-white font-semibold text-sm shadow-lg shadow-blue-500/10">
                    Simpan Perubahan
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