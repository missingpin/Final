<?php
require 'vendor/autoload.php';
include 'connect.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_FILES['file'])) {
    if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        die("Upload failed with error code " . $_FILES['file']['error']);
    }

    $file = $_FILES['file']['tmp_name'];

    if (empty($file)) {
        die("No file was uploaded.");
    }

    $spreadsheet = IOFactory::load($file);
    $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

    foreach ($sheetData as $row) {
        if ($row['A'] != 'ID') { 
            // Sanitize inputs
            $uid = mysqli_real_escape_string($con, $row['B']); 
            $image = mysqli_real_escape_string($con, $row['C']); 
            $productname = mysqli_real_escape_string($con, $row['D']);
            $type = mysqli_real_escape_string($con, $row['E']);
            $quantity = mysqli_real_escape_string($con, $row['F']);
            $exp = mysqli_real_escape_string($con, $row['G']);
            $sale = mysqli_real_escape_string($con, $row['H']);
            $purchase = mysqli_real_escape_string($con, $row['I']);

            // Automatically set uid to 0 if it's empty
            if (empty($uid)) {
                $uid = '0';  // Default to 0 if uid is empty
            }

            // Handle empty values for other fields
            $image = empty($image) ? null : "'$image'";
            $productname = empty($productname) ? 'NULL' : "'$productname'";
            $type = empty($type) ? 'NULL' : "'$type'";
            $quantity = empty($quantity) ? 'NULL' : $quantity; // Assuming quantity is numeric
            $exp = empty($exp) ? 'NULL' : "'$exp'";
            $sale = empty($sale) ? 'NULL' : $sale; // Assuming sale is numeric
            $purchase = empty($purchase) ? 'NULL' : $purchase; // Assuming purchase is numeric

            // Prepare SQL query based on whether `image` is empty or not
            if ($image === null) {
                // Skip the `image` column if it's empty
                $sql = "INSERT INTO product (productname, type, uid, sale, purchase, quantity, exp) 
                        VALUES ($productname, $type, '$uid', $sale, $purchase, $quantity, $exp)";
            } else {
                // Include the `image` column if it's not empty
                $sql = "INSERT INTO product (productname, type, uid, sale, purchase, quantity, exp, image) 
                        VALUES ($productname, $type, '$uid', $sale, $purchase, $quantity, $exp, $image)";
            }

            // Execute the query
            if (!mysqli_query($con, $sql)) {
                echo "Error: " . mysqli_error($con) . "<br>";
            } else {
                echo "Inserted: $productname, $uid, $type, $quantity, $exp, $sale, $purchase, $image<br>";
            }
        }
    }

    echo "Data imported successfully!";
}
?>
