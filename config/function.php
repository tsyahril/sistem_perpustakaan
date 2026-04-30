<?php
session_start();

// cek login
if (!isset($_SESSION['login'])) {
    header("Location: ../auth/login.php");
    exit;
}
?>