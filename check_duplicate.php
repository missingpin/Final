<?php

class ProductChecker {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    public function checkProductExists($productname) {
        $sql = "SELECT COUNT(*) as count FROM product WHERE productname = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $productname);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row['count'] > 0;
    }
}

if (isset($_POST['productname'])) {
    include 'connect.php';

    $productChecker = new ProductChecker($conn);
    $productname = $_POST['productname'];

    $exists = $productChecker->checkProductExists($productname);

    echo json_encode(['exists' => $exists]);
} else {
    echo json_encode(['exists' => false]);
}

?>