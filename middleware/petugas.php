<?php
include 'auth.php';

if ($_SESSION['role'] != 'petugas') {
    header("Location: ../auth/login.php");
    exit;
}