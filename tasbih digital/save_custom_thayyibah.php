<?php
include 'config.php';

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Anda harus login terlebih dahulu.']);
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['custom_thayyibah'])) {
    $custom_thayyibah = trim($_POST['custom_thayyibah']);
    $user_id = $_SESSION['user_id'];
    
    // Validasi input
    if(empty($custom_thayyibah)) {
        echo json_encode(['success' => false, 'message' => 'Kalimat tidak boleh kosong.']);
        exit();
    }
    
    if(strlen($custom_thayyibah) > 100) {
        echo json_encode(['success' => false, 'message' => 'Kalimat terlalu panjang (maksimal 100 karakter).']);
        exit();
    }
    
    // Cek apakah custom thayyibah sudah ada di session
    if(!isset($_SESSION['custom_thayyibah'])) {
        $_SESSION['custom_thayyibah'] = [];
    }
    
    // Cek duplikat di session
    if(in_array($custom_thayyibah, $_SESSION['custom_thayyibah'])) {
        echo json_encode(['success' => false, 'message' => 'Kalimat ini sudah ada dalam daftar.']);
        exit();
    }
    
    // Cek juga duplikat dengan preset
    $preset_thayyibah = [
        'Subhanallah', 'Alhamdulillah', 'Allahuakbar', 'Astaghfirullah', 'Lailahaillallah'
    ];
    
    if(in_array($custom_thayyibah, $preset_thayyibah)) {
        echo json_encode(['success' => false, 'message' => 'Kalimat ini sudah termasuk dalam preset.']);
        exit();
    }
    
    try {
        // Simpan ke database
        $stmt = $pdo->prepare("INSERT INTO user_custom_thayyibah (user_id, thayyibah_text) VALUES (?, ?)");
        $stmt->execute([$user_id, $custom_thayyibah]);
        
        // Tambahkan ke session
        $_SESSION['custom_thayyibah'][] = $custom_thayyibah;
        
        echo json_encode(['success' => true, 'message' => 'Kalimat custom berhasil disimpan!']);
        
    } catch(PDOException $e) {
        // Jika error karena duplikat di database
        if($e->getCode() == 23000) {
            echo json_encode(['success' => false, 'message' => 'Kalimat ini sudah ada dalam daftar.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>