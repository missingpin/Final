<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function sendLowStockAlert($userEmail, $productName, $currentQuantity) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.office365.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'your_email@outlook.com'; // Your Microsoft email
        $mail->Password = 'your_password'; // Your email password or App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->SMTPDebug = 2; // For verbose output

        // Recipients
        $mail->setFrom('your_email@outlook.com', 'Your Company');
        $mail->addAddress($userEmail); // User's email

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Low Stock Alert';
        $mail->Body = "Dear User,<br><br>The product '{$productName}' is low in stock (current quantity: {$currentQuantity}). Please consider restocking it.<br><br>Best regards,<br>Your Company";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo); // Log the error
        return false;
    }
}

?>
