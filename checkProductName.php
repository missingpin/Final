<?php
include('connect.php');

if (isset($_POST['productname'])) {
    $productname = mysqli_real_escape_string($con, $_POST['productname']);

    $query = "SELECT COUNT(*) FROM product WHERE productname = '$productname'";
    $result = mysqli_query($con, $query);

    $row = mysqli_fetch_row($result);

    if ($row[0] > 0) {
        echo 'exists';
    } else {
        echo 'not_exists';
    }
}
?>
