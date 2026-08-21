<?php
session_start();

$conn = new mysqli("localhost", "root", "", "bookstore");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Redirect if not logged in or not a customer
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}

$email_session = $_SESSION['user'];
$success = $error = "";

// Get user ID and current email
$user_query = $conn->query("SELECT id, email FROM users WHERE email = '$email_session'");
if ($user_query->num_rows === 0) {
    $error = "User not found.";
} else {
    $user = $user_query->fetch_assoc();
    $user_id = $user['id'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_email = $conn->real_escape_string(trim($_POST['email']));
    $new_password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (!empty($new_password) && $new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Update SQL
        $update_sql = "UPDATE users SET email = '$new_email'";
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_sql .= ", password = '$hashed_password'";
        }
        $update_sql .= " WHERE id = $user_id";

        if ($conn->query($update_sql)) {
            $success = "Details updated successfully.";
            $user['email'] = $new_email;
            $_SESSION['user'] = $new_email; // Update session email too
        } else {
            $error = "Failed to update. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Account Settings</title>
    <style>
        body {
            background-color: white;
            font-family: Arial, sans-serif;
            color: #000;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 500px;
            margin: 60px auto;
            padding: 30px;
            background-color: #f4f4f4;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #0a2a66;
            margin-bottom: 20px;
        }

        form label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        button {
            margin-top: 20px;
            width: 100%;
            padding: 12px;
            background-color: #0a2a66;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0044aa;
        }

        .message {
            text-align: center;
            margin-top: 10px;
        }

        .success {
            color: green;
        }

        .error {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Update Account Settings</h2>

        <?php if ($success): ?>
            <p class="message success"><?= htmlspecialchars($success) ?></p>
        <?php elseif ($error): ?>
            <p class="message error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST" action="settings.php">
            <label for="email">Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

            <label for="password">New Password (optional):</label>
            <input type="password" name="password" placeholder="Leave blank to keep current password">

            <label for="confirm_password">Confirm Password:</label>
            <input type="password" name="confirm_password" placeholder="Re-enter password">

            <button type="submit">Save Changes</button>
        </form>
    </div>
</body>
</html>
