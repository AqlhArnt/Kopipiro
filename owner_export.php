<?php
// owner_export.php
include 'auth.php';
include 'config.php';

if (!is_logged_in() || !is_owner()) {
    exit("Akses ditolak.");
}

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Penjualan_Kopipiro.xls");

$orders = mysqli_query($conn, "SELECT o.*, u.username AS cashier_name FROM orders o LEFT JOIN users u ON o.cashier_id = u.id ORDER BY o.created_at DESC");
?>
<table border="1">
    <tr>
        <th>ID Order</th>
        <th>Nama Pemesan</th>
        <th>Total Harga</th>
        <th>Status Diskon</th>
        <th>Status Pesanan</th>
        <th>Kasir Pengelola</th>
        <th>Waktu Pemesanan</th>
    </tr>
    <?php while ($o = mysqli_fetch_assoc($orders)): ?>
    <tr>
        <td><?= $o['id'] ?></td>
        <td><?= $o['customer_name'] ?></td>
        <td><?= $o['total_price'] ?></td>
        <td><?= $o['is_discounted'] ? 'Diskon 10%' : 'Normal' ?></td>
        <td><?= $o['status'] ?></td>
        <td><?= $o['cashier_name'] ?? '-' ?></td>
        <td><?= $o['created_at'] ?></td>
    </tr>
    <?php endwhile; ?>
</table>