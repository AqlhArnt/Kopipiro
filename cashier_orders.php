<?php
// cashier_orders.php
include 'auth.php';
include 'config.php';

if (!is_logged_in() || !is_cashier()) {
    die("Akses ditolak! Halaman ini khusus untuk Kasir.");
}

$cashier_id = $_SESSION['user_id'];
$message = "";

// Aksi Kasir mengambil alih atau memperbarui status order
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $status   = $_POST['status'];

    $stmt = mysqli_prepare($conn, "UPDATE orders SET status = ?, cashier_id = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "sii", $status, $cashier_id, $order_id);
    mysqli_stmt_execute($stmt);
    $message = "<div class='alert alert-success'>Status pesanan berhasil diperbarui!</div>";
}

// Ambil daftar order yang belum diklaim atau dikelola oleh kasir ini sendiri
$orders = mysqli_query($conn, "SELECT * FROM orders WHERE cashier_id IS NULL OR cashier_id = $cashier_id ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir Panel - Kopipiro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="cashier_orders.php">☕ Kopipiro - Kasir Panel</a>
        <div>
            <span class="text-white me-3">Kasir: <?= htmlspecialchars($_SESSION['username']) ?></span>
            <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container my-4">
    <?= $message ?>
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Kelola Pesanan Masuk</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Pemesan</th>
                            <th>Total Harga</th>
                            <th>Diskon</th>
                            <th>Status Saat Ini</th>
                            <th>Aksi / Ubah Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($o = mysqli_fetch_assoc($orders)): ?>
                        <tr>
                            <td>#<?= $o['id'] ?></td>
                            <td><?= htmlspecialchars($o['customer_name']) ?></td>
                            <td>Rp <?= number_format($o['total_price'], 0, ',', '.') ?></td>
                            <td><?= $o['is_discounted'] ? '<span class="badge bg-success">10% Off</span>' : '-' ?></td>
                            <td>
                                <span class="badge bg-<?= $o['status'] == 'Completed' ? 'success' : 'warning text-dark' ?>">
                                    <?= $o['status'] ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" class="d-flex gap-2">
                                    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                    <select name="status" class="form-select form-select-sm">
                                        <option value="Pending" <?= $o['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="Processing" <?= $o['status'] == 'Processing' ? 'selected' : '' ?>>Processing</option>
                                        <option value="Completed" <?= $o['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn btn-sm btn-dark">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>