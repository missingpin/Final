<?php
include 'connect.php';

if (isset($_POST['productSend']) && isset($_POST['typeSend']) && isset($_POST['etypeSend']) && isset($_POST['quantitySend']) && isset($_POST['expirationSend']) && isset($_POST['saleSend']) && isset($_POST['purchaseSend']) && isset($_POST['uidSend']) && isset($_FILES['imageSend'])) {

    $productSend = $_POST['productSend'];
    $quantitySend = $_POST['quantitySend'];
    $expirationSend = $_POST['expirationSend'];
    $typeSend = $_POST['typeSend'];
    $saleSend = $_POST['saleSend'];
    $purchaseSend = $_POST['purchaseSend'];
    $uidSend = $_POST['uidSend'];
    $etypeSend = $_POST['etypeSend'];

    $image = $_FILES['imageSend'];
    $imageName = $image['name'];
    $imageTmpName = $image['tmp_name'];
    
    $uploadDir = 'uploads/';
    $imageExtension = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
    $newImageName = uniqid("IMG_", true) . '.' . $imageExtension;
    $uploadFile = $uploadDir . $newImageName;

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($imageExtension, $allowedTypes)) {
        echo "Error: Only image files (JPG, JPEG, PNG, GIF) are allowed.";
        exit();
    }

    if (move_uploaded_file($imageTmpName, $uploadFile)) {
        $stmt = $con->prepare("INSERT INTO product (productname, quantity, exp, sale, purchase, uid, image, type, etype) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sisiiisss", $productSend, $quantitySend, $expirationSend, $saleSend, $purchaseSend, $uidSend, $newImageName, $typeSend, $etypeSend);
        
        if ($stmt->execute()) {
            $activityDescription = "Added a new product: $productSend";

            $logStmt = $con->prepare("INSERT INTO activity_log (activity_description) VALUES (?)");
            $logStmt->bind_param("s", $activityDescription);

            if ($logStmt->execute()) {
                echo "Product added successfully and activity logged!";
            } else {
                echo "Error: Could not log activity.";
            }

            $logStmt->close();
            $stmt->close();
        } else {
            echo "Error: Could not insert product.";
        }
    } else {
        echo "Error: File upload failed.";
    }

    $lowStockThreshold = 5;
    if ($quantitySend < $lowStockThreshold) {
        $userEmail = 'user_email@example.com';
        $productName = $productSend;
        $currentQuantity = $quantitySend;

        if (sendLowStockAlert($userEmail, $productName, $currentQuantity)) {
            echo "Low stock alert sent!";
        } else {
            echo "Failed to send low stock alert.";
        }
    }

} else {
    echo "Error: Missing product details or image.";
}
?>
