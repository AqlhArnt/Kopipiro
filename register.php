<?php
// register.php
include 'config.php';
$error = "";
$success = "";

if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role     = 'customer'; // Otomatis terdaftar sebagai customer/member

    if (strlen($password) < 6) {
        $error = "Password minimal harus 6 karakter.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, "INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sss", $username, $hashed_password, $role);
        
        try {
            mysqli_stmt_execute($stmt);
            $success = "Registrasi berhasil! Silakan <a href='login.php'>Login di sini</a>.";
        } catch (mysqli_sql_exception $e) {
            $error = "Username sudah digunakan, pilih username lain.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Member - Kopipiro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container">
    <div class="row justify-content-center" style="margin-top: 10vh;">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white text-center">
                    <h5 class="mb-0">Daftar Member Kopipiro (Diskon 10%)</h5>
                </div>
                <div class="card-body">
                    <?php if ($error): ?><div class="alert alert-danger py-2"><?= $error ?></div><?php endif; ?>
                    <?php if ($success): ?><div class="alert alert-success py-2"><?= $success ?></div><?php endif; ?>
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
                            <button type="submit" name="register" class="btn btn-success">Register</button>
                        </div>
                    </form>
                    <div class="text-center mt-3">
                        <small>Sudah punya akun? <a href="login.php">Login</a></small><br>
                        <small><a href="index.php">Kembali ke Katalog</a></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>