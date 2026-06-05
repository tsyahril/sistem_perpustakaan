<?php
include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../middleware/petugas.php";

$where = [];

$where[] = "
    (
        peminjaman.status IN ('kembali', 'selesai')
        OR peminjaman.tanggal_dikembalikan IS NOT NULL
    )
";

$where[] = "user.role IN ('anggota', 'user')";

if (!empty($_GET['tanggal_awal'])) {
    $tanggal_awal = mysqli_real_escape_string($conn, $_GET['tanggal_awal']);
    $where[] = "DATE(peminjaman.tanggal_dikembalikan) >= '$tanggal_awal'";
}

if (!empty($_GET['tanggal_akhir'])) {
    $tanggal_akhir = mysqli_real_escape_string($conn, $_GET['tanggal_akhir']);
    $where[] = "DATE(peminjaman.tanggal_dikembalikan) <= '$tanggal_akhir'";
}

if (!empty($_GET['id_user'])) {
    $id_user = (int) $_GET['id_user'];
    $where[] = "peminjaman.id_user = '$id_user'";
}

$where_sql = "WHERE " . implode(" AND ", $where);

$query = mysqli_query($conn, "
    SELECT
        peminjaman.*,
        user.nama,
        buku.judul

    FROM peminjaman

    JOIN user
        ON peminjaman.id_user = user.id_user

    JOIN buku
        ON peminjaman.id_buku = buku.id_buku

    $where_sql

    ORDER BY peminjaman.tanggal_dikembalikan DESC, peminjaman.id_pinjam DESC
");

if (!$query) {
    die("Query Error: " . mysqli_error($conn));
}

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
    <title>Laporan Peminjaman Anggota</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        @media print {
            .no-print { display: none !important; }
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            @page { margin: 14mm; }
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
            Laporan Riwayat Peminjaman Anggota
        </h1>

        <p class="text-slate-500 text-sm mt-2">
            Sistem Informasi Perpustakaan
        </p>

        <div class="w-full h-[3px] bg-slate-900 mt-5"></div>

        <p class="text-sm mt-4">
            Periode:
            <span class="font-semibold">
                <?= $periode_awal; ?> - <?= $periode_akhir; ?>
            </span>
        </p>
    </div>

    <div class="overflow-hidden border border-slate-900 rounded-xl">

        <table class="w-full border-collapse text-sm">

            <thead>
                <tr class="bg-slate-900 text-white">
                    <th class="border border-slate-900 px-3 py-3 w-[5%]">No</th>
                    <th class="border border-slate-900 px-3 py-3 text-left">Anggota</th>
                    <th class="border border-slate-900 px-3 py-3 text-left">Buku</th>
                    <th class="border border-slate-900 px-3 py-3">Tanggal Pinjam</th>
                    <th class="border border-slate-900 px-3 py-3">Deadline</th>
                    <th class="border border-slate-900 px-3 py-3">Dikembalikan</th>
                    <th class="border border-slate-900 px-3 py-3">Keterangan</th>
                </tr>
            </thead>

            <tbody>
                <?php $no = 1; ?>

                <?php if (mysqli_num_rows($query) > 0): ?>

                    <?php while ($row = mysqli_fetch_assoc($query)): ?>

                        <?php
                        $hari_telat = 0;

                        if (
                            !empty($row['tanggal_dikembalikan'])
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

                        $keterangan = $hari_telat > 0
                            ? 'Telat ' . $hari_telat . ' Hari'
                            : 'Dikembalikan';
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
                                <?= date('d-m-Y', strtotime($row['tanggal_pinjam'])); ?>
                            </td>

                            <td class="border border-slate-300 px-3 py-3 text-center">
                                <?= date('d-m-Y', strtotime($row['tanggal_kembali'])); ?>
                            </td>

                            <td class="border border-slate-300 px-3 py-3 text-center">
                                <?= !empty($row['tanggal_dikembalikan'])
                                    ? date('d-m-Y', strtotime($row['tanggal_dikembalikan']))
                                    : '-'; ?>
                            </td>

                            <td class="border border-slate-300 px-3 py-3 text-center font-semibold">
                                <?= htmlspecialchars($keterangan); ?>
                            </td>
                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="7" class="border border-slate-300 px-3 py-8 text-center text-slate-500">
                            Data peminjaman anggota tidak ditemukan.
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