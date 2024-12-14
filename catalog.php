<?php
session_start();
include 'sidebar.php';
include 'connect.php';

$result = $con->query("SELECT * FROM product");
$total_pages = 5;
$page = isset($_GET['page']) ? $_GET['page'] : 1; 

$table = '<table class="table table-striped table-bordered">
    <thead class="thead-dark">
        <tr>
            <th scope="col" style="text-align: center;">ID</th>
            <th scope="col" style="text-align: center;">UID</th>
            <th scope="col" style="text-align: center;">Product Image</th>
            <th scope="col" style="text-align: center;">Product Name</th>
            <th scope="col" style="text-align: center;">Product Type</th>
            <th scope="col" style="text-align: center;">Unit</th>
            <th scope="col" style="text-align: center;">Expiration</th>
            <th scope="col" style="text-align: center;">Sale Price</th>
            <th scope="col" style="text-align: center;">Pur. Price</th>
            <th scope="col" style="text-align: center;">Total Sale</th>
        </tr>
    </thead>
    <tbody>';

$number = 1;
while ($row = $result->fetch_assoc()) {
    $id = $row['id'];
    $image = $row['image'] ? 'uploads/' . $row['image'] : 'no-image.jpg';
    $productname = htmlspecialchars($row['productname']);
    $uid = htmlspecialchars($row['uid']);
    $quantity = htmlspecialchars($row['quantity']);
    $exp = htmlspecialchars($row['exp']);
    $type = htmlspecialchars($row['type']);
    $sale = htmlspecialchars($row['sale']);
    $purchase = htmlspecialchars($row['purchase']);
    $totalSale = $sale * $quantity;

    $table .= '<tr>
        <td>' . $number . '</td>
        <td>' . $uid . '</td>
        <td><img src="' . $image . '" width="100" alt="' . $productname . '"></td>
        <td>' . $productname . '</td>
        <td>' . $type . '</td>
        <td>' . $quantity . '</td>
        <td>' . $exp . '</td>
        <td>' . '₱' . $sale . '</td>
        <td>' . '₱' . $purchase . '</td>
        <td>' . '₱' . number_format($totalSale, 2) . '</td>
        <td>
            <a href="table.php?action=edit&id=' . $id . '" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i> Edit</a>
        </td>
    </tr>';
    $number++;
}

$table .= '</tbody></table>';

echo $table;

// Pagination links
if ($total_pages > 1) {
    echo "<nav aria-label='Page navigation'>
        <ul class='pagination'>";
    for ($i = 1; $i <= $total_pages; $i++) {
        $activeClass = ($i === $page) ? 'active' : '';
        echo "<li class='page-item $activeClass'><a class='page-link' href='javascript:void(0)' onclick='loadPage($i)'>$i</a></li>";
    }
    echo "</ul></nav>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="table.css">
</head>
<body>
    
</body>
</html>