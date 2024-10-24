<?php
require_once 'db_functions.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_service'])) {
    $serviceId = $_POST['service_id'];
    updateRecord('services', ['status' => 'Dibatalkan'], ['id' => $serviceId, 'user_id' => $_SESSION['user_id']]);
}

$services = getRecords('services', ['user_id' => $_SESSION['user_id']], 'date DESC');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- ... (head content) ... -->
</head>
<body>
    <!-- ... (navigation and other content) ... -->
    <div class="container m-5">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Merek</th>
                    <th>Jenis Alat</th>
                    <th>Kerusakan</th>
                    <th>Metode Pembayaran</th>
                    <th>Catatan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $index => $service): ?>
                <tr>
                    <th><?php echo $index + 1; ?>.</th>
                    <td><?php echo htmlspecialchars($service['date']); ?></td>
                    <td><?php echo htmlspecialchars($service['brand']); ?></td>
                    <td><?php echo htmlspecialchars($service['device_type']); ?></td>
                    <td><?php echo htmlspecialchars($service['issue']); ?></td>
                    <td><?php echo htmlspecialchars($service['payment_method']); ?></td>
                    <td><?php echo htmlspecialchars($service['notes']); ?></td>
                    <td><?php echo htmlspecialchars($service['status']); ?></td>
                    <td>
                        <?php if ($service['status'] == 'Menunggu Konfirmasi'): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                <button type="submit" name="cancel_service" value="1" class="btn btn-outline-danger">Batalkan</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="js/bootstrap.js"></script>
    <script src="js/popper.min.js"></script>
</body>
</html>