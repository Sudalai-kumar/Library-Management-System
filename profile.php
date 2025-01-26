<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'library_management_system');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user details
$user_id = $_SESSION['user_id'];
$sql = "SELECT register_number, name, email, role FROM users WHERE register_number = '$user_id'";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

// Handle profile update
$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);

    $update_sql = "UPDATE users SET name = '$name', email = '$email' WHERE register_number = '$user_id'";
    if ($conn->query($update_sql)) {
        $message = "Profile updated successfully.";
        $user['name'] = $name;
        $user['email'] = $email;
    } else {
        $message = "Error updating profile: " . $conn->error;
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password === $confirm_password) {
        // Verify current password
        $password_sql = "SELECT password FROM users WHERE register_number = '$user_id'";
        $password_result = $conn->query($password_sql);
        $password_row = $password_result->fetch_assoc();

        if (password_verify($current_password, $password_row['password'])) {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_password_sql = "UPDATE users SET password = '$hashed_password' WHERE register_number = '$user_id'";
            if ($conn->query($update_password_sql)) {
                $message = "Password changed successfully.";
            } else {
                $message = "Error changing password: " . $conn->error;
            }
        } else {
            $message = "Current password is incorrect.";
        }
    } else {
        $message = "New passwords do not match.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page</title>
    <link href="bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container my-5">
        <h2 class="text-center mb-4">Your Profile</h2>

        <!-- Display User Info -->
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <h5>Profile Information</h5>
            </div>
            <div class="card-body">
                <p><strong>Register Number:</strong> <?php echo $user['register_number']; ?></p>
                <p><strong>Name:</strong> <?php echo $user['name']; ?></p>
                <p><strong>Email:</strong> <?php echo $user['email']; ?></p>
                <p><strong>Role:</strong> <?php echo ucfirst($user['role']); ?></p>
            </div>
        </div>

        <!-- Update Profile Form -->
        <!-- <form method="post" action="">
            <h5>Update Profile</h5>
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo $user['name']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>" required>
            </div>
            <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
        </form> -->

        <!-- Change Password Form -->
        <form method="post" action="" class="mt-4">
            <h5>Change Password</h5>
            <div class="mb-3">
                <label for="current_password" class="form-label">Current Password</label>
                <input type="password" class="form-control" id="current_password" name="current_password" required>
            </div>
            <div class="mb-3">
                <label for="new_password" class="form-label">New Password</label>
                <input type="password" class="form-control" id="new_password" name="new_password" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm New Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" name="change_password" class="btn btn-warning">Change Password</button>
        </form>

        <?php
        $dashboard_url = ($_SESSION['role'] === 'student') ? 'student_dashboard.php' : 'faculty_dashboard.php';
        ?>
        <a href="<?php echo $dashboard_url; ?>" class="btn btn-secondary mt-4">Back to Dashboard</a>

        <?php if ($message): ?>
            <div class="alert alert-info mt-3"><?php echo $message; ?></div>
        <?php endif; ?>
    </div>
</body>

</html>