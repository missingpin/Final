<?php
session_start();
include 'connect.php';
include 'sidebar.php';

function fetchCurrentStockAndSummary($con) {
    $sql = "SELECT id, productname, quantity FROM product";
    $result = mysqli_query($con, $sql);

    if (!$result) {
        die("Error fetching stock: " . mysqli_error($con));
    }

    $currentStock = [];
    $inStock = [];
    $outOfStock = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $product = [
            'id' => $row['id'],
            'productname' => $row['productname'],
            'quantity' => $row['quantity']
        ];

        if ($row['quantity'] > 0) {
            $inStock[] = $product;
        } else {
            $outOfStock[] = $product;
        }

        $currentStock[] = $product;
    }

    return [
        'currentStock' => $currentStock,
        'inStock' => $inStock,
        'outOfStock' => $outOfStock
    ];
}

$stockData = fetchCurrentStockAndSummary($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Module</title>
    <link rel="stylesheet" href="stock.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <h2 class="header">Stock Status</h2>
    <div class="row">
        <!-- Current Stock Table (Left) -->
        <div class="current-stock col-md-6">
            <div class="table-container">
                <h3 class="stock-header">Current Stock</h3>
                <table class="table table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>Product Name</th>
                            <th>Unit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($stockData['currentStock'] as $product) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($product['productname']) . "</td>";
                            echo "<td>" . $product['quantity'] . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="stock-summary col-md-6">
            <div class="table-container">
                <h3 class="stock-header">Stock Summary</h3>
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <thead class="thead-dark">
                                <tr>
                                    <th>In Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($stockData['inStock'] as $product) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($product['productname']) . "</td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Out of Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($stockData['outOfStock'] as $product) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($product['productname']) . "</td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
