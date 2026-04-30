<?php
include 'auth.php';

if ($_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}