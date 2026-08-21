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

$book_id = $_GET['id'] ?? null;
if (!$book_id) {
    echo "No book ID provided.";
    exit();
}

$error = '';
$title = $author = $genre = '';
$price = 0.00;
$stock = 0;

// Fetch book details
$stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();
$book = $result->fetch_assoc();

if (!$book) {
    echo "Book not found.";
    exit();
}

// Set default values
$title  = $book['title'];
$author = $book['author'];
$genre  = $book['genre'];
$price  = $book['price'];
$stock  = $book['stock'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title  = trim($_POST["title"]);
    $author = trim($_POST["author"]);
    $genre  = trim($_POST["genre"]);
    $price  = floatval($_POST["price"]);
    $stock  = intval($_POST["stock"]);

    if (!empty($title) && !empty($author) && !empty($genre) && $price > 0) {
        $update = $conn->prepare("UPDATE books SET title = ?, author = ?, genre = ?, price = ?, stock = ? WHERE id = ?");
        $update->bind_param("sssdii", $title, $author, $genre, $price, $stock, $book_id);
        $update->execute();

        header("Location: dashboard.php?message=" . urlencode("Book updated successfully!"));
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
    <title>Edit Book</title>
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
            <h2>Edit Book</h2>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Title:</label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($title) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Author:</label>
                <input type="text" name="author" class="form-control" value="<?= htmlspecialchars($author) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Genre:</label>
                <input type="text" name="genre" class="form-control" value="<?= htmlspecialchars($genre) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Price (Ksh):</label>
                <input type="number" step="0.01" name="price" class="form-control" value="<?= htmlspecialchars($price) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Stock:</label>
                <input type="number" name="stock" class="form-control" value="<?= htmlspecialchars($stock) ?>" required>
            </div>

            <button type="submit" class="btn btn-primary">Update Book</button>
            <a href="dashboard.php" class="btn btn-secondary">Back</a>
        </form>
    </div>
</body>
</html>
