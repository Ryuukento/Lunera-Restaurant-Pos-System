<?php
session_start();
include("connection.php");

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Convert password to MD5 hash para tumugma sa database
    $hashed_password = md5($password);

    $stmt = $conn->prepare("SELECT * FROM login WHERE username=? AND password=? LIMIT 1");
    if (!$stmt) {
        die("Database query failed: " . $conn->error);
    }

    $stmt->bind_param("ss", $username, $hashed_password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Redirect based sa role
if ($user['role'] === 'admin') {
    header("Location: admin/admin.php");
} else {
    header("Location: index.php");
}
exit();}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="login-container">
    <div class="login-box">
        <h2>Login</h2>
        <?php if ($error != "") echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</div>
</body>
</html>
