<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "bookstore";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Failed to connect to MySQL: " . $conn->connect_error);
} else {
    echo "✅ Connected to MySQL successfully!";
}
?>



if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user['email'];
        header("Location: dashboard.php");
        exit();
    } else {
        $loginError = "Invalid email or password.";
    }