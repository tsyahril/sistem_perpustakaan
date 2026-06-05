<?php
include __DIR__ . "/../../config/koneksi.php";

// ==========================
// FILTER
// ==========================
$where = [];

// Ambil data riwayat saja
$where[] = "
    (
        peminjaman.status IN ('kembali', 'selesai')
        OR peminjaman.tanggal_dikembalikan IS NOT NULL
    )
";

// Filter tanggal awal
if (!empty($_GET['tanggal_awal'])) {
    $tanggal_awal = mysqli_real_escape_string($conn, $_GET['tanggal_awal']);
    $where[] = "DATE(peminjaman.tanggal_dikembalikan) >= '$tanggal_awal'";
}

// Filter tanggal akhir
if (!empty($_GET['tanggal_akhir'])) {
    $tanggal_akhir = mysqli_real_escape_string($conn, $_GET['tanggal_akhir']);
    $where[] = "DATE(peminjaman.tanggal_dikembalikan) <= '$tanggal_akhir'";
}

// Filter user peminjam
if (!empty($_GET['id_user'])) {
    $id_user = intval($_GET['id_user']);
    $where[] = "peminjaman.id_user = '$id_user'";
}

$where_sql = "WHERE " . implode(" AND ", $where);

// ==========================
// QUERY RIWAYAT PEMINJAMAN
// ==========================
$query = mysqli_query($conn, "
    SELECT 
        peminjaman.id_pinjam,
        peminjaman.tanggal_pinjam,
        peminjaman.tanggal_kembali,
        peminjaman.tanggal_dikembalikan,
        peminjaman.status,

        buku.judul,

        user.nama

    FROM peminjaman

    JOIN buku 
        ON peminjaman.id_buku = buku.id_buku

    JOIN user 
        ON peminjaman.id_user = user.id_user

    $where_sql

    ORDER BY peminjaman.tanggal_dikembalikan DESC, peminjaman.id_pinjam DESC
");

if (!$query) {
    die("Query Error: " . mysqli_error($conn));
}

// ==========================
// FORMAT FILTER
// ==========================
$periode_awal = !empty($_GET['tanggal_awal'])
    ? date('d F Y', strtotime($_GET['tanggal_awal']))
    : 'Awal';

$periode_akhir = !empty($_GET['tanggal_akhir'])
    ? date('d F Y', strtotime($_GET['tanggal_akhir']))
    : 'Akhir';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Riwayat Peminjaman</title>

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

        <!-- BUTTON PRINT -->
        <div class="no-print mb-6 flex justify-end">

            <button onclick="window.print()"
                class="bg-slate-900 hover:bg-slate-800 text-white px-5 py-3 rounded-xl font-semibold shadow">

                Print Ulang

            </button>

        </div>

        <!-- HEADER -->
        <div class="text-center mb-8">

            <h1 class="text-3xl font-bold uppercase tracking-wide">
                Laporan Riwayat Peminjaman
            </h1>

            <p class="text-slate-500 text-sm mt-2">
                Sistem Informasi Perpustakaan
            </p>

            <div class="w-full h-[3px] bg-slate-900 mt-5"></div>

            <div class="mt-5 flex justify-center text-sm">

                <div class="border border-slate-300 rounded-lg px-4 py-2">
                    <span class="font-semibold">Periode:</span>
                    <?= $periode_awal; ?> - <?= $periode_akhir; ?>
                </div>

            </div>

        </div>

        <!-- TABLE -->
        <div class="overflow-hidden border border-slate-900 rounded-xl">

            <table class="w-full border-collapse text-sm">

                <thead>

                    <tr class="bg-slate-900 text-white">

                        <th class="border border-slate-900 px-3 py-3 w-[5%]">
                            No
                        </th>

                        <th class="border border-slate-900 px-3 py-3 text-left">
                            Peminjam
                        </th>

                        <th class="border border-slate-900 px-3 py-3 text-left">
                            Buku
                        </th>

                        <th class="border border-slate-900 px-3 py-3 w-[13%]">
                            Tgl Pinjam
                        </th>

                        <th class="border border-slate-900 px-3 py-3 w-[13%]">
                            Deadline
                        </th>

                        <th class="border border-slate-900 px-3 py-3 w-[13%]">
                            Dikembalikan
                        </th>

                        <th class="border border-slate-900 px-3 py-3 w-[18%]">
                            Keterangan
                        </th>

                    </tr>

                </thead>

                <tbody>

                    <?php $no = 1; ?>

                    <?php if (mysqli_num_rows($query) > 0): ?>

                        <?php while ($row = mysqli_fetch_assoc($query)): ?>

                            <?php
                            // ==========================
                            // HITUNG TELAT
                            // ==========================
                            $hari_telat = 0;

                            if (!empty($row['tanggal_dikembalikan']) && !empty($row['tanggal_kembali'])) {

                                $tanggal_dikembalikan = strtotime($row['tanggal_dikembalikan']);
                                $deadline = strtotime($row['tanggal_kembali']);

                                if ($tanggal_dikembalikan > $deadline) {
                                    $hari_telat = floor(
                                        ($tanggal_dikembalikan - $deadline) / (60 * 60 * 24)
                                    );
                                }
                            }

                            // ==========================
                            // KETERANGAN
                            // ==========================
                            if ($hari_telat > 0) {
                                $keterangan = 'Telat ' . $hari_telat . ' Hari';
                                $ketClass = 'text-red-700 font-semibold';
                            } else {
                                $keterangan = 'Dikembalikan';
                                $ketClass = 'text-emerald-700 font-semibold';
                            }
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
                                    <?= !empty($row['tanggal_pinjam'])
                                        ? date('d-m-Y', strtotime($row['tanggal_pinjam']))
                                        : '-'; ?>
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

                                <td class="border border-slate-300 px-3 py-3 text-center <?= $ketClass; ?>">
                                    <?= htmlspecialchars($keterangan); ?>
                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="7"
                                class="border border-slate-300 px-3 py-8 text-center text-slate-500">
                                Data riwayat peminjaman tidak ditemukan.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

        <!-- FOOTER -->
        <div class="mt-14 flex justify-end">

            <div class="text-center w-56">

                <p class="text-sm">
                    <?= date('d F Y'); ?>
                </p>

                <div class="h-24"></div>

                <p class="font-bold">
                    Administrator
                </p>

            </div>

        </div>

    </div>

    <script>
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 500);
        });
    </script>

</body>

</html>