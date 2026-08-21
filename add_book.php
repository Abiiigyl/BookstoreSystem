<?php
session_start();

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "bookstore");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = '';
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title  = trim($_POST["title"]);
    $author = trim($_POST["author"]);
    $genre  = trim($_POST["genre"]);
    $price  = floatval($_POST["price"]);
    $stock  = intval($_POST["stock"]);

    if (!empty($title) && !empty($author) && !empty($genre) && $price > 0) {
        $stmt = $conn->prepare("INSERT INTO books (title, author, genre, price, stock) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssdi", $title, $author, $genre, $price, $stock);
        $stmt->execute();

        header("Location: dashboard.php?message=" . urlencode("Book added successfully!"));
        exit();
    } else {
        $error = "Please fill in all fields correctly.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Book</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: white;
        }
        .form-container {
            max-width: 600px;
            margin: auto;
        }
        .header {
            background-color: #002855;
            color: white;
            padding: 20px;
        }
        .btn-primary {
            background-color: #0056b3;
            border-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container mt-5 form-container">
        <div class="header rounded text-center mb-4">
            <h2>Add New Book</h2>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Title:</label>
                <input type="text" name="title" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Author:</label>
                <input type="text" name="author" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Genre:</label>
                <input type="text" name="genre" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Price (Ksh):</label>
                <input type="number" step="0.01" name="price" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Stock:</label>
                <input type="number" name="stock" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">Add Book</button>
            <a href="dashboard.php" class="btn btn-secondary">Back</a>
        </form>
    </div>
</body>
</html>
