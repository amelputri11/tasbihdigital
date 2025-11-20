<?php
include 'config.php';

try {
    echo "Memulai update database...<br>";
    
    // Update user_targets table - tanpa IF NOT EXISTS
    echo "Memperbarui tabel user_targets...<br>";
    
    // Cek dan tambah kolom is_active jika belum ada
    try {
        $pdo->exec("ALTER TABLE user_targets ADD COLUMN is_active BOOLEAN DEFAULT true");
        echo "✓ Kolom is_active ditambahkan<br>";
    } catch (PDOException $e) {
        echo "ℹ️ Kolom is_active sudah ada<br>";
    }
    
    // Cek dan tambah kolom updated_at jika belum ada
    try {
        $pdo->exec("ALTER TABLE user_targets ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
        echo "✓ Kolom updated_at ditambahkan<br>";
    } catch (PDOException $e) {
        echo "ℹ️ Kolom updated_at sudah ada<br>";
    }
    
    // Create user_reminders table if not exists (cara manual)
    echo "Membuat tabel user_reminders...<br>";
    $table_check = $pdo->query("SHOW TABLES LIKE 'user_reminders'")->fetch();
    if (!$table_check) {
        $sql = "CREATE TABLE user_reminders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            reminder_type ENUM('daily', 'prayer_time') NOT NULL,
            reminder_time TIME NULL,
            is_active BOOLEAN DEFAULT true,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )";
        $pdo->exec($sql);
        echo "✓ Tabel user_reminders berhasil dibuat<br>";
    } else {
        echo "ℹ️ Tabel user_reminders sudah ada<br>";
    }
    
    // Set default targets for existing users
    echo "Mengatur target default untuk user yang ada...<br>";
    
    // Untuk daily target
    $sql = "INSERT INTO user_targets (user_id, target_type, target_count) 
            SELECT id, 'daily', 100 FROM users
            WHERE NOT EXISTS (
                SELECT 1 FROM user_targets ut 
                WHERE ut.user_id = users.id AND ut.target_type = 'daily'
            )";
    $pdo->exec($sql);
    echo "✓ Target harian default berhasil diatur<br>";
    
    // Untuk monthly target  
    $sql = "INSERT INTO user_targets (user_id, target_type, target_count) 
            SELECT id, 'monthly', 3000 FROM users
            WHERE NOT EXISTS (
                SELECT 1 FROM user_targets ut 
                WHERE ut.user_id = users.id AND ut.target_type = 'monthly'
            )";
    $pdo->exec($sql);
    echo "✓ Target bulanan default berhasil diatur<br>";
    
    echo "<br>✅ Update database selesai!";
    echo "<br><a href='index.php'>Kembali ke Aplikasi</a>";
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
    echo "<br><a href='index.php'>Kembali ke Aplikasi</a>";
}
?>