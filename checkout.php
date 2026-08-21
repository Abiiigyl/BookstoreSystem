<?php
session_start();

// Redirect if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

// Connect to DB
$conn = new mysqli("localhost", "root", "", "bookstore");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get book details from DB
$bookIds = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($bookIds), '?'));
$stmt = $conn->prepare("SELECT * FROM books WHERE id IN ($placeholders)");
$stmt->bind_param(str_repeat('i', count($bookIds)), ...$bookIds);
$stmt->execute();
$result = $stmt->get_result();

$booksInCart = [];
$total = 0;

while ($row = $result->fetch_assoc()) {
    $id = $row['id'];
    if (isset($_SESSION['cart'][$id]) && is_numeric($_SESSION['cart'][$id])) {
        $quantity = (int) $_SESSION['cart'][$id];
        $subtotal = $row['price'] * $quantity;

        $row['quantity'] = $quantity;
        $row['subtotal'] = $subtotal;
        $total += $subtotal;
        $booksInCart[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Checkout</title>
    <style>
        body {
            background-color: white;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #000;
        }

        .container {
            max-width: 700px;
            margin: 40px auto;
            padding: 30px;
            background-color: #f4f4f4;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        h2, h3 {
            color: #0a2a66;
        }

        ul {
            list-style-type: none;
            padding: 0;
            text-align: left;
        }

        li {
            background: #fff;
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        form {
            text-align: left;
            margin-top: 20px;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="email"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 4px;
            border: 1px solid #aaa;
            box-sizing: border-box;
        }

        button {
            background-color: #0a2a66;
            color: white;
            padding: 12px 20px;
            font-size: 16px;
            margin-top: 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background-color: #0044aa;
        }

        p strong {
            font-size: 18px;
            display: block;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Checkout</h2>

        <h3>Order Summary</h3>
        <ul>
            <?php foreach ($booksInCart as $book): ?>
                <li>
                    <?= htmlspecialchars($book['title']) ?> × <?= $book['quantity'] ?> — Ksh <?= number_format($book['subtotal'], 2) ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <p><strong>Total: Ksh <?= number_format($total, 2) ?></strong></p>

        <h3>Customer Information</h3>
        <form action="process_order.php" method="POST">
            <label>Full Name:</label>
            <input type="text" name="name" required>

            <label>Email:</label>
            <input type="email" name="email" required>

            <label>Delivery Address:</label>
            <textarea name="address" rows="4" required></textarea>

            <input type="hidden" name="total" value="<?= $total ?>">

            <button type="submit">Place Order</button>
        </form>
    </div>
</body>
</html>
