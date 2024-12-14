<?php
session_start();
include 'connect.php';
include 'sidebar.php';

$error_message = '';

$categories_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $categories_per_page;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ptype = $_POST['ptype'];

    $stmt = $con->prepare("SELECT COUNT(*) FROM category WHERE ptype = ?");
    $stmt->bind_param("s", $ptype);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        $error_message = "Category '$ptype' already exists!";
    } else {
        $stmt = $con->prepare("INSERT INTO category (ptype) VALUES (?)");
        $stmt->bind_param("s", $ptype);
        $stmt->execute();
        $stmt->close();

        $activity_desc = "Added a new category: $ptype";
        $stmt = $con->prepare("INSERT INTO activity_log (activity_description) VALUES (?)");
        $stmt->bind_param("s", $activity_desc);
        $stmt->execute();
        $stmt->close();
    }
}

$result = $con->query("SELECT COUNT(*) AS total FROM category");
$row = $result->fetch_assoc();
$total_categories = $row['total'];
$total_pages = ceil($total_categories / $categories_per_page);

$stmt = $con->prepare("SELECT * FROM category LIMIT ?, ?");
$stmt->bind_param("ii", $offset, $categories_per_page);
$stmt->execute();
$result = $stmt->get_result();

$categories = '';
$number = $offset + 1;
while ($row = $result->fetch_assoc()) {
    $id = $row['id'];
    $categories .= "<tr>
        <td style='text-align: center;'>$number</td>
        <td style='text-align: center;' class='category-name'>{$row['ptype']}</td>
        <td>
            <button class='btn btn-primary btn-sm' onclick='editline($id)'> <i class='fas fa-edit'></i> </button>
            <button class='btn btn-danger btn-sm' onclick='deleteline($id)'><i class='fas fa-trash-alt'></i></button>
        </td>
    </tr>";
    $number++;
}
$stmt->close();

$table = '<table class="table table-striped table-bordered" id="categoryTable">
    <thead class="thead-dark">
        <tr>
            <th scope="col" style="text-align: center;">ID</th>
            <th scope="col" style="text-align: center;">Category</th>
            <th scope="col" style="text-align: center;">Actions</th> 
        </tr>
    </thead>
    <tbody>
        ' . $categories . '
    </tbody>
</table>';

$pagination = '<nav aria-label="Page navigation">
    <ul class="pagination justify-content-start">';
if ($current_page > 1) {
    $pagination .= '<li class="page-item"><a class="page-link" href="?page=' . ($current_page - 1) . '">Previous</a></li>';
}
for ($i = 1; $i <= $total_pages; $i++) {
    $pagination .= '<li class="page-item ' . ($i == $current_page ? 'active' : '') . '">
        <a class="page-link" href="?page=' . $i . '">' . $i . '</a>
    </li>';
}
if ($current_page < $total_pages) {
    $pagination .= '<li class="page-item"><a class="page-link" href="?page=' . ($current_page + 1) . '">Next</a></li>';
}
$pagination .= '</ul>
</nav>';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="categories.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Categories</title>
</head>
<body>

<div class="container mt-5">
    <h1 class="header">Categories</h1>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addCategoryModal">
        Add Category
    </button>
    <input type="text" id="searchInput" class="search-input" placeholder="Search..." onkeyup="filterCategories()">

    <?php if ($error_message): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <?php echo $table; ?>
    <?php echo $pagination; ?>

    <div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryModalLabel">Add New Category</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="categoryForm" method="POST" action="">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="ptype">Category Name</label>
                            <input type="text" class="form-control" id="ptype" name="ptype" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const ptypeInput = document.getElementById('ptype');

ptypeInput.addEventListener('keydown', function(e) {
if (
  e.key === 'Backspace' || 
  e.key === 'Delete' || 
  (e.keyCode >= 37 && e.keyCode <= 40) ||
  e.key === 'Tab' ||
  e.key === ' '
) {
  return;
}

if (/[^A-Za-z0-9]/.test(e.key)) {
  e.preventDefault();
}
});

const searchInput = document.getElementById('searchInput');

searchInput.addEventListener('keydown', function(e) {
if (
  e.key === 'Backspace' || 
  e.key === 'Delete' || 
  (e.keyCode >= 37 && e.keyCode <= 40) ||
  e.key === 'Tab' ||
  e.key === ' '
) {
  return;
}

if (/[^A-Za-z0-9]/.test(e.key)) {
  e.preventDefault();
}
});

    $('#addCategoryModal').on('hidden.bs.modal', function () {
        $('#categoryForm')[0].reset();
    });

    function filterCategories() {
        const searchInput = document.getElementById('searchInput').value.toLowerCase();
        const categoryTable = document.getElementById('categoryTable');
        const rows = categoryTable.getElementsByTagName('tr');

        for (let i = 1; i < rows.length; i++) {
            const categoryName = rows[i].getElementsByClassName('category-name')[0].textContent.toLowerCase();
            if (categoryName.includes(searchInput)) {
                rows[i].style.display = '';
            } else {
                rows[i].style.display = 'none';
            }
        }
    }
</script>
</body>
</html>
