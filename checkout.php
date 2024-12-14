<?php
session_start();
include 'connect.php';
include 'sidebar.php';

$sql = "SELECT * FROM product";
$result = mysqli_query($con, $sql);

if (!$result) {
    die("Error fetching products: " . mysqli_error($con));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="checkout.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://unpkg.com/quagga/dist/quagga.min.js"></script> <!-- Barcode Scanner Library -->
</head>
<body>
<div class="container mt-5">
    <h2 class="header">Checkout</h2>
    <div class="row">
        <?php
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $productId = $row['id'];
                $image = $row['image'] ? 'uploads/' . $row['image'] : 'no-image.jpg';
                $productName = htmlspecialchars($row['productname']);
                $salePrice = htmlspecialchars($row['sale']);
                ?>
                <div class="col-md-2 col-sm-8 col-12">
                    <div class="product-card" onclick="addToCart(<?php echo $productId; ?>, '<?php echo $productName; ?>', '<?php echo $image; ?>', <?php echo $salePrice; ?>)">
                        <img src="<?php echo $image; ?>" class="product-image" alt="<?php echo $productName; ?>">
                        <div class="product-info">
                            <div class="product-name"><?php echo $productName; ?></div>
                            <div class="product-price">₱<?php echo number_format($salePrice, 2); ?></div>
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            echo "<p>No products found.</p>";
        }
        ?>
    </div>
</div>

<!-- Modal for Barcode Scanner -->
<div class="modal fade" id="scannerModal" tabindex="-1" role="dialog" aria-labelledby="scannerModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scannerModalLabel">Barcode Scan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <video id="barcode-video" width="100%" height="100%" autoplay></video>
                <div id="productInfo" style="margin-top: 15px;"></div>
                <div id="scannedUID" style="margin-top: 15px;"></div>

                <div id="scannedProductsList" style="margin-top: 20px; padding: 10px; border-top: 1px solid #ddd;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="checkoutScannedProducts()">Checkout</button>
            </div>
        </div>
    </div>
</div>

<!-- Floating Cart and Scan Buttons Container -->
<div id="buttonsContainer">
    <button id="floatingCartButton" class="btn btn-primary" data-toggle="modal" data-target="#cartModal">
        <i class="fas fa-shopping-cart"></i>Manual Cart
    </button>
    <button id="scanButton" class="btn btn-primary" data-toggle="modal" data-target="#scannerModal">
        <i class="fas fa-qrcode"></i> Use Scan
    </button>
</div>


<!-- Modal for Cart -->
<div class="modal fade" id="cartModal" tabindex="-1" role="dialog" aria-labelledby="cartModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cartModalLabel">Your Cart</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="cartItemsList">
                    <!-- Cart items will be dynamically inserted here -->
                </div>
                <hr>
                <div class="total-amount">
                    <strong>Total: ₱<span id="totalAmount">0.00</span></strong>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="processCheckout()">Proceed to Checkout</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script src="https://kit.fontawesome.com/a076d05399.js"></script>

<script>
let scannedProducts = [];  
let cart = []; // The cart array where selected products will be stored
let isCooldown = false;  
let cooldownTime = 5000;  // cooldown to prevent multiple barcode scans in quick succession

function addToCart(id, name, image, price) {
    const existingProductIndex = cart.findIndex(product => product.id === id);
    if (existingProductIndex >= 0) {
        // If the product is already in the cart, increase the quantity
        cart[existingProductIndex].quantity += 1;
    } else {
        // Otherwise, add a new product to the cart
        cart.push({ id, name, image, price, quantity: 1 });
    }
    updateCartUI(); // Update the cart UI to reflect changes
}

function updateCartUI() {
    const cartItemsList = document.getElementById("cartItemsList");
    cartItemsList.innerHTML = '';  // Clear the current cart items list

    let total = 0;

    // Loop through each item in the cart and display it
    cart.forEach(item => {
        total += item.price * item.quantity;

        cartItemsList.innerHTML += `
            <div class="cart-item">
                <img src="${item.image}" class="cart-item-image" alt="${item.name}">
                <div class="cart-item-details">
                    <strong>${item.name}</strong><br>
                    ₱${parseFloat(item.price).toFixed(2)} x ${item.quantity} = ₱${(item.price * item.quantity).toFixed(2)}
                </div>
            </div>
            <hr>
        `;
    });

    // Update the total price
    document.getElementById("totalAmount").innerText = total.toFixed(2);
}

function checkProductByUID(barcode) {
    $.ajax({
        url: 'checkoutbarcode.php',  
        method: 'POST',
        data: { barcode: barcode },
        success: function(response) {
            const product = JSON.parse(response);
            if (product) {
                document.getElementById('scannerModalLabel').innerHTML = 'Scanned Product: ' + product.productname;
                document.getElementById('scannedUID').innerHTML = 'UID: ' + barcode;
                document.getElementById('productInfo').innerHTML = `
                    <strong>Product Name:</strong> ${product.productname} <br>
                    <strong>Price:</strong> ₱${parseFloat(product.sale).toFixed(2)} <br>  
                `;
                
                addScannedProduct(product);  // Add the scanned product to the scanned products list
            } else {
                alert("Product not found.");
            }
        },
        error: function(xhr, status, error) {
            alert("An error occurred: " + error);
        }
    });
}

// Add scanned product to the scanned products list and update the UI
function addScannedProduct(product) {
    scannedProducts.push(product);
    updateScannedProductsUI();
}

// Update the list of scanned products in the modal
function updateScannedProductsUI() {
    const scannedProductsList = document.getElementById("scannedProductsList");
    scannedProductsList.innerHTML = '';  

    scannedProducts.forEach(product => {
        scannedProductsList.innerHTML += `
            <div class="scanned-item">
                <strong>Product:</strong> ${product.productname} <br>
                <strong>UID:</strong> ${product.uid} <br>
                <strong>Price:</strong> ₱${parseFloat(product.sale).toFixed(2)} <br>
                <hr>
            </div>
        `;
    });
}

function startScanner() {
    navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
        .then(function (stream) {
            const videoElement = document.getElementById("barcode-video");
            videoElement.srcObject = stream;

            Quagga.init({
                inputStream: {
                    name: "Live",
                    type: "LiveStream",
                    target: videoElement, 
                    constraints: {
                        facingMode: "environment", 
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
                Quagga.start();
            });

            Quagga.onDetected(function (result) {
                const barcode = result.codeResult.code;
                console.log("Detected Barcode: ", barcode); 

                if (!isCooldown) {
                    isCooldown = true;
                    checkProductByUID(barcode);  

                    setTimeout(function() {
                        isCooldown = false;
                    }, cooldownTime);
                }
            });
        }).catch(function (err) {
            console.error("Error accessing the camera: ", err);
        });
}

function stopCamera(stream) {
    if (stream) {
        const tracks = stream.getTracks();
        tracks.forEach(function(track) {
            track.stop();
        });
    }
}

$('#scannerModal').on('shown.bs.modal', function () {
    startScanner();  
});

$('#scannerModal').on('hidden.bs.modal', function () {
    stopCamera(videoStream);  
    Quagga.stop();  
});

function checkoutScannedProducts() {
    if (scannedProducts.length === 0) {
        alert('Your cart is empty. Please scan products before proceeding.');
        return;
    }

    const cartData = scannedProducts.map(product => ({
        id: product.id,
        name: product.productname,
        quantity: 1
    }));

    const total = scannedProducts.reduce((sum, product) => sum + product.sale, 0);
    $.ajax({
        url: 'checkoutprocess.php',
        method: 'POST',
        data: {
            cart: JSON.stringify(cartData),
            total: total
        },
        success: function(response) {
            const data = JSON.parse(response);
            if (data.success) {
                alert(data.message); 
                scannedProducts = [];  // Clear the scanned products list after successful checkout
                updateScannedProductsUI(); // Update the UI after checkout
                $('#scannerModal').modal('hide');  // Close the scanner modal
            } else {
                alert('An error occurred during checkout: ' + data.message); 
            }
        },
        error: function(xhr, status, error) {
            alert("An error occurred during checkout: " + error);
        }
    });
}
function processCheckout() {
    if (cart.length === 0) {
        alert('Your cart is empty. Please add products before proceeding.');
        return;
    }

    const cartData = cart.map(item => ({
        id: item.id,
        name: item.name,
        quantity: item.quantity,
        price: item.price
    }));

    const total = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);

    // Send the cart data to the server for checkout
    $.ajax({
        url: 'checkoutprocess.php',  // Same handler for both scanned products and regular cart
        method: 'POST',
        data: {
            cart: JSON.stringify(cartData),  // Send cart data as JSON
            total: total  // Send the total amount
        },
        success: function(response) {
            const data = JSON.parse(response);
            if (data.success) {
                alert(data.message);  // Show success message
                cart = [];  // Clear the cart after successful checkout
                updateCartUI();  // Update the cart UI to reflect the cleared cart
                $('#cartModal').modal('hide');  // Close the cart modal
            } else {
                alert('An error occurred during checkout: ' + data.message);  // Show error message
            }
        },
        error: function(xhr, status, error) {
            alert("An error occurred during checkout: " + error);  // Show error message
        }
    });
}
function searchProducts() {
    const searchQuery = document.getElementById("productSearch").value.toLowerCase();
    const productCards = document.querySelectorAll(".product-card");

    productCards.forEach(card => {
        const productName = card.querySelector(".product-name").textContent.toLowerCase();
        if (productName.includes(searchQuery)) {
            card.style.display = "block";  // Show product if it matches the search query
        } else {
            card.style.display = "none";   // Hide product if it does not match
        }
    });
}
</script>

</body>
</html>
