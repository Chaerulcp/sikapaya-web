<?php
require_once 'db_functions.php';

session_start();

// Fungsi untuk mencatat percobaan login gagal
function recordFailedLogin($email) {
    $failedLogins = getRecords('failed_logins', ['email' => $email], '');
    if (empty($failedLogins)) {
        insertRecord('failed_logins', ['email' => $email, 'attempts' => 1, 'last_attempt' => date('Y-m-d H:i:s')]);
    } else {
        updateRecord('failed_logins', 
            ['attempts' => $failedLogins[0]['attempts'] + 1, 'last_attempt' => date('Y-m-d H:i:s')], 
            ['email' => $email]
        );
    }
}

// Fungsi untuk memeriksa apakah akun terkunci
function isAccountLocked($email) {
    $failedLogins = getRecords('failed_logins', ['email' => $email], '');
    if (!empty($failedLogins) && $failedLogins[0]['attempts'] >= 5) {
        $lastAttempt = new DateTime($failedLogins[0]['last_attempt']);
        $now = new DateTime();
        $diff = $now->diff($lastAttempt);
        
        if ($diff->i < 15) { // Jika kurang dari 15 menit sejak percobaan terakhir
            return true;
        } else {
            // Reset percobaan login jika sudah lebih dari 15 menit
            updateRecord('failed_logins', ['attempts' => 0], ['email' => $email]);
        }
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (isAccountLocked($email)) {
        $error = "Akun Anda terkunci. Silakan coba lagi setelah 15 menit.";
    } else {
        $user = getRecords('users', ['email' => $email], '');

        if ($user && count($user) > 0) {
            if (password_verify($password, $user[0]['password'])) {
                $_SESSION['user_id'] = $user[0]['id'];
                $_SESSION['user_role'] = $user[0]['role'];

                // Reset percobaan login yang gagal
                updateRecord('failed_logins', ['attempts' => 0], ['email' => $email]);

                if ($user[0]['role'] == 'user') {
                    header("Location: landing.php");
                } else {
                    header("Location: service.php");
                }
                exit();
            }
        }
        
        recordFailedLogin($email);
        $error = "Email atau password salah";
    }
}
?>

<!DOCTYPE html>
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
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>   
                        <input type="email" class="form-control" id="email" name="email" aria-describedby="emailHelp" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Login</button>
                        <a href="daftar.php" class="btn btn-secondary">Daftar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="js/bootstrap.js"></script>
    <script src="js/popper.min.js"></script>
</body>
</html>