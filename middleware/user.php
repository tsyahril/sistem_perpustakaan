<?php
include 'auth.php';

if ($_SESSION['role'] != 'user') {
    header("Location: ../auth/login.php");
    exit;
}