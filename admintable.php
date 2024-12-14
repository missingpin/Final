<?php
include 'connect.php';

if (isset($_POST['displaySend'])) {
    $table = '
    <table class="table table-striped table-bordered">
        <thead class="thead-dark">
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Username</th>
                <th scope="col">Password</th>
                <th scope="col">Status</th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>';

    $sql = "SELECT * FROM form WHERE level <= 1";
    $result = mysqli_query($con, $sql);
    $number = 1;

    while ($row = mysqli_fetch_assoc($result)) {
        $id = $row['id'];
        $username = $row['username'];
        $password = $row['password'];
        $status = $row['status'];

        $statusClass = '';
        if ($status === 'approved') {
            $statusClass = 'status-approved';
        } elseif ($status === 'pending') {
            $statusClass = 'status-pending';
        }

        $actionButtons = '';

        if ($status === 'pending') {
            $actionButtons .= '<button class="btn btn-success btn-sm" onclick="approveUser(' . $id . ')">Approve</button>
                               <button class="btn btn-danger btn-sm" onclick="declineUser(' . $id . ')">Decline</button>';
        } else if ($status === 'approved') {
            $actionButtons .= '<button class="btn btn-primary btn-sm" onclick="edituser(' . $id . ')"><i class="fas fa-edit"></i></button>
                               <button class="btn btn-danger btn-sm" onclick="deleteuser(' . $id . ')"><i class="fas fa-trash"></i></button>';
        }

        $table .= '<tr>
            <td scope="row">' . $number . '</td>
            <td>' . htmlspecialchars($username) . '</td>
            <td>' . htmlspecialchars($password) . '</td>
            <td class="' . $statusClass . '">' . ucfirst($status) . '</td> <!-- Add status styling class here -->
            <td style="width: 250px;">' . $actionButtons . '</td>
        </tr>';

        $number++;
    }

    $table .= '</tbody></table>';
    echo $table;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admindashboard.css">
</head>
</html>