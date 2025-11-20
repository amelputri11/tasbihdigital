<?php
include 'config.php';

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Anda harus login terlebih dahulu.']);
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['custom_thayyibah'])) {
    $custom_thayyibah = trim($_POST['custom_thayyibah']);
    $user_id = $_SESSION['user_id'];
    
    try {
        // Hapus dari database jika ada
        $stmt = $pdo->prepare("DELETE FROM user_custom_thayyibah WHERE user_id = ? AND thayyibah_text = ?");
        $stmt->execute([$user_id, $custom_thayyibah]);
        
        // Hapus dari session
        if(isset($_SESSION['custom_thayyibah'])) {
            $_SESSION['custom_thayyibah'] = array_filter($_SESSION['custom_thayyibah'], function($item) use ($custom_thayyibah) {
                return $item !== $custom_thayyibah;
            });
            // Re-index array
            $_SESSION['custom_thayyibah'] = array_values($_SESSION['custom_thayyibah']);
        }
        
        echo json_encode(['success' => true, 'message' => 'Kalimat custom berhasil dihapus!']);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>