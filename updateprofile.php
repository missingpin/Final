<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = mysqli_real_escape_string($con, $_POST['firstname']);
    $lastname = mysqli_real_escape_string($con, $_POST['lastname']);
    $mi = mysqli_real_escape_string($con, $_POST['mi']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $gender = mysqli_real_escape_string($con, $_POST['gender']);
    
    $profile = '';

    if (isset($_POST['cropped_image']) && !empty($_POST['cropped_image'])) {
        $cropped_image = $_POST['cropped_image'];
        $cropped_image = str_replace('data:image/jpeg;base64,', '', $cropped_image);
        $cropped_image = str_replace('data:image/png;base64,', '', $cropped_image);
        $cropped_image = str_replace('data:image/gif;base64,', '', $cropped_image);
        $image_data = base64_decode($cropped_image);
        $file_name = 'uploads/' . time() . '_' . uniqid() . '.jpg';
        file_put_contents($file_name, $image_data);
        $profile = $file_name;
    } else {
        $sql = "SELECT profile FROM form WHERE username = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($current_profile);
        $stmt->fetch();
        $stmt->close();

        $profile = $current_profile ? $current_profile : 'defaultprofile.jpg';
    }

    $sql = "UPDATE form SET firstname = ?, lastname = ?, mi = ?, email = ?, profile = ?, gender = ? WHERE username = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("sssssss", $firstname, $lastname, $mi, $email, $profile, $gender, $username);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Profile updated successfully!";
        header("Location: userprofile.php");
        exit();
    } else {
        $_SESSION['error'] = "There was an error updating your profile.";
        header("Location: userprofile.php");
        exit();
    }
    if (!in_array($image_type, ['image/jpeg', 'image/png', 'image/gif, image/jpg'])) {
        $_SESSION['error'] = "Invalid image type. Only JPEG, PNG, or GIF are allowed.";
        header("Location: userprofile.php");
        exit();
    }
}
?>
