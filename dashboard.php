<?php
session_start();
include 'connect.php';
include 'sidebar.php';

if (!isset($_SESSION['username'])) {
    header("Location:login.php"); 
    exit;
}

class Database {
    private $con;

    public function __construct($con) {
        $this->con = $con;
    }

    public function query($sql) {
        return mysqli_query($this->con, $sql);
    }

    public function fetchAssoc($result) {
        return mysqli_fetch_assoc($result);
    }

    public function prepareAndExecute($sql) {
        $stmt = $this->con->prepare($sql);
        $stmt->execute();
        return $stmt;
    }
}

class Activity {
    private $db;
    
    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function getRecentActivities() {
        $activities = [];
        $stmt = $this->db->prepareAndExecute("SELECT activity_description, timestamp, user_id FROM activity_log ORDER BY timestamp DESC LIMIT 5");

        $activity_desc = '';
        $timestamp = '';
        $user_id = '';
        
        $stmt->bind_result($activity_desc, $timestamp, $user_id);
        
        while ($stmt->fetch()) {
            $activities[] = ['description' => $activity_desc, 'timestamp' => $timestamp, 'user_id' => $user_id];
        }
        
        $stmt->close();
        return $activities;
    }
}


class Dashboard {
    private $db;
    private $activity;

    public function __construct(Database $db, Activity $activity) {
        $this->db = $db;
        $this->activity = $activity;
    }

    public function getTotalProducts() {
        $result = $this->db->query("SELECT COUNT(*) AS totalProducts FROM product");
        return $this->db->fetchAssoc($result)['totalProducts'];
    }

    public function getTotalCategories() {
        $result = $this->db->query("SELECT COUNT(*) AS totalCategories FROM category");
        return $this->db->fetchAssoc($result)['totalCategories'];
    }

    public function getTotalUsers() {
        $result = $this->db->query("SELECT COUNT(*) AS totalUsers FROM form");
        return $this->db->fetchAssoc($result)['totalUsers'];
    }

    public function getStockInfo() {
        $totalQuantity = $this->db->fetchAssoc($this->db->query("SELECT SUM(quantity) AS totalQuantity FROM product"))['totalQuantity'];
        
        if (is_null($totalQuantity)) {
            $totalQuantity = 0;
        }

        $inStockCount = $this->db->fetchAssoc($this->db->query("SELECT COUNT(*) AS inStockCount FROM product WHERE quantity > 0"))['inStockCount'];
        $outOfStockCount = $this->db->fetchAssoc($this->db->query("SELECT COUNT(*) AS outOfStockCount FROM product WHERE quantity = 0"))['outOfStockCount'];

        return [
            'totalQuantity' => $totalQuantity,
            'inStockCount' => $inStockCount,
            'outOfStockCount' => $outOfStockCount
        ];
    }
}

$db = new Database($con);
$activity = new Activity($db);
$dashboard = new Dashboard($db, $activity);

$activities = $activity->getRecentActivities();
$totalProducts = $dashboard->getTotalProducts();
$totalCategories = $dashboard->getTotalCategories();
$totalUsers = $dashboard->getTotalUsers();
$stockInfo = $dashboard->getStockInfo();

$totalQuantity = $stockInfo['totalQuantity'];
$inStockCount = $stockInfo['inStockCount'];
$outOfStockCount = $stockInfo['outOfStockCount'];

echo "Total Quantity: $totalQuantity, In Stock: $inStockCount, Out of Stock: $outOfStockCount";
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
<h1>Dashboard</h1>
<div class="container">
    <div class="frame total-products">
        <div class="text-container">
            <div class="header-text">Total Products</div>
            <i class='bx bx-package'></i>
            <div class="number"><?php echo $totalProducts; ?></div>
        </div>
    </div>
    <div class="frame total-categories">
        <div class="text-container">
            <div class="header-text">Total Categories</div>
            <i class='bx bx-purchase-tag-alt'></i>
            <div class="number"><?php echo $totalCategories; ?></div>
        </div>
    </div>
    <div class="frame overall-quantity">
        <div class="text-container">
            <div class="header-text">Total Users</div>
            <i class='bx bx-group'></i> 
            <div class="number"><?php echo $totalUsers; ?></div>
        </div>
    </div>
</div>
<div class="status-box combined-chart">
    <h2 class="stock-info-header">Stock Summary</h2>
    <div class="custom-labels">
        <canvas id="combinedChart"></canvas>
        <ul style="list-style-type: none; padding: 0; margin: 0;">
            <li class="custom-label" id="inStockLabel">
                <span class="color-indicator" style="background-color: #36A2EB;"></span>
                <span class="label-title">In Stock</span>: <span class="label-value" id="inStockValue">0</span>
            </li>
            <li class="custom-label" id="outOfStockLabel">
                <span class="color-indicator" style="background-color: #FF6384;"></span>
                <span class="label-title">Out of Stock</span>: <span class="label-value" id="outOfStockValue">0</span>
            </li>
        </ul>
    </div>
</div>


<div class="recent-activities-box">
    <div class="recent-activities-header">Recent Activities</div>
    <div class="recent-activities-content">
    <ul>
        <?php if (empty($activities)): ?>
            <li>No recent activities found.</li>
        <?php else: ?>
            <?php foreach ($activities as $activity): ?>
                <li>
                    <div class="activity-description"><?php echo htmlspecialchars($activity['description']); ?></div>
                    <div class="activity-timestamp"><span class="text-muted"><?php echo date('F j, Y, g:i a', strtotime($activity['timestamp'])); ?></span></div>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
    </div>
</div>

<script>
const inStockCount = <?php echo $inStockCount; ?>;
const outOfStockCount = <?php echo $outOfStockCount; ?>;

let inStockPercentage = 0;
let outOfStockPercentage = 0;

// Only calculate percentages if there is any count for In Stock or Out of Stock
if (inStockCount + outOfStockCount > 0) {
    inStockPercentage = (inStockCount / (inStockCount + outOfStockCount)) * 100;
    outOfStockPercentage = 100 - inStockPercentage;
} else {
    // If there is no data for In Stock or Out of Stock, set both to 0
    inStockPercentage = 0;
    outOfStockPercentage = 0;
}

// Display the percentages in the custom labels
document.getElementById('inStockValue').textContent = `${Math.round(inStockPercentage)}%`;
document.getElementById('outOfStockValue').textContent = `${Math.round(outOfStockPercentage)}%`;

// Create the chart
function createCombinedChart() {
    const ctx = document.getElementById('combinedChart').getContext('2d');

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: [
                `In Stock: ${inStockCount}`, 
                `Out of Stock: ${outOfStockCount}`
            ],
            datasets: [{
                data: [inStockPercentage, outOfStockPercentage],
                backgroundColor: ['#36A2EB', '#FF6384'],  // Blue for In Stock, Red for Out of Stock
                borderWidth: 0
            }]
        },
        options: {
            cutout: '70%',
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return tooltipItem.label + ': ' + Math.round(tooltipItem.raw) + '%';
                        }
                    }
                },
                legend: {
                    display: false  // Hide the legend since we are showing it in the custom labels
                }
            }
        }
    });
}

createCombinedChart();


</script>
</body>
</html>