<?php
include 'auth.php';


if ($_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
