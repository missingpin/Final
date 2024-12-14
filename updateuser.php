<?php
include 'connect.php';

if (isset($_POST['userId'], $_POST['username'], $_POST['password'], $_POST['email'], $_POST['gender'])) {
    $userId = $_POST['userId'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];

    $sql = "UPDATE form SET username='$username', password='$password', email='$email', gender='$gender' WHERE id=$userId";
    if (mysqli_query($con, $sql)) {
        echo "User updated successfully.";
    } else {
        echo "Error updating user: " . mysqli_error($con);
    }
}
?>
