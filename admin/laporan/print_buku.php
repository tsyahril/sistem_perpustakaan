<?php
include __DIR__ . "/../../config/koneksi.php";

// ========================== //
// FILTER DATA (Ambil dari GET)
// ========================== //
$where = [];
$having = [];

// Filter Kategori
if (!empty($_GET['id_kategori'])) {
    $id_kategori = intval($_GET['id_kategori']);
    $where[] = "buku.id_kategori = '$id_kategori'";
}

// Filter Stok
if (!empty($_GET['stok'])) {
    $stok_filter = mysqli_real_escape_string($conn, $_GET['stok']);
    if ($stok_filter === 'tersedia') {
        $where[] = "buku.stok > 0";
    } elseif ($stok_filter === 'habis') {
        $where[] = "buku.stok = 0";
    }
}

// Filter Rating (Menggunakan HAVING karena rating hasil dari fungsi agregasi AVG)
if (!empty($_GET['rating'])) {
    $rating_filter = intval($_GET['rating']);
    if ($rating_filter === 5) {
        $having[] = "rating = 5";
    } else {
        $having[] = "rating >= '$rating_filter'";
    }
}

// Menyusun SQL String
$where_sql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
$having_sql = !empty($having) ? "HAVING " . implode(" AND ", $having) : "";

// ========================== //
// QUERY DATA BUKU            //
// ========================== //
$query = mysqli_query($conn, "
    SELECT 
        buku.*,
        kategori.nama_kategori,
        COALESCE(AVG(ulasan.rating), 0) AS rating
    FROM buku
    LEFT JOIN kategori ON buku.id_kategori = kategori.id_kategori
    LEFT JOIN ulasan ON buku.id_buku = ulasan.id_buku
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
    <title>Laporan Data Buku</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            padding: 40px;
            color: #111;
            background: #fff;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 35px;
        }

        .header h1 {
            font-size: 26px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .header p {
            color: #555;
            font-size: 14px;
        }

        .line {
            width: 100%;
            height: 2px;
            background: #000;
            margin-top: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }

        table th {
            background: #111827;
            color: white;
            padding: 12px 10px;
            border: 1px solid #000;
            font-size: 13px;
            text-transform: uppercase;
        }

        table td {
            border: 1px solid #000;
            padding: 10px;
            font-size: 12px;
            vertical-align: middle;
        }

        table tr:nth-child(even) {
            background: #f9fafb;
        }

        .text-center {
            text-align: center;
        }

        .text-danger {
            color: #dc2626;
            font-weight: bold;
        }

        .badge-stok {
            font-weight: bold;
        }

        .footer {
            margin-top: 60px;
            display: flex;
            justify-content: flex-end;
            page-break-inside: avoid;
        }

        .ttd {
            text-align: center;
            width: 220px;
        }

        .ttd-space {
            height: 75px;
        }

        @media print {
            body {
                padding: 20px;
            }
            table th {
                background: #111827 !important;
                color: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>Laporan Data Buku</h1>
        <p>Sistem Informasi Perpustakaan</p>
        <div class="line"></div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Judul Buku</th>
                <th width="20%">Kategori</th>
                <th width="12%">Stok</th>
                <th width="15%">Rating</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1; 
            if (mysqli_num_rows($query) > 0):
                while ($row = mysqli_fetch_assoc($query)): 
                    $rating = round($row['rating'], 1);
                    $stok = intval($row['stok']);
                    $nama_kategori = !empty($row['nama_kategori']) ? $row['nama_kategori'] : 'Tanpa Kategori';
            ?>
                    <tr>
                        <td class="text-center"><?= $no++; ?></td>
                        <td><strong><?= htmlspecialchars($row['judul']); ?></strong></td>
                        <td><?= htmlspecialchars($nama_kategori); ?></td>
                        <td class="text-center <?= ($stok === 0) ? 'text-danger' : ''; ?>">
                            <span class="badge-stok"><?= $stok; ?></span>
                        </td>
                        <td class="text-center">
                            <?= ($rating == 0) ? "-" : "⭐ " . $rating . " / 5"; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center" style="padding: 20px; color: #666; font-style: italic;">
                        Data buku tidak ditemukan atau tidak memenuhi kriteria filter.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        <div class="ttd">
            <p><?= date('d F Y'); ?></p>
            <div class="ttd-space"></div>
            <p><strong>Administrator</strong></p>
        </div>
    </div>

    <script>
        // Otomatis trigger print setelah asset selesai diload
        window.addEventListener('DOMContentLoaded', () => {
            window.print();
        });
    </script>

</body>
</html>