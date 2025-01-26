<?php include 'header.php'; ?>

<?php
session_start();

// Check if admin is logged in
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

// Pagination setup
$results_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $results_per_page;

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
    $selected_role = $conn->real_escape_string($_POST['role']);
    if (isset($_FILES['excel_file']['tmp_name']) && $_FILES['excel_file']['tmp_name'] != '') {
        $file = $_FILES['excel_file']['tmp_name'];
        try {
            $spreadsheet = IOFactory::load($file);
            $sheetData = $spreadsheet->getActiveSheet()->toArray();
            foreach ($sheetData as $index => $row) {
                if ($index == 0 || empty($row[0]) || empty($row[1]) || empty($row[2]) || empty($row[3])) continue;
                $register_number = $conn->real_escape_string($row[0]);
                $name = $conn->real_escape_string($row[1]);
                $email = $conn->real_escape_string($row[2]);
                $password = password_hash($row[3], PASSWORD_DEFAULT);
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
    $sql = "DELETE FROM users WHERE register_number = '$user_id'";
    $message = $conn->query($sql) ? "User deleted successfully!" : "Error: " . $conn->error;
}

// Fetch users
$sql = "SELECT * FROM users LIMIT $results_per_page OFFSET $offset";
$result = $conn->query($sql);
$users = $result->fetch_all(MYSQLI_ASSOC);

// Fetch total user count for pagination
$sql_total = "SELECT COUNT(*) AS total FROM users";
$result_total = $conn->query($sql_total);
$total_users = $result_total->fetch_assoc()['total'];
$total_pages = ceil($total_users / $results_per_page);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link href="bootstrap.min.css" rel="stylesheet">
    <script src="jquery-3.6.0.min.js"></script>
    <style>
        .sticky-header {
            position: sticky;
            top: 0;
            background-color: white;
            z-index: 100;
        }

        .table-responsive {
            max-height: 500px;
            overflow-y: auto;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container my-5">
        <h2 class="text-center mb-4">Manage Users</h2>

        <!-- Messages -->
        <?php if ($message): ?>
            <div class="alert alert-info text-center"><?php echo $message; ?></div>
        <?php endif; ?>

        <!-- Add User -->
        <form method="post" action="" class="row g-2 mb-4">
            <div class="col-md-3"><input type="text" name="register_number" class="form-control" placeholder="Register Number" required></div>
            <div class="col-md-3"><input type="text" name="name" class="form-control" placeholder="Name" required></div>
            <div class="col-md-3"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
            <div class="col-md-2"><select name="role" class="form-select" required><option value="student">Student</option><option value="faculty">Faculty</option><option value="admin">Admin</option></select></div>
            <div class="col-md-2"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
            <div class="col-md-1"><button type="submit" name="add_user" class="btn btn-primary">Add</button></div>
        </form>

        <!-- Import Users -->
        <form method="post" action="" enctype="multipart/form-data" class="row g-2 mb-4">
            <div class="col-md-2">
                <label class="form-label">Select Role:</label>
                <div><input type="radio" id="student" name="role" value="student" required><label for="student">Student</label></div>
                <div><input type="radio" id="faculty" name="role" value="faculty" required><label for="faculty">Faculty</label></div>
            </div>
            <div class="col-md-8"><input type="file" name="excel_file" class="form-control" accept=".xlsx, .xls" required></div>
            <div class="col-md-2"><button type="submit" name="import_users" class="btn btn-success">Import</button></div>
        </form>

        <!-- Search Bar -->
        <div class="input-group mb-3">
            <input type="text" id="searchInput" class="form-control" placeholder="Search users by name, email, or register number...">
        </div>

        <!-- Current Users -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light sticky-header">
                    <tr><th>Register Number</th><th>Name</th><th>Email</th><th>Role</th><th>Actions</th></tr>
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
        </div>

        <!-- Pagination -->
        <nav>
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php if ($i === $page) echo 'active'; ?>">
                        <a class="page-link pagination-btn" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>

        <!-- Export Users -->
        <form method="get" action="export.php" class="text-end">
            <input type="hidden" name="type" value="users">
            <button type="submit" class="btn btn-success">Export Users</button>
        </form>

        <div class="text-center mt-4">
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            // Filter table rows based on search input
            $("#searchInput").on("input", function () {
                const query = $(this).val().toLowerCase();
                $("tbody tr").each(function () {
                    const rowText = $(this).text().toLowerCase();
                    $(this).toggle(rowText.includes(query));
                });
            });
        });
    </script>
</body>

</html>

<?php include 'footer.php'; ?>
