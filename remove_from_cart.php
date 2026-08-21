<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_id']) && isset($_SESSION['cart'])) {
    $book_id = intval($_POST['book_id']);

    if (in_array($book_id, $_SESSION['cart'])) {
        $_SESSION['cart'] = array_filter($_SESSION['cart'], function($id) use ($book_id) {
            return $id != $book_id;
        });
        $_SESSION['cart'] = array_values($_SESSION['cart']);

        header("Location: cart.php?message=" . urlencode("Book removed from cart."));
        exit();
    } else {
        header("Location: cart.php?error=" . urlencode("Book not found in cart."));
        exit();
    }
} else {
    header("Location: cart.php?error=" . urlencode("Invalid request."));
    exit();
}
