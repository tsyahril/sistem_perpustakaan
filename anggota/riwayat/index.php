<?php
session_start();

include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../middleware/anggota.php";

$id_user = $_SESSION['id_user'];

// ==========================
// AMBIL SETTING DENDA
// ==========================
$getDenda = mysqli_query($conn, "
    SELECT *
    FROM denda
    LIMIT 1
");

$setting = mysqli_fetch_assoc($getDenda);

$denda_per_hari = $setting['jumlah_denda'] ?? 5000;
$rumus_rusak    = $setting['denda_rusak'] ?? 2;
$rumus_hilang   = $setting['denda_hilang'] ?? 2;

if ($rumus_rusak <= 0) {
    $rumus_rusak = 2;
}

if ($rumus_hilang <= 0) {
    $rumus_hilang = 2;
}

// ==========================
// HAPUS RIWAYAT TERPILIH
// HANYA BOLEH JIKA TIDAK ADA DENDA / DENDA SUDAH DIBAYAR
// ==========================
if (isset($_POST['hapus_selected'])) {

    if (!empty($_POST['id_pinjam']) && is_array($_POST['id_pinjam'])) {

        mysqli_begin_transaction($conn);

        try {

            $berhasil = 0;
            $gagal = 0;

            foreach ($_POST['id_pinjam'] as $id_pinjam) {

                $id_pinjam = mysqli_real_escape_string($conn, $id_pinjam);

                // CEK DATA MILIK USER
                $cek = mysqli_query($conn, "
                    SELECT 
                        p.*,
                        b.harga
                    FROM peminjaman p
                    JOIN buku b
                        ON p.id_buku = b.id_buku
                    WHERE p.id_pinjam = '$id_pinjam'
                    AND p.id_user = '$id_user'
                    AND (
                        p.status = 'kembali'
                        OR p.tanggal_dikembalikan IS NOT NULL
                    )
                    LIMIT 1
                ");

                if (!$cek) {
                    throw new Exception(mysqli_error($conn));
                }

                if (mysqli_num_rows($cek) == 0) {
                    $gagal++;
                    continue;
                }

                $dataCek = mysqli_fetch_assoc($cek);

                $kondisi_buku = strtolower($dataCek['kondisi_buku'] ?? 'baik');

                if (!in_array($kondisi_buku, ['baik', 'rusak', 'hilang'])) {
                    $kondisi_buku = 'baik';
                }

                // HITUNG TELAT
                $hari_telat = 0;

                if (
                    !empty($dataCek['tanggal_dikembalikan'])
                    &&
                    !empty($dataCek['tanggal_kembali'])
                    &&
                    strtotime($dataCek['tanggal_dikembalikan']) > strtotime($dataCek['tanggal_kembali'])
                ) {
                    $hari_telat = floor(
                        (
                            strtotime($dataCek['tanggal_dikembalikan'])
                            -
                            strtotime($dataCek['tanggal_kembali'])
                        ) / 86400
                    );
                }

                // HITUNG DENDA
                $denda_telat = $hari_telat * $denda_per_hari;
                $denda_rusak = 0;
                $denda_hilang = 0;

                if ($kondisi_buku == 'rusak') {
                    $denda_rusak = $dataCek['harga'] / $rumus_rusak;
                }

                if ($kondisi_buku == 'hilang') {
                    $denda_hilang = $dataCek['harga'] * $rumus_hilang;
                }

                $total_denda = $denda_telat + $denda_rusak + $denda_hilang;

                // KALAU ADA DENDA DAN BELUM DIBAYAR, TIDAK BOLEH HAPUS
                if ($total_denda > 0 && $dataCek['status_denda'] != 'sudah_dibayar') {
                    $gagal++;
                    continue;
                }

                // HAPUS DATA RELASI DULU
                mysqli_query($conn, "
                    DELETE FROM detail_peminjaman 
                    WHERE id_pinjam = '$id_pinjam'
                ");

                mysqli_query($conn, "
                    DELETE FROM denda 
                    WHERE id_pinjam = '$id_pinjam'
                ");

                // HAPUS PEMINJAMAN
                $hapus = mysqli_query($conn, "
                    DELETE FROM peminjaman 
                    WHERE id_pinjam = '$id_pinjam'
                    AND id_user = '$id_user'
                ");

                if ($hapus) {
                    $berhasil++;
                } else {
                    $gagal++;
                }
            }

            mysqli_commit($conn);

            if ($berhasil > 0 && $gagal == 0) {
                $_SESSION['success'] = "$berhasil riwayat berhasil dihapus.";
            } elseif ($berhasil > 0 && $gagal > 0) {
                $_SESSION['warning'] = "$berhasil riwayat berhasil dihapus, $gagal riwayat belum dibayar jadi tidak dihapus.";
            } else {
                $_SESSION['error'] = "Riwayat tidak bisa dihapus karena denda belum dibayar.";
            }

        } catch (Exception $e) {

            mysqli_rollback($conn);
            $_SESSION['error'] = "Gagal menghapus riwayat.";
        }

    } else {
        $_SESSION['error'] = "Pilih minimal 1 riwayat.";
    }

    header("Location: index.php");
    exit;
}

include __DIR__ . "/../layout/header.php";

// ==========================
// AMBIL RIWAYAT PEMINJAMAN USER
// ==========================
$query = mysqli_query($conn, "
    SELECT 
        p.*,

        b.judul,
        b.cover,
        b.harga,

        kategori.nama_kategori,
        COALESCE(penulis.nama_penulis, '-') AS nama_penulis,
        COALESCE(penerbit.nama_penerbit, '-') AS nama_penerbit

    FROM peminjaman p

    JOIN buku b
        ON p.id_buku = b.id_buku

    LEFT JOIN kategori
        ON b.id_kategori = kategori.id_kategori

    LEFT JOIN penulis
        ON b.id_penulis = penulis.id_penulis

    LEFT JOIN penerbit
        ON b.id_penerbit = penerbit.id_penerbit

    WHERE p.id_user = '$id_user'
    AND (
        p.status = 'kembali'
        OR p.tanggal_dikembalikan IS NOT NULL
    )

    ORDER BY p.tanggal_dikembalikan DESC, p.id_pinjam DESC
");

if (!$query) {
    die("Query Error: " . mysqli_error($conn));
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (isset($_SESSION['success'])): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: <?= json_encode($_SESSION['success']); ?>,
        background: '#0f172a',
        color: '#fff',
        confirmButtonColor: '#10b981'
    });
</script>
<?php unset($_SESSION['success']); endif; ?>

<?php if (isset($_SESSION['warning'])): ?>
<script>
    Swal.fire({
        icon: 'warning',
        title: 'Sebagian Terhapus',
        text: <?= json_encode($_SESSION['warning']); ?>,
        background: '#0f172a',
        color: '#fff',
        confirmButtonColor: '#f59e0b'
    });
</script>
<?php unset($_SESSION['warning']); endif; ?>

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

<div class="space-y-8">

    <div>
        <h1 class="text-4xl font-bold text-white">
            Riwayat Peminjaman
        </h1>

        <p class="text-slate-400 mt-2">
            Daftar buku yang sudah kamu kembalikan.
        </p>
    </div>

    <form method="POST" id="formHapusRiwayat">

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">

            <label class="flex items-center gap-2 text-slate-300 text-sm">
                <input 
                    type="checkbox" 
                    id="checkAll"
                    class="w-4 h-4 accent-red-500"
                >
                Pilih semua yang bisa dihapus
            </label>

            <button
                type="button"
                onclick="confirmHapus()"
                class="bg-red-500 hover:bg-red-600 transition px-5 py-3 rounded-xl text-white font-semibold">
                Hapus Riwayat Terpilih
            </button>

        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">

            <div class="overflow-x-auto">

                <table class="w-full text-sm text-left text-slate-300">

                    <thead class="bg-slate-800 text-slate-300 uppercase text-xs">

                        <tr>
                            <th class="px-6 py-4">Pilih</th>
                            <th class="px-6 py-4">Buku</th>
                            <th class="px-6 py-4">Kategori</th>
                            <th class="px-6 py-4">Tanggal Pinjam</th>
                            <th class="px-6 py-4">Deadline</th>
                            <th class="px-6 py-4">Dikembalikan</th>
                            <th class="px-6 py-4">Kondisi</th>
                            <th class="px-6 py-4">Denda</th>
                            <th class="px-6 py-4">Pembayaran</th>
                            <th class="px-6 py-4">Keterangan</th>
                        </tr>

                    </thead>

                    <tbody class="divide-y divide-slate-800">

                        <?php if (mysqli_num_rows($query) > 0): ?>

                            <?php while ($row = mysqli_fetch_assoc($query)): ?>

                                <?php
                                $kondisi = strtolower($row['kondisi_buku'] ?? 'baik');

                                if (!in_array($kondisi, ['baik', 'rusak', 'hilang'])) {
                                    $kondisi = 'baik';
                                }

                                $hari_telat = 0;

                                if (
                                    !empty($row['tanggal_dikembalikan'])
                                    &&
                                    !empty($row['tanggal_kembali'])
                                    &&
                                    strtotime($row['tanggal_dikembalikan']) > strtotime($row['tanggal_kembali'])
                                ) {
                                    $hari_telat = floor(
                                        (
                                            strtotime($row['tanggal_dikembalikan'])
                                            -
                                            strtotime($row['tanggal_kembali'])
                                        ) / 86400
                                    );
                                }

                                $denda_telat = $hari_telat * $denda_per_hari;
                                $denda_rusak = 0;
                                $denda_hilang = 0;

                                if ($kondisi == 'rusak') {
                                    $denda_rusak = $row['harga'] / $rumus_rusak;
                                }

                                if ($kondisi == 'hilang') {
                                    $denda_hilang = $row['harga'] * $rumus_hilang;
                                }

                                $jumlah_denda = $denda_telat + $denda_rusak + $denda_hilang;

                                if ($kondisi == 'hilang') {
                                    $keterangan = 'Buku Hilang';
                                    $ketClass = 'text-red-400';
                                } elseif ($kondisi == 'rusak') {
                                    $keterangan = $hari_telat > 0
                                        ? 'Telat ' . $hari_telat . ' Hari - Rusak'
                                        : 'Dikembalikan - Rusak';

                                    $ketClass = 'text-yellow-400';
                                } else {
                                    $keterangan = $hari_telat > 0
                                        ? 'Telat ' . $hari_telat . ' Hari'
                                        : 'Dikembalikan';

                                    $ketClass = $hari_telat > 0
                                        ? 'text-red-400'
                                        : 'text-emerald-400';
                                }

                                if ($kondisi == 'hilang') {
                                    $badgeClass = 'bg-red-500/10 text-red-400 border-red-500/20';
                                } elseif ($kondisi == 'rusak') {
                                    $badgeClass = 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20';
                                } else {
                                    $badgeClass = 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
                                }

                                if ($jumlah_denda <= 0) {
                                    $bayarText = 'Tidak Ada Denda';
                                    $bayarClass = 'bg-slate-500/10 text-slate-300 border-slate-500/20';
                                    $bolehHapus = true;
                                } elseif ($row['status_denda'] == 'sudah_dibayar') {
                                    $bayarText = 'Sudah Dibayar';
                                    $bayarClass = 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
                                    $bolehHapus = true;
                                } else {
                                    $bayarText = 'Belum Dibayar';
                                    $bayarClass = 'bg-red-500/10 text-red-400 border-red-500/20';
                                    $bolehHapus = false;
                                }
                                ?>

                                <tr class="hover:bg-slate-800/50 transition">

                                    <td class="px-6 py-4">

                                        <?php if ($bolehHapus): ?>
                                            <input 
                                                type="checkbox" 
                                                name="id_pinjam[]" 
                                                value="<?= $row['id_pinjam']; ?>"
                                                class="checkbox-riwayat w-4 h-4 accent-red-500">
                                        <?php else: ?>
                                            <input 
                                                type="checkbox" 
                                                disabled
                                                class="w-4 h-4 opacity-30 cursor-not-allowed">
                                        <?php endif; ?>

                                    </td>

                                    <td class="px-6 py-4">

                                        <div class="flex items-center gap-4 min-w-[260px]">

                                            <img 
                                                src="../../assets/img/cover/<?= htmlspecialchars($row['cover']); ?>"
                                                class="w-14 h-20 object-cover rounded-xl border border-slate-700 shadow"
                                            >

                                            <div>
                                                <h3 class="font-semibold text-white">
                                                    <?= htmlspecialchars($row['judul']); ?>
                                                </h3>

                                                <p class="text-xs text-slate-500 mt-1">
                                                    <?= htmlspecialchars($row['nama_penulis']); ?> • <?= htmlspecialchars($row['nama_penerbit']); ?>
                                                </p>
                                            </div>

                                        </div>

                                    </td>

                                    <td class="px-6 py-4">
                                        <?= htmlspecialchars($row['nama_kategori'] ?? '-'); ?>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?= !empty($row['tanggal_pinjam'])
                                            ? date('d M Y', strtotime($row['tanggal_pinjam']))
                                            : '-'; ?>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?= !empty($row['tanggal_kembali'])
                                            ? date('d M Y', strtotime($row['tanggal_kembali']))
                                            : '-'; ?>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?= !empty($row['tanggal_dikembalikan'])
                                            ? date('d M Y', strtotime($row['tanggal_dikembalikan']))
                                            : '-'; ?>
                                    </td>

                                    <td class="px-6 py-4">
                                        <span class="border px-3 py-1 rounded-full text-xs font-semibold <?= $badgeClass; ?>">
                                            <?= strtoupper($kondisi); ?>
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">

                                        <?php if ($jumlah_denda > 0): ?>
                                            <span class="text-red-400 font-semibold">
                                                Rp <?= number_format($jumlah_denda, 0, ',', '.'); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-slate-500">
                                                -
                                            </span>
                                        <?php endif; ?>

                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="border px-3 py-1 rounded-full text-xs font-semibold <?= $bayarClass; ?>">
                                            <?= $bayarText; ?>
                                        </span>
                                    </td>

                                    <td class="px-6 py-4">
                                        <span class="<?= $ketClass; ?> font-semibold">
                                            <?= htmlspecialchars($keterangan); ?>
                                        </span>
                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="10" class="px-6 py-12 text-center text-slate-500">
                                    Belum ada riwayat peminjaman.
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

        <input type="hidden" name="hapus_selected" value="1">

    </form>

</div>

<script>
    const checkAll = document.getElementById('checkAll');

    if (checkAll) {
        checkAll.addEventListener('change', function () {
            const checkboxRiwayat = document.querySelectorAll('.checkbox-riwayat');

            checkboxRiwayat.forEach(function (checkbox) {
                checkbox.checked = checkAll.checked;
            });
        });
    }

    function confirmHapus() {
        const checked = document.querySelectorAll('.checkbox-riwayat:checked');

        if (checked.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Belum Ada Yang Dipilih',
                text: 'Pilih minimal 1 riwayat yang bisa dihapus.',
                background: '#0f172a',
                color: '#fff',
                confirmButtonColor: '#f59e0b'
            });

            return;
        }

        Swal.fire({
            title: 'Hapus Riwayat?',
            text: 'Riwayat tanpa denda atau denda yang sudah dibayar akan dihapus.',
            icon: 'warning',
            background: '#0f172a',
            color: '#fff',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('formHapusRiwayat').submit();
            }
        });
    }
</script>

<?php include __DIR__ . "/../layout/footer.php"; ?>