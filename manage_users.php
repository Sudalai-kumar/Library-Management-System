<?php include 'header.php'; ?>

<?php
session_start();

// Check if admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'library_management_system');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize message
$message = "";

// Handle adding a new user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $register_number = $_POST['register_number'];
    $sql = "INSERT INTO users (name, email, register_number, role, password) 
        VALUES ('$name', '$email', '$register_number', '$role', '$password')";

    $message = $conn->query($sql) ? "User added successfully!" : "Error: " . $conn->error;
}

// Include PhpSpreadsheet for importing users
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Handle importing users
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['import_users'])) {
    $selected_role = $conn->real_escape_string($_POST['role']); // Get the selected role

    if (isset($_FILES['excel_file']['tmp_name']) && $_FILES['excel_file']['tmp_name'] != '') {
        $file = $_FILES['excel_file']['tmp_name'];
        try {
            $spreadsheet = IOFactory::load($file);
            $sheetData = $spreadsheet->getActiveSheet()->toArray();

            foreach ($sheetData as $index => $row) {
                if ($index == 0) continue; // Skip header row

                // Skip rows with missing required fields
                if (empty($row[0]) || empty($row[1]) || empty($row[2]) || empty($row[3])) {
                    continue;
                }

                // Extract and sanitize row data
                $register_number = $conn->real_escape_string($row[0]);
                $name = $conn->real_escape_string($row[1]);
                $email = $conn->real_escape_string($row[2]);
                $password = password_hash($row[3], PASSWORD_DEFAULT);

                // Insert into the database with the selected role
                $sql = "INSERT INTO users (register_number, name, email, role, password) 
                        VALUES ('$register_number', '$name', '$email', '$selected_role', '$password')";
                $conn->query($sql);
            }
            $message = "Users imported successfully!";
        } catch (Exception $e) {
            $message = "Error: Unable to import users. " . $e->getMessage();
        }
    } else {
        $message = "No file selected.";
    }
}

// Handle deleting a user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    $sql = "DELETE FROM users WHERE id = '$user_id'";
    $message = $conn->query($sql) ? "User deleted successfully!" : "Error: " . $conn->error;
}

// Fetch all users
$sql = "SELECT * FROM users";
$result = $conn->query($sql);
$users = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container my-5">
        <h2 class="text-center mb-4">Manage Users</h2>

        <!-- Messages -->
        <?php if ($message): ?>
            <div class="alert alert-info text-center"><?php echo $message; ?></div>
        <?php endif; ?>

        <!-- Add User -->
        <h3>Add a New User</h3>
        <form method="post" action="" class="mb-4">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="register_number" class="form-control" placeholder="Register Number" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="name" class="form-control" placeholder="Name" required>
                </div>
                <div class="col-md-3">
                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                </div>
                <div class="col-md-3">
                    <select name="role" class="form-select" required>
                        <option value="student">Student</option>
                        <option value="faculty">Faculty</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>
                <div class="col-md-12 text-end mt-2">
                    <button type="submit" name="add_user" class="btn btn-primary">Add User</button>
                </div>
            </div>
        </form>

        <!-- Import Users -->
        <h3>Import Users</h3>
        <form method="post" action="" enctype="multipart/form-data">
            <h5>Import Users</h5>
            <label>Select Role:</label>
            <div>
                <input type="radio" id="student" name="role" value="student" required>
                <label for="student">Student</label>
                <input type="radio" id="faculty" name="role" value="faculty" required>
                <label for="faculty">Faculty</label>
            </div>
            <div class="mt-3">
                <input type="file" name="excel_file" accept=".xlsx, .xls" required>
                <button type="submit" name="import_users" class="btn btn-success">Import Users</button>
            </div>
        </form>
        <!-- Current Users -->
        <h3>All Users</h3>
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>Register Number</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['register_number']; ?></td>
                        <td><?php echo $user['name']; ?></td>
                        <td><?php echo $user['email']; ?></td>
                        <td><?php echo ucfirst($user['role']); ?></td>
                        <td>
                            <form method="post" action="" class="d-inline">
                                <input type="hidden" name="user_id" value="<?php echo $user['register_number']; ?>">
                                <button type="submit" name="delete_user" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="text-end mb-3">
            <form method="get" action="export.php">
                <input type="hidden" name="type" value="users">
                <button type="submit" class="btn btn-success">Export Users</button>
            </form>
        </div>
        <div class="text-center mt-4">
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>
</body>

</html>

<?php include 'footer.php'; ?>