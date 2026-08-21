<?php
session_start();

// Only admins can access this
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

// Connect to the database
$conn = new mysqli("localhost", "root", "", "bookstore");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get and validate book ID
$book_id = $_GET['id'] ?? null;
if (!$book_id || !is_numeric($book_id)) {
    header("Location: dashboard.php?error=Invalid+book+ID");
    exit();
}

// Delete the book using prepared statement
$stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
$stmt->bind_param("i", $book_id);

if ($stmt->execute()) {
    header("Location: dashboard.php?message=Book+deleted+successfully");
    exit();
} else {
    header("Location: dashboard.php?error=Failed+to+delete+book");
    exit();
}
?>
