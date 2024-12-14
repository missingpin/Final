<?php
session_start(); 

include 'connect.php';

if ($con) {
    $query = "SELECT productname, quantity, exp, id FROM product 
              WHERE quantity <= 50 OR (DATEDIFF(exp, CURDATE()) <= 7 AND DATEDIFF(exp, CURDATE()) >= 0) 
              ORDER BY last_updated DESC";

    $result = mysqli_query($con, $query);

    if (!$result) {
        die("Query failed: " . mysqli_error($con));
    }

    $alerts = [];
    while ($row = mysqli_fetch_assoc($result)) {
        if (isset($row['productname']) && isset($row['quantity'])) {
            $daysLeft = (strtotime($row['exp']) - time()) / (60 * 60 * 24);
            $daysLeft = round($daysLeft);

            $is_seen = isset($_SESSION['seen_alerts']) && in_array($row['id'], $_SESSION['seen_alerts']);

            if ($row['quantity'] <= 50) {
                $alerts[] = [
                    'id' => $row['id'], 
                    'message' => "Low unit for: <strong>" . htmlspecialchars($row['productname']) . "</strong> - " . $row['quantity'] . " left!",
                    'type' => 'low-stock',
                    'is_seen' => $is_seen
                ];
            }
            if ($daysLeft <= 7 && $daysLeft >= 0) {
                $alerts[] = [
                    'id' => $row['id'],
                    'message' => "<strong>" . htmlspecialchars($row['productname']) . "</strong> is about to expire in " . $daysLeft . " days! Please replace the item.",
                    'type' => 'expiration',
                    'is_seen' => $is_seen
                ];
            }
        }
    }

    header('Content-Type: application/json');
    echo json_encode($alerts);
} else {
    die("Connection is not established.");
}

mysqli_close($con);
?>
