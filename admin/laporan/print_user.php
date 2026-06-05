<?php
include __DIR__ . "/../../config/koneksi.php";

// ========================== //
// FILTER DATA (Ambil dari GET)
// ========================== //
$where = [];

if (!empty($_GET['role'])) {
    $role = mysqli_real_escape_string($conn, $_GET['role']);
    $role = strtolower($role);
    
    // Validasi role yang diperbolehkan masuk ke query
    if (in_array($role, ['admin', 'petugas', 'anggota'])) {
        $where[] = "LOWER(role) = '$role'";
    }
}

// Menyusun SQL String jika ada filter
$where_sql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// ========================== //
// QUERY DATA USER            //
// ========================== //
$query = mysqli_query($conn, "SELECT * FROM user $where_sql ORDER BY nama ASC");

if (!$query) {
    die("Query Error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Data User</title>
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

        .filter-info {
            margin-top: 15px;
            font-size: 13px;
            color: #333;
            text-align: center;
            font-style: italic;
            text-transform: capitalize;
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

        .badge-role {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
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
        <h1>Laporan Data User</h1>
        <p>Sistem Informasi Perpustakaan</p>
        <div class="line"></div>
        <?php if (!empty($_GET['role'])): ?>
            <div class="filter-info">
                Filter Role: <strong><?= htmlspecialchars($_GET['role']); ?></strong>
            </div>
        <?php endif; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Nama Lengkap</th>
                <th>Alamat Email</th>
                <th width="20%">Role / Hak Akses</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1; 
            if (mysqli_num_rows($query) > 0):
                while ($row = mysqli_fetch_assoc($query)): 
            ?>
                    <tr>
                        <td class="text-center"><?= $no++; ?></td>
                        <td><strong><?= htmlspecialchars($row['nama']); ?></strong></td>
                        <td><?= htmlspecialchars($row['email']); ?></td>
                        <td class="text-center">
                            <span class="badge-role"><?= htmlspecialchars($row['role']); ?></span>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center" style="padding: 20px; color: #666; font-style: italic;">
                        Data akun user tidak ditemukan atau tidak memenuhi kriteria filter.
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
        window.addEventListener('DOMContentLoaded', () => {
            window.print();
        });
    </script>

</body>
</html>