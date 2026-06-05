<?php
include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../middleware/petugas.php";

$where = [];

if (!empty($_GET['id_kategori'])) {
    $id_kategori = (int) $_GET['id_kategori'];
    $where[] = "buku.id_kategori = '$id_kategori'";
}

if (!empty($_GET['stok'])) {
    if ($_GET['stok'] == 'tersedia') {
        $where[] = "buku.stok > 0";
    }

    if ($_GET['stok'] == 'habis') {
        $where[] = "buku.stok <= 0";
    }
}

$where_sql = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

$having_sql = "";

if (!empty($_GET['rating'])) {
    $rating = (int) $_GET['rating'];

    if ($rating >= 1 && $rating <= 5) {
        $having_sql = "HAVING rating >= '$rating'";
    }
}

$query = mysqli_query($conn, "
    SELECT 
        buku.*,
        kategori.nama_kategori,
        COALESCE(AVG(ulasan.rating), 0) AS rating

    FROM buku

    LEFT JOIN kategori
        ON buku.id_kategori = kategori.id_kategori

    LEFT JOIN ulasan
        ON buku.id_buku = ulasan.id_buku

    $where_sql

    GROUP BY buku.id_buku

    $having_sql

    ORDER BY buku.id_buku DESC
");

if (!$query) {
    die("Query Error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Buku</title>
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

    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold uppercase tracking-wide">
            Laporan Data Buku
        </h1>

        <p class="text-slate-500 text-sm mt-2">
            Sistem Informasi Perpustakaan
        </p>

        <div class="w-full h-[3px] bg-slate-900 mt-5"></div>
    </div>

    <div class="overflow-hidden border border-slate-900 rounded-xl">

        <table class="w-full border-collapse text-sm">

            <thead>
                <tr class="bg-slate-900 text-white">
                    <th class="border border-slate-900 px-3 py-3 w-[5%]">No</th>
                    <th class="border border-slate-900 px-3 py-3 text-left">Judul Buku</th>
                    <th class="border border-slate-900 px-3 py-3">Kategori</th>
                    <th class="border border-slate-900 px-3 py-3">Stok</th>
                    <th class="border border-slate-900 px-3 py-3">Rating</th>
                </tr>
            </thead>

            <tbody>
                <?php $no = 1; ?>

                <?php if (mysqli_num_rows($query) > 0): ?>

                    <?php while ($row = mysqli_fetch_assoc($query)): ?>

                        <?php $rating = round($row['rating'], 1); ?>

                        <tr class="<?= ($no % 2 == 0) ? 'bg-slate-50' : 'bg-white'; ?>">
                            <td class="border border-slate-300 px-3 py-3 text-center">
                                <?= $no++; ?>
                            </td>

                            <td class="border border-slate-300 px-3 py-3 font-semibold">
                                <?= htmlspecialchars($row['judul']); ?>
                            </td>

                            <td class="border border-slate-300 px-3 py-3 text-center">
                                <?= htmlspecialchars($row['nama_kategori'] ?? '-'); ?>
                            </td>

                            <td class="border border-slate-300 px-3 py-3 text-center">
                                <?= $row['stok']; ?>
                            </td>

                            <td class="border border-slate-300 px-3 py-3 text-center">
                                <?= $rating > 0 ? '⭐ ' . $rating . '/5' : '-'; ?>
                            </td>
                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="5" class="border border-slate-300 px-3 py-8 text-center text-slate-500">
                            Data buku tidak ditemukan.
                        </td>
                    </tr>

                <?php endif; ?>
            </tbody>

        </table>

    </div>

    <div class="mt-14 flex justify-end">
        <div class="text-center w-56">
            <p class="text-sm"><?= date('d F Y'); ?></p>
            <div class="h-24"></div>
            <p class="font-bold">Petugas Perpustakaan</p>
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