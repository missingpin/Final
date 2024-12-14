<?php
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';
$level = isset($_SESSION['level']) ? $_SESSION['level'] : 1;
$current_page = basename($_SERVER['PHP_SELF']);
$sql = "SELECT profile FROM form WHERE username = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($profile_picture);

$default_profile_image = 'defaultprofile.jpg';
$user_profile_picture = $default_profile_image;

if ($stmt->num_rows > 0) {
    $stmt->fetch();
    if (!empty($profile_picture)) {
        $user_profile_picture = $profile_picture;
    }
}

$stmt->close();
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="sidebar.css">
    <script src="header.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<div class="overlay" id="overlay"></div>
<div id="menu-toggle">
    <div class="topbar">
        <i class='bx bx-bell' id="alert-icon" style="font-size: 24px; color: white; margin-left: 20px; cursor: pointer;" onclick="toggleAlerts(); fetchLowStockProducts();">
            <span id="alert-badge" class="notification-badge">!</span>
        </i>
    </div>
</div>

<div id="alert-container" class="alert-container">
    <div id="low-stock-alerts" class="low-stock-alerts"></div>
    <a href="alerts.php" class="go-to-alerts">Go to Alerts</a>
    
</div>

<div class="sidebar">    
    <div class="logo-details">
        <div class="logo_name">SIBULO STORE</div>
        <i class='bx bx-menu' id="btn"></i> <!-- HAMBORGER BUTTON -->
    </div>


    <!-- Sidebar menu -->
    <ul class="nav-list">
        <li class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
            <a href="dashboard.php">
                <i class='bx bx-pie-chart-alt-2'></i>
                <span class="links_name">Dashboard</span>
            </a>
            <span class="tooltip">Dashboard</span>
        </li>
        <li class="<?php echo ($current_page == 'categories.php') ? 'active' : ''; ?>">
            <a href="categories.php">
                <i class='bx bx-purchase-tag'></i>
                <span class="links_name">Categories</span>
            </a>
            <span class="tooltip">Categories</span>
        </li>
        <li class="<?php echo ($current_page == 'table.php') ? 'active' : ''; ?>" id="products-menu">
            <a href="#.php" id="products-button">
                <i class='bx bx-box'></i>
                <span class="links_name">Inventory</span>
            </a>
            <ul class="dropdown-menu">
                <li><a href="table.php">Catalog</a></li>
                <li><a href="checkout.php">Checkout</a></li>
            </ul>
            <span class="tooltip">Inventory</span>
        </li>
        <li class="<?php echo ($current_page == 'stock.php') ? 'active' : ''; ?>">
            <a href="stock.php">
                <i class='bx bx-grid-alt'></i>
                <span class="links_name">Stock</span>
            </a>
            <span class="tooltip">Stock</span>
        </li>
        <?php if ($level == 1): ?>
            <li class="<?php echo ($current_page == 'alerts.php') ? 'active' : ''; ?>">
                <a href="alerts.php">
                    <i class='bx bx-notification'></i>
                    <span class="links_name">Alerts</span>
                </a>
                <span class="tooltip">Alerts</span>
            </li>
        <?php endif; ?>
        <?php if ($level == 2): ?>
            <li class="<?php echo ($current_page == 'admindashboard.php') ? 'active' : ''; ?>">
                <a href="admindashboard.php">
                    <i class='bx bx-user'></i>
                    <span class="links_name">Users</span>
                </a>
                <span class="tooltip">Users</span>
            </li>
            <li class="<?php echo ($current_page == 'reports.php') ? 'active' : ''; ?>">
                <a href="reports.php">
                    <i class='bx bx-stats'></i>
                    <span class="links_name">Reports</span>
                </a>
                <span class="tooltip">Reports</span>
            </li>
        <?php endif; ?>
        <li class="profile">
        <a href="userprofile.php" class="profile-link">
        <div class="profile-details">
            <img src="<?php echo $user_profile_picture; ?>" alt="profileImg" class="profile-img">
            
            <div class="name_job">
                <div class="name"><?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'User'; ?></div>
            </div>
        </div>
    </a>
    <a href="logout.php" class="logout-icon">
        <i class='bx bx-log-out' id="log_out"></i>
    </a>
</li>
    </ul>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        let sidebar = document.querySelector(".sidebar");
        let closeBtn = document.querySelector("#btn");
        let overlay = document.getElementById("overlay");
        let productsMenu = document.getElementById("products-menu");
        let dropdownMenu = productsMenu.querySelector(".dropdown-menu");

        closeBtn.addEventListener("click", () => {
            sidebar.classList.toggle("open");
            menuBtnChange();

            if (sidebar.classList.contains("open")) {
                overlay.classList.add('show');
            } else {
                overlay.classList.remove('show');
            }
        });

        overlay.addEventListener('click', () => {
            sidebar.classList.remove("open");
            overlay.classList.remove('show');
            menuBtnChange();
        });

        document.addEventListener('click', function (event) {
            if (!sidebar.contains(event.target) && !closeBtn.contains(event.target) && !overlay.contains(event.target)) {
                sidebar.classList.remove("open");
                overlay.classList.remove('show');
                menuBtnChange();
            }
            });
        
        document.getElementById("products-button").addEventListener("click", function(e) {
            e.preventDefault();
            dropdownMenu.classList.toggle("show");
        });

        function menuBtnChange() {
            if (sidebar.classList.contains("open")) {
                closeBtn.classList.replace("bx-menu", "bx-menu-alt-right");
            } else {
                closeBtn.classList.replace("bx-menu-alt-right", "bx-menu");
            }
        }
    });

    // Alert functionality
    document.addEventListener("DOMContentLoaded", function () {
        fetchLowStockProducts();
        setInterval(fetchLowStockProducts, 10000);
        document.addEventListener('click', function (event) {
            const alertContainer = document.getElementById('alert-container');
            if (!alertContainer.contains(event.target) && !event.target.classList.contains('bx-bell')) {
                alertContainer.style.display = 'none';
            }
        });
    });

    function toggleAlerts() {
        const alertContainer = document.getElementById('alert-container');
        alertContainer.style.display = alertContainer.style.display === 'none' ? 'block' : 'none';
    }

    function fetchLowStockProducts() {
        console.log("Fetching low stock products...");
        fetch('alertcheck.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log("Fetched alerts:", data);
                displayLowStockAlerts(data);
            })
            .catch(error => console.error('Error fetching low stock products:', error));
    }

    function displayLowStockAlerts(products) {
        const alertContainer = document.getElementById('low-stock-alerts');
        const alertBadge = document.getElementById('alert-badge');
        alertContainer.innerHTML = '';

        if (products.length === 0) {
            alertContainer.innerHTML = '<p>No low stock alerts.</p>';
            alertBadge.style.display = 'none'; // Hide badge if no alerts
        } else {
            const limitedProducts = products.slice(0, 5);
            limitedProducts.forEach(product => {
                const alertItem = document.createElement('div');
                alertItem.className = 'alert alert-warning';
                alertItem.innerHTML = product.message; // Ensure product.message is defined
                alertContainer.appendChild(alertItem);
            });
            alertBadge.style.display = 'inline-block'; // Show badge if alerts are present
        }
    }
</script>

</body>
</html>