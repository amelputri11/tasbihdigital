<?php
include 'config.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

// Default values
$daily_target = 100;
$monthly_target = 3000;
$daily_reminder_time = '08:00';
$daily_reminder_enabled = false;
$sound_enabled = true;

try {
    // Ambil target - dengan error handling
    try {
        $stmt = $pdo->prepare("SELECT target_type, target_count FROM user_targets WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $targets = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        if(isset($targets['daily'])) $daily_target = $targets['daily'];
        if(isset($targets['monthly'])) $monthly_target = $targets['monthly'];
    } catch (PDOException $e) {
        // Jika error, gunakan default values
        error_log("Error getting targets: " . $e->getMessage());
    }
    
    // Ambil reminder settings - dengan error handling
    try {
        $stmt = $pdo->prepare("SELECT reminder_type, reminder_time, is_active FROM user_reminders WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $reminders = $stmt->fetchAll();
        
        foreach($reminders as $reminder) {
            if($reminder['reminder_type'] == 'daily') {
                $daily_reminder_time = $reminder['reminder_time'];
                $daily_reminder_enabled = (bool)$reminder['is_active'];
            }
        }
    } catch (PDOException $e) {
        // Jika error, gunakan default values
        error_log("Error getting reminders: " . $e->getMessage());
    }
    
} catch(PDOException $e) {
    $message = "Error: " . $e->getMessage();
}

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $daily_target = intval($_POST['daily_target']);
    $monthly_target = intval($_POST['monthly_target']);
    $daily_reminder_enabled = isset($_POST['daily_reminder_enabled']) ? 1 : 0;
    $daily_reminder_time = $_POST['daily_reminder_time'];
    $sound_enabled = isset($_POST['sound_enabled']);
    
    try {
        $pdo->beginTransaction();
        
        // Update targets - dengan error handling untuk kolom is_active
        try {
            // Coba dengan kolom is_active
            $stmt = $pdo->prepare("INSERT INTO user_targets (user_id, target_type, target_count, is_active) 
                                  VALUES (?, 'daily', ?, 1) 
                                  ON DUPLICATE KEY UPDATE target_count = VALUES(target_count)");
            $stmt->execute([$user_id, $daily_target]);
        } catch (PDOException $e) {
            // Fallback tanpa kolom is_active
            $stmt = $pdo->prepare("INSERT INTO user_targets (user_id, target_type, target_count) 
                                  VALUES (?, 'daily', ?) 
                                  ON DUPLICATE KEY UPDATE target_count = VALUES(target_count)");
            $stmt->execute([$user_id, $daily_target]);
        }
        
        try {
            $stmt = $pdo->prepare("INSERT INTO user_targets (user_id, target_type, target_count, is_active) 
                                  VALUES (?, 'monthly', ?, 1) 
                                  ON DUPLICATE KEY UPDATE target_count = VALUES(target_count)");
            $stmt->execute([$user_id, $monthly_target]);
        } catch (PDOException $e) {
            $stmt = $pdo->prepare("INSERT INTO user_targets (user_id, target_type, target_count) 
                                  VALUES (?, 'monthly', ?) 
                                  ON DUPLICATE KEY UPDATE target_count = VALUES(target_count)");
            $stmt->execute([$user_id, $monthly_target]);
        }
        
        // Update reminders - dengan error handling jika tabel belum ada
        try {
            $stmt = $pdo->prepare("INSERT INTO user_reminders (user_id, reminder_type, reminder_time, is_active) 
                                  VALUES (?, 'daily', ?, ?) 
                                  ON DUPLICATE KEY UPDATE reminder_time = VALUES(reminder_time), is_active = VALUES(is_active)");
            $stmt->execute([$user_id, $daily_reminder_time, $daily_reminder_enabled]);
        } catch (PDOException $e) {
            // Jika tabel reminders belum ada, buat dulu
            $pdo->exec("CREATE TABLE IF NOT EXISTS user_reminders (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT,
                reminder_type ENUM('daily', 'prayer_time') NOT NULL,
                reminder_time TIME NULL,
                is_active BOOLEAN DEFAULT true,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )");
            
            // Coba lagi
            $stmt = $pdo->prepare("INSERT INTO user_reminders (user_id, reminder_type, reminder_time, is_active) 
                                  VALUES (?, 'daily', ?, ?)");
            $stmt->execute([$user_id, $daily_reminder_time, $daily_reminder_enabled]);
        }
        
        $pdo->commit();
        
        // Update session untuk sound preference
        $_SESSION['sound_enabled'] = $sound_enabled;
        
        $message = "Pengaturan berhasil disimpan!";
        
    } catch(PDOException $e) {
        $pdo->rollBack();
        $message = "Error: " . $e->getMessage();
    }
}

// Ambil sound preference dari session
$sound_enabled = isset($_SESSION['sound_enabled']) ? $_SESSION['sound_enabled'] : true;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - Tasbih Digital</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Pengaturan</h1>
            <a href="index.php" class="btn-back">Kembali</a>
            <a href="logout.php" class="btn-logout">Logout</a>
        </header>
        
        <?php if($message): ?>
            <div class="message <?php echo strpos($message, 'Error') !== false ? 'error' : 'success'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="settings-container">
            <form method="POST" class="settings-form">
                
                <!-- Section Target -->
                <div class="settings-section">
                    <h3>🎯 Target Harian & Bulanan</h3>
                    
                    <div class="form-group">
                        <label for="daily_target">Target Harian:</label>
                        <input type="number" id="daily_target" name="daily_target" 
                               value="<?php echo $daily_target; ?>" min="1" max="10000" required>
                        <small>Jumlah target wirid per hari</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="monthly_target">Target Bulanan:</label>
                        <input type="number" id="monthly_target" name="monthly_target" 
                               value="<?php echo $monthly_target; ?>" min="1" max="100000" required>
                        <small>Jumlah target wirid per bulan</small>
                    </div>
                </div>
                
                <!-- Section Reminder -->
                <div class="settings-section">
                    <h3>⏰ Pengingat Harian</h3>
                    
                    <div class="form-group checkbox-group">
                        <label>
                            <input type="checkbox" name="daily_reminder_enabled" value="1" 
                                   <?php echo $daily_reminder_enabled ? 'checked' : ''; ?>>
                            Aktifkan Pengingat Harian
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label for="daily_reminder_time">Waktu Pengingat:</label>
                        <input type="time" id="daily_reminder_time" name="daily_reminder_time" 
                               value="<?php echo $daily_reminder_time; ?>" 
                               <?php echo !$daily_reminder_enabled ? 'disabled' : ''; ?>>
                    </div>
                </div>
                
                <!-- Section Sound -->
                <div class="settings-section">
                    <h3>🔊 Sound Effects</h3>
                    
                    <div class="form-group checkbox-group">
                        <label>
                            <input type="checkbox" name="sound_enabled" value="1"
                                   <?php echo $sound_enabled ? 'checked' : ''; ?>>
                            Aktifkan Sound Effects
                        </label>
                        <small>Suara ketika tap dan aksi lainnya</small>
                    </div>
                    
                    <div class="sound-test">
                        <button type="button" id="testTapSound" class="btn-test">Test Tap Sound</button>
                        <button type="button" id="testCompleteSound" class="btn-test">Test Complete Sound</button>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
            </form>
        </div>
    </div>

    <script>
        // Enable/disable time input based on checkbox
        const reminderCheckbox = document.querySelector('input[name="daily_reminder_enabled"]');
        const timeInput = document.getElementById('daily_reminder_time');
        
        if (reminderCheckbox && timeInput) {
            reminderCheckbox.addEventListener('change', function() {
                timeInput.disabled = !this.checked;
            });
        }
        
        // Sound test functions
        document.getElementById('testTapSound')?.addEventListener('click', function() {
            playTapSound();
        });
        
        document.getElementById('testCompleteSound')?.addEventListener('click', function() {
            playCompleteSound();
        });
        
        function playTapSound() {
            // Simple beep sound menggunakan Web Audio API
            try {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();
                
                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);
                
                oscillator.frequency.value = 800;
                oscillator.type = 'sine';
                
                gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1);
                
                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.1);
            } catch (e) {
                console.log('Sound not supported:', e);
            }
        }
        
        function playCompleteSound() {
            try {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();
                
                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);
                
                oscillator.frequency.setValueAtTime(523.25, audioContext.currentTime);
                oscillator.frequency.setValueAtTime(659.25, audioContext.currentTime + 0.1);
                oscillator.frequency.setValueAtTime(783.99, audioContext.currentTime + 0.2);
                oscillator.type = 'sine';
                
                gainNode.gain.setValueAtTime(0.5, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
                
                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.3);
            } catch (e) {
                console.log('Sound not supported:', e);
            }
        }
    </script>
</body>
</html>