<?php
session_start();

include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../middleware/petugas.php";
include __DIR__ . "/../../petugas/layout/header.php";
?>

<!-- TAILWIND -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- SWEETALERT -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    html,
    body {
        overflow-x: hidden;
    }

    ::-webkit-scrollbar {
        width: 0;
        height: 0;
        display: none;
    }

    * {
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .overflow-x-auto::-webkit-scrollbar,
    .overflow-y-auto::-webkit-scrollbar,
    .overflow-auto::-webkit-scrollbar {
        width: 0;
        height: 0;
        display: none;
    }

    .overflow-x-auto,
    .overflow-y-auto,
    .overflow-auto {
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
</style>

<?php
// ==========================
// HAPUS MULTIPLE RIWAYAT
// ==========================
if (isset($_POST['hapus_selected'])) {

    if (!empty($_POST['selected'])) {

        $ids = $_POST['selected'];
        $gagal = false;

        foreach ($ids as $id) {

            $id = mysqli_real_escape_string($conn, $id);

            // CEK DENDA
            $cek = mysqli_query($conn, "
                SELECT jumlah_denda, status
                FROM denda
                WHERE id_pinjam = '$id'
                LIMIT 1
            ");

            $data_denda = mysqli_fetch_assoc($cek);

            $jumlah_denda = $data_denda['jumlah_denda'] ?? 0;
            $status_denda = $data_denda['status'] ?? 'sudah_bayar';

            if ($jumlah_denda > 0 && $status_denda == 'belum_bayar') {
                $gagal = true;
                continue;
            }

            mysqli_begin_transaction($conn);

            try {

                mysqli_query($conn, "
                    DELETE FROM detail_peminjaman
                    WHERE id_pinjam = '$id'
                ");

                mysqli_query($conn, "
                    DELETE FROM denda
                    WHERE id_pinjam = '$id'
                ");

                mysqli_query($conn, "
                    DELETE FROM peminjaman
                    WHERE id_pinjam = '$id'
                ");

                mysqli_commit($conn);

            } catch (Exception $e) {

                mysqli_rollback($conn);
                $gagal = true;
            }
        }

        if ($gagal) {

            echo "
            <script>
                Swal.fire({
                    icon: 'warning',
                    title: 'Sebagian gagal dihapus',
                    text: 'Beberapa data masih memiliki denda belum dibayar atau gagal diproses.',
                    background: '#0f172a',
                    color: '#fff',
                    confirmButtonColor: '#f59e0b'
                }).then(() => {
                    window.location='index.php';
                });
            </script>";
            exit;

        } else {

            echo "
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Riwayat berhasil dihapus.',
                    background: '#0f172a',
                    color: '#fff',
                    confirmButtonColor: '#10b981'
                }).then(() => {
                    window.location='index.php';
                });
            </script>";
            exit;
        }

    } else {

        echo "
        <script>
            Swal.fire({
                icon: 'warning',
                title: 'Oops',
                text: 'Pilih data terlebih dahulu.',
                background: '#0f172a',
                color: '#fff',
                confirmButtonColor: '#f59e0b'
            }).then(() => {
                window.location='index.php';
            });
        </script>";
        exit;
    }
}
?>

<!-- HEADER -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-5 mb-8">

    <div>

        <h1 class="text-3xl font-bold text-white">
            Riwayat Peminjaman
        </h1>

        <p class="text-slate-400 mt-1">
            Daftar buku anggota yang sudah dikembalikan.
        </p>

    </div>

    <div class="flex gap-3">

        <a href="../peminjaman/index.php"
            class="bg-slate-700 hover:bg-slate-600 transition px-5 py-3 rounded-xl text-white flex items-center gap-2">

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

    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">

        <div class="overflow-x-auto">

            <table class="w-full text-sm text-left text-slate-300">

                <thead class="bg-slate-800 text-slate-200 uppercase text-xs">

                    <tr>

                        <th class="px-6 py-4">
                            <input type="checkbox"
                                id="checkAll"
                                class="w-4 h-4 rounded border-slate-600 bg-slate-700">
                        </th>

                        <th class="px-6 py-4">Peminjam</th>
                        <th class="px-6 py-4">Buku</th>
                        <th class="px-6 py-4">Tanggal Pinjam</th>
                        <th class="px-6 py-4">Deadline</th>
                        <th class="px-6 py-4">Dikembalikan</th>
                        <th class="px-6 py-4">Kondisi Buku</th>
                        <th class="px-6 py-4 text-center">Keterangan</th>

                    </tr>

                </thead>

                <tbody>

                    <?php
                    $query = mysqli_query($conn, "
                        SELECT 
                            peminjaman.id_pinjam,
                            peminjaman.tanggal_pinjam,
                            peminjaman.tanggal_kembali,
                            peminjaman.tanggal_dikembalikan,
                            peminjaman.kondisi_buku,
                            peminjaman.status,

                            buku.judul,
                            buku.cover,

                            user.nama,
                            user.role

                        FROM peminjaman

                        JOIN buku 
                            ON peminjaman.id_buku = buku.id_buku

                        JOIN user 
                            ON peminjaman.id_user = user.id_user

                        WHERE 
                            (
                                peminjaman.status IN ('kembali', 'selesai')
                                OR peminjaman.tanggal_dikembalikan IS NOT NULL
                            )

                        AND user.role IN ('anggota', 'user')

                        ORDER BY peminjaman.id_pinjam DESC
                    ");

                    if (!$query) {
                        die("Query Error: " . mysqli_error($conn));
                    }

                    if (mysqli_num_rows($query) == 0):
                    ?>

                        <tr>

                            <td colspan="8"
                                class="text-center py-12 text-slate-500">

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
                            // HITUNG TELAT
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
                                    ) / (60 * 60 * 24)
                                );
                            }

                            // ==========================
                            // KETERANGAN
                            // ==========================
                            if ($kondisi_buku == 'hilang') {

                                $keterangan = 'Buku Hilang';

                            } elseif ($kondisi_buku == 'rusak') {

                                if ($hari_telat > 0) {
                                    $keterangan = 'Telat ' . $hari_telat . ' Hari - Rusak';
                                } else {
                                    $keterangan = 'Dikembalikan - Rusak';
                                }

                            } else {

                                if ($hari_telat > 0) {
                                    $keterangan = 'Telat ' . $hari_telat . ' Hari';
                                } else {
                                    $keterangan = 'Dikembalikan';
                                }
                            }

                            // ==========================
                            // BADGE KONDISI
                            // ==========================
                            $badgeClass = 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';

                            if ($kondisi_buku == 'rusak') {
                                $badgeClass = 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20';
                            }

                            if ($kondisi_buku == 'hilang') {
                                $badgeClass = 'bg-red-500/10 text-red-400 border-red-500/20';
                            }

                            // ==========================
                            // WARNA KETERANGAN
                            // ==========================
                            $ketClass = 'text-emerald-400';

                            if ($kondisi_buku == 'rusak') {
                                $ketClass = 'text-yellow-400';
                            }

                            if ($kondisi_buku == 'hilang' || $hari_telat > 0) {
                                $ketClass = 'text-red-400';
                            }
                            ?>

                            <tr class="border-b border-slate-800 hover:bg-slate-800/50 transition">

                                <!-- CHECKBOX -->
                                <td class="px-6 py-4">

                                    <input type="checkbox"
                                        name="selected[]"
                                        value="<?= $row['id_pinjam']; ?>"
                                        class="checkbox-item w-4 h-4 rounded border-slate-600 bg-slate-700">

                                </td>

                                <!-- USER -->
                                <td class="px-6 py-4">

                                    <div class="font-semibold text-white">
                                        <?= htmlspecialchars($row['nama']); ?>
                                    </div>

                                </td>

                                <!-- BUKU -->
                                <td class="px-6 py-4">

                                    <div class="flex items-center gap-3 min-w-[220px]">

                                        <img src="../../assets/img/cover/<?= htmlspecialchars($row['cover']); ?>"
                                            class="w-12 h-16 object-cover rounded-lg shadow border border-slate-700">

                                        <div class="text-white font-semibold">
                                            <?= htmlspecialchars($row['judul']); ?>
                                        </div>

                                    </div>

                                </td>

                                <!-- TANGGAL PINJAM -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?= !empty($row['tanggal_pinjam'])
                                        ? date('d M Y', strtotime($row['tanggal_pinjam']))
                                        : '-'; ?>
                                </td>

                                <!-- DEADLINE -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?= !empty($row['tanggal_kembali'])
                                        ? date('d M Y', strtotime($row['tanggal_kembali']))
                                        : '-'; ?>
                                </td>

                                <!-- DIKEMBALIKAN -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?= !empty($row['tanggal_dikembalikan'])
                                        ? date('d M Y', strtotime($row['tanggal_dikembalikan']))
                                        : '-'; ?>
                                </td>

                                <!-- KONDISI -->
                                <td class="px-6 py-4">

                                    <span class="border px-3 py-1 rounded-full text-xs font-semibold <?= $badgeClass; ?>">
                                        <?= strtoupper($kondisi_buku); ?>
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

    </div>

    <input type="hidden" name="hapus_selected" value="1">

</form>

<script>
    const checkAll = document.getElementById('checkAll');

    if (checkAll) {
        checkAll.addEventListener('change', function() {

            let checkboxes = document.querySelectorAll('.checkbox-item');

            checkboxes.forEach((checkbox) => {
                checkbox.checked = this.checked;
            });

        });
    }

    function confirmDeleteSelected() {

        let checked = document.querySelectorAll('.checkbox-item:checked');

        if (checked.length === 0) {

            Swal.fire({
                icon: 'warning',
                title: 'Oops',
                text: 'Pilih data terlebih dahulu',
                background: '#0f172a',
                color: '#fff',
                confirmButtonColor: '#f59e0b'
            });

            return;
        }

        Swal.fire({
            title: 'Hapus riwayat terpilih?',
            text: 'Data yang dihapus tidak bisa dikembalikan',
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

<?php include __DIR__ . "/../../petugas/layout/footer.php"; ?>