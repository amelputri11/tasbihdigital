<?php
include 'config.php';

// Hapus semua session
session_unset();
session_destroy();

// Redirect ke halaman utama (index.php)
header("Location: index.php");
exit();
?>