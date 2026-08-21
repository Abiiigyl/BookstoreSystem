<?php
// Connect to database
$conn = new mysqli("localhost", "root", "", "bookstore");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the order ID from URL
$order_id = $_GET['order_id'] ?? 0;
if (!$order_id) {
    die("Invalid order ID.");
}

// Fetch order details
$orderStmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$orderStmt->bind_param("i", $order_id);
$orderStmt->execute();
$orderResult = $orderStmt->get_result();
$order = $orderResult->fetch_assoc();

if (!$order) {
    die("Order not found.");
}

// Fetch ordered items with book titles
$itemsStmt = $conn->prepare("
    SELECT oi.quantity, oi.price, b.title
    FROM order_items oi
    JOIN books b ON oi.book_id = b.id
    WHERE oi.order_id = ?
");
$itemsStmt->bind_param("i", $order_id);
$itemsStmt->execute();
$itemsResult = $itemsStmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt</title>
    <style>
        body {
            background-color: white;
            font-family: Arial, sans-serif;
            padding: 0;
            margin: 0;
        }

        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        h2 {
            margin-bottom: 20px;
            color: #0a2a66;
        }

        p {
            margin: 10px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ccc;
            text-align: center;
        }

        th {
            background-color: #e0e0e0;
            color: #0a2a66;
        }

        .total {
            font-weight: bold;
            background-color: #f0f0f0;
        }

        .back-link {
            display: inline-block;
            padding: 12px 25px;
            background: #0a2a66;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }

        .back-link:hover {
            background: #003c99;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Receipt</h2>

        <p><strong>Name:</strong> <?= htmlspecialchars($order['name']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
        <p><strong>Address:</strong> <?= htmlspecialchars($order['address']) ?></p>
        <p><strong>Order Date:</strong> <?= $order['created_at'] ?></p>

        <table>
            <tr>
                <th>Book Title</th>
                <th>Unit Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
            </tr>

            <?php
            $grandTotal = 0;
            while ($item = $itemsResult->fetch_assoc()):
                $subtotal = $item['price'] * $item['quantity'];
                $grandTotal += $subtotal;
            ?>
            <tr>
                <td><?= htmlspecialchars($item['title']) ?></td>
                <td>KES <?= number_format($item['price'], 2) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td>KES <?= number_format($subtotal, 2) ?></td>
            </tr>
            <?php endwhile; ?>
            <tr class="total">
                <td colspan="3">Total</td>
                <td>KES <?= number_format($grandTotal, 2) ?></td>
            </tr>
        </table>

        <a class="back-link" href="books.php">← Back to Bookstore</a>
    </div>

</body>
</html>
