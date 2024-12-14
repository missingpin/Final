<?php
include 'connect.php';

if (isset($_POST['barcode'])) {
    $barcode = mysqli_real_escape_string($con, $_POST['barcode']);

    $sql = "SELECT * FROM product WHERE uid = '$barcode'";
    $result = mysqli_query($con, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $product = mysqli_fetch_assoc($result);
        echo json_encode($product); 
    } else {
        echo json_encode(null);
    }
}
?>
