<?php 
include '../config/koneksi.php'; 
include '../middleware/user.php';
include 'layouts/header.php'; 
?>

<div class="dashboard-header">
    <div class="tabs">
        <button class="tab-btn active">Semua Buku</button>
        <button class="tab-btn">Terbaru</button>
        <button class="tab-btn">Sering Dipinjam</button>
    </div>
</div>

<section class="book-section">
    <div class="section-header">
        <h2>Koleksi Buku Kami</h2>
        </div>

    <div class="book-grid">
        <?php 
        // Query ambil semua buku, diurutkan berdasarkan judul (A-Z)
        $query = mysqli_query($conn, "SELECT * FROM buku ORDER BY judul ASC");

        if(mysqli_num_rows($query) > 0) {
            while($data = mysqli_fetch_assoc($query)) {
        ?>
            <div class="book-card">
                <div class="book-cover">
                    <img src="../assets/img/<?= $data['cover']; ?>" alt="<?= $data['judul']; ?>" onerror="this.src='https://via.placeholder.com/150x220?text=No+Cover'">
                    
                    <div class="rating-badge">
                        <i class='bx bxs-star'></i> 4.8
                    </div>
                </div>
                <div class="book-info">
                    <h3><?= $data['judul']; ?></h3>
                    <p><?= $data['penulis']; ?></p>
                    <span class="price">Stok: <?= $data['stok']; ?></span>
                </div>
                
                <div style="margin-top: 10px;">
                    <a href="proses_pinjam.php?id=<?= $data['id_buku']; ?>" 
                       style="font-size: 12px; color: var(--biru-muda); text-decoration: none; font-weight: 600;">
                       Pinjam Sekarang →
                    </a>
                </div>
            </div>
        <?php 
            }
        } else {
            echo "<p style='color: #666;'>Maaf, saat ini belum ada buku yang terdaftar.</p>";
        }
        ?>
    </div>
</section>

<?php include 'layouts/footer.php'; ?>