<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

include 'connect.php';

$query = "SELECT * FROM product";
$result = mysqli_query($con, $query);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$sheet->setCellValue('A1', 'ID');
$sheet->setCellValue('B1', 'UID');
$sheet->setCellValue('C1', 'Image');
$sheet->setCellValue('D1', 'Product Name');
$sheet->setCellValue('E1', 'Type');
$sheet->setCellValue('F1', 'Unit');
$sheet->setCellValue('G1', 'Expiration');
$sheet->setCellValue('H1', 'Sale');
$sheet->setCellValue('I1', 'Purchase');

$sql = "SELECT * FROM product";
$result = mysqli_query($con, $sql);
$rowNumber = 2;

while ($row = mysqli_fetch_assoc($result)) {
    $sheet->setCellValue('A' . $rowNumber, $row['id']);
    if (isset($row['image']) && !empty($row['image'])) {
        $imagePath = 'C:\\xampp\\htdocs\\uploads\\' . $row['image'];
        if (file_exists($imagePath)) {
            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setName('Image');
            $drawing->setDescription('Image');
            $drawing->setPath($imagePath);
            $drawing->setCoordinates('C' . $rowNumber);
            $drawing->setHeight(50);
            $drawing->setWidth(50);
            $drawing->setWorksheet($sheet);
        }
    }
    $sheet->setCellValue('B' . $rowNumber, $row['uid']);
    $sheet->setCellValue('D' . $rowNumber, $row['productname']);
    $sheet->setCellValue('E' . $rowNumber, $row['type']);
    $sheet->setCellValue('F' . $rowNumber, $row['quantity']);
    $sheet->setCellValue('G' . $rowNumber, $row['exp']);
    $sheet->setCellValue('H' . $rowNumber, $row['sale']);
    $sheet->setCellValue('I' . $rowNumber, $row['purchase']);

    $rowNumber++;
}

$writer = new Xlsx($spreadsheet);
$filename = 'products.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer->save('php://output');
exit;
?>
