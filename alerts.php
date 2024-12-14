<?php
session_start();
include 'connect.php';
include 'sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Alerts</title>
    <link rel="stylesheet" href="alerts.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<h1 class="header">Alerts</h1>

<table class="table table-striped table-bordered" id="categoryTable">
    <thead class="thead-dark">
        <tr>
            <th scope="col" style="text-align: center;">ID</th>
            <th scope="col" style="text-align: center;">Product</th>
            <th scope="col" style="text-align: center;">Alert Type</th>
            <th scope="col" style="text-align: center;">Message</th>
        </tr>
    </thead>
    <tbody id="alertTableBody">
    </tbody>
</table>

<script>
$(document).ready(function() {
    $.ajax({
        url: 'alertcheck.php',
        method: 'GET',
        dataType: 'json',
        success: function(alerts) {
            if (alerts.length > 0) {
                let alertRows = '';
                alerts.forEach(function(alert, index) {
                    let alertType = alert.type === 'low-stock' ? 'Low Stock' : 'Expiration';
                    let productName = '';
                    let message = alert.message;

                    if (alert.type === 'low-stock') {
                        let parts = alert.message.split('Low stock for:');
                        if (parts.length > 1) {
                            productName = parts[1].split('-')[0].trim();
                            let stockLeft = parts[1].split('-')[1].trim();
                            message = `This product has a unit of ${stockLeft}, please restock!`;
                        }
                    }

                    if (alert.type === 'expiration') {
                        let parts = alert.message.split('is about to expire in');
                        if (parts.length > 1) {
                            productName = parts[0].trim();
                            let expirationDays = parts[1].split('days')[0].trim();
                            message = `This product is about to expire in ${expirationDays} days, please replace or pull-out the product!`;
                        }
                    }

                    alertRows += 
                        `<tr>
                            <td style="text-align: center;">${index + 1}</td>
                            <td style="text-align: center;">${productName}</td>
                            <td style="text-align: center;">${alertType}</td>
                            <td style="text-align: center;">${message}</td>
                        </tr>`;
                });
                $('#alertTableBody').html(alertRows);
            } else {
                $('#alertTableBody').html('<tr><td colspan="4" style="text-align: center;">No alerts found.</td></tr>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error fetching alerts: ', error);
            $('#alertTableBody').html('<tr><td colspan="4" style="text-align: center;">Failed to load alerts.</td></tr>');
        }
    });
});
</script>

</body>
</html>
