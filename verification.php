<?php
session_start();
include("connect.php");

if (isset($_POST["verify_email"])) {
    $email = $_POST["email"];
    $verification_code = $_POST["verification_code"];
    $sql = "UPDATE form SET email_verified_at = NOW() WHERE email = '$email' AND verification = '$verification_code'";
    
    $result = mysqli_query($con, $sql);
    if (mysqli_affected_rows($con) == 0) {
        die("Verification code failed!");
    }
    echo "<p>Email Verified! You can log in now.</p>";
    exit();
}
?>

<form method="POST">
    <input type="hidden" name="email" value="<?php echo htmlspecialchars($_GET['email']); ?>" required>
    <input type="text" name="verification_code" placeholder="Enter verification code" required />
    <input type="submit" name="verify_email" value="Verify Email">
</form>
