<?php
require_once 'db_functions.php';

// Ambil semua data service yang masih menunggu konfirmasi
$pendingServices = getRecords('services', ['status' => 'Menunggu Konfirmasi'], 'date DESC');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && isset($_POST['service_id'])) {
        $serviceId = $_POST['service_id'];
        $action = $_POST['action'];
        
        if ($action === 'terima') {
            updateRecord('services', ['status' => 'Diterima'], ['id' => $serviceId]);
        } elseif ($action === 'tolak') {
            updateRecord('services', ['status' => 'Ditolak'], ['id' => $serviceId]);
        }
        
        // Refresh halaman setelah update
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/bootstrap.css" rel="stylesheet">
    <title>Daftar Service</title>
</head>
<body>
    <style>
        .btn-close {
          background-color: #ff0000;
          color: #fff;
          padding: 10px 20px;
          font-size: 16px;
          cursor: pointer;
        }
      
        .btn-close:hover {
          background-color: #cc0000;
        }
      
        .container {
          position: relative;
        }
      
        .btn-close {
          position: absolute;
          top: 10px;
          right: 10px;
        }
    </style>
      
    <div class="container">
        <a href="service.html" class="btn-close btn-lg" aria-label="Close"></a>
    </div>
    <div class="container m-5">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Alamat</th>
                    <th>Tanggal</th>
                    <th>Nomor HP</th>
                    <th>Merek</th>
                    <th>Jenis Alat</th>
                    <th>Kerusakan</th>
                    <th>Metode Pembayaran</th>
                    <th>Catatan</th>
                    <th>Status Penerimaan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendingServices as $index => $service): ?>
                <tr>
                    <th><?php echo $index + 1; ?>.</th>
                    <td><?php echo htmlspecialchars($service['name']); ?></td>
                    <td><?php echo htmlspecialchars($service['address']); ?></td>
                    <td><?php echo htmlspecialchars($service['date']); ?></td>
                    <td><?php echo htmlspecialchars($service['phone_number']); ?></td>
                    <td><?php echo htmlspecialchars($service['brand']); ?></td>
                    <td><?php echo htmlspecialchars($service['device_type']); ?></td>
                    <td><?php echo htmlspecialchars($service['issue']); ?></td>
                    <td><?php echo htmlspecialchars($service['payment_method']); ?></td>
                    <td><?php echo htmlspecialchars($service['notes']); ?></td>
                    <td>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                            <button type="submit" name="action" value="terima" class="btn btn-primary">Terima</button>
                            <button type="submit" name="action" value="tolak" class="btn btn-outline-danger">Tolak</button>
                        </form>
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