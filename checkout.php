<?php
// checkout.php
include 'auth.php';
include 'config.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Hapus atau ubah jumlah item di keranjang
if (isset($_GET['action']) && $_GET['action'] == 'remove') {
    $id = $_GET['id'];
    unset($_SESSION['cart'][$id]);
    header("Location: checkout.php");
    exit;
}

$is_member = is_logged_in();
$message = "";

// Proses Checkout & Simpan Pesanan ke Database
if (isset($_POST['checkout'])) {
    $customer_name = trim($_POST['customer_name']);
    if (empty($customer_name)) {
        $customer_name = $is_member ? $_SESSION['username'] : 'Guest';
    }

    if (empty($_SESSION['cart'])) {
        $message = "<div class='alert alert-danger'>Keranjang belanja Anda masih kosong!</div>";
    } else {
        // Hitung total harga keseluruhan
        $total_price = 0;
        $cart_items = [];

        foreach ($_SESSION['cart'] as $pid => $qty) {
            $q = mysqli_query($conn, "SELECT * FROM products WHERE id = $pid");
            $prod = mysqli_fetch_assoc($q);
            if ($prod) {
                $price = $is_member ? $prod['price'] * 0.9 : $prod['price'];
                $subtotal = $price * $qty;
                $total_price += $subtotal;
                $cart_items[] = [
                    'product_id' => $pid,
                    'qty' => $qty,
                    'price' => $price
                ];
            }
        }

        $user_id = $is_member ? $_SESSION['user_id'] : NULL;
        $is_discounted = $is_member ? 1 : 0;
        $status = 'Pending';
        $cashier_id = NULL; // Belum diklaim kasir

        // Simpan ke tabel orders
        $stmt = mysqli_prepare($conn, "INSERT INTO orders (user_id, customer_name, total_price, is_discounted, status, cashier_id) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "isdisi", $user_id, $customer_name, $total_price, $is_discounted, $status, $cashier_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $order_id = mysqli_insert_id($conn);

            // Simpan detail item ke tabel order_items
            foreach ($cart_items as $item) {
                $stmt_item = mysqli_prepare($conn, "INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt_item, "iiid", $order_id, $item['product_id'], $item['qty'], $item['price']);
                mysqli_stmt_execute($stmt_item);
            }

            // Kosongkan keranjang
            $_SESSION['cart'] = [];
            $message = "<div class='alert alert-success'>Pesanan berhasil dibuat dengan ID Order: <b>#$order_id</b>! Silakan tunggu konfirmasi dari kasir. <a href='index.php'>Kembali ke Katalog</a></div>";
        } else {
            $message = "<div class='alert alert-danger'>Terjadi kesalahan saat memproses pesanan.</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Kopipiro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">☕ Kopipiro - Checkout</a>
        <a href="index.php" class="btn btn-outline-light btn-sm">Lanjut Belanja</a>
    </div>
</nav>

<div class="container my-4">
    <?= $message ?>
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Keranjang Belanja & Checkout</h5>
        </div>
        <div class="card-body">
            <?php if (empty($_SESSION['cart'])): ?>
                <div class="text-center py-5">
                    <p class="text-muted">Keranjang Anda kosong.</p>
                    <a href="index.php" class="btn btn-dark">Mulai Belanja</a>
                </div>
            <?php else: ?>
                <table class="table table-bordered align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Produk</th>
                            <th>Kategori</th>
                            <th>Harga Satuan</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                    $grand_total = 0;
                    foreach ($_SESSION['cart'] as $pid => $qty):
                        $q = mysqli_query($conn, "SELECT * FROM products WHERE id = $pid");
                        $p = mysqli_fetch_assoc($q);
                        if (!$p) continue;
                        $unit_price = $is_member ? $p['price'] * 0.9 : $p['price'];
                        $subtotal = $unit_price * $qty;
                        $grand_total += $subtotal;
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($p['name']) ?></td>
                            <td><span class="badge bg-secondary"><?= $p['category'] ?></span></td>
                            <td>Rp <?= number_format($unit_price, 0, ',', '.') ?></td>
                            <td><?= $qty ?></td>
                            <td>Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
                            <td><a href="checkout.php?action=remove&id=<?= $pid ?>" class="btn btn-danger btn-sm">Hapus</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="row justify-content-end mt-4">
                    <div class="col-md-5">
                        <div class="card bg-light p-3">
                            <h4>Total Pembayaran: <span class="text-success">Rp <?= number_format($grand_total, 0, ',', '.') ?></span></h4>
                            <?php if ($is_member): ?>
                                <small class="text-success fw-bold">✓ Diskon member 10% sudah diterapkan otomatis.</small>
                            <?php else: ?>
                                <small class="text-muted">Ingin diskon 10%? <a href="login.php">Login sebagai member</a>.</small>
                            <?php endif; ?>

                            <form method="POST" class="mt-3">
                                <div class="mb-3">
                                    <label class="form-label">Nama Pemesan</label>
                                    <input type="text" class="form-control" name="customer_name" value="<?= $is_member ? htmlspecialchars($_SESSION['username']) : '' ?>" placeholder="Masukkan nama Anda" required>
                                </div>
                                <button type="submit" name="checkout" class="btn btn-success w-100">Konfirmasi Pesanan</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>