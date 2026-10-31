<?php
// config.php
$host     = 'localhost';
$user     = 'root';
$password = '';
$db       = 'kopipiro';

$conn = mysqli_connect($host, $user, $password, $db);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>