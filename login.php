<?php
session_start();

$host = "localhost";
$user = "root";
$password = "";
$dbname = "bookstore";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$loginError = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION["user"] = $user["email"];
        $_SESSION["name"] = $user["name"];
        $_SESSION["role"] = $user["role"]; 
        
        // Redirect based on role
        if ($user["role"] === "admin") {
            header("Location: dashboard.php");
        } else {
            header("Location: books.php");
        }
        exit();
    } else {
        $loginError = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        body {
            background-color: white;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 400px;
            margin: 70px auto;
            background-color: #f1f1f1;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        h2 {
            color: #0a2a66;
            margin-bottom: 20px;
        }

        input[type="email"],
        input[type="password"] {
            width: 90%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            background-color: #0a2a66;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background-color: #003c99;
        }

        .error {
            color: red;
            margin-bottom: 15px;
        }

        .link {
            margin-top: 15px;
        }

        .link a {
            color: #0a2a66;
            text-decoration: none;
        }

        .link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Welcome To Nairobi Books!!</h2>

    <?php if ($loginError): ?>
        <p class="error"><?= $loginError ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="email" name="email" required placeholder="Email"><br>
        <input type="password" name="password" required placeholder="Password"><br>
        <button type="submit">Login</button>
    </form>

    <div class="link">
        Don’t have an account? <a href="register.php">Sign Up</a>
    </div>
</div>
</body>
</html>
