<?php
session_start();

include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../middleware/petugas.php";
include __DIR__ . "/../../petugas/layout/header.php";

// ==========================
// AMBIL SETTING DENDA ADMIN
// ==========================
$getDenda = mysqli_query($conn, "
    SELECT jumlah_denda
    FROM denda
    LIMIT 1
");

$setting_denda = mysqli_fetch_assoc($getDenda);
$denda_per_hari = $setting_denda['jumlah_denda'] ?? 5000;

// ==========================
// DENDA TAMBAHAN
// ==========================
// Rusak = setengah harga buku
// Hilang = harga penuh buku
// Kalau rumus admin kamu beda, ubah bagian ini saja.
$persen_rusak = 0.5;
$persen_hilang = 1;

// ==========================
// TOTAL BUKU
// ==========================
$total_buku = mysqli_num_rows(mysqli_query($conn, "
    SELECT id_buku 
    FROM buku
"));

// ==========================
// TOTAL PEMINJAMAN AKTIF ANGGOTA
// ==========================
$total_dipinjam = mysqli_num_rows(mysqli_query($conn, "
    SELECT peminjaman.id_pinjam

    FROM peminjaman

    JOIN user
        ON peminjaman.id_user = user.id_user

    WHERE peminjaman.status = 'dipinjam'
    AND user.role IN ('anggota', 'user')
"));

// ==========================
// TOTAL ANGGOTA TERLAMBAT AKTIF
// ==========================
$total_terlambat = mysqli_num_rows(mysqli_query($conn, "
    SELECT peminjaman.id_pinjam

    FROM peminjaman

    JOIN user
        ON peminjaman.id_user = user.id_user

    WHERE peminjaman.status = 'dipinjam'
    AND peminjaman.tanggal_kembali < CURDATE()
    AND user.role IN ('anggota', 'user')
"));

// ==========================
// DATA DENDA DARI RIWAYAT
// ==========================
$queryDenda = mysqli_query($conn, "
    SELECT
        peminjaman.id_pinjam,
        peminjaman.tanggal_pinjam,
        peminjaman.tanggal_kembali,
        peminjaman.tanggal_dikembalikan,
        peminjaman.kondisi_buku,
        peminjaman.status,

        user.nama,

        buku.judul,
        buku.harga

    FROM peminjaman

    JOIN user
        ON peminjaman.id_user = user.id_user

    JOIN buku
        ON peminjaman.id_buku = buku.id_buku

    WHERE 
        (
            peminjaman.status IN ('kembali', 'selesai')
            OR peminjaman.tanggal_dikembalikan IS NOT NULL
        )

    AND user.role IN ('anggota', 'user')

    AND (
        DATE(peminjaman.tanggal_dikembalikan) > DATE(peminjaman.tanggal_kembali)
        OR peminjaman.kondisi_buku = 'rusak'
        OR peminjaman.kondisi_buku = 'hilang'
    )

    ORDER BY peminjaman.tanggal_dikembalikan DESC, peminjaman.id_pinjam DESC

    LIMIT 10
");

if (!$queryDenda) {
    die("Query Error: " . mysqli_error($conn));
}
?>

<div class="mb-8">

    <h1 class="text-3xl font-bold text-white">
        Dashboard Petugas
    </h1>

    <p class="text-slate-400 mt-1">
        Ringkasan data peminjaman dan denda anggota.
    </p>

</div>

<!-- STAT CARDS -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

    <!-- TOTAL BUKU -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">

        <div class="flex items-center justify-between">

            <div>

                <p class="text-slate-400 text-sm">
                    Total Buku
                </p>

                <h2 class="text-3xl font-bold text-white mt-2">
                    <?= number_format($total_buku, 0, ',', '.'); ?>
                </h2>

            </div>

            <div class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center text-2xl">
                <i class='bx bxs-book'></i>
            </div>

        </div>

    </div>

    <!-- SEDANG DIPINJAM -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">

        <div class="flex items-center justify-between">

            <div>

                <p class="text-slate-400 text-sm">
                    Sedang Dipinjam
                </p>

                <h2 class="text-3xl font-bold text-blue-400 mt-2">
                    <?= number_format($total_dipinjam, 0, ',', '.'); ?>
                </h2>

            </div>

            <div class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center text-2xl">
                <i class='bx bx-transfer'></i>
            </div>

        </div>

    </div>

    <!-- ANGGOTA TERLAMBAT -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">

        <div class="flex items-center justify-between">

            <div>

                <p class="text-slate-400 text-sm">
                    Anggota Terlambat
                </p>

                <h2 class="text-3xl font-bold text-red-400 mt-2">
                    <?= number_format($total_terlambat, 0, ',', '.'); ?>
                </h2>

            </div>

            <div class="w-12 h-12 rounded-xl bg-red-500/10 text-red-400 flex items-center justify-center text-2xl">
                <i class='bx bx-time-five'></i>
            </div>

        </div>

    </div>

</div>

<!-- TABLE DENDA -->
<div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">

    <div class="p-6 border-b border-slate-800 flex flex-col md:flex-row md:items-center md:justify-between gap-4">

        <div>

            <h2 class="text-xl font-bold text-white">
                Data Denda Anggota
            </h2>

            <p class="text-slate-400 text-sm mt-1">
                Denda dihitung dari telat, buku rusak, dan buku hilang.
            </p>

        </div>

        <a href="../denda/index.php"
            class="bg-blue-500 hover:bg-blue-600 transition px-4 py-2 rounded-xl text-white text-sm font-semibold w-fit">

            Lihat Semua

        </a>

    </div>

    <div class="overflow-x-auto">

        <table class="w-full text-sm text-slate-300">

            <thead class="bg-slate-800 text-slate-300 uppercase text-xs">

                <tr>
                    <th class="px-6 py-4 text-left">No</th>
                    <th class="px-6 py-4 text-left">Nama Anggota</th>
                    <th class="px-6 py-4 text-left">Judul Buku</th>
                    <th class="px-6 py-4 text-left">Deadline</th>
                    <th class="px-6 py-4 text-left">Dikembalikan</th>
                    <th class="px-6 py-4 text-left">Telat</th>
                    <th class="px-6 py-4 text-left">Kondisi Buku</th>
                    <th class="px-6 py-4 text-left">Total Denda</th>
                </tr>

            </thead>

            <tbody>

                <?php if (mysqli_num_rows($queryDenda) > 0): ?>

                    <?php
                    $no = 1;
                    $total_semua_denda = 0;
                    ?>

                    <?php while ($row = mysqli_fetch_assoc($queryDenda)): ?>

                        <?php
                        // ==========================
                        // KONDISI BUKU
                        // ==========================
                        $kondisi_buku = strtolower($row['kondisi_buku'] ?? 'baik');

                        if (!in_array($kondisi_buku, ['baik', 'rusak', 'hilang'])) {
                            $kondisi_buku = 'baik';
                        }

                        // ==========================
                        // HITUNG HARI TELAT
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
                        // HITUNG DENDA
                        // ==========================
                        $harga_buku = $row['harga'] ?? 0;

                        $denda_telat = $hari_telat * $denda_per_hari;
                        $denda_rusak = 0;
                        $denda_hilang = 0;

                        if ($kondisi_buku == 'rusak') {
                            $denda_rusak = $harga_buku * $persen_rusak;
                        }

                        if ($kondisi_buku == 'hilang') {
                            $denda_hilang = $harga_buku * $persen_hilang;
                        }

                        $total_denda = $denda_telat + $denda_rusak + $denda_hilang;
                        $total_semua_denda += $total_denda;

                        // ==========================
                        // BADGE WARNA
                        // ==========================
                        $badgeClass = 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';

                        if ($kondisi_buku == 'rusak') {
                            $badgeClass = 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20';
                        }

                        if ($kondisi_buku == 'hilang') {
                            $badgeClass = 'bg-red-500/10 text-red-400 border-red-500/20';
                        }
                        ?>

                        <tr class="border-b border-slate-800 hover:bg-slate-800/60 transition">

                            <td class="px-6 py-4">
                                <?= $no++; ?>
                            </td>

                            <td class="px-6 py-4 font-semibold text-white">
                                <?= htmlspecialchars($row['nama']); ?>
                            </td>

                            <td class="px-6 py-4">
                                <?= htmlspecialchars($row['judul']); ?>
                            </td>

                            <td class="px-6 py-4">
                                <?= !empty($row['tanggal_kembali'])
                                    ? date('d-m-Y', strtotime($row['tanggal_kembali']))
                                    : '-'; ?>
                            </td>

                            <td class="px-6 py-4">
                                <?= !empty($row['tanggal_dikembalikan'])
                                    ? date('d-m-Y', strtotime($row['tanggal_dikembalikan']))
                                    : '-'; ?>
                            </td>

                            <td class="px-6 py-4">

                                <?php if ($hari_telat > 0): ?>

                                    <span class="bg-red-500/10 text-red-400 border border-red-500/20 px-3 py-1 rounded-full text-xs font-semibold">
                                        <?= $hari_telat; ?> Hari
                                    </span>

                                <?php else: ?>

                                    <span class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 px-3 py-1 rounded-full text-xs font-semibold">
                                        Tidak Telat
                                    </span>

                                <?php endif; ?>

                            </td>

                            <td class="px-6 py-4">

                                <span class="border px-3 py-1 rounded-full text-xs font-semibold <?= $badgeClass; ?>">
                                    <?= strtoupper($kondisi_buku); ?>
                                </span>

                            </td>

                            <td class="px-6 py-4 text-yellow-400 font-bold">
                                Rp <?= number_format($total_denda, 0, ',', '.'); ?>
                            </td>

                        </tr>

                    <?php endwhile; ?>

                    <tr class="bg-slate-800">

                        <td colspan="7" class="px-6 py-4 text-right font-bold text-white">
                            Total Semua Denda
                        </td>

                        <td class="px-6 py-4 text-yellow-400 font-bold">
                            Rp <?= number_format($total_semua_denda, 0, ',', '.'); ?>
                        </td>

                    </tr>

                <?php else: ?>

                    <tr>
                        <td colspan="8" class="px-6 py-10 text-center text-slate-500">
                            Belum ada data denda anggota.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

<?php include __DIR__ . "/../../petugas/layout/footer.php"; ?>