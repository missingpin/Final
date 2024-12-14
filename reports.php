<?php
include 'connect.php';
session_start();
include 'sidebar.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$activities = [];
$stmt = $con->prepare("SELECT activity_description, timestamp FROM activity_log ORDER BY timestamp DESC LIMIT 3");
$stmt->execute();
$stmt->bind_result($activity_desc, $timestamp);
while ($stmt->fetch()) {
    $activities[] = ['description' => $activity_desc, 'timestamp' => $timestamp];
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <link rel="stylesheet" href="reports.css">
</head>
<body>

<h1 class="main-header">Reports</h1>

<div class="graph">
<h3 class="graph-header">Sale Chart</h3>
<canvas id="myChart" style="width:100%;max-width:450px"></canvas>
</div>
<div class="recent-activities-box">
    <div class="recent-activities-header">Users Activities</div>
    <div class="recent-activities-content">
        <ul>
            <?php if (empty($activities)): ?>
                <li>No activities found.</li>
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
    <div class="see-all-container">
        <a href="activities.php" class="see-all-btn">See All Activities</a>
    </div>
</div>
<div class="container mt-5"> 
    <div class="sale-container"> 
        <div class="frame total-sale-day"> 
            <div class="text-container"> 
                <div class="header-text">Total sale of the Day</div> <div class="number">₱0.00</div> 
            </div> 
        </div> 
        <div class="frame total-sale-week"> 
            <div class="text-container"> 
                <div class="header-text">Total sale of the Week
                </div> 
                <div class="number">₱0.00
                </div> 
            </div> 
        </div> 
        <div class="frame total-sale-month"> 
            <div class="text-container"> 
                <div class="header-text">Total sale of the Month</div> <div class="number">₱0.00</div> 
            </div> 
        </div> 
    </div> 
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
<script>
const xValues = [50,60,70,80,90,100,110,120,130,140,150];
const yValues = [7,9,8,9,9,9,10,11,14,14,15];

new Chart("myChart", {
  type: "line",
  data: {
    labels: xValues,
    datasets: [{
      fill: false,
      lineTension: 0,
      backgroundColor: "rgba(0,0,255,1.0)",
      borderColor: "rgba(0,0,255,0.1)",
      data: yValues
    }]
  },
  options: {
    legend: {display: false},
    scales: {
      yAxes: [{ticks: {min: 6, max:16}}],
    }
  }
});
</script>

</body>
</html>
