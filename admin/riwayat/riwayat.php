<?php
session_start();

include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../middleware/admin.php";

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
// HAPUS MULTIPLE RIWAYAT
// TIDAK BOLEH HAPUS JIKA ADA DENDA BELUM DIBAYAR
// ==========================
if (isset($_POST['hapus_selected'])) {

    if (!empty($_POST['selected']) && is_array($_POST['selected'])) {

        mysqli_begin_transaction($conn);

        try {

            $berhasil = 0;
            $gagal = 0;

            foreach ($_POST['selected'] as $id) {

                $id = mysqli_real_escape_string($conn, $id);

                // ==========================
                // CEK DATA RIWAYAT + HARGA BUKU
                // ==========================
                $cek = mysqli_query($conn, "
                    SELECT 
                        p.*,
                        b.harga
                    FROM peminjaman p
                    JOIN buku b
                        ON p.id_buku = b.id_buku
                    WHERE p.id_pinjam = '$id'
                    AND (
                        p.status IN ('kembali', 'selesai')
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

                $data = mysqli_fetch_assoc($cek);

                $kondisi_buku = strtolower($data['kondisi_buku'] ?? 'baik');

                if (!in_array($kondisi_buku, ['baik', 'rusak', 'hilang'])) {
                    $kondisi_buku = 'baik';
                }

                // ==========================
                // HITUNG TELAT
                // ==========================
                $hari_telat = 0;

                if (
                    !empty($data['tanggal_dikembalikan'])
                    &&
                    !empty($data['tanggal_kembali'])
                    &&
                    strtotime($data['tanggal_dikembalikan']) > strtotime($data['tanggal_kembali'])
                ) {
                    $hari_telat = floor(
                        (
                            strtotime($data['tanggal_dikembalikan'])
                            -
                            strtotime($data['tanggal_kembali'])
                        ) / 86400
                    );
                }

                // ==========================
                // HITUNG TOTAL DENDA
                // ==========================
                $denda_telat = $hari_telat * $denda_per_hari;
                $denda_rusak = 0;
                $denda_hilang = 0;

                if ($kondisi_buku == 'rusak') {
                    $denda_rusak = $data['harga'] / $rumus_rusak;
                }

                if ($kondisi_buku == 'hilang') {
                    $denda_hilang = $data['harga'] * $rumus_hilang;
                }

                $total_denda = $denda_telat + $denda_rusak + $denda_hilang;

                // ==========================
                // JIKA ADA DENDA DAN BELUM DIBAYAR, JANGAN HAPUS
                // ==========================
                if ($total_denda > 0 && $data['status_denda'] != 'sudah_dibayar') {
                    $gagal++;
                    continue;
                }

                // ==========================
                // HAPUS RELASI DULU
                // ==========================
                mysqli_query($conn, "
                    DELETE FROM detail_peminjaman 
                    WHERE id_pinjam = '$id'
                ");

                mysqli_query($conn, "
                    DELETE FROM denda 
                    WHERE id_pinjam = '$id'
                ");

                $hapus = mysqli_query($conn, "
                    DELETE FROM peminjaman
                    WHERE id_pinjam = '$id'
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
        $_SESSION['error'] = "Pilih data terlebih dahulu.";
    }

    header("Location: riwayat.php");
    exit;
}

include __DIR__ . "/../../admin/layout/header.php";
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

<!-- HEADER -->
<div class="flex justify-between items-center mb-6">

    <div>
        <h1 class="text-2xl font-bold text-white">
            Riwayat Peminjaman
        </h1>

        <p class="text-slate-400 text-sm mt-1">
            Daftar buku yang sudah dikembalikan.
        </p>
    </div>

    <div class="flex gap-3">

        <a href="../peminjaman/peminjaman.php"
            class="bg-slate-700 hover:bg-slate-600 transition px-4 py-3 rounded-xl text-white flex items-center gap-2">

            <i class='bx bx-arrow-back'></i>
            Kembali

        </a>

        <button type="button"
            onclick="confirmDeleteSelected()"
            class="bg-red-500 hover:bg-red-600 transition px-5 py-3 rounded-xl text-white font-semibold flex items-center gap-2 shadow-lg">

            <i class='bx bx-trash'></i>
            Hapus Dipilih

        </button>

    </div>

</div>

<!-- TABLE -->
<form method="POST" id="formHapus">

    <div class="overflow-x-auto bg-slate-900 border border-slate-800 rounded-2xl shadow-xl">

        <table class="w-full text-sm text-left text-slate-300">

            <thead class="bg-slate-800 text-slate-200 uppercase text-xs">

                <tr>
                    <th class="px-6 py-4">
                        <input type="checkbox" id="checkAll">
                    </th>

                    <th class="px-6 py-4">Peminjam</th>
                    <th class="px-6 py-4">Buku</th>
                    <th class="px-6 py-4">Tanggal Pinjam</th>
                    <th class="px-6 py-4">Deadline</th>
                    <th class="px-6 py-4">Dikembalikan</th>
                    <th class="px-6 py-4">Kondisi Buku</th>
                    <th class="px-6 py-4">Denda</th>
                    <th class="px-6 py-4">Pembayaran</th>
                    <th class="px-6 py-4 text-center">Keterangan</th>
                </tr>

            </thead>

            <tbody>

                <?php
                // ==========================
                // AMBIL DATA RIWAYAT
                // ==========================
                $query = mysqli_query($conn, "
                    SELECT 
                        peminjaman.id_pinjam,
                        peminjaman.tanggal_pinjam,
                        peminjaman.tanggal_kembali,
                        peminjaman.tanggal_dikembalikan,
                        peminjaman.kondisi_buku,
                        peminjaman.status,
                        peminjaman.status_denda,

                        buku.judul,
                        buku.cover,
                        buku.harga,

                        user.nama

                    FROM peminjaman

                    JOIN buku 
                        ON peminjaman.id_buku = buku.id_buku

                    JOIN user 
                        ON peminjaman.id_user = user.id_user

                    WHERE 
                        peminjaman.status IN ('kembali', 'selesai')
                        OR peminjaman.tanggal_dikembalikan IS NOT NULL

                    ORDER BY peminjaman.id_pinjam DESC
                ");

                if (!$query):
                ?>

                    <tr>
                        <td colspan="10" class="text-center py-10 text-red-400">
                            Query Error: <?= mysqli_error($conn); ?>
                        </td>
                    </tr>

                <?php elseif (mysqli_num_rows($query) == 0): ?>

                    <tr>
                        <td colspan="10" class="text-center py-10 text-slate-500">
                            Belum ada riwayat peminjaman.
                        </td>
                    </tr>

                <?php else: ?>

                    <?php while ($row = mysqli_fetch_assoc($query)): ?>

                        <?php
                        // ==========================
                        // KONDISI BUKU
                        // ==========================
                        $kondisi_buku = strtolower($row['kondisi_buku'] ?? 'baik');

                        if (!in_array($kondisi_buku, ['baik', 'rusak', 'hilang'])) {
                            $kondisi_buku = 'baik';
                        }

                        // ==========================
                        // CEK KETERLAMBATAN
                        // ==========================
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

                        // ==========================
                        // HITUNG DENDA
                        // ==========================
                        $denda_telat = $hari_telat * $denda_per_hari;
                        $denda_rusak = 0;
                        $denda_hilang = 0;

                        if ($kondisi_buku == 'rusak') {
                            $denda_rusak = $row['harga'] / $rumus_rusak;
                        }

                        if ($kondisi_buku == 'hilang') {
                            $denda_hilang = $row['harga'] * $rumus_hilang;
                        }

                        $total_denda = $denda_telat + $denda_rusak + $denda_hilang;

                        // ==========================
                        // KETERANGAN
                        // ==========================
                        if ($kondisi_buku == 'hilang') {
                            $keterangan = 'Buku Hilang';
                            $ketClass = 'text-red-400';
                        } elseif ($kondisi_buku == 'rusak') {
                            $keterangan = ($hari_telat > 0)
                                ? 'Telat ' . $hari_telat . ' Hari - Rusak'
                                : 'Dikembalikan - Rusak';

                            $ketClass = 'text-yellow-400';
                        } else {
                            $keterangan = ($hari_telat > 0)
                                ? 'Telat ' . $hari_telat . ' Hari'
                                : 'Dikembalikan';

                            $ketClass = ($hari_telat > 0)
                                ? 'text-red-400'
                                : 'text-emerald-400';
                        }

                        // ==========================
                        // BADGE KONDISI
                        // ==========================
                        $badge = 'emerald';

                        if ($kondisi_buku == 'rusak') {
                            $badge = 'yellow';
                        }

                        if ($kondisi_buku == 'hilang') {
                            $badge = 'red';
                        }

                        // ==========================
                        // STATUS BAYAR
                        // ==========================
                        if ($total_denda <= 0) {
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

                        <tr class="border-b border-slate-800 hover:bg-slate-800/50 transition">

                            <!-- CHECKBOX -->
                            <td class="px-6 py-4">

                                <?php if ($bolehHapus): ?>

                                    <input type="checkbox"
                                        name="selected[]"
                                        value="<?= $row['id_pinjam']; ?>"
                                        class="checkbox-item w-4 h-4">

                                <?php else: ?>

                                    <input type="checkbox"
                                        disabled
                                        class="w-4 h-4 opacity-30 cursor-not-allowed">

                                <?php endif; ?>

                            </td>

                            <!-- USER -->
                            <td class="px-6 py-4 font-semibold text-white">
                                <?= htmlspecialchars($row['nama']); ?>
                            </td>

                            <!-- BUKU -->
                            <td class="px-6 py-4">

                                <div class="flex items-center gap-3">

                                    <img src="../../assets/img/cover/<?= htmlspecialchars($row['cover']); ?>"
                                        class="w-12 h-16 object-cover rounded-lg shadow">

                                    <div class="text-white font-semibold">
                                        <?= htmlspecialchars($row['judul']); ?>
                                    </div>

                                </div>

                            </td>

                            <!-- TANGGAL PINJAM -->
                            <td class="px-6 py-4">
                                <?= date('d M Y', strtotime($row['tanggal_pinjam'])); ?>
                            </td>

                            <!-- DEADLINE -->
                            <td class="px-6 py-4">
                                <?= date('d M Y', strtotime($row['tanggal_kembali'])); ?>
                            </td>

                            <!-- TANGGAL DIKEMBALIKAN -->
                            <td class="px-6 py-4">
                                <?php if (!empty($row['tanggal_dikembalikan'])): ?>
                                    <?= date('d M Y', strtotime($row['tanggal_dikembalikan'])); ?>
                                <?php else: ?>
                                    <span class="text-slate-500">-</span>
                                <?php endif; ?>
                            </td>

                            <!-- KONDISI -->
                            <td class="px-6 py-4">

                                <span class="bg-<?= $badge; ?>-500/10 text-<?= $badge; ?>-400 border border-<?= $badge; ?>-500/20 px-3 py-1 rounded-full text-xs font-semibold">
                                    <?= strtoupper($kondisi_buku); ?>
                                </span>

                            </td>

                            <!-- DENDA -->
                            <td class="px-6 py-4 whitespace-nowrap">

                                <?php if ($total_denda > 0): ?>

                                    <span class="text-red-400 font-semibold">
                                        Rp <?= number_format($total_denda, 0, ',', '.'); ?>
                                    </span>

                                <?php else: ?>

                                    <span class="text-slate-500">
                                        -
                                    </span>

                                <?php endif; ?>

                            </td>

                            <!-- PEMBAYARAN -->
                            <td class="px-6 py-4 whitespace-nowrap">

                                <span class="border px-3 py-1 rounded-full text-xs font-semibold <?= $bayarClass; ?>">
                                    <?= $bayarText; ?>
                                </span>

                            </td>

                            <!-- KETERANGAN -->
                            <td class="px-6 py-4 text-center">

                                <span class="<?= $ketClass; ?> font-semibold">
                                    <?= htmlspecialchars($keterangan); ?>
                                </span>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

    <input type="hidden" name="hapus_selected" value="1">

</form>

<script>
    // ==========================
    // CHECK ALL
    // ==========================
    const checkAll = document.getElementById('checkAll');

    if (checkAll) {
        checkAll.addEventListener('change', function() {

            let checkboxes = document.querySelectorAll('.checkbox-item');

            checkboxes.forEach((checkbox) => {
                checkbox.checked = this.checked;
            });

        });
    }

    // ==========================
    // CONFIRM DELETE
    // ==========================
    function confirmDeleteSelected() {

        let checked = document.querySelectorAll('.checkbox-item:checked');

        if (checked.length === 0) {

            Swal.fire({
                icon: 'warning',
                title: 'Oops',
                text: 'Pilih data yang bisa dihapus terlebih dahulu.',
                background: '#0f172a',
                color: '#fff',
                confirmButtonColor: '#f59e0b'
            });

            return;
        }

        Swal.fire({
            title: 'Hapus riwayat terpilih?',
            text: 'Riwayat yang belum membayar denda tidak akan bisa dihapus.',
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
                document.getElementById('formHapus').submit();
            }

        });
    }
</script>

<?php include __DIR__ . "/../../admin/layout/footer.php"; ?>