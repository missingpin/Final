<?php
include 'connect.php';
session_start();
include 'sidebar.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($currentPage - 1) * $limit;

$stmt = $con->prepare("SELECT COUNT(*) FROM activity_log");
$stmt->execute();
$stmt->bind_result($totalActivities);
$stmt->fetch();
$stmt->close();

$activities = [];
$stmt = $con->prepare("SELECT activity_description, timestamp FROM activity_log ORDER BY timestamp DESC LIMIT ? OFFSET ?");
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$stmt->bind_result($activity_desc, $timestamp);
while ($stmt->fetch()) {
    $activities[] = ['description' => $activity_desc, 'timestamp' => $timestamp];
}
$stmt->close();

$totalPages = ceil($totalActivities / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Activities</title>
    <link rel="stylesheet" href="reports.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css">
</head>
<body>

<h1 class="header">All User Activities</h1>
<div class="return-container">
    <a href="reports.php" class="return-btn"> <- Return</a>
</div>
<div class="container mt-5">
    <table class="table table-striped table-bordered">
        <thead class="thead-dark">
            <tr>
                <th scope="col" style="text-align: center;">#</th>
                <th scope="col" style="text-align: center;">User ID</th>
                <th scope="col" style="text-align: center;">Activity Description</th>
                <th scope="col" style="text-align: center;">Timestamp</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($activities)): ?>
                <tr>
                    <td colspan="3" style="text-align: center;">No activities found.</td>
                </tr>
            <?php else: ?>
                <?php $number = $offset + 1; ?>
                <?php foreach ($activities as $activity): ?>
                    <tr>
                        <td style="text-align: center;"><?php echo $number++; ?></td>
                        <td style="text-align: center;"></td>
                        <td><?php echo htmlspecialchars($activity['description']); ?></td>
                        <td style="text-align: center;"><?php echo date('F j, Y, g:i a', strtotime($activity['timestamp'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <?php if ($currentPage > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $currentPage - 1; ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($currentPage < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $currentPage + 1; ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>

</div>

</body>
</html>
