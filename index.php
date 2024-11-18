<?php
session_start(); // Memulai sesi
require_once 'config.php';

if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-success text-center">' . $_SESSION['message'] . '</div>';
    unset($_SESSION['message']);
}

session_start(); // Memulai sesi
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    // Jika pengguna sudah login, redirect ke halaman sesuai role
    if ($_SESSION['role'] == 'admin') {
        header("Location: service.php");
        exit();
    } else {
        header("Location: landing.php");
        exit();
    }
}

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        if ($user['is_active'] == 0) {
            $error = "Akun Anda belum diaktifkan. Silakan periksa email Anda untuk konfirmasi.";
        } else {
            // Regenerasi ID sesi untuk keamanan
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['nama'] = $user['nama'];

            // Redirect ke halaman sesuai role
            if ($user['role'] == 'admin') {
                header("Location: service.php");
            } else {
                header("Location: landing.php");
            }
            exit();
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
    <title>Login - Service Request Portal</title>
    <style>
        body {
            background: url('Assets/login.png') no-repeat center center fixed;
            background-size: cover;
        }
        .card {
            margin-top: 50px;
            border: none;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .card-title {
            font-weight: bold;
            color: #007bff;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .btn-warning {
            background-color: #ffc107;
            border: none;
        }
        .btn-warning:hover {
            background-color: #e0a800;
        }
        .btn-link {
            color: #007bff;
        }
        .btn-link:hover {
            color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">   
                    <div class="card-body">
                        <h4 class="card-title text-center">Selamat datang di Portal Layanan sikapayya</h4>
                        <p class="text-center text-muted">Silakan login untuk melanjutkan</p>
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
                            <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
                            <a href="daftar.php" class="btn btn-warning w-100 mt-2">Daftar</a>
                            <button type="button" class="btn btn-link w-100 mt-2" onclick="location.href='lupa_password.php'">Lupa password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="js/bootstrap.js"></script>
</body>
</html>