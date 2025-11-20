<?php
include 'config.php';

// Script ini bisa dijadikan cron job untuk mengirim email reminder
// Contoh: */5 * * * * /usr/bin/php /path/to/reminder_cron.php

try {
    // Cari user yang memiliki reminder aktif
    $stmt = $pdo->prepare("
        SELECT u.id, u.username, ur.reminder_time, ut.target_count 
        FROM users u 
        JOIN user_reminders ur ON u.id = ur.user_id 
        LEFT JOIN user_targets ut ON u.id = ut.user_id AND ut.target_type = 'daily'
        WHERE ur.reminder_type = 'daily' AND ur.is_active = true
    ");
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    $current_time = date('H:i');
    
    foreach($users as $user) {
        if($user['reminder_time'] == $current_time) {
            // Cek progress hari ini
            $progress_stmt = $pdo->prepare("
                SELECT SUM(count) as today_count 
                FROM tasbih_records 
                WHERE user_id = ? AND date = CURDATE()
            ");
            $progress_stmt->execute([$user['id']]);
            $progress = $progress_stmt->fetch();
            $today_count = $progress['today_count'] ?: 0;
            
            $target = $user['target_count'] ?: 0;
            
            if($today_count < $target) {
                // Di sini bisa dikirim email atau notifikasi push
                // Untuk sekarang kita log saja
                error_log("Reminder untuk user {$user['username']} - Progress: {$today_count}/{$target}");
            }
        }
    }
    
} catch(PDOException $e) {
    error_log("Reminder cron error: " . $e->getMessage());
}
?>