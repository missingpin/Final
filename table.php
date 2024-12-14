<?php
session_start();
include 'connect.php';
include 'sidebar.php';
$errorMessages = [];

$result = $con->query("SELECT ptype FROM category");
$options = '';

while ($row = $result->fetch_assoc()) {
    $options .= "<option value='{$row['ptype']}'>{$row['ptype']}</option>";
}

if (!empty($errorMessages)) {
    file_put_contents('error_log.txt', implode("\n", $errorMessages), FILE_APPEND);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="table.css">
    <title>Table</title>
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css">
</head>

<body>
    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Products</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="importForm" enctype="multipart/form-data" method="POST" action="import.php">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="file">Choose Excel File</label>
                            <input type="file" class="form-control" id="file" name="file" accept=".xlsx, .xls">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="productmodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered custom-modal" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Product</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                <label class="btn btn-primary" id="scan-button">Scan</label>
                <div id="scanner-container">
    <video id="barcode-video" autoplay></video>
</div>
                    <div class="form-group">
                        <label for="productname">Product Name</label>
                        <input type="text" class="form-control" id="productname" placeholder="Insert Product Name">
                    </div>
                    <div class="form-group">
                        <label for="productname">UID</label>
                        <input type="number" class="form-control" id="uid" placeholder="Insert UID">
                    </div>
                    <div class="form-group">
                        <label for="type">Product Type:</label>
                        <select id="type" class="form-control">
                            <option value="" disabled selected>Select a Product Type</option>
                            <?php echo $options; ?>
                        </select>
                    </div>
                    <div class="form-group">
    <label for="etype">Expiry Type</label>
    <select id="etype" class="form-control">
        <option value="Expiry">Expiry</option>
        <option value="Non-Expiry">Non-Expiry</option>
    </select>
</div>

                    <div class="form-row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="quantity">Unit</label>
                                <input type="number" class="form-control" id="quantity"
                                    placeholder="Insert the Unit of Product" min="1">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="exp">Expiration</label>
                                <?php
                                $currentDate = date('Y-m-d');
                                $futureDate = date('Y-m-d', strtotime("+2 months"));
                                ?>
                                <input type="date" class="form-control" id="exp" placeholder="Insert Expiration Date"
                                    min="<?php echo $futureDate; ?>">
                            </div>
                        </div>

                    </div>
                    <div class="form-row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sale">Sale Price</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">₱</span>
                                    </div>
                                    <input type="number" class="form-control" id="sale" min="0.01">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="purchase">Purchase Price</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">₱</span>
                                    </div>
                                    <input type="number" class="form-control" id="purchase" min="0.01">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="image">Image</label>
                        <input type="file" class="form-control" id="image" placeholder="Insert Product Image">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" onclick="addproduct()">Insert</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div class="modal fade" id="editmodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered custom-modal" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Product</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="editproductname">Product Name</label>
                        <input type="text" class="form-control" id="editproductname" placeholder="Insert Product Name">
                    </div>
                    <div class="form-group">
                        <label for="edittype">Product Type:</label>
                        <select id="edittype" class="form-control">
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="editquantity">Unit</label>
                        <input type="number" class="form-control" id="editquantity"
                            placeholder="Insert the Unit of Product" min="1">
                    </div>
                    <div class="form-group">
                        <label for="editexp">Expiration</label>
                        <input type="date" class="form-control" id="editexp" placeholder="Insert Expiration Date"
                            min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="sale">Sale Price</label>
                        <input type="number" class="form-control" id="editsale" placeholder="Insert Product Price"
                            min="0.01">
                    </div>
                    <div class="form-group">
                        <label for="purchase">Purchase Price</label>
                        <input type="number" class="form-control" id="editpurchase" placeholder="Insert Product Price"
                            min="0.01">
                    </div>
                    <div class="form-group">
                        <label for="editimage">Image</label>
                        <input type="file" class="form-control" id="editimage" placeholder="Insert Product Image">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" onclick="showUpdateConfirmation()">Update</button>
                    <input type="hidden" id="hiddendata">
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Update Modal -->
    <div class="modal fade" id="confirmUpdateModal" tabindex="-1" role="dialog" aria-labelledby="confirmUpdateTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered custom-modal" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmUpdateTitle">Confirm Update</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to update this item?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" id="confirmUpdateButton">Update</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Delete Modal -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered custom-modal" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteTitle">Confirm Deletion</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this item?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteButton">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter/Sort Modal -->
    <div class="modal fade" id="filterModal" tabindex="-1" role="dialog" aria-labelledby="filterModalTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered custom-modal" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Filter/Sort Products</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="sort">Sort by</label>
                        <select id="sort" class="form-control">
                            <option value="id">Default</option>
                            <option value="asc">Name Ascending(A-Z)</option>
                            <option value="desc">Name Descending(Z-A)</option>
                            <option value="high">Highest Quantity</option>
                            <option value="low">Lowest Quantity</option>
                            <option value="farthest">Closest Expiry Date</option>
                            <option value="closest">Farthest Expiry Date</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="revertFilters" class="btn btn-warning"
                        data-dismiss="modal">Revert</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" id="apply-filters">Apply Filters</button>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-3">
    <h1 class="text-center">Inventory</h1>
    <div class="card p-1">
        <div class="d-flex justify-content-between align-items-center my-2">
        <div class="button-container d-flex">
        <button type="button" class="btn btn-info mr-2" data-toggle="modal" data-target="#productmodal">Add Product</button>
                <button type="button" class="btn btn-info mr-2" data-toggle="modal" data-target="#filterModal">Filter/Sort</button>
                <button type="button" class="btn btn-info mr-2" data-toggle="modal" data-target="#importModal">
                    <i class='bx bx-upload' style="font-size: 20px;"></i>
                </button>
                <button type="button" class="btn btn-success mr-3" onclick="exportProducts()">
                    <i class='bx bx-download' style="font-size: 20px;"></i>
                </button>
            </div>
            <div>
                <input type="text" id="searchInput" class="search-input" placeholder="Search..." onkeyup="filterTable()">
            </div>
        </div>
    </div>
    <div id="displaytable"></div>
</div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@ericblade/quagga2/dist/quagga.min.js"></script>

    <script>
    let videoStream = null;  // To store the video stream for cleanup

function startScanner() {
    // Set up the video stream from the camera
    navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
        .then(function (stream) {
            const videoElement = document.getElementById("barcode-video");
            videoElement.srcObject = stream;
            videoStream = stream;  // Store the stream for later cleanup

            // Initialize QuaggaJS barcode scanner
            Quagga.init({
                inputStream: {
                    name: "Live",
                    type: "LiveStream",
                    target: videoElement, // The video element
                    constraints: {
                        facingMode: "environment", // Back camera for mobile devices
                        width: 640,
                        height: 480
                    }
                },
                decoder: {
                    readers: ["code_128_reader", "ean_reader", "ean_8_reader", "code_39_reader", "upc_reader", "upc_e_reader"]
                }
            }, function (err) {
                if (err) {
                    console.error(err);
                    return;
                }
                Quagga.start(); // Start the scanner
            });

            // Event listener for barcode detection
            Quagga.onDetected(function (result) {
                const barcode = result.codeResult.code; // Get the barcode data
                document.getElementById("uid").value = barcode;  // Fill the UID input with the barcode

                // Stop the scanner and hide the camera
                Quagga.stop();
                stopCamera(videoStream);

                // Optionally, send the barcode data to your PHP backend using AJAX
                sendBarcodeToServer(barcode);

                // Hide the scanner UI
                const scannerContainer = document.getElementById("scanner-container");
                scannerContainer.style.display = "none";
            });
        }).catch(function (err) {
            console.error("Error accessing the camera: ", err);
        });
}

function stopCamera(stream) {
    // Stop all video tracks of the stream to release the camera
    if (stream) {
        const tracks = stream.getTracks();
        tracks.forEach(function(track) {
            track.stop();
        });
    }
}

function sendBarcodeToServer(barcode) {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "process_barcode.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            console.log("Server Response: ", xhr.responseText);
        }
    };
    xhr.send("barcode=" + barcode);
}

// Start the scanner when the "Scan" button is clicked
document.getElementById("scan-button").addEventListener("click", function() {
    const scannerContainer = document.getElementById("scanner-container");
    scannerContainer.style.display = "block"; // Show the scanner
    startScanner(); // Start the scanner after making it visible
});

// Add an event listener for the modal being hidden or removed
$('#productmodal').on('hidden.bs.modal', function () {
    // Stop the camera if the modal is closed
    if (videoStream) {
        stopCamera(videoStream);
    }
    // Hide the scanner UI
    const scannerContainer = document.getElementById("scanner-container");
    scannerContainer.style.display = "none";
});

        function exportProducts() {
            window.location.href = 'export.php';
        }


        $(document).ready(function () {
            displayData();
        });

        function displayData(page = 1) {
            $.ajax({
                url: "display.php?page=" + page,
                type: 'post',
                data: { displaySend: "true" },
                success: function (data) {
                    $('#displaytable').html(data);
                    $('#displaytable img').each(function () {
                        if ($(this).attr('src') === '') {
                            $(this).attr('src', 'C:/xampp/htdocs/no-image.jpg');
                            $(this).attr('alt', 'No image available');
                        }
                    });
                }
            });
        }

        function loadPage(page) {
            displayData(page);
        }

        function isNonNegative(number) {
            return number >= 1;
        }

        function isFutureDate(date) {
            return date >= new Date().toISOString().split('T')[0];
        }
        function isAtLeastTwoMonthsFromToday(expirationDate) {
            var currentDate = new Date();
            var futureDate = new Date(currentDate);
            futureDate.setMonth(currentDate.getMonth() + 2);

            var expDate = new Date(expirationDate);

            return expDate >= futureDate;
        }
        const searchInput = document.getElementById('searchInput');

        searchInput.addEventListener('keydown', function (e) {
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

        $(document).ready(function () {
    function handleExpirationField() {
        var etype = $('#etype').val();

        if (etype === "Non-Expiry") {
            $('#exp').prop('disabled', true);
            $('#exp').val("");
            $('#exp-label').text("None");
        } else {
            $('#exp').prop('disabled', false);
            $('#exp-label').text("");
        }
    }

    $('#productmodal').on('show.bs.modal', function () {
        handleExpirationField(); // Call the function to check the `etype` on modal open
    });

    $('#etype').on('change', function () {
        handleExpirationField(); // Adjust expiration field when `etype` is changed
    });
});


        function addproduct() {
    var productname = $('#productname').val();
    var quantity = $('#quantity').val();
    var expiration = $('#exp').val();
    var type = $('#type').val();
    var sale = $('#sale').val();
    var purchase = $('#purchase').val();
    var uid = $('#uid').val();
    var etype = $('#etype').val();

    // If Expiry Type is Non-Expiry, set expiration to 'None' and skip validation
    if (etype === "Non-Expiry") {
        expiration = "None";  // Set expiration as 'None' for non-expiry items
    }

    $.ajax({
        url: 'checkProductname.php',
        type: 'POST',
        data: { productname: productname },
        success: function (response) {
            if (response === 'exists') {
                alert("The product name already exists. Please choose a different name.");
                return; // Stop further execution if the product name exists
            }

            if (!isNonNegative(quantity)) {
                alert("Quantity cannot be negative or zero!");
                return;
            }

            // If Expiry Type is Expiry, validate expiration date
            if (etype === "Expiry") {
                if (!isFutureDate(expiration)) {
                    alert("Expiration date cannot be in the past!");
                    return;
                }

                // Check if the expiration date is at least 2 months from today
                if (!isAtLeastTwoMonthsFromToday(expiration)) {
                    alert("Expiration date must be at least 2 months from today!");
                    return;
                }
            }

            var formData = new FormData();
            formData.append('productSend', productname);
            formData.append('quantitySend', quantity);
            formData.append('expirationSend', expiration);
            formData.append('typeSend', type);
            formData.append('saleSend', sale);
            formData.append('purchaseSend', purchase);
            formData.append('uidSend', uid);
            formData.append('etypeSend', etype);

            var fileInput = $('#image')[0];
            if (fileInput.files.length > 0) {
                var file = fileInput.files[0];
                formData.append('imageSend', file, file.name);
            } else {
                console.error("No file selected.");
                return;
            }

            $.ajax({
                url: "insert.php",
                type: 'post',
                data: formData,
                processData: false,
                contentType: false,
                success: function () {
                    displayData();
                },
                error: function (xhr, status, error) {
                    console.error("Error inserting product:", error);
                    alert("Failed to add product. Please try again.");
                },
                complete: function () {
                    $('#productmodal').modal('hide');
                    $('#productname').val('');
                    $('#type').val('');
                    $('#quantity').val('');
                    $('#exp').val('');
                    $('#sale').val('');
                    $('#purchase').val('');
                    $('#uid').val('');
                    $('#image').val('');
                    $('#etype').val('');
                    $('.modal-backdrop').remove();
                }
            });
        }
    });
}


        let itemToDelete = null;

        function deleteline(deleteval) {
            itemToDelete = deleteval;
            $('#confirmDeleteModal').modal('show');
        }

        $('#confirmDeleteButton').on('click', function () {
            if (itemToDelete) {
                $.ajax({
                    url: "delete.php",
                    type: 'post',
                    data: { deletedata: itemToDelete },
                    success: function () {
                        displayData();
                        $('#confirmDeleteModal').modal('hide');
                    }
                });
            }
        });

        function editline(editval) {
            $('#hiddendata').val(editval);
            $.post("edit.php", { editval: editval }, function (data) {
                var userid = JSON.parse(data);
                $('#editproductname').val(userid.productname);
                $('#edituid').val(userid.uid);
                $('#editquantity').val(userid.quantity);
                $('#editexp').val(userid.exp);
                $('#edittype').val(userid.type);
                $('#editsale').val(userid.sale);
                $('#editpurchase').val(userid.purchase);
                $('#editimage').val(userid.image);
                $('#editetype').val(userid.etype);
            });
            $('#editmodal').modal("show");
        }

        function showUpdateConfirmation() {
            $('#confirmUpdateModal').modal('show');
        }

        $('#confirmUpdateButton').on('click', function () {
            updateproduct();
            $('#confirmUpdateModal').modal('hide');
        });

        function updateproduct() {
            var editproductname = $('#editproductname').val();
            var editquantity = $('#editquantity').val();
            var editexp = $('#editexp').val();
            var edittype = $('#edittype').val();
            var editsale = $('#editsale').val();
            var editpurchase = $('#editpurchase').val();
            var hiddendata = $('#hiddendata').val();
            var editetype = $('#editetype').val();

            if (!isNonNegative(editquantity)) {
                alert("Quantity cannot be negative or zero!");
                return;
            }
            if (!isFutureDate(editexp)) {
                alert("Expiration date cannot be in the past!");
                return;
            }

            $.post("edit.php", {
                editproductname: editproductname,
                editquantity: editquantity,
                editexp: editexp,
                edittype: edittype,
                editetype: editetype,
                editsale: editsale,
                editpurchase: editpurchase,
                editdescription: editdescription,
                hiddendata: hiddendata
            }, function () {
                $('#editmodal').modal("hide");
                displayData();
            });
        }

        function resetFilters() {
            $('#sort').val('id'); // Default to ID sorting
            $('#type-sort').val('All'); // Reset to show all types
            $('#expiration-sort').val('id'); // Reset expiration sort to default

            // Optionally, refresh the displayed data to reflect the default state
            displayData();
        }

        $(document).ready(function () {
            displayData();

            // Attach click event to the revert button
            $('#revertFilters').on('click', function () {
                resetFilters(); // Call reset function when clicked
            });
        });

        $('#apply-filters').on('click', function () {
            const sort = $('#sort').val();
            const typeSort = $('#type-sort').val();
            const expirationSort = $('#expiration-sort').val();

            $.ajax({
                url: "display.php",
                type: 'post',
                data: {
                    sort: sort,
                    typeSort: typeSort,
                    expirationSort: expirationSort,
                    displaySend: "true"
                },
                success: function (data) {
                    $('#displaytable').html(data);
                    $('#filterModal').modal('hide'); // Ensure modal is hidden
                    $('.modal-backdrop').remove(); // Remove backdrop
                }
            });
        });
    </script>
</body>
</html>