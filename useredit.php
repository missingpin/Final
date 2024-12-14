<?php
include "connect.php";

if (isset($_POST['useredit'])) {
    $user_id = $_POST['useredit'];
    $sql = "select * from form where id = $user_id";
    $result = mysqli_query($con, $sql);
    
    $response = array();
    if ($row = mysqli_fetch_assoc($result)) {
        $response = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

if (isset($_POST['hiddendata'])) {
    $uniqueid = $_POST['hiddendata'];
    $username = $_POST['editusername'];
    $password = $_POST['editpassword'];
    $email = $_POST['editemail'];
    $email = $_POST['editgender'];

    $sql = "UPDATE form SET username='$username', password='$password', email='$email', gender='$gender' WHERE id=$uniqueid";
    $result = mysqli_query($con, $sql);
}
?>
