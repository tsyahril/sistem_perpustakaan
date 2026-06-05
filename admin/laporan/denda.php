<?php
session_start();

include __DIR__ . "/../../config/koneksi.php";

// ==========================
// FILTER USER
// ==========================
$where = "";

if (isset($_GET['all'])) {

    $where = "";

} elseif (isset($_GET['users'])) {

    $users = $_GET['users'];

    $ids = array_map('intval', explode(',', $users));

    $ids = implode(',', $ids);

    $where = "AND user.id_user IN ($ids)";
}

// ==========================
// QUERY DATA
// ==========================
$query = mysqli_query($conn, "
    SELECT
        peminjaman.*,
        user.nama,
        buku.judul,
        buku.harga,
        buku.status_buku

    FROM peminjaman

    JOIN user
    ON peminjaman.id_user = user.id_user

    JOIN buku
    ON peminjaman.id_buku = buku.id_buku

    WHERE peminjaman.status NOT IN ('pending', 'dipinjam')

    $where

    ORDER BY user.nama ASC
");

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

$total_semua = 0;
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Print Denda</title>

    <style>

        body{
            font-family: Arial, sans-serif;
            padding:30px;
        }

        h1{
            margin-bottom:5px;
        }

        p{
            margin-bottom:25px;
            color:#555;
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        table th,
        table td{
            border:1px solid #000;
            padding:10px;
            text-align:left;
        }

        table th{
            background:#eee;
        }

        .total{
            margin-top:20px;
            text-align:right;
            font-size:20px;
            font-weight:bold;
        }

    </style>

</head>

<body onload="window.print()">

    <h1>Data Denda Perpustakaan</h1>

    <p>
        Tanggal Print :
        <?= date('d-m-Y H:i'); ?>
    </p>

    <table>

        <thead>

            <tr>

                <th>No</th>
                <th>User</th>
                <th>Buku</th>
                <th>Telat</th>
                <th>Status Buku</th>
                <th>Total Denda</th>
                <th>Status</th>

            </tr>

        </thead>

        <tbody>

            <?php
            $no = 1;

            while ($row = mysqli_fetch_assoc($query)):

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

                $status_buku = strtolower($row['status_buku']);

                $denda_telat  = $hari_telat * $denda_per_hari;
                $denda_rusak  = 0;
                $denda_hilang = 0;

                if ($status_buku == 'rusak') {
                    $denda_rusak = $row['harga'] / $rumus_rusak;
                }

                if ($status_buku == 'hilang') {
                    $denda_hilang = $row['harga'] * $rumus_hilang;
                }

                $total_denda =
                    $denda_telat +
                    $denda_rusak +
                    $denda_hilang;

                $total_semua += $total_denda;
            ?>

                <tr>

                    <td><?= $no++; ?></td>

                    <td>
                        <?= htmlspecialchars($row['nama']); ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($row['judul']); ?>
                    </td>

                    <td>
                        <?= $hari_telat; ?> Hari
                    </td>

                    <td>
                        <?= strtoupper($status_buku); ?>
                    </td>

                    <td>
                        Rp <?= number_format($total_denda, 0, ',', '.'); ?>
                    </td>

                    <td>
                        <?= $row['status_denda'] == 'sudah_dibayar'
                            ? 'Sudah Dibayar'
                            : 'Belum Dibayar'; ?>
                    </td>

                </tr>

            <?php endwhile; ?>

        </tbody>

    </table>

    <div class="total">

        Total Semua Denda :
        Rp <?= number_format($total_semua, 0, ',', '.'); ?>

    </div>

</body>

</html>