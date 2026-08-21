<?php
session_start();
if (!isset($_SESSION["user"]) || $_SESSION["role"] !== 'admin') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "bookstore");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
} elseif (isset($_GET['error'])) {
    $error = htmlspecialchars($_GET['error']);
}

$sql = "SELECT * FROM books ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: white;
        }
        .navbar {
            background-color: #002855;
        }
        .navbar .nav-link, .navbar-brand {
            color: white !important;
        }
        .navbar .nav-link:hover {
            text-decoration: underline;
        }
        .low-stock {
            background-color: #f8d7da !important;
            color: #842029 !important;
            font-weight: bold;
        }
        .table th {
            background-color: #0056b3;
            color: white;
        }
        .center-text {
            text-align: center;
        }
       
    </style>
</head>
<body>

<!-- Navigation bar -->
<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">📚 Bookstore Admin</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="add_book.php">Add Book</a></li>
                <li class="nav-item"><a class="nav-link" href="orders_report.php">Orders Report</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="text-center">
        <h2>Welcome,  Admin</h2>
        <p class="text-muted">Manage your bookstore inventory and view reports</p>
    </div>

    <?php if (isset($message)): ?>
        <div class="alert alert-success mt-3"><?= $message ?></div>
    <?php elseif (isset($error)): ?>
        <div class="alert alert-danger mt-3"><?= $error ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
        <h3 class="text-primary" >Books Inventory</h3>
        <a href="add_book.php" class="btn btn-primary">+ Add New Book</a>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="center-text">
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Genre</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="center-text">
                    <?php while ($book = $result->fetch_assoc()): ?>
                        <tr class="<?= ($book['stock'] <= 5) ? 'low-stock' : '' ?>">
                            <td><?= htmlspecialchars($book['title']) ?></td>
                            <td><?= htmlspecialchars($book['author']) ?></td>
                            <td><?= htmlspecialchars($book['genre']) ?></td>
                            <td>Ksh <?= number_format($book['price'], 2) ?></td>
                            <td><?= (int)$book['stock'] ?></td>
                            <td>
                                <a href="edit_book.php?id=<?= $book['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                <a href="delete_book.php?id=<?= $book['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted mt-3">No books found.</p>
    <?php endif; ?>
</div>

</body>
</html>
