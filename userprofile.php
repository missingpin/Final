    <?php
    session_start();
    include 'connect.php';
    include 'sidebar.php';

    if (!isset($_SESSION['username'])) {
        header("Location: login.php");
        exit();
    }

    $username = $_SESSION['username'];

    $sql = "SELECT firstname, lastname, mi, level, profile, gender FROM form WHERE username = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($firstname, $lastname, $mi, $level, $profile, $gender);
    $stmt->fetch();
    $stmt->close();

    $profile_pic = empty($profile) ? 'defaultprofile.jpg' : $profile;

    $user_level_description = '';
    if ($level == 1) {
        $user_level_description = 'Store Staff';
    } elseif ($level == 2) {
        $user_level_description = 'Store Owner';
    }

    if (isset($_SESSION['message'])) {
        echo "<div id='successMessage' class='alert alert-success'>{$_SESSION['message']}</div>";
        unset($_SESSION['message']);
    }
    if (isset($_SESSION['error'])) {
        echo "<div class='alert alert-danger'>{$_SESSION['error']}</div>";
        unset($_SESSION['error']);
    }
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>User Profile</title>
        <link rel="stylesheet" href="userprofile.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
    </head>
    <body>
        <div class="edit-form-container" style="display: none;">
        <h3 class="edit-profile-title">Edit Profile</h3>
            <form action="updateprofile.php" method="POST" enctype="multipart/form-data" class="edit-profile-form">
                <div class="left-side-form">
                    <div class="profile-picture-container">
                        <img id="currentProfilePic" src="<?php echo isset($profile) && !empty($profile) ? $profile : 'defaultprofile.jpg'; ?>" alt="Current Profile Image" class="profile-pic">
                        <span id="pencilIcon" onclick="showFileInput()" class="edit-icon">&#9998;</span>
                    </div>
                    <input type="file" id="profile" name="profile" accept="image/*" style="display: none;">
                    <div class="form-group">
                        <label>Preview and Crop Image:</label>
                        <div id="imagePreviewContainer" style="width: 100%; text-align: center;">
                            <img id="imagePreviewImage" src="#" alt="Profile Image" class="profile-pic" style="display: none;">
                        </div>
                        <canvas id="canvas" style="display: none;"></canvas>
                    </div>
                </div>

                <div class="right-side-form">
                <div class="form-group">
                    <label for="firstname">First Name:</label>
                    <input type="text" id="firstname" name="firstname" value="<?php echo $firstname; ?>" required>
                </div>
                <div class="form-group">
                    <label for="lastname">Last Name:</label>
                    <input type="text" id="lastname" name="lastname" value="<?php echo $lastname; ?>" required>
                </div>
                <div class="form-group">
    <label for="gender">Gender:</label>
    <select id="gender" name="gender">
        <option value="male" <?php echo ($gender == 'male') ? 'selected' : ''; ?>>Male</option>
        <option value="female" <?php echo ($gender == 'female') ? 'selected' : ''; ?>>Female</option>
        <option value="other" <?php echo ($gender == 'other') ? 'selected' : ''; ?>>Other</option>
    </select>
</div>

                <div class="form-group">
    <label for="mi">Middle Initial:</label>
    <input type="text" id="mi" name="mi" value="<?php echo $mi; ?>" maxlength="1">
</div>

                <button type="submit" class="btn btn-success">Save Changes</button>
                <button type="button" class="btn btn-secondary" onclick="cancelEdit()">Cancel</button>
            </form>
        </div>
        </div>

        <!-- Profile Display Card -->
        <div class="profile-container">
            <div class="profile-card">
                <div class="left-section">
                    <img src="<?php echo isset($profile) && !empty($profile) ? $profile : 'defaultprofile.jpg'; ?>" alt="profileImg" class="profile-pic">
                    <h3><?php echo $lastname . ', ' . $firstname . ' ' . (!empty($mi) ? $mi . '.' : ''); ?></h3>
                    <p><strong><?php echo $user_level_description; ?></strong></p>
                </div>
                <div class="right-section">
                    <div class="info-box">
                        <button class="btn btn-primary" onclick="editProfile()">Edit</button>
                        <h4>Information</h4>
                        <table class="infotable">
                            <tr>
                                <td>Full Name</td>
                                <td>: <?php echo $firstname . ' ' . $mi . ' ' . $lastname; ?></td>
                            </tr>
                            <tr>
                                <td>Username</td>
                                <td>: <?php echo $username; ?></td>
                            </tr>
                            <tr>
                                <td>Password</td>
                                <td>: [Hidden for security]</td>
                            </tr>
                            <tr>
    <td>Gender</td>
    <td>: <?php echo ucfirst($gender); ?></td>
</tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
        <script>
            let cropper;
            const imageInput = document.getElementById('profile');
            const imagePreview = document.getElementById('imagePreviewImage');
            const imagePreviewContainer = document.getElementById('imagePreviewContainer');
            const canvas = document.getElementById('canvas');
            const form = document.querySelector('form');

            imageInput.addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        imagePreview.src = e.target.result;
                        imagePreview.style.display = 'block';
                        imagePreviewContainer.style.display = 'block';

                        if (cropper) {
                            cropper.destroy();
                        }

                        cropper = new Cropper(imagePreview, {
                            aspectRatio: 1,
                            viewMode: 2,
                            dragMode: 'move',
                            zoomable: true,
                            scalable: true,
                            responsive: true,
                        });
                    };
                    reader.readAsDataURL(file);
                }
            });

            form.addEventListener('submit', function(e) {
        e.preventDefault();

        if (cropper && imageInput.files.length > 0) {
            const croppedImage = cropper.getCroppedCanvas().toDataURL('image/jpeg');
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'cropped_image';
            hiddenInput.value = croppedImage;
            form.appendChild(hiddenInput);
        } else {
            const hiddenInput = document.querySelector('input[name="cropped_image"]');
            if (hiddenInput) {
                form.removeChild(hiddenInput);
            }
        }

        // Now submit the form with the appropriate data
        form.submit();
    });


            // Function to show the file input when pencil icon is clicked
            function showFileInput() {
                document.getElementById('profile').style.display = 'block'; // Show the file input
                document.getElementById('pencilIcon').style.display = 'none'; // Hide the pencil icon
            }

            // Function to hide success message after a delay
            window.onload = function() {
                const successMessage = document.getElementById('successMessage');
                if (successMessage) {
                    setTimeout(function() {
                        successMessage.style.display = 'none';
                    }, 3000); // Hide after 3 seconds
                }
            }

            function editProfile() {
                document.querySelector('.profile-container').style.display = 'none'; // Hide profile card
                document.querySelector('.edit-form-container').style.display = 'block'; // Show edit form
            }

            function cancelEdit() {
                document.querySelector('.profile-container').style.display = 'block'; // Show profile card
                document.querySelector('.edit-form-container').style.display = 'none'; // Hide edit form
            }
        </script>
    </body>
    </html>
