<?php
include("connect.php");

class UserApproval {
    private $con;

    public function __construct($dbConnection) {
        $this->con = $dbConnection;
    }
    public function approveUser($userId) {
        $query = "UPDATE form SET status = 'approved' WHERE id = ?";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param("i", $userId);
        
        if ($stmt->execute()) {
            echo "User approved successfully.";
        } else {
            echo "Error approving user.";
        }
        $stmt->close();
    }
}

if (isset($_POST['userId'])) {
    $userId = $_POST['userId'];
    $userApproval = new UserApproval($con);
    $userApproval->approveUser($userId);
}
?>
