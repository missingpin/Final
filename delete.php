<?php
include "connect.php";

if (isset($_POST['deletedata'])) {
    $unique = intval($_POST['deletedata']);
    $sql = "DELETE FROM product WHERE id = $unique";
    $result = mysqli_query($con, $sql);
}

if (isset($_POST['deleteform'])) {
    $unique = intval($_POST['deleteform']);
    $sql = "DELETE FROM form WHERE id = $unique";
    $result = mysqli_query($con, $sql);
}
?>