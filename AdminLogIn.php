<?php
session_start();

$valid_username = 'admin';
$valid_password = 'root';
$valid_pin = '1234';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($username === $valid_username && $password === $valid_password) {
        $_SESSION['username'] = $username;
        header('Location: admindashboard.php');
        exit;
    } else {
        $error = "Invalid Username or Password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="adminlogin.css">
</head>
<body>
<div class="wrapper">
    <form action="" method="POST">
        <h1>Login</h1>

        <?php if (isset($error)) { echo '<p class="error">' . htmlspecialchars($error) . '</p>'; } ?>

        <div class="input-group">
            <div class="input-field" id="nameField">
                <i class="fa-solid fa-user"></i>
                <input type="text" placeholder="Username" name="username" required>
            </div>

            <div class="input-field" id="pwField">
                <i class="fa-solid fa-lock"></i>
                <input type="password" placeholder="Password" name="password" required>
            </div>
            <div class="input-field" id="nameField">
                <i class="fa-solid fa-pin"></i>
                <input type="number" placeholder="Enter PIN" name="pin" required minlength="4" maxlength="4" min="1000" max="9999" oninput="this.value = this.value.slice(0, 4);">
                </div>
            <button type="submit" class="btn">Login</button>
        </div>
    </form>
</div>
</body>
</html>