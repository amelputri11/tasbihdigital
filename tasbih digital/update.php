<?php
include 'config.php';

// Cek apakah user sudah login
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['record_id']) && isset($_POST['count'])) {
    $user_id = $_SESSION['user_id'];
    $record_id = intval($_POST['record_id']);
    $count = intval($_POST['count']);
    
    if($count <= 0) {
        $_SESSION['error'] = "Jumlah tidak valid.";
        header("Location: history.php");
        exit();
    }
    
    try {
        // Update record
        $stmt = $pdo->prepare("UPDATE tasbih_records SET count = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$count, $record_id, $user_id]);
        
        $_SESSION['message'] = "Record berhasil diupdate!";
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}

header("Location: history.php");
exit();
?>