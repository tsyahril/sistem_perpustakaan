-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 13, 2026 at 02:28 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12
a
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sistem_perpustakaan`
--

-- --------------------------------------------------------

--
-- Table structure for table `buku`
--

CREATE TABLE `buku` (
  `id_buku` int(11) NOT NULL,
  `judul` varchar(150) NOT NULL,
  `cover` varchar(200) DEFAULT 'default.jpg',
  `penulis` varchar(100) DEFAULT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `penerbit` varchar(100) DEFAULT NULL,
  `tahun` int(11) DEFAULT NULL,
  `stok` int(11) DEFAULT 0,
  `id_kategori` int(11) DEFAULT NULL,
  `status_buku` enum('baik','rusak','hilang') DEFAULT 'baik',
  `deskripsi` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `buku`
--

INSERT INTO `buku` (`id_buku`, `judul`, `cover`, `penulis`, `isbn`, `penerbit`, `tahun`, `stok`, `id_kategori`, `status_buku`, `deskripsi`) VALUES
(5, 'Tujuh belas dosa Soeharto', '1778377869-7dosabesarsoeharto.jpg', 'Firdaus Jaya (Firm)', '97876123409332', 'Firdaus Jaya', 1998, 67, 5, 'baik', '17 dosa soeharto'),
(6, 'the king in  yellow', '1778377925-thekinginyellow.jpg', 'Robert W. Chambers', '9781503364127', 'F.Tennyson Neely', 1895, 10, 2, 'baik', 'The King in Yellow is a 1895 collection of short stories by Robert W. Chambers that blends supernatural horror, romance, and weird fiction.  The book is named after a fictional, cursed two-act play of');

-- --------------------------------------------------------

--
-- Table structure for table `denda`
--

CREATE TABLE `denda` (
  `id_denda` int(11) NOT NULL,
  `id_pinjam` int(11) DEFAULT NULL,
  `jumlah_denda` int(11) DEFAULT NULL,
  `status` enum('belum_bayar','sudah_bayar') DEFAULT 'belum_bayar'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `detail_peminjaman`
--

CREATE TABLE `detail_peminjaman` (
  `id_detail` int(11) NOT NULL,
  `id_pinjam` int(11) DEFAULT NULL,
  `id_buku` int(11) DEFAULT NULL,
  `jumlah` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int(11) NOT NULL,
  `nama_kategori` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`) VALUES
(2, 'novel'),
(4, 'komik'),
(5, 'cerita rayat');

-- --------------------------------------------------------

--
-- Table structure for table `peminjaman`
--

CREATE TABLE `peminjaman` (
  `id_pinjam` int(11) NOT NULL,
  `id_buku` int(11) DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL,
  `tanggal_pinjam` date DEFAULT NULL,
  `tanggal_kembali` date DEFAULT NULL,
  `status` enum('dipinjam','kembali') DEFAULT 'dipinjam'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `peminjaman`
--

INSERT INTO `peminjaman` (`id_pinjam`, `id_buku`, `id_user`, `tanggal_pinjam`, `tanggal_kembali`, `status`) VALUES
(1, 6, 4, '2026-05-12', '2026-05-19', '');

-- --------------------------------------------------------

--
-- Table structure for table `ulasan`
--

CREATE TABLE `ulasan` (
  `id_ulasan` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `id_buku` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `ulasan` text DEFAULT NULL,
  `tanggal_ulasan` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ulasan`
--

INSERT INTO `ulasan` (`id_ulasan`, `id_user`, `id_buku`, `rating`, `ulasan`, `tanggal_ulasan`) VALUES
(1, 4, 6, 5, 'mantap', '2026-05-08 07:45:45'),
(2, 10, 6, 3, 'mantap', '2026-05-08 07:45:45'),
(3, 4, 5, 4, 'cakep', '2026-05-12 01:05:47');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id_user` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` text DEFAULT NULL,
  `role` enum('admin','petugas','user') NOT NULL DEFAULT 'user',
  `no_hp` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id_user`, `nama`, `email`, `password`, `role`, `no_hp`) VALUES
(1, 'Admin', 'admin@gmail.com', 'admin123', 'admin', '0812345678'),
(4, 'ryu', 'ryu@gmail.com', 'ryu123', 'user', '0812345678'),
(5, 'pulu', 'pulu@gmail.com', 'pulu123', 'petugas', '08123456789'),
(6, 'wander', 'wander@gmail.com', 'wander123', 'admin', '0812345678'),
(7, 'pp', 'pp@gmail.com', 'pp123', 'petugas', '0812345678'),
(8, 'badrul', 'badrul@gmail.com', 'badrul123', 'user', '0812345678'),
(10, 'ang', 'ang@gmail.com', 'ang123', 'user', '0812345678');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `buku`
--
ALTER TABLE `buku`
  ADD PRIMARY KEY (`id_buku`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- Indexes for table `denda`
--
ALTER TABLE `denda`
  ADD PRIMARY KEY (`id_denda`),
  ADD KEY `id_pinjam` (`id_pinjam`);

--
-- Indexes for table `detail_peminjaman`
--
ALTER TABLE `detail_peminjaman`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_pinjam` (`id_pinjam`),
  ADD KEY `id_buku` (`id_buku`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indexes for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD PRIMARY KEY (`id_pinjam`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `ulasan`
--
ALTER TABLE `ulasan`
  ADD PRIMARY KEY (`id_ulasan`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_buku` (`id_buku`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `buku`
--
ALTER TABLE `buku`
  MODIFY `id_buku` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `denda`
--
ALTER TABLE `denda`
  MODIFY `id_denda` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `detail_peminjaman`
--
ALTER TABLE `detail_peminjaman`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `id_pinjam` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ulasan`
--
ALTER TABLE `ulasan`
  MODIFY `id_ulasan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `buku`
--
ALTER TABLE `buku`
  ADD CONSTRAINT `buku_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON DELETE SET NULL;

--
-- Constraints for table `denda`
--
ALTER TABLE `denda`
  ADD CONSTRAINT `denda_ibfk_1` FOREIGN KEY (`id_pinjam`) REFERENCES `peminjaman` (`id_pinjam`);

--
-- Constraints for table `detail_peminjaman`
--
ALTER TABLE `detail_peminjaman`
  ADD CONSTRAINT `detail_peminjaman_ibfk_1` FOREIGN KEY (`id_pinjam`) REFERENCES `peminjaman` (`id_pinjam`),
  ADD CONSTRAINT `detail_peminjaman_ibfk_2` FOREIGN KEY (`id_buku`) REFERENCES `buku` (`id_buku`);

--
-- Constraints for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD CONSTRAINT `peminjaman_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`);

--
-- Constraints for table `ulasan`
--
ALTER TABLE `ulasan`
  ADD CONSTRAINT `ulasan_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`),
  ADD CONSTRAINT `ulasan_ibfk_2` FOREIGN KEY (`id_buku`) REFERENCES `buku` (`id_buku`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


ALTER TABLE peminjaman 
ADD denda INT DEFAULT 0;

CREATE TABLE setting_denda (
    id_setting INT AUTO_INCREMENT PRIMARY KEY,
    denda_per_hari INT NOT NULL
);

INSERT INTO setting_denda (denda_per_hari)
VALUES (5000);


ALTER TABLE denda
ADD denda_rusak INT DEFAULT 0,
ADD denda_hilang INT DEFAULT 0;

ALTER TABLE buku
ADD harga INT DEFAULT 0;

ALTER TABLE buku 
ADD harga BIGINT NOT NULL DEFAULT 0;

ALTER TABLE peminjaman 
ADD kondisi_buku ENUM('baik','rusak','hilang') DEFAULT 'baik';

ALTER TABLE peminjaman
ADD tanggal_dikembalikan DATE NULL;

CREATE TABLE penulis (
    id_penulis INT AUTO_INCREMENT PRIMARY KEY,
    nama_penulis VARCHAR(255) NOT NULL
);

ALTER TABLE buku
ADD id_penulis INT NULL,
ADD id_penerbit INT NULL;

CREATE TABLE penerbit (
    id_penerbit INT AUTO_INCREMENT PRIMARY KEY,
    nama_penerbit VARCHAR(100) NOT NULL
);

ALTER TABLE buku
DROP COLUMN penulis;

ALTER TABLE buku
DROP COLUMN penerbit;

ALTER TABLE buku
ADD CONSTRAINT fk_penulis
FOREIGN KEY (id_penulis)
REFERENCES penulis(id_penulis)
ON DELETE SET NULL
ON UPDATE CASCADE;

ALTER TABLE buku
ADD CONSTRAINT fk_penerbit
FOREIGN KEY (id_penerbit)
REFERENCES penerbit(id_penerbit)
ON DELETE SET NULL
ON UPDATE CASCADE;

ALTER TABLE denda
ADD COLUMN status_bayar ENUM('belum','lunas') DEFAULT 'belum',
ADD COLUMN tanggal_bayar DATE NULL,
ADD COLUMN metode_bayar VARCHAR(50) NULL;

ALTER TABLE peminjaman
ADD status_denda VARCHAR(50) NULL,
ADD tanggal_bayar_denda DATETIME NULL;

ALTER TABLE user 
MODIFY role ENUM('admin', 'petugas', 'anggota') NOT NULL;

ALTER TABLE user 
ADD last_login DATETIME NULL;

ALTER TABLE buku 
DROP COLUMN status_buku;