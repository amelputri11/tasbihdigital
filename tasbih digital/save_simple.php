<?php
include 'config.php';

if(!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Harus login dulu';
    header('Location: login.php');
    exit();
}

if(isset($_POST['count'])) {
    $user_id = $_SESSION['user_id'];
    $count = intval($_POST['count']);
    
    if($count > 0) {
        try {
            $stmt = $pdo->prepare("SELECT id, count FROM tasbih_records WHERE user_id = ? AND date = CURDATE()");
            $stmt->execute([$user_id]);
            $existing = $stmt->fetch();
            
            if($existing) {
                $new_count = $existing['count'] + $count;
                $stmt = $pdo->prepare("UPDATE tasbih_records SET count = ? WHERE id = ?");
                $stmt->execute([$new_count, $existing['id']]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO tasbih_records (user_id, count, date) VALUES (?, ?, CURDATE())");
                $stmt->execute([$user_id, $count]);
            }
            
            $_SESSION['message'] = 'Berhasil disimpan!';
            header('Location: history.php');
            exit();
            
        } catch(Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }
    }
}

header('Location: index.php');
exit();
?>