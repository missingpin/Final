<?php
session_start();
include 'connect.php';

if (isset($_POST['cart'])) {
    $cartData = json_decode($_POST['cart'], true);
    $total = $_POST['total'];

    mysqli_begin_transaction($con);

    foreach ($cartData as $product) {
        $productId = $product['id'];
        $quantity = $product['quantity'];

        $query = "SELECT quantity FROM product WHERE id = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $currentQuantity = $row['quantity'];

            if ($currentQuantity >= $quantity) {
                $newQuantity = $currentQuantity - $quantity;

                $updateQuery = "UPDATE product SET quantity = ? WHERE id = ?";
                $updateStmt = $con->prepare($updateQuery);
                $updateStmt->bind_param('ii', $newQuantity, $productId);

                if (!$updateStmt->execute()) {
                    mysqli_rollback($con);
                    echo json_encode(['success' => false, 'message' => 'Error updating product quantity: ' . mysqli_error($con)]);
                    exit;
                }
            } else {
                mysqli_rollback($con);
                echo json_encode(['success' => false, 'message' => "Insufficient stock for product: " . $product['name']]);
                exit;
            }
        } else {
            mysqli_rollback($con);
            echo json_encode(['success' => false, 'message' => 'Error fetching product data: ' . mysqli_error($con)]);
            exit;
        }
    }

    mysqli_commit($con);
    echo json_encode(['success' => true, 'message' => 'Checkout successful. Thank you for your purchase!']);
} else {
    echo json_encode(['success' => false, 'message' => 'No cart data received.']);
}
?>
