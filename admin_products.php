<?php
// admin_products.php
include 'auth.php';
include 'config.php';

if (!is_logged_in() || !is_owner()) {
    die("Akses ditolak! Halaman ini khusus untuk Owner.");
}

$message = "";

if (isset($_POST['add_product'])) {
    $name        = trim($_POST['name']);
    $category    = $_POST['category'];
    $description = trim($_POST['description']);
    $price       = floatval($_POST['price']);
    $rating      = floatval($_POST['rating']);

    $image_name  = $_FILES['image']['name'];
    $image_tmp   = $_FILES['image']['tmp_name'];
    $image_ext   = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
    $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

    if (in_array($image_ext, $allowed_ext)) {
        $new_image_name = uniqid('kopipiro_', true) . '.' . $image_ext;
        $upload_dir     = 'uploads/';

        if (move_uploaded_file($image_tmp, $upload_dir . $new_image_name)) {
            $stmt = mysqli_prepare($conn, "INSERT INTO products (name, category, description, price, rating, image) VALUES (?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sssdss", $name, $category, $description, $price, $rating, $new_image_name);
            
            if (mysqli_stmt_execute($stmt)) {
                $message = "<div class='alert alert-success'>Produk berhasil ditambahkan!</div>";
            } else {
                $message = "<div class='alert alert-danger'>Gagal menyimpan ke database.</div>";
            }
        } else {
            $message = "<div class='alert alert-danger'>Gagal mengunggah gambar.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Format gambar harus JPG, JPEG, PNG, atau WEBP.</div>";
    }
}

$products = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - Owner Kopipiro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="owner_dashboard.php">☕ Kopipiro - Owner Panel</a>
        <div>
            <a href="owner_dashboard.php" class="btn btn-outline-light btn-sm">Dashboard & Laporan</a>
            <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <?= $message ?>
    <div class="row g-4">
        <!-- Form Tambah Produk -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">+ Tambah Produk Baru</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Nama Produk</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select class="form-select" name="category" required>
                                <option value="Coffee">Coffee</option>
                                <option value="Toast">Toast</option>
                                <option value="Non-Caffeine">Non-Caffeine</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="description" rows="2" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Harga (Rp)</label>
                            <input type="number" class="form-control" name="price" step="100" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rating (1.0 - 5.0)</label>
                            <input type="number" class="form-control" name="rating" step="0.1" min="1" max="5" value="5.0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Foto Produk</label>
                            <input type="file" class="form-control" name="image" accept="image/*" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="add_product" class="btn btn-success">Simpan Produk</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Daftar Produk -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Daftar Menu Kopipiro</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Foto</th>
                                    <th>Nama</th>
                                    <th>Kategori</th>
                                    <th>Harga</th>
                                    <th>Rating</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php $no = 1; while ($p = mysqli_fetch_assoc($products)): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><img src="uploads/<?= htmlspecialchars($p['image']) ?>" alt="Img" width="45" height="45" class="object-fit-cover rounded"></td>
                                    <td><?= htmlspecialchars($p['name']) ?></td>
                                    <td><span class="badge bg-secondary"><?= $p['category'] ?></span></td>
                                    <td>Rp <?= number_format($p['price'], 0, ',', '.') ?></td>
                                    <td>⭐ <?= $p['rating'] ?></td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>