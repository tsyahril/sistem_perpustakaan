<?php
session_start();

include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../middleware/petugas.php";

// ==========================
// FILTER USER
// ==========================
$where_user = "";

if (isset($_GET['all'])) {

    $where_user = "";

} elseif (isset($_GET['users'])) {

    $users = $_GET['users'];

    $ids = array_map('intval', explode(',', $users));
    $ids = array_filter($ids);

    if (!empty($ids)) {
        $ids = implode(',', $ids);
        $where_user = "AND user.id_user IN ($ids)";
    }
}

// ==========================
// FILTER SEARCH
// ==========================
$where_search = "";

if (!empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);

    $where_search = "
        AND (
            user.nama LIKE '%$search%'
            OR buku.judul LIKE '%$search%'
        )
    ";
}

// ==========================
// SETTING DENDA
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
// QUERY DATA DENDA ANGGOTA
// ==========================
$query = mysqli_query($conn, "
    SELECT
        peminjaman.*,
        user.nama,
        user.role,
        buku.judul,
        buku.harga

    FROM peminjaman

    JOIN user
        ON peminjaman.id_user = user.id_user

    JOIN buku
        ON peminjaman.id_buku = buku.id_buku

    WHERE 
        peminjaman.status NOT IN ('pending', 'dipinjam')

    AND user.role IN ('anggota', 'user')

    AND (
        peminjaman.status_denda IS NULL
        OR peminjaman.status_denda != 'expired'
    )

    AND (
        DATE(peminjaman.tanggal_dikembalikan) > DATE(peminjaman.tanggal_kembali)
        OR peminjaman.kondisi_buku = 'rusak'
        OR peminjaman.kondisi_buku = 'hilang'
    )

    $where_user
    $where_search

    ORDER BY user.nama ASC, peminjaman.id_pinjam DESC
");

if (!$query) {
    die("Query Error: " . mysqli_error($conn));
}

$total_semua = 0;
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Print Denda Anggota</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            @page {
                margin: 14mm;
            }
        }
    </style>
</head>

<body class="bg-white text-slate-900 font-sans">

    <div class="max-w-7xl mx-auto px-8 py-8">

        <div class="no-print mb-6 flex justify-end">
            <button onclick="window.print()"
                class="bg-slate-900 hover:bg-slate-800 text-white px-5 py-3 rounded-xl font-semibold">
                Print Ulang
            </button>
        </div>

        <!-- HEADER -->
        <div class="text-center mb-8">

            <h1 class="text-3xl font-bold uppercase tracking-wide">
                Laporan Denda Anggota
            </h1>

            <p class="text-slate-500 text-sm mt-2">
                Sistem Informasi Perpustakaan
            </p>

            <div class="w-full h-[3px] bg-slate-900 mt-5"></div>

            <p class="text-sm mt-4">
                Tanggal Print:
                <span class="font-semibold">
                    <?= date('d-m-Y H:i'); ?>
                </span>
            </p>

        </div>

        <!-- TABLE -->
        <div class="overflow-hidden border border-slate-900 rounded-xl">

            <table class="w-full border-collapse text-sm">

                <thead>
                    <tr class="bg-slate-900 text-white">
                        <th class="border border-slate-900 px-3 py-3 w-[5%]">No</th>
                        <th class="border border-slate-900 px-3 py-3 text-left">Anggota</th>
                        <th class="border border-slate-900 px-3 py-3 text-left">Buku</th>
                        <th class="border border-slate-900 px-3 py-3">Deadline</th>
                        <th class="border border-slate-900 px-3 py-3">Dikembalikan</th>
                        <th class="border border-slate-900 px-3 py-3">Telat</th>
                        <th class="border border-slate-900 px-3 py-3">Kondisi</th>
                        <th class="border border-slate-900 px-3 py-3">Total Denda</th>
                        <th class="border border-slate-900 px-3 py-3">Status</th>
                    </tr>
                </thead>

                <tbody>

                    <?php $no = 1; ?>

                    <?php if (mysqli_num_rows($query) > 0): ?>

                        <?php while ($row = mysqli_fetch_assoc($query)): ?>

                            <?php
                            $kondisi_buku = strtolower($row['kondisi_buku'] ?? 'baik');

                            if (!in_array($kondisi_buku, ['baik', 'rusak', 'hilang'])) {
                                $kondisi_buku = 'baik';
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
                                    ) / (60 * 60 * 24)
                                );
                            }

                            $denda_telat  = $hari_telat * $denda_per_hari;
                            $denda_rusak  = 0;
                            $denda_hilang = 0;

                            if ($kondisi_buku == 'rusak') {
                                $denda_rusak = $row['harga'] / $rumus_rusak;
                            }

                            if ($kondisi_buku == 'hilang') {
                                $denda_hilang = $row['harga'] * $rumus_hilang;
                            }

                            $total_denda = $denda_telat + $denda_rusak + $denda_hilang;
                            $total_semua += $total_denda;
                            ?>

                            <tr class="<?= ($no % 2 == 0) ? 'bg-slate-50' : 'bg-white'; ?>">

                                <td class="border border-slate-300 px-3 py-3 text-center">
                                    <?= $no++; ?>
                                </td>

                                <td class="border border-slate-300 px-3 py-3 font-semibold">
                                    <?= htmlspecialchars($row['nama']); ?>
                                </td>

                                <td class="border border-slate-300 px-3 py-3">
                                    <?= htmlspecialchars($row['judul']); ?>
                                </td>

                                <td class="border border-slate-300 px-3 py-3 text-center">
                                    <?= !empty($row['tanggal_kembali'])
                                        ? date('d-m-Y', strtotime($row['tanggal_kembali']))
                                        : '-'; ?>
                                </td>

                                <td class="border border-slate-300 px-3 py-3 text-center">
                                    <?= !empty($row['tanggal_dikembalikan'])
                                        ? date('d-m-Y', strtotime($row['tanggal_dikembalikan']))
                                        : '-'; ?>
                                </td>

                                <td class="border border-slate-300 px-3 py-3 text-center">
                                    <?= $hari_telat; ?> Hari
                                </td>

                                <td class="border border-slate-300 px-3 py-3 text-center font-semibold">
                                    <?= strtoupper($kondisi_buku); ?>
                                </td>

                                <td class="border border-slate-300 px-3 py-3 text-center font-bold">
                                    Rp <?= number_format($total_denda, 0, ',', '.'); ?>
                                </td>

                                <td class="border border-slate-300 px-3 py-3 text-center">
                                    <?= $row['status_denda'] == 'sudah_dibayar'
                                        ? 'Sudah Dibayar'
                                        : 'Belum Dibayar'; ?>
                                </td>

                            </tr>

                        <?php endwhile; ?>

                        <tr class="bg-slate-900 text-white">

                            <td colspan="7"
                                class="border border-slate-900 px-3 py-3 text-right font-bold">
                                Total Semua Denda
                            </td>

                            <td colspan="2"
                                class="border border-slate-900 px-3 py-3 font-bold">
                                Rp <?= number_format($total_semua, 0, ',', '.'); ?>
                            </td>

                        </tr>

                    <?php else: ?>

                        <tr>
                            <td colspan="9"
                                class="border border-slate-300 px-3 py-8 text-center text-slate-500">
                                Data denda anggota tidak ditemukan.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

        <!-- FOOTER TTD -->
        <div class="mt-14 flex justify-end">

            <div class="text-center w-56">

                <p class="text-sm">
                    <?= date('d F Y'); ?>
                </p>

                <div class="h-24"></div>

                <p class="font-bold">
                    Petugas Perpustakaan
                </p>

            </div>

        </div>

    </div>

    <script>
        window.addEventListener('load', function () {
            setTimeout(function () {
                window.print();
            }, 500);
        });
    </script>

</body>

</html>