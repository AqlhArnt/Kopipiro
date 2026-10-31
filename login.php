<?php
// login.php
session_start();
include 'config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = "";

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = mysqli_prepare($conn, "SELECT id, username, password, role FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role'];

        // Pengarahan halaman berdasarkan role
        if ($user['role'] === 'owner') {
            header("Location: owner_dashboard.php");
        } elseif ($user['role'] === 'kasir') {
            header("Location: cashier_orders.php");
        } else {
            header("Location: index.php");
        }
        exit;
    } else {
        $error = "Username atau password salah.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Kopipiro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container">
    <div class="row justify-content-center" style="margin-top: 12vh;">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white text-center">
                    <h5 class="mb-0">Login Kopipiro</h5>
                </div>
                <div class="card-body">
                    <?php if ($error): ?><div class="alert alert-danger py-2"><?= $error ?></div><?php endif; ?>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="login" class="btn btn-dark">Login</button>
                        </div>
                    </form>
                    <div class="text-center mt-3">
                        <small>Belum punya akun? <a href="register.php">Register Member</a></small><br>
                        <small><a href="index.php">Kembali ke Katalog</a></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>