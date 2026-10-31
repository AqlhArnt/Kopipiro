<?php
// index.php
include 'auth.php';
include 'config.php';

// Inisialisasi Keranjang Belanja di Session
if (!isset($_SESSION['cart'])) {$_SESSION['cart'] = [];
}

// Tambah produk ke keranjang
if (isset($_POST['add_to_cart'])) {
    $product_id = intval($_POST['product_id']);
    $qty = intval($_POST['qty']);

    if (isset($_SESSION['cart'][$product_id])) {$_SESSION['cart'][$product_id] +=$qty;
    } else {
        $_SESSION['cart'][$product_id] =$qty;
    }
    header("Location: index.php?status=added");
    exit;
}

// Filter Kategori
$category_filter = isset($_GET['category']) ?$_GET['category'] : '';
if ($category_filter) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE category = ? ORDER BY id DESC");
    mysqli_stmt_bind_param($stmt, "s", $category_filter);
    mysqli_stmt_execute($stmt);
    $products = mysqli_stmt_get_result($stmt);
} else {
    $products = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");
}

$is_member = is_logged_in();
$cart_count = array_sum($_SESSION['cart']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Kopipiro - Nikmati Kopi Terbaik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Navbar Pelanggan -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">☕ Kopipiro</a>
        <div class="d-flex align-items-center gap-2">
            <a href="checkout.php" class="btn btn-outline-warning btn-sm position-relative">
                🛒 Keranjang
                <?php if ($cart_count > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?= $cart_count ?>
                    </span>
                <?php endif; ?>
            </a>
            <?php if (is_logged_in()): ?>
                <span class="text-white small ms-2">Halo, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></span>
                <?php if (is_owner()): ?>
                    <a href="owner_dashboard.php" class="btn btn-outline-light btn-sm">Owner Panel</a>
                <?php elseif (is_cashier()): ?>
                    <a href="cashier_orders.php" class="btn btn-outline-light btn-sm">Kasir Panel</a>
                <?php endif; ?>
                <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-outline-light btn-sm">Login</a>
                <a href="register.php" class="btn btn-success btn-sm">Register (Diskon 10%)</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container my-4">
    <!-- Banner Diskon Member -->
    <?php if (!$is_member): ?>
        <div class="alert alert-info text-center shadow-sm">
            🎉 <strong>Promo Spesial Member:</strong> <a href="register.php" class="alert-link">Daftar dan Login sekarang</a> untuk otomatis mendapatkan **diskon 10%** di halaman checkout!
        </div>
    <?php else: ?>
        <div class="alert alert-success text-center shadow-sm">
            ✨ Selamat datang, Member Setia! Harga spesial **Diskon 10% aktif** untuk setiap pemesanan Anda.
        </div>
    <?php endif; ?>

    <!-- Filter Kategori Produk -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2>Eksplorasi Menu Kopipiro</h2>
        <div class="btn-group">
            <a href="index.php" class="btn btn-outline-dark <?= $category_filter == '' ? 'active' : '' ?>">Semua</a>
            <a href="index.php?category=Coffee" class="btn btn-outline-dark <?= $category_filter == 'Coffee' ? 'active' : '' ?>">Coffee</a>
            <a href="index.php?category=Toast" class="btn btn-outline-dark <?= $category_filter == 'Toast' ? 'active' : '' ?>">Toast</a>
            <a href="index.php?category=Non-Caffeine" class="btn btn-outline-dark <?= $category_filter == 'Non-Caffeine' ? 'active' : '' ?>">Non-Caffeine</a>
            <a href="index.php?category=Other" class="btn btn-outline-dark <?= $category_filter == 'Other' ? 'active' : '' ?>">Other</a>
        </div>
    </div>

    <!-- Grid Produk -->
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php while ($row = mysqli_fetch_assoc($products)): ?>
            <?php
                $normal_price =$row['price'];
                $display_price =$is_member ? $normal_price * 0.9 :$normal_price;
            ?>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <img src="uploads/<?= htmlspecialchars($row['image']) ?>" class="card-img-top object-fit-cover" style="height: 200px;" alt="Product">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-secondary"><?= $row['category'] ?></span>
                            <small class="text-warning fw-bold">⭐ <?= $row['rating'] ?></small>
                        </div>
                        <h5 class="card-title"><?= htmlspecialchars($row['name']) ?></h5>
                        <p class="card-text text-muted small"><?= htmlspecialchars($row['description']) ?></p>
                        
                        <div class="mt-auto">
                            <?php if ($is_member): ?>
                                <div class="text-muted text-decoration-line-through small">Rp <?= number_format($normal_price, 0, ',', '.') ?></div>
                                <div class="text-success fw-bold fs-5">Rp <?= number_format($display_price, 0, ',', '.') ?> <span class="badge bg-danger fs-6">10% Off</span></div>
                            <?php else: ?>
                                <div class="fw-bold fs-5 text-dark">Rp <?= number_format($normal_price, 0, ',', '.') ?></div>
                            <?php endif; ?>

                            <form method="POST" class="mt-3 d-flex gap-2">
                                <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                                <input type="number" name="qty" value="1" min="1" class="form-control form-control-sm text-center" style="width: 70px;">
                                <button type="submit" name="add_to_cart" class="btn btn-dark btn-sm w-100">+ Masukkan Keranjang</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>