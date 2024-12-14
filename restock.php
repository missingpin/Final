<?php
include 'connect.php';

if (isset($_POST['id']) && isset($_POST['quantity']) && isset($_POST['exp'])) {
    $id = mysqli_real_escape_string($con, $_POST['id']);
    $additionalQuantity = mysqli_real_escape_string($con, $_POST['quantity']);
    $exp = mysqli_real_escape_string($con, $_POST['exp']);
    
    // Get the current quantity to add the new quantity to it
    $sql = "SELECT quantity FROM product WHERE id = $id";
    $result = mysqli_query($con, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $currentQuantity = $row['quantity'];
        
        // Add the new quantity to the existing one
        $newQuantity = $currentQuantity + $additionalQuantity;
        
        // Update the product's quantity and expiration date
        $updateSql = "UPDATE product SET quantity = $newQuantity, exp = '$exp' WHERE id = $id";
        
        if (mysqli_query($con, $updateSql)) {
            echo "Product restocked successfully!";
        } else {
            echo "Error: " . mysqli_error($con);
        }
    } else {
        echo "Product not found.";
    }
}
?>
