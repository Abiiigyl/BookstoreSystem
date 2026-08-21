<?php
session_start();

$conn = new mysqli("localhost", "root", "", "bookstore");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if admin is logged in
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Fetch all orders
$orders_sql = "SELECT id, name, email, created_at FROM orders ORDER BY created_at DESC";
$orders_result = $conn->query($orders_sql);

// Prepare book stock info
$stock_data = [];
$stock_result = $conn->query("SELECT id, title, stock FROM books");
while ($row = $stock_result->fetch_assoc()) {
    $stock_data[$row['id']] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Orders Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: white;
        }
        .header {
            background-color: #002855;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .order {
            background-color: #f4f6f8;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 10px;
        }
        .low-stock {
            background-color: #ffe5e5;
            color: red;
            font-weight: bold;
        }
        .medium-stock {
            background-color: #fff7e6;
            color: #cc7a00;
            font-weight: bold;
        }
        .high-stock {
            background-color: #e6ffec;
            color: green;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <div class="header mb-4">
        <h2>Orders Report</h2>
        <a href="dashboard.php" class="btn btn-light mt-3">⬅ Back to Dashboard</a>
    </div>

    <?php while ($order = $orders_result->fetch_assoc()): ?>
        <div class="order shadow-sm">
            <h5>Order #<?= $order['id'] ?> - <?= htmlspecialchars($order['email']) ?></h5>
            <p><strong>Placed on:</strong> <?= $order['created_at'] ?></p>

            <table class="table table-bordered mt-3">
                <thead class="table-light">
                    <tr>
                        <th>Book Title</th>
                        <th>Quantity Ordered</th>
                        <th>Stock Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $order_id = $order['id'];
                    $items_sql = "SELECT book_id, quantity FROM order_items WHERE order_id = $order_id";
                    $items_result = $conn->query($items_sql);

                    if ($items_result && $items_result->num_rows > 0):
                        while ($item = $items_result->fetch_assoc()):
                            $book_id = $item['book_id'];
                            $book = $stock_data[$book_id] ?? ['title' => 'Unknown', 'stock' => 0];
                            $stock = (int) $book['stock'];

                            if ($stock <= 2) {
                                $stock_class = 'low-stock';
                            } elseif ($stock <= 5) {
                                $stock_class = 'medium-stock';
                            } else {
                                $stock_class = 'high-stock';
                            }
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($book['title']) ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td class="<?= $stock_class ?>">Stock: <?= $stock ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="text-center text-muted">No items found for this order.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endwhile; ?>
</div>
</body>
</html>
