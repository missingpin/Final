<?php
session_start();
include("connect.php");

function generateUniqueUserId($con) {
    do {
        $randomUserId = rand(100000, 999999);
        $checkQuery = "SELECT COUNT(*) FROM form WHERE userid = ?";
        $stmt = $con->prepare($checkQuery);
        if ($stmt === false) {
            die('Error preparing statement: ' . $con->error);
        }
        $stmt->bind_param("i", $randomUserId);
        $stmt->execute();
        if ($stmt->error) {
            die('Error executing query: ' . $stmt->error);
        }
        $result = $stmt->get_result();
        $row = $result->fetch_row();
        $count = $row[0];
    } while ($count > 0);
    return $randomUserId;
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $mi = $_POST['mi'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    $passwordPattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/";

    if (!empty($password) && !empty($username)) {
        if (!preg_match($passwordPattern, $password)) {
            echo "<script>alert('Password must be at least 8 characters long, include at least one uppercase letter, one number, and one special character.');</script>";
        } else {
            $stmt = $con->prepare("SELECT * FROM form WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                echo "<script>alert('Username already taken.');</script>";
            } else {
                $userid = generateUniqueUserId($con);
                $level = 1;
                $query = "INSERT INTO form (userid, firstname, lastname, mi, username, password, level, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')";
                $stmt = $con->prepare($query);
                $stmt->bind_param("isssssi", $userid, $firstname, $lastname, $mi, $username, $password, $level);

                if ($stmt->execute()) {
                    echo "<script>alert('Successfully Registered. Awaiting admin approval.');</script>";
                    echo "<script>window.location.href = 'login.php';</script>";
                } else {
                    echo "<script>alert('Error: Could not register');</script>";
                }
            }
        }
    } else {
        echo "<script>alert('Please enter valid information.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up Page</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="wrapper">
        <form action="" method="POST">
            <h1>Sign Up</h1>

            <div class="input-group">
                <div class="input-field" id="fnameField">
                    <i class="fa-solid fa-user"></i>
                    <input type="text" placeholder="First Name" name="firstname" required>
                </div>
                <div class="input-field" id="lnameField">
                    <i class="fa-solid fa-user"></i>
                    <input type="text" placeholder="Last Name" name="lastname" required>
                </div>
                <div class="input-field" id="mifield">
                    <i class="fa-solid fa-user"></i>
                    <input type="text" placeholder="M.I" name="mi" maxlength="1" pattern="[A-Za-z]" title="Middle Initial must be a single letter." required>
                    </div>
                <div class="input-field" id="usernameField">
                    <i class="fa-solid fa-user"></i>
                    <input type="text" placeholder="Username" name="username" required>
                </div>

                <div class="input-field" id="pwField">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" placeholder="Password" name="password" required>
                </div>

                <button type="submit" class="btn">Sign Up</button>

                <div class="login-link">
                    <a href="login.php">Back to Login</a>
                </div>
            </div>
        </form>
    </div>
</body>
</html>

<script>
    document.querySelector('form').addEventListener('submit', function(event) {
        const password = document.querySelector('[name="password"]').value;
        const pwField = document.getElementById('pwField');
        
        const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

        if (!passwordPattern.test(password)) {
            event.preventDefault();
            alert('Password must be at least 8 characters long, include at least one uppercase letter, one number, and one special character.');
            pwField.querySelector('input').focus();
        }
    });
    document.querySelector('form').addEventListener('submit', function(event) {
    const miField = document.querySelector('[name="mi"]');
    if (miField.value && !/^[A-Za-z]$/.test(miField.value)) {
        event.preventDefault();
        alert('M.I must be a single letter.');
        miField.focus();
    }
});
</script>
