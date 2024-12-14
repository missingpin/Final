<?php
include 'connect.php';

class Category {
    private $db;
    public function __construct($db) {
        $this->db = $db;
    }
    public function insertCategory($type) {
        $type = mysqli_real_escape_string($this->db, $type);

        $sql = "INSERT INTO category (type) VALUES ('$type')";
        if (mysqli_query($this->db, $sql)) {
            return ['success' => true];
        } else {
            return ['success' => false, 'message' => mysqli_error($this->db)];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = new Category($con);
    $type = $_POST['ptype'];

    $response = $category->insertCategory($type);
    echo json_encode($response);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
