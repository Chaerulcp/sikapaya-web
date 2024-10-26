<?php
require_once 'config.php';

if(isset($_POST['daftar'])) {
    $nama = $_POST['nama'];
    $alamat = $_POST['alamat'];
    $nohp = $_POST['nohp'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    
    try {
        $stmt = $db->prepare("INSERT INTO users (nama, alamat, no_hp, email, password, role) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nama, $alamat, $nohp, $email, $password, $role]);
        header("Location: index.php?status=success");
    } catch(PDOException $e) {
        $error = "Pendaftaran gagal: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/bootstrap.css" rel="stylesheet">
    <title>Daftar</title>
</head>
<body>
    <div class="container" style="padding-top: 50px;">
        <h2>Daftarkan Akun Anda</h2>
        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Nama</label>
                <input type="text" class="form-control" name="nama" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Alamat</label>
                <input type="text" class="form-control" name="alamat" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Nomor HP</label>
                <input type="text" class="form-control" name="nohp" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="role" value="user" checked>
                <label class="form-check-label">User</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="role" value="admin">
                <label class="form-check-label">Admin</label>
            </div>
            <button type="submit" name="daftar" class="btn btn-primary">Daftar</button>
            <a href="index.php" class="btn btn-danger">Kembali</a>
        </form>
    </div>
    <script src="js/bootstrap.js"></script>
</body>
</html>