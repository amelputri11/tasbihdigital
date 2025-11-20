<?php
include 'config.php';

// Cek apakah user sudah login
if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Anda harus login terlebih dahulu.']);
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['count'])) {
    $user_id = $_SESSION['user_id'];
    $count = intval($_POST['count']);
    $action = isset($_POST['action']) ? $_POST['action'] : 'complete';
    $thayyibah = isset($_POST['thayyibah']) ? $_POST['thayyibah'] : 'Subhanallah';
    $session_id = isset($_POST['session_id']) ? intval($_POST['session_id']) : null;
    
    // Validasi thayyibah
    if(empty(trim($thayyibah))) {
        $thayyibah = 'Subhanallah';
    }
    
    if($count <= 0) {
        echo json_encode(['success' => false, 'message' => 'Jumlah tidak valid.']);
        exit();
    }
    
    try {
        if($action === 'pause') {
            // Jika ada session_id, update session yang ada
            if($session_id) {
                $stmt = $pdo->prepare("UPDATE tasbih_records SET count = ?, thayyibah_text = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
                $stmt->execute([$count, $thayyibah, $session_id, $user_id]);
            } else {
                // Buat session baru yang dijeda (tanpa end_time)
                $stmt = $pdo->prepare("INSERT INTO tasbih_records (user_id, count, date, start_time, thayyibah_text) VALUES (?, ?, CURDATE(), NOW(), ?)");
                $stmt->execute([$user_id, $count, $thayyibah]);
            }
            
        } else {
            // Action: complete (simpan & selesai)
            if($session_id) {
                // Update session yang ada dengan end_time
                $stmt = $pdo->prepare("UPDATE tasbih_records SET count = ?, thayyibah_text = ?, end_time = NOW(), updated_at = NOW() WHERE id = ? AND user_id = ?");
                $stmt->execute([$count, $thayyibah, $session_id, $user_id]);
            } else {
                // Buat session baru yang selesai
                $stmt = $pdo->prepare("INSERT INTO tasbih_records (user_id, count, date, start_time, end_time, thayyibah_text) VALUES (?, ?, CURDATE(), NOW(), NOW(), ?)");
                $stmt->execute([$user_id, $count, $thayyibah]);
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'Data berhasil disimpan!']);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>