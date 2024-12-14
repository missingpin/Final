<?php
include("connect.php");

$query = "SELECT * FROM form WHERE status = 'pending'";
$result = mysqli_query($con, $query);

$output = "<table class='table'>
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Username</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>";

while($row = mysqli_fetch_assoc($result)) {
    $output .= "<tr>
                    <td>{$row['email']}</td>
                    <td>{$row['username']}</td>
                    <td><button class='btn btn-success' onclick='approveUser({$row['id']})'>Approve</button></td>
                </tr>";
}

$output .= "</tbody></table>";
echo $output;
?>
<script>
function approveUser(userId) {
    fetch('approve.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `userId=${userId}`
    })
    .then(response => response.text())
    .then(data => {
        alert(data);
        location.reload();
    });
}
</script>