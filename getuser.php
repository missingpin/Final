<?php
include 'connect.php';

if (isset($_POST['userId'])) {
    $userId = $_POST['userId'];
    $sql = "SELECT * FROM form WHERE id = $userId";
    $result = mysqli_query($con, $sql);
    
    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode($row);
    }
}
?>
