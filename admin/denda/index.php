<?php
session_start();
include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../middleware/admin.php";

include __DIR__ .  "/../../admin/layout/header.php";

// ==========================
// AMBIL DATA DENDA
// ==========================
$query = mysqli_query($conn, "
    SELECT * FROM denda 
    LIMIT 1
");

$data = mysqli_fetch_assoc($query);

// ==========================
// UPDATE DENDA
// ==========================
if (isset($_POST['simpan'])) {

    $jumlah_denda = trim($_POST['jumlah_denda']);
    $denda_rusak   = trim($_POST['denda_rusak']);
    $denda_hilang  = trim($_POST['denda_hilang']);

    // VALIDASI
    if (
        $jumlah_denda === '' ||
        $denda_rusak === '' ||
        $denda_hilang === ''
    ) {

        $_SESSION['error'] = "Semua field wajib diisi!";

    } elseif (
        !is_numeric($jumlah_denda) ||
        !is_numeric($denda_rusak) ||
        !is_numeric($denda_hilang)
    ) {

        $_SESSION['error'] = "Semua nilai harus berupa angka!";

    } elseif (
        $denda_rusak <= 0 ||
        $denda_hilang <= 0
    ) {

        $_SESSION['error'] = "Nilai tidak boleh 0!";

    } else {

        $jumlah_denda = mysqli_real_escape_string($conn, $jumlah_denda);
        $denda_rusak   = mysqli_real_escape_string($conn, $denda_rusak);
        $denda_hilang  = mysqli_real_escape_string($conn, $denda_hilang);

        // UPDATE
        if ($data) {

            $update = mysqli_query($conn, "
                UPDATE denda 
                SET 
                    jumlah_denda = '$jumlah_denda',
                    denda_rusak   = '$denda_rusak',
                    denda_hilang  = '$denda_hilang'
            ");

        } else {

            $update = mysqli_query($conn, "
                INSERT INTO denda
                (
                    jumlah_denda,
                    denda_rusak,
                    denda_hilang
                )
                VALUES
                (
                    '$jumlah_denda',
                    '$denda_rusak',
                    '$denda_hilang'
                )
            ");
        }

        if ($update) {

            $_SESSION['success'] = "Pengaturan denda berhasil diperbarui!";

        } else {

            $_SESSION['error'] = "Gagal memperbarui denda!";
        }

        header("Location: index.php");
        exit;
    }

    header("Location: index.php");
    exit;
}
?>

<style>
input[type="number"] {
    appearance: textfield;
}

input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    appearance: none;
    margin: 0;
}
</style>

<div class="max-w-5xl mx-auto">

    <!-- HEADER -->
    <div class="flex items-center justify-between mb-6">

        <div>

            <h1 class="text-3xl font-bold text-white">
                Pengaturan Denda
            </h1>

            <p class="text-slate-400 text-sm mt-1">
                Atur sistem denda perpustakaan
            </p>

        </div>

        <a href="../dashboard/index.php"
            class="bg-slate-800 hover:bg-slate-700 transition px-4 py-3 rounded-xl text-white flex items-center gap-2">

            <i class='bx bx-arrow-back'></i>

            Kembali

        </a>

    </div>

    <!-- CARD -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-8 shadow-2xl">

        <form method="POST" class="space-y-8">

            <!-- DENDA TERLAMBAT -->
            <div>

                <label class="block text-sm text-slate-300 mb-3">
                    Denda Keterlambatan / Hari
                </label>

                <div class="relative">

                    <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 font-semibold">
                        Rp
                    </span>

                    <input type="number"
                        name="jumlah_denda"
                        min="0"
                        required
                        value="<?= htmlspecialchars($data['jumlah_denda'] ?? 5000); ?>"
                        class="w-full bg-slate-800 border border-slate-700 rounded-2xl pl-14 pr-5 py-4 outline-none focus:border-blue-500 text-white">

                </div>

            </div>

            <!-- DENDA RUSAK -->
            <div class="bg-slate-800/60 border border-slate-700 rounded-2xl p-6">

                <h2 class="text-xl font-bold text-yellow-400 mb-5">
                    Denda Buku Rusak
                </h2>

                <div>

                    <label class="block text-sm text-slate-300 mb-3">
                        Harga Buku Dibagi
                    </label>

                    <input type="number"
                        name="denda_rusak"
                        min="1"
                        required
                        value="<?= htmlspecialchars($data['denda_rusak'] ?? 2); ?>"
                        class="w-full bg-slate-800 border border-slate-700 rounded-2xl px-5 py-4 outline-none focus:border-yellow-500 text-white">

                </div>

                <p class="text-sm text-slate-400 mt-4">
                    Contoh:
                    <span class="text-yellow-400 font-semibold">
                        Harga Buku ÷ 2
                    </span>
                </p>

            </div>

            <!-- DENDA HILANG -->
            <div class="bg-slate-800/60 border border-slate-700 rounded-2xl p-6">

                <h2 class="text-xl font-bold text-red-400 mb-5">
                    Denda Buku Hilang
                </h2>

                <div>

                    <label class="block text-sm text-slate-300 mb-3">
                        Harga Buku Dikali
                    </label>

                    <input type="number"
                        name="denda_hilang"
                        min="1"
                        required
                        value="<?= htmlspecialchars($data['denda_hilang'] ?? 2); ?>"
                        class="w-full bg-slate-800 border border-slate-700 rounded-2xl px-5 py-4 outline-none focus:border-red-500 text-white">

                </div>

                <p class="text-sm text-slate-400 mt-4">
                    Contoh:
                    <span class="text-red-400 font-semibold">
                        Harga Buku × 2
                    </span>
                </p>

            </div>

            <!-- PREVIEW -->
            <div class="grid md:grid-cols-3 gap-4">

                <!-- Terlambat -->
                <div class="bg-slate-800 rounded-2xl p-5 border border-slate-700">

                    <p class="text-slate-400 text-sm mb-2">
                        Denda Terlambat
                    </p>

                    <h2 class="text-2xl font-bold text-blue-400">
                        Rp <?= number_format($data['jumlah_denda'] ?? 5000, 0, ',', '.'); ?>
                    </h2>

                </div>

                <!-- Rusak -->
                <div class="bg-slate-800 rounded-2xl p-5 border border-slate-700">

                    <p class="text-slate-400 text-sm mb-2">
                        Buku Rusak
                    </p>

                    <h2 class="text-2xl font-bold text-yellow-400">
                        Harga ÷ <?= $data['denda_rusak'] ?? 2; ?>
                    </h2>

                </div>

                <!-- Hilang -->
                <div class="bg-slate-800 rounded-2xl p-5 border border-slate-700">

                    <p class="text-slate-400 text-sm mb-2">
                        Buku Hilang
                    </p>

                    <h2 class="text-2xl font-bold text-red-400">
                        Harga × <?= $data['denda_hilang'] ?? 2; ?>
                    </h2>

                </div>

            </div>

            <!-- BUTTON -->
            <button type="submit"
                name="simpan"
                class="w-full bg-blue-500 hover:bg-blue-600 transition py-4 rounded-2xl font-semibold text-lg shadow-lg flex items-center justify-center gap-2">

                <i class='bx bx-save text-2xl'></i>

                Simpan Pengaturan Denda

            </button>

        </form>

    </div>

</div>

<?php include __DIR__ .  "/../../admin/layout/footer.php"; ?>