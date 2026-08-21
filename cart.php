<?php
session_start();

// Connect to the database
$conn = new mysqli("localhost", "root", "", "bookstore");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize the cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle "Add to Cart" or "Buy Now" actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_id'])) {
    $book_id = intval($_POST['book_id']);
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    // If already in cart, increase quantity
    if (isset($_SESSION['cart'][$book_id])) {
        $_SESSION['cart'][$book_id] += $quantity;
    } else {
        $_SESSION['cart'][$book_id] = $quantity;
    }

    // Redirect based on button pressed
    if (isset($_POST['buy_now'])) {
        header("Location: checkout.php?book_id=$book_id");
    } else {
        header("Location: cart.php");
    }
    exit();
}

// Fetch cart book details
$books_in_cart = [];
if (!empty($_SESSION['cart'])) {
    $ids = implode(',', array_map('intval', array_keys($_SESSION['cart'])));
    $result = $conn->query("SELECT * FROM books WHERE id IN ($ids)");
    while ($row = $result->fetch_assoc()) {
        $row['quantity'] = $_SESSION['cart'][$row['id']];
        $books_in_cart[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Cart</title>
    <style>
        body {
            background-color: white;
            font-family: Arial, sans-serif;
            color: #000;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background-color: #f4f4f4;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        h2 {
            color: #0a2a66;
            margin-bottom: 20px;
        }

        ul {
            list-style: none;
            padding: 0;
        }

        li {
            background-color: #fff;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            text-align: left;
            border-radius: 5px;
        }

        button {
            background-color: #0a2a66;
            color: white;
            padding: 10px 18px;
            font-size: 14px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }

        button:hover {
            background-color: #0044aa;
        }

        form[onsubmit] button {
            background-color: red;
            color: white;
        }

        hr {
            border: none;
            border-top: 1px solid #ccc;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Your Cart</h2>

        <?php if (empty($books_in_cart)): ?>
            <p>Your cart is empty.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($books_in_cart as $book): ?>
                    <li>
                        <strong><?= htmlspecialchars($book['title']) ?></strong><br>
                        Author: <?= htmlspecialchars($book['author']) ?><br>
                        Price: Ksh <?= number_format($book['price'], 2) ?><br>
                        Quantity: <?= $book['quantity'] ?><br><br>

                        <!-- Remove Button -->
                        <form method="POST" action="remove_from_cart.php" onsubmit="return confirm('Remove this book from cart?')" style="display:inline;">
                            <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                            <button type="submit">Remove</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>

            <!-- Proceed to Checkout -->
            <form action="checkout.php" method="POST">
                <button type="submit">Proceed to Checkout</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
