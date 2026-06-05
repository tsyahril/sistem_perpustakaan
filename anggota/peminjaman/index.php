<?php
session_start();

include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../middleware/anggota.php";
include __DIR__ .  "/../layout/header.php";

$id_user = $_SESSION['id_user'];

if (isset($_SESSION['success'])): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: '<?= $_SESSION['success']; ?>',
        background: '#0f172a',
        color: '#fff',
        confirmButtonColor: '#10b981'
    });
</script>
<?php unset($_SESSION['success']); endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<script>
    Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: '<?= $_SESSION['error']; ?>',
        background: '#0f172a',
        color: '#fff',
        confirmButtonColor: '#ef4444'
    });
</script>
<?php unset($_SESSION['error']); endif; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php

// ==========================
// AMBIL SETTING DENDA
// ==========================
$get_denda = mysqli_query($conn, "
    SELECT jumlah_denda 
    FROM denda 
    LIMIT 1
");

$data_denda = mysqli_fetch_assoc($get_denda);
$denda_per_hari = $data_denda['jumlah_denda'] ?? 5000;
?>

<!-- HEADER -->
<div class="flex justify-between items-center mb-6">

    <div>
        <p class="text-slate-400 text-sm mt-1">
            Daftar buku yang sedang dipinjam atau menunggu persetujuan.
        </p>
    </div>

    <div class="flex gap-3">

        <a href="../riwayat/riwayat.php"
            class="bg-slate-700 hover:bg-slate-600 transition px-4 py-3 rounded-xl text-white flex items-center gap-2">

            <i class='bx bx-history text-xl'></i>
            Riwayat Selesai

        </a>

        <a href="tambanyak.php"
            class="bg-blue-500 hover:bg-blue-600 transition px-5 py-3 rounded-xl text-white font-semibold flex items-center gap-2 shadow-lg">

            <i class='bx bx-plus'></i>
            Pinjam

        </a>

    </div>
</div>

<!-- CARD INFO -->
<div class="grid md:grid-cols-3 gap-5 mb-6">

    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">

        <div class="flex items-center justify-between">

            <div>

                <p class="text-slate-400 text-sm">
                    Peminjaman Aktif
                </p>

                <?php
                $q_aktif = mysqli_query($conn, "
                    SELECT COUNT(*) as total 
                    FROM peminjaman 
                    WHERE status = 'dipinjam'
                    AND id_user = '$id_user'
                ");

                $aktif = mysqli_fetch_assoc($q_aktif);
                ?>

                <h2 class="text-3xl font-bold text-white mt-2">
                    <?= $aktif['total']; ?>
                </h2>

            </div>

            <div class="w-14 h-14 rounded-2xl bg-blue-500/10 text-blue-400 flex items-center justify-center text-3xl">
                <i class='bx bxs-book-bookmark'></i>
            </div>

        </div>

    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">

        <div class="flex items-center justify-between">

            <div>

                <p class="text-slate-400 text-sm">
                    Denda / Hari
                </p>

                <h2 class="text-3xl font-bold text-red-400 mt-2">
                    Rp <?= number_format($denda_per_hari, 0, ',', '.'); ?>
                </h2>

            </div>

            <div class="w-14 h-14 rounded-2xl bg-red-500/10 text-red-400 flex items-center justify-center text-3xl">
                <i class='bx bx-money'></i>
            </div>

        </div>

    </div>

</div>

<!-- TABLE -->
<div class="overflow-x-auto bg-slate-900 border border-slate-800 rounded-2xl shadow-xl">

    <table class="w-full text-sm text-left text-slate-300">

        <thead class="bg-slate-800 text-slate-200 uppercase text-xs">

            <tr>

                <th class="px-6 py-4">Peminjam</th>
                <th class="px-6 py-4">Buku</th>
                <th class="px-6 py-4">Tanggal Pinjam</th>
                <th class="px-6 py-4">Deadline</th>
                <th class="px-6 py-4">Status</th>
                <th class="px-6 py-4">Keterlambatan</th>
                <th class="px-6 py-4">Denda</th>
                <th class="px-6 py-4 text-center">Aksi</th>

            </tr>

        </thead>

        <tbody>

            <?php
            $query = mysqli_query($conn, "
            SELECT 
                user.nama,
                peminjaman.id_user,
                peminjaman.tanggal_pinjam,
                peminjaman.tanggal_kembali,
                peminjaman.status,

                GROUP_CONCAT(buku.judul SEPARATOR '||') as daftar_buku,
                GROUP_CONCAT(buku.cover SEPARATOR '||') as daftar_cover,

                GROUP_CONCAT(
                    COALESCE(penulis.nama_penulis, '-') 
                    SEPARATOR '||'
                ) as daftar_penulis,

                GROUP_CONCAT(
                    COALESCE(penerbit.nama_penerbit, '-') 
                    SEPARATOR '||'
                ) as daftar_penerbit,

                GROUP_CONCAT(peminjaman.id_pinjam SEPARATOR ',') as daftar_id,

                COUNT(buku.id_buku) as total_buku

            FROM peminjaman

            JOIN buku 
                ON peminjaman.id_buku = buku.id_buku

            LEFT JOIN penulis
                ON buku.id_penulis = penulis.id_penulis

            LEFT JOIN penerbit
                ON buku.id_penerbit = penerbit.id_penerbit

            JOIN user 
                ON peminjaman.id_user = user.id_user

            WHERE peminjaman.status IN ('pending', 'dipinjam')
            AND peminjaman.id_user = '$id_user'

            GROUP BY 
                peminjaman.id_user,
                peminjaman.tanggal_pinjam,
                peminjaman.tanggal_kembali,
                peminjaman.status

            ORDER BY MAX(peminjaman.id_pinjam) DESC
        ");

            if (mysqli_num_rows($query) == 0):
            ?>

                <tr>

                    <td colspan="8" class="text-center py-10 text-slate-500">
                        Tidak ada peminjaman aktif saat ini.
                    </td>

                </tr>

            <?php else: ?>

                <?php while ($row = mysqli_fetch_assoc($query)):

                    $denda = 0;
                    $telat_hari = 0;

                    $tanggal_sekarang = strtotime(date('Y-m-d'));
                    $tanggal_deadline = strtotime($row['tanggal_kembali']);

                    if ($row['status'] == 'dipinjam' && $tanggal_sekarang > $tanggal_deadline) {

                        $telat_hari = floor(($tanggal_sekarang - $tanggal_deadline) / (60 * 60 * 24));
                        $denda = $telat_hari * $denda_per_hari;
                    }

                    $listJudul = explode('||', $row['daftar_buku']);
                    $listCover = explode('||', $row['daftar_cover']);
                    $listPenulis = explode('||', $row['daftar_penulis']);
                    $listPenerbit = explode('||', $row['daftar_penerbit']);

                    $firstId = explode(',', $row['daftar_id'])[0];
                ?>

                    <tr class="border-b border-slate-800 hover:bg-slate-800/50 transition">

                        <!-- USER -->
                        <td class="px-6 py-4 font-semibold text-white">
                            <?= htmlspecialchars($row['nama']); ?>
                        </td>

                        <!-- BUKU -->
                        <td class="px-6 py-4">

                            <div class="flex items-center gap-4">

                                <!-- COVER SLIDER -->
                                <div class="relative w-14 h-20 overflow-hidden rounded-lg">

                                    <?php foreach ($listCover as $i => $cover): ?>

                                        <img src="../../assets/img/cover/<?= $cover; ?>"
                                            class="cover-slide-<?= $firstId; ?> absolute inset-0 w-14 h-20 object-cover rounded-lg shadow transition-all duration-500 <?= $i == 0 ? 'opacity-100' : 'opacity-0'; ?>">

                                    <?php endforeach; ?>

                                </div>

                                <!-- INFO -->
                                <div>

                                    <div class="text-white font-semibold">
                                        <?= htmlspecialchars($listJudul[0]); ?>
                                    </div>

                                    <?php if ($row['total_buku'] > 1): ?>

                                        <div class="text-xs text-slate-400 mt-1">
                                            +<?= $row['total_buku'] - 1; ?> buku lainnya
                                        </div>

                                    <?php endif; ?>

                                </div>

                            </div>

                        </td>

                        <!-- TANGGAL -->
                        <td class="px-6 py-4">
                            <?= date('d M Y', strtotime($row['tanggal_pinjam'])); ?>
                        </td>

                        <!-- DEADLINE -->
                        <td class="px-6 py-4">

                            <span class="<?= ($denda > 0) ? 'text-red-400 font-semibold' : 'text-slate-300'; ?>">
                                <?= date('d M Y', strtotime($row['tanggal_kembali'])); ?>
                            </span>

                        </td>

                        <!-- STATUS -->
                        <td class="px-6 py-4">

                            <?php
                            $color = ($row['status'] == 'pending') ? 'yellow' : 'blue';
                            ?>

                            <span class="bg-<?= $color; ?>-500/10 text-<?= $color; ?>-400 border border-<?= $color; ?>-500/20 px-3 py-1 rounded-full text-xs font-semibold">
                                <?= strtoupper($row['status']); ?>
                            </span>

                        </td>

                        <!-- TELAT -->
                        <td class="px-6 py-4">

                            <?php if ($telat_hari > 0): ?>

                                <span class="text-red-400 font-semibold">
                                    <?= $telat_hari; ?> Hari
                                </span>

                            <?php else: ?>

                                <span class="text-slate-400">-</span>

                            <?php endif; ?>

                        </td>

                        <!-- DENDA -->
                        <td class="px-6 py-4">

                            <?php if ($denda > 0): ?>

                                <span class="text-red-400 font-bold">
                                    Rp <?= number_format($denda, 0, ',', '.'); ?>
                                </span>

                            <?php else: ?>

                                <span class="text-slate-400">-</span>

                            <?php endif; ?>

                        </td>

                        <!-- AKSI -->
                        <td class="px-6 py-4 text-center">

                            <div class="flex justify-center items-center gap-3 flex-wrap">

                                <?php if ($row['total_buku'] > 1): ?>

                                    <!-- DETAIL -->
                                    <button
                                        type="button"

                                        onclick='openDetail(
                                        <?= json_encode($listJudul); ?>,
                                        <?= json_encode($listCover); ?>,
                                        <?= json_encode($listPenulis); ?>,
                                        <?= json_encode($listPenerbit); ?>,
                                        "<?= date('d M Y', strtotime($row['tanggal_pinjam'])); ?>",
                                        "<?= date('d M Y', strtotime($row['tanggal_kembali'])); ?>"
                                                )'

                                        class="bg-blue-500 hover:bg-blue-600 transition text-white px-4 py-2 rounded-xl text-sm font-semibold flex items-center gap-2">

                                        <i class='bx bx-detail'></i>
                                        Detail

                                    </button>

                                <?php endif; ?>

                                <?php if ($row['status'] == 'pending'): ?>

                                    <!-- APPROVE -->
                                    <a href="proses.php?action=approve&id=<?= $firstId; ?>"
                                        onclick="confirmApprove(event, this.href)"
                                        class="bg-emerald-500 hover:bg-emerald-600 transition text-white px-4 py-2 rounded-xl text-sm font-semibold flex items-center gap-2">

                                        <i class='bx bx-check'></i>
                                        Approve

                                    </a>

                                    <!-- REJECT -->
                                    <a href="proses.php?action=reject&id=<?= $firstId; ?>"
                                        onclick="confirmReject(event, this.href)"
                                        class="bg-red-500 hover:bg-red-600 transition text-white px-4 py-2 rounded-xl text-sm font-semibold flex items-center gap-2">

                                        <i class='bx bx-x'></i>
                                        Reject

                                    </a>

                                <?php else: ?>

                                    <!-- SELESAI -->
                                    <a href="detail.php?id=<?= $firstId; ?>"
                                        onclick="confirmSelesai(event, this.href)"
                                        class="bg-emerald-500 hover:bg-emerald-600 transition text-white px-4 py-2 rounded-xl text-sm font-semibold flex items-center gap-2">

                                        <i class='bx bx-check-circle'></i>
                                        Selesai

                                    </a>

                                <?php endif; ?>

                            </div>

                        </td>

                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php endif; ?>

        </tbody>

    </table>

</div>

<script>
    function confirmApprove(event, url) {

        event.preventDefault();

        Swal.fire({
            title: 'Setujui Peminjaman?',
            icon: 'question',
            background: '#0f172a',
            color: '#fff',
            showCancelButton: true,
            confirmButtonText: 'Ya'
        }).then((res) => {

            if (res.isConfirmed) {
                window.location.href = url;
            }

        });

    }

    function confirmReject(event, url) {

        event.preventDefault();

        Swal.fire({
            title: 'Tolak Peminjaman?',
            icon: 'warning',
            background: '#0f172a',
            color: '#fff',
            showCancelButton: true
        }).then((res) => {

            if (res.isConfirmed) {
                window.location.href = url;
            }

        });

    }

    function confirmSelesai(event, url) {

        event.preventDefault();

        Swal.fire({
            title: 'Selesaikan peminjaman?',
            icon: 'question',
            background: '#0f172a',
            color: '#fff',
            showCancelButton: true,
            confirmButtonText: 'Ya'
        }).then((res) => {

            if (res.isConfirmed) {
                window.location.href = url;
            }

        });

    }
</script>

<script>
    function openDetail(
        judul,
        cover,
        penulis,
        penerbit,
        tanggalPinjam,
        tanggalKembali
    ) {

        let html = `
            <div class="overflow-x-auto">

                <table class="w-full text-sm text-left border border-slate-700 rounded-xl overflow-hidden">

                    <thead class="bg-slate-800 text-slate-300">

                        <tr>
                            <th class="px-4 py-3">Cover</th>
                            <th class="px-4 py-3">Judul</th>
                            <th class="px-4 py-3">Penulis</th>
                            <th class="px-4 py-3">Penerbit</th>
                            <th class="px-4 py-3">Pinjam</th>
                            <th class="px-4 py-3">Kembali</th>
                        </tr>

                    </thead>

                    <tbody>
        `;

        for (let i = 0; i < judul.length; i++) {

            html += `
                <tr class="border-t border-slate-700 bg-slate-900 text-white">

                    <td class="px-4 py-4">
                        <img 
                            src="../../assets/img/cover/${cover[i]}"
                            class="w-14 h-20 object-cover rounded-lg shadow"
                        >
                    </td>

                    <td class="px-4 py-4 font-semibold">
                        ${judul[i]}
                    </td>

                    <td class="px-4 py-4">
                        ${penulis[i]}
                    </td>

                    <td class="px-4 py-4">
                        ${penerbit[i]}
                    </td>

                    <td class="px-4 py-4">
                        ${tanggalPinjam}
                    </td>

                    <td class="px-4 py-4">
                        ${tanggalKembali}
                    </td>

                </tr>
            `;
        }

        html += `
                    </tbody>
                </table>
            </div>
        `;

        Swal.fire({
            title: 'Detail Peminjaman',
            html: html,
            background: '#0f172a',
            color: '#fff',
            width: 1200,
            confirmButtonColor: '#3b82f6'
        });

    }

    // AUTO SLIDE COVER
    document.querySelectorAll('[class^="cover-slide-"]').forEach((el) => {

        let className = [...el.classList]
            .find(c => c.startsWith('cover-slide-'));

        if (!window[className]) {

            window[className] = document.querySelectorAll('.' + className);

            let slides = window[className];

            if (slides.length > 1) {

                let index = 0;

                setInterval(() => {

                    slides[index].classList.remove('opacity-100');
                    slides[index].classList.add('opacity-0');

                    index = (index + 1) % slides.length;

                    slides[index].classList.remove('opacity-0');
                    slides[index].classList.add('opacity-100');

                }, 1000);

            }

        }

    });
</script>

<?php include __DIR__ .  "/../layout/footer.php"; ?>