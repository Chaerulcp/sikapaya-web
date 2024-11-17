<?php
session_start();
require_once 'config.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Cek token
    $stmt = $db->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
    $stmt ->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        if (isset($_POST['update_password'])) {
            $new_password = $_POST['new_password'];
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Update password dan reset token
            $stmt = $db->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
            $stmt->execute([$hashed_password, $user['id']]);

            $success = "Password berhasil diperbarui! Anda akan dialihkan ke halaman utama dalam 3 detik.";
            echo "<script>
                    setTimeout(function(){
                        window.location.href = 'index.php';
                    }, 3000);
                  </script>";
        }
    } else {
        $error = "Token tidak valid atau telah kedaluwarsa.";
    }
} else {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/bootstrap.css" rel="stylesheet">
    <title>Reset Password</title>
</head>
<body>
    <div class="container mt-5">
        <h2>Reset Password</h2>
        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <?php if(isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label for="new_password" class="form-label">Password Baru </label>
                <input type="password" class="form-control" id="new_password" name="new_password" required>
            </div>
            <button type="submit" name="update_password" class="btn btn-primary">Perbarui Password</button>
        </form>
    </div>
    <script src="js/bootstrap.js"></script>
</body>
</html>