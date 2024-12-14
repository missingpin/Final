<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="table.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<?php
include 'connect.php';

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$sql = "SELECT * FROM product WHERE 1=1";

if (isset($_POST['typeSort']) && $_POST['typeSort'] !== 'All') {
    $typeSort = mysqli_real_escape_string($con, $_POST['typeSort']);
    $sql .= " AND type = '$typeSort'";
}

$search = isset($_POST['search']) ? mysqli_real_escape_string($con, $_POST['search']) : '';
if (!empty($search)) {
    $sql .= " AND productname LIKE '%$search%'";
}

$sort = isset($_POST['sort']) ? $_POST['sort'] : 'id';
$orderBy = [];
if ($sort === 'asc') {
    $orderBy[] = "productname ASC";
} elseif ($sort === 'desc') {
    $orderBy[] = "productname DESC";
} elseif ($sort === 'high') {
    $orderBy[] = "quantity DESC";
} elseif ($sort === 'low') {
    $orderBy[] = "quantity ASC";
} elseif ($sort === 'closest') {
    $orderBy[] = "exp DESC";
} elseif ($sort === 'farthest') {
    $orderBy[] = "exp ASC";
}

if (!empty($orderBy)) {
    $sql .= " ORDER BY " . implode(', ', $orderBy);
} else {
    $sql .= " ORDER BY id ASC";
}

$totalResult = mysqli_query($con, $sql);
$total_records = mysqli_num_rows($totalResult);
$total_pages = ceil($total_records / $limit);

$sql .= " LIMIT $limit OFFSET $offset";
$result = mysqli_query($con, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $table = '
    <table class="table table-striped table-bordered">
    <thead class="thead-dark">
        <tr>
            <th scope="col" style="text-align: center;">ID</th>
            <th scope="col" style="text-align: center;">UID</th>
            <th scope="col" style="text-align: center;">Product Image</th>
            <th scope="col" style="text-align: center;">Product Name</th>
            <th scope="col" style="text-align: center;">Product Type</th>
            <th scope="col" style="text-align: center; width: 90px;">Unit</th>
            <th scope="col" style="text-align: center;">Expiration</th>
            <th scope="col" style="text-align: center;">Sale Price</th>
            <th scope="col" style="text-align: center; width: 90px; font-size: 12px;">Pur. Price</th>
            <th scope="col" style="text-align: center;">Total Sale</th>
            <th scope="col" style="text-align: center;">Actions</th>
        </tr>
    </thead>
    <tbody>';

    $number = $offset + 1;
    while ($row = mysqli_fetch_assoc($result)) {
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
            <td scope="row">' . $number . '</td>
            <td>' . $uid . '</td>
            <td><img src="' . $image . '" width="100" alt="' . $productname . '" data-toggle="modal" data-target="#imageModal" data-img="' . $image . '"></td>
            <td>' . $productname . '</td>
            <td>' . $type . '</td>
            <td>' . $quantity . '</td>
            <td>' . $exp . '</td>
            <td>' . '₱' . $sale. '</td>
            <td>' . '₱' . $purchase. '</td>
            <td>' . '₱' . number_format($totalSale, 2) . '</td>
            <td>
    <button class="btn btn-primary btn-sm" onclick="openRestockModal(' . $id . ', ' . $quantity . ', \'' . $exp . '\')"> <i class="fas fa-plus"></i> </button>
                <button class="btn btn-primary btn-sm" onclick="editline(' . $id . ')"> <i class="fas fa-edit"></i> </button>
                <button class="btn btn-danger btn-sm" onclick="deleteline(' . $id . ')"><i class="fas fa-trash-alt"></i></button>
            </td>
        </tr>';
        $number++;
    }

    $table .= '</tbody></table>';
    echo $table;

    if ($total_pages > 1) {
        echo "<nav aria-label='Page navigation'>";
        echo "<ul class='pagination'>";
        for ($i = 1; $i <= $total_pages; $i++) {
            $activeClass = ($i === $page) ? 'active' : '';
            echo "<li class='page-item $activeClass'><a class='page-link' href='javascript:void(0)' onclick='loadPage($i)'>$i</a></li>";
        }
        echo "</ul>";
        echo "</nav>";
    }
} else {
    echo "No records found.";
}
?>

<div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Product Image</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body d-flex justify-content-center align-items-center">
                <img id="modalImage" src="" alt="" style="max-width: 100%; max-height: 90vh; object-fit: contain;">
            </div>
        </div>
    </div>
</div>
<!-- Restock Modal -->
<div class="modal fade" id="restockModal" tabindex="-1" role="dialog" aria-labelledby="restockModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="restockModalLabel">Restock Product</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="restockForm">
                    <input type="hidden" id="restockId" name="id">
                    <div class="form-group">
                        <label for="newQuantity">Additional Quantity</label>
                        <input type="number" class="form-control" id="newQuantity" name="quantity" min="1" required>
                    </div>
                    <div class="form-group">
                        <label for="newExpiration">New Expiration Date</label>
                        <input type="date" class="form-control" id="newExpiration" name="exp" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Restock</button>
                </form>
            </div>
        </div>
    </div>
</div>


<script>
    function openRestockModal(id, currentQuantity, currentExp) {
    // Set the values of the modal fields
    $('#restockId').val(id);
    $('#newQuantity').val('');  // Clear the quantity input for fresh entry
    $('#newExpiration').val(currentExp); // Pre-fill expiration date
    
    // Show the modal
    $('#restockModal').modal('show');
}

$('#restockForm').submit(function(event) {
    event.preventDefault();
    
    var id = $('#restockId').val();
    var additionalQuantity = $('#newQuantity').val(); // The additional quantity to add
    var newExp = $('#newExpiration').val(); // The new expiration date
    
    $.ajax({
        url: 'restock.php', // This is the file where you'll process the restocking
        method: 'POST',
        data: {
            id: id,
            quantity: additionalQuantity, // Send additional quantity to add
            exp: newExp
        },
        success: function(response) {
            alert('Product restocked successfully!');
            $('#restockModal').modal('hide');
            location.reload(); // Reload the page to reflect changes
        },
        error: function() {
            alert('Failed to restock product.');
        }
    });
});


    function loadPage(page) {
        const sort = $('#sort').val();
        const typeSort = $('#type-sort').val();
        
        $.ajax({
            url: "display.php?page=" + page,
            type: 'post',
            data: {
                sort: sort,
                typeSort: typeSort,
                displaySend: "true"
            },
            success: function(data) {
                $('#displaytable').html(data);
            }
        });
    }

    function displayData() {
        var display = "true";
        $.ajax({
            url: "display.php",
            type: 'post',
            data: {
                displaySend: display
            },
            success: function(data, status) {
                $('#displaytable').html(data);
            }
        });
    }

    function filterTable() {
        const input = document.getElementById('searchInput');
        const filter = input.value.toLowerCase();
        const sort = $('#sort').val();
        const typeSort = $('#type-sort').val();
        const expirationSort = $('#expiration-sort').val();

        $.ajax({
            url: "display.php",
            type: 'post',
            data: {
                search: filter,
                sort: sort,
                typeSort: typeSort,
                expirationSort: expirationSort,
                displaySend: "true"
            },
            success: function(data) {
                $('#displaytable').html(data);
            }
        });
    }

    $(document).on('click', 'img[data-toggle="modal"]', function() {
        var imgSrc = $(this).data('img');
        $('#modalImage').attr('src', imgSrc);
    });
</script>

</body>
</html>
