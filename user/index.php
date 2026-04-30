<?php 
include '../middleware/user.php';
include 'layouts/header.php'; 

// Simulasi ambil data dari database
$kategori = ["Disarankan untuk Anda", "Buku Bestseller", "Bisnis & Investasi"];
?>

<div class="dashboard-header">
    <div class="tabs">
        <button class="tab-btn active">Untuk Anda</button>
        <button class="tab-btn">Terlaris</button>
        <button class="tab-btn">Rilis Baru</button>
    </div>
</div>

<?php foreach($kategori as $kat): ?>
<section class="book-section">
    <div class="section-header">
        <h2><?= $kat ?></h2>
        <a href="buku.php">Lihat semua <i class='bx bx-chevron-right'></i></a>
    </div>
    <div class="book-grid">
        <?php for($i=1; $i<=5; $i++): ?>
        <div class="book-card">
            <div class="book-cover">
                <img src="https://via.placeholder.com/150x220" alt="Cover Buku">
                <div class="rating-badge"><i class='bx bxs-star'></i> 4.5</div>
            </div>
            <div class="book-info">
                <h3>Judul Buku Contoh <?= $i ?></h3>
                <p>Penulis Buku</p>
                <span class="price">Gratis</span>
            </div>
        </div>
        <?php endfor; ?>
    </div>
</section>
<?php endforeach; ?>

<?php include 'layouts/footer.php'; ?>