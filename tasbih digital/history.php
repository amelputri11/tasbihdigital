<?php
include 'config.php';

// Cek apakah user sudah login
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=history");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

// Daftar kalimat thayyibah preset untuk tampilan
$preset_thayyibah = [
    'Subhanallah' => 'سُبْحَانَ اللَّهِ',
    'Alhamdulillah' => 'الْحَمْدُ لِلَّهِ',
    'Allahuakbar' => 'اللَّهُ أَكْبَرُ',
    'Astaghfirullah' => 'أَسْتَغْفِرُ اللَّهَ',
    'Lailahaillallah' => 'لَا إِلَهَ إِلَّا اللَّهُ'
];

// Pindahkan bagian complete ke sini (SETELAH $user_id didefinisikan)
if(isset($_GET['complete'])) {
    $session_id = $_GET['complete'];
    
    try {
        // Update session dengan end_time (selesaikan)
        $stmt = $pdo->prepare("UPDATE tasbih_records SET end_time = NOW(), updated_at = NOW() WHERE id = ? AND user_id = ?");
        $stmt->execute([$session_id, $user_id]);
        $message = "Sesi berhasil diselesaikan!";
    } catch(PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// Tangani aksi lanjutkan sesi
if(isset($_GET['continue'])) {
    $session_id = $_GET['continue'];
    
    try {
        // Update end_time untuk session yang dilanjutkan
        $stmt = $pdo->prepare("UPDATE tasbih_records SET end_time = NOW() WHERE id = ? AND user_id = ?");
        $stmt->execute([$session_id, $user_id]);
        $message = "Sesi sebelumnya telah diselesaikan. Silakan mulai sesi baru di halaman utama.";
    } catch(PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// Tangani aksi hapus
if(isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $record_id = $_GET['id'];
    
    try {
        if($action == 'delete') {
            $stmt = $pdo->prepare("DELETE FROM tasbih_records WHERE id = ? AND user_id = ?");
            $stmt->execute([$record_id, $user_id]);
            $message = "Record berhasil dihapus!";
        }
    } catch(PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// Ambil riwayat tasbih (yang sudah selesai)
try {
    $stmt = $pdo->prepare("SELECT id, count, date, start_time, end_time, thayyibah_text FROM tasbih_records WHERE user_id = ? AND end_time IS NOT NULL ORDER BY end_time DESC");
    $stmt->execute([$user_id]);
    $records = $stmt->fetchAll();
    
    // Ambil sesi yang masih aktif (belum selesai)
    $active_stmt = $pdo->prepare("SELECT id, count, date, start_time, thayyibah_text FROM tasbih_records WHERE user_id = ? AND end_time IS NULL ORDER BY start_time DESC");
    $active_stmt->execute([$user_id]);
    $active_sessions = $active_stmt->fetchAll();
    
    // Hitung total semua tasbih
    $total_stmt = $pdo->prepare("SELECT SUM(count) as total FROM tasbih_records WHERE user_id = ?");
    $total_stmt->execute([$user_id]);
    $total_result = $total_stmt->fetch();
    $total_count = $total_result['total'] ?: 0;
    
} catch(PDOException $e) {
    $records = [];
    $active_sessions = [];
    $total_count = 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat - Tasbih Digital</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
    <header class="header-fixed">
    <a href="index.php" class="btn-back">kembali</a>

    <div class="title-group">
        <h1><br>Riwayat Tasbih</h1>
       
    </div>

    <a href="logout.php" class="btn-logout">Logout</a>
</header>


    <?php if($message): ?>
        <div class="message <?php echo strpos($message, 'Error') !== false ? 'error' : 'success'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

        
        <div class="history-container">
            <!-- Sesi Aktif (Belum Selesai) -->
            <?php if(count($active_sessions) > 0): ?>
                <div class="active-sessions">
                    <h3> Sesi yang Belum Selesai</h3>
                    <?php foreach($active_sessions as $session): ?>
                        <div class="session-item active">
                            <div class="session-info">
                                <div class="session-time">
                                    Mulai: <?php echo date('d M Y H:i', strtotime($session['start_time'])); ?>
                                </div>
                                <div class="session-count"><?php echo $session['count']; ?> kali</div>
                                <div class="session-thayyibah">
                                    Kalimat: <?php echo htmlspecialchars($session['thayyibah_text'] ?? 'Subhanallah'); ?>
                                </div>
                            </div>
                            <div class="session-actions">
                                <a href="index.php?continue_session=<?php echo $session['id']; ?>" class="btn-continue">Lanjutkan di Halaman Utama</a>
                                <a href="history.php?complete=<?php echo $session['id']; ?>" class="btn-complete" onclick="return confirm('Selesaikan sesi ini? Data akan disimpan.')">
                                    Selesaikan
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- Riwayat Selesai -->
            <div class="completed-sessions">
                <h3> Riwayat Selesai</h3>
                <?php if(count($records) > 0): ?>
                    <div class="history-list">
                        <?php foreach($records as $record): ?>
                            <div class="history-item">
                                <div class="history-info">
                                    <div class="history-time">
                                        <?php 
                                            $start = date('H:i', strtotime($record['start_time']));
                                            $end = date('H:i', strtotime($record['end_time']));
                                            $date = date('d M Y', strtotime($record['date']));
                                            echo "$date | $start - $end";
                                        ?>
                                    </div>
                                    <div class="history-count"><?php echo $record['count']; ?> kali</div>
                                    <div class="history-thayyibah">
                                        Kalimat: <?php echo htmlspecialchars($record['thayyibah_text'] ?? 'Subhanallah'); ?>
                                    </div>
                                    <div class="history-duration">
                                        Durasi: <?php
                                            $start_time = new DateTime($record['start_time']);
                                            $end_time = new DateTime($record['end_time']);
                                            $duration = $start_time->diff($end_time);
                                            if($duration->h > 0) {
                                                echo $duration->format('%h jam %i menit');
                                            } else {
                                                echo $duration->format('%i menit');
                                            }
                                        ?>
                                    </div>
                                </div>
                                <div class="history-actions">
                                    <button class="btn-delete" onclick="deleteRecord(<?php echo $record['id']; ?>)">
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <p>Belum ada riwayat tasbih yang selesai.</p>
                        <a href="index.php" class="btn btn-primary">Mulai Menghitung</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function deleteRecord(id) {
            if(confirm('Apakah Anda yakin ingin menghapus record ini?')) {
                window.location.href = 'history.php?action=delete&id=' + id;
            }
        }
    </script>
</body>
</html>