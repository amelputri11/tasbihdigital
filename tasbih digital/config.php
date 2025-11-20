<?php
session_start();

$host = 'localhost';
$dbname = 'tasbih_digital';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Koneksi database gagal: " . $e->getMessage();
    exit();
}

// Load custom thayyibah dari database jika user login
if(isset($_SESSION['user_id']) && !isset($_SESSION['custom_thayyibah'])) {
    $user_id = $_SESSION['user_id'];
    try {
        $stmt = $pdo->prepare("SELECT thayyibah_text FROM user_custom_thayyibah WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
        $custom_thayyibah_db = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $_SESSION['custom_thayyibah'] = $custom_thayyibah_db;
    } catch(PDOException $e) {
        $_SESSION['custom_thayyibah'] = [];
    }
}
?>