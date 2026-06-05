<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role'])) {
    header("Location: /library/auth/login.php");
    exit;
}

if ($_SESSION['role'] != 'anggota' && $_SESSION['role'] != 'user') {
    header("Location: /library/403.php");
    exit;
}