<?php
session_start();

if (!isset($_SESSION["user"]) || $_SESSION["role"] !== "customer") {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "bookstore");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$search = $_GET['search'] ?? '';

if ($search) {
    $sql = "SELECT * FROM books WHERE title LIKE ? OR author LIKE ? OR genre LIKE ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $like = '%' . $search . '%';
    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT * FROM books ORDER BY created_at DESC";
    $result = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Bookstore</title>
    <style>
        body {
            background-color: #ffffff;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #000;
        }

        .navbar {
            background-color: #0d1b2a;
            padding: 15px;
            text-align: center;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            margin: 0 20px;
            font-weight: bold;
        }

        .navbar a:hover {
            text-decoration: underline;
        }

        h1, h2 {
            text-align: center;
            margin: 20px 0;
            color: #0d1b2a;
        }

        form {
            text-align: center;
            margin-bottom: 30px;
        }

        input[type="text"] {
            padding: 10px;
            width: 300px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            padding: 10px 16px;
            font-size: 16px;
            background-color: #0d1b2a;
            color: white;
            border: none;
            border-radius: 5px;
            margin-top: 10px;
            cursor: pointer;
        }

        button:hover {
            background-color: #003566;
        }

        p {
            text-align: center;
        }

        .book-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 25px;
            margin: 0 auto;
            max-width: 1200px;
            padding: 20px;
        }

        .book-card {
            background-color: #f0f0f0;
            padding: 15px;
            width: 230px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .book-card img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 5px;
        }

        .book-card h3 {
            margin: 10px 0 5px;
            color: #0d1b2a;
        }

        .book-card p {
            margin: 5px 0;
        }

        .book-card form {
            margin-top: 10px;
        }

        .book-card button {
            width: 100%;
            margin-top: 5px;
        }

        .no-books {
            text-align: center;
            font-style: italic;
            color: grey;
        }
    </style>
</head>
<body>

    <div class="navbar">
        <a href="books.php" class="active">Home</a>
        <a href="cart.php">Cart</a>
        <a href="settings.php">Settings</a>
        <a href="logout.php">Logout</a>
    </div>

    <h1>Welcome to Nairobi Books!</h1>

    <form method="GET" action="books.php">
        <input 
            type="text" 
            name="search" 
            placeholder="Search by title, author, or genre" 
            value="<?= htmlspecialchars($search) ?>"
        >
        <button type="submit">Search</button>
    </form>

    <h2>Hello, <?= htmlspecialchars($_SESSION["name"]) ?>!</h2>

    <div class="book-grid">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="book-card">
                    <?php if (!empty($row['cover_image']) && file_exists($row['cover_image'])): ?>
                        <img src="<?= htmlspecialchars($row['cover_image']) ?>" alt="Cover">
                    <?php else: ?>
                        <div style="width: 100%; height: 250px; background: #ddd; display: flex; align-items: center; justify-content: center; color: #555;">No Image</div>
                    <?php endif; ?>
                    <h3><?= htmlspecialchars($row['title']) ?></h3>
                    <p><strong>Author:</strong> <?= htmlspecialchars($row['author']) ?></p>
                    <p><strong>Genre:</strong> <?= htmlspecialchars($row['genre']) ?></p>
                    <p><strong>Price:</strong> Ksh <?= number_format($row['price'], 2) ?></p>
                    
                    <form method="POST" action="cart.php">
                        <input type="hidden" name="book_id" value="<?= $row['id'] ?>">
                        <button type="submit" name="add_to_cart">Add to Cart</button>
                        <button type="submit" name="buy_now">Buy Now</button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="no-books">No books available at the moment.</p>
        <?php endif; ?>
    </div>

</body>
</html>
