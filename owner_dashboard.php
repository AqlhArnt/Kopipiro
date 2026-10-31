<?php
// owner_dashboard.php
include 'auth.php';
include 'config.php';

if (!is_logged_in() || !is_owner()) {
    die("Akses ditolak! Halaman ini khusus untuk Owner.");
}

// Ambil semua data pesanan beserta kasir yang bertugas
$orders = mysqli_query($conn, "SELECT o.*, u.username AS cashier_name FROM orders o LEFT JOIN users u ON o.cashier_id = u.id ORDER BY o.created_at DESC");

// Total Pendapatan & Statistik Ringkas
$stats = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total_orders, SUM(total_price) as total_revenue FROM orders"));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Dashboard - Kopipiro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Chart.js untuk Grafik Penjualan -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="owner_dashboard.php">☕ Kopipiro - Owner Dashboard</a>
        <div>
            <a href="admin_products.php" class="btn btn-outline-warning btn-sm me-2">Kelola Produk</a>
            <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container my-4">
    <!-- Kartu Statistik -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body">
                    <h5>Total Pendapatan</h5>
                    <h3>Rp <?= number_format($stats['total_revenue'] ?? 0, 0, ',', '.') ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-success text-white shadow-sm">
                <div class="card-body">
                    <h5>Total Pesanan Masuk</h5>
                    <h3><?= $stats['total_orders'] ?> Transaksi</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafik Penjualan Sederhana -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">📈 Grafik Ringkasan Penjualan</h5>
        </div>
        <div class="card-body">
            <canvas id="salesChart" height="90"></canvas>
        </div>
    </div>

    <!-- Tabel Semua Order + Tombol Export Excel -->
    <div class="card shadow-sm">
        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Semua Riwayat Pesanan Pelanggan</h5>
            <a href="owner_export.php" class="btn btn-success btn-sm">Unduh Excel</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID Order</th>
                            <th>Pemesan</th>
                            <th>Total Bayar</th>
                            <th>Status Diskon</th>
                            <th>Status Pesanan</th>
                            <th>Kasir Pengelola</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($o = mysqli_fetch_assoc($orders)): ?>
                        <tr>
                            <td>#<?= $o['id'] ?></td>
                            <td><?= htmlspecialchars($o['customer_name']) ?></td>
                            <td>Rp <?= number_format($o['total_price'], 0, ',', '.') ?></td>
                            <td>
                                <?= $o['is_discounted'] ? '<span class="badge bg-success">Diskon 10% Member</span>' : '<span class="badge bg-secondary">Normal</span>' ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= $o['status'] == 'Completed' ? 'success' : ($o['status'] == 'Processing' ? 'warning text-dark' : 'secondary') ?>">
                                    <?= $o['status'] ?>
                                </span>
                            </td>
                            <td><?= $o['cashier_name'] ? htmlspecialchars($o['cashier_name']) : '<em class="text-muted">Belum diproses</em>' ?></td>
                            <td><?= $o['created_at'] ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // Contoh Inisialisasi Grafik Chart.js
    const ctx = document.getElementById('salesChart');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Pending', 'Processing', 'Completed'],
            datasets: [{
                label: 'Jumlah Status Pesanan',
                data: [
                    <?= mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE status='Pending'"))['c'] ?>,
                    <?= mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE status='Processing'"))['c'] ?>,
                    <?= mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE status='Completed'"))['c'] ?>
                ],
                backgroundColor: ['#ffc107', '#0dcaf0', '#198754']
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>