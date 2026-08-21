<?php
session_start();

// Redirect if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

// Get user input from form
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$address = $_POST['address'] ?? '';
$total = $_POST['total'] ?? 0;

// Basic validation
if (empty($name) || empty($email) || empty($address)) {
    die("Please fill in all the required fields.");
}

// ✅ MANUAL database connection (no db.php)
$conn = new mysqli("localhost", "root", "", "bookstore");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Insert order into 'orders' table
$insertOrder = $conn->prepare("INSERT INTO orders (name, email, address, total_amount) VALUES (?, ?, ?, ?)");
$insertOrder->bind_param("sssd", $name, $email, $address, $total);
$insertOrder->execute();
$order_id = $insertOrder->insert_id; // Get the ID of the new order

// Fetch book prices from DB
$bookIds = array_keys($_SESSION['cart']);
if (empty($bookIds)) {
    die("Cart is empty or invalid.");
}

$placeholders = implode(',', array_fill(0, count($bookIds), '?'));
$types = str_repeat('i', count($bookIds)); // e.g., 'iii' for 3 books

$stmt = $conn->prepare("SELECT id, price FROM books WHERE id IN ($placeholders)");
$stmt->bind_param($types, ...$bookIds);
$stmt->execute();
$result = $stmt->get_result();

$booksData = [];
while ($row = $result->fetch_assoc()) {
    $booksData[$row['id']] = $row;
}

// Insert into order_items table
foreach ($_SESSION['cart'] as $book_id => $quantity) {
    if (!isset($booksData[$book_id])) {
        continue; // Skip if book not found
    }

    $price = $booksData[$book_id]['price'];

    $insertItem = $conn->prepare("INSERT INTO order_items (order_id, book_id, quantity, price) VALUES (?, ?, ?, ?)");
    $insertItem->bind_param("iiid", $order_id, $book_id, $quantity, $price);
    $insertItem->execute();

    // Update book stock
$updateStock = $conn->prepare("UPDATE books SET stock = stock - ? WHERE id = ?");
$updateStock->bind_param("ii", $quantity, $book_id);
$updateStock->execute();

}

// Clear the cart
unset($_SESSION['cart']);

// Redirect to receipt
header("Location: receipt.php?order_id=" . $order_id);
exit();
?>
