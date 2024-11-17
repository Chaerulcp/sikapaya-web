<?php
session_start();
require_once 'config.php';

if(isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['nama'] = $user['nama'];
        
        if($user['role'] == 'admin') {
            header("Location: service.php");
        } else {
            header("Location: landing.php");
        }
    } else {
        $error = "Email atau password salah!";
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="css/bootstrap.css" rel="stylesheet">
    <title>Login Page</title>
</head>
<body>
    <div class="container mt-5">
        <div class="card">   
            <div class="card-body">
                <h4 class="card-title text-center">Login</h4>
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>   
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>   
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div id="passHelp" class="form-text">Password minimal 8 karakter.</div>
                    </div>   
                    <button type="submit" name="login" class="btn btn-primary">Login</button>
                    <a href="daftar.php" class="btn btn-warning">Daftar</a>
                    <button type="button" class="btn btn-link" onclick="location.href='lupa_password.php'">Lupa password</button>
                </form>
            </div>
        </div>
    </div>
    <script src="js/bootstrap.js"></script>
</body>
</html>