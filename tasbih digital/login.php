<?php
include 'config.php';

if(isset($_SESSION['user_id'])) {
    // Redirect berdasarkan parameter
    $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index';
    
    if($redirect == 'save') {
        header("Location: index.php?from_login=true");
    } else {
        header("Location: $redirect.php");
    }
    exit();
}

$error = '';
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index';
$count = isset($_GET['count']) ? intval($_GET['count']) : 0;

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $redirect = $_POST['redirect'];
    $count = intval($_POST['count']);
    
    try {
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            // Redirect berdasarkan kebutuhan
            if($redirect == 'save' || $count > 0) {
                // Jika ingin menyimpan data, redirect ke index dengan parameter
                header("Location: index.php?from_login=true");
            } else {
                header("Location: $redirect.php");
            }
            exit();
        } else {
            $error = "Username atau password salah!";
        }
    } catch(PDOException $e) {
        $error = "Terjadi kesalahan: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Tasbih Digital</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="auth-form">
            <h1>Login Tasbih Digital</h1>
            
            <?php if($count > 0): ?>
                <div class="info-message">
                    Anda memiliki <?php echo $count; ?> jumlah tasbih yang menunggu untuk disimpan.
                </div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="hidden" name="redirect" value="<?php echo $redirect; ?>">
                <input type="hidden" name="count" value="<?php echo $count; ?>">
                
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            
            <p class="auth-link">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
        </div>
    </div>
</body>
</html>