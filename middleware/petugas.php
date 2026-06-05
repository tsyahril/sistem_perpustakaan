<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_user']) || !isset($_SESSION['role'])) {
    header("Location: /library/auth/login.php");
    exit;
}

if ($_SESSION['role'] !== 'petugas') {
    header("Location: /library/auth/login.php");
    exit;
}