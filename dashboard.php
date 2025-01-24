<?php include 'header.php'; ?>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Redirect to login if not logged in
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
$role = $_SESSION['role'];

// Fetch metrics
$sql_total_books = "SELECT COUNT(*) AS total_books FROM books";
$total_books = $conn->query($sql_total_books)->fetch_assoc()['total_books'];

$sql_active_books = "SELECT COUNT(*) AS active_books FROM books WHERE is_removed = 0";
$active_books = $conn->query($sql_active_books)->fetch_assoc()['active_books'];

$sql_borrowed_books = "SELECT COUNT(*) AS borrowed_books FROM borrowed_books WHERE return_date IS NULL";
$borrowed_books = $conn->query($sql_borrowed_books)->fetch_assoc()['borrowed_books'];

$sql_overdue_books = "SELECT COUNT(*) AS overdue_books FROM borrowed_books WHERE return_date IS NULL AND due_date < CURDATE()";
$overdue_books = $conn->query($sql_overdue_books)->fetch_assoc()['overdue_books'];

$sql_total_fines = "SELECT SUM(fine) AS total_fines FROM borrowed_books WHERE return_date IS NOT NULL";
$total_fines = $conn->query($sql_total_fines)->fetch_assoc()['total_fines'];
// $sql_reservations = "
//     SELECT r.id, b.title, u.name AS reserved_by, r.reservation_date, r.expiration_date
//     FROM reservations r
//     JOIN books b ON r.book_id = b.id
//     JOIN users u ON r.student_id = u.register_number
//     WHERE r.status = 'pending'
//     ORDER BY r.reservation_date ASC";
// $result_reservations = $conn->query($sql_reservations);
// $reservations = $result_reservations->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Dashboard</title>
    <link href="bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container my-5">
        <h2 class="text-center mb-4"> SANKAR POLYTECHNIC COLLEGE</h2>
        <h2 class="text-center mb-4">Welcome to the Library Management System</h2>
        <p class="text-center">Role: <strong><?php echo ucfirst($role); ?></strong></p>

        <!-- Dashboard Metrics -->
        <div class="row">
            <!-- Total Books -->
            <div class="col-md-3">
                <a href="manage_books.php" class="text-decoration-none">
                    <div class="card text-white bg-primary mb-3 d-flex justify-content-center align-items-center">
                        <div class="card-body text-center">
                            <h5 class="card-title">Total Books</h5>
                            <p class="card-text fs-3"><?php echo $active_books; ?></p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Borrowed Books -->
            <div class="col-md-3">
                <a href="view_borrowed_books.php" class="text-decoration-none">
                    <div class="card text-white bg-warning mb-3 d-flex justify-content-center align-items-center">
                        <div class="card-body text-center">
                            <h5 class="card-title">Borrowed Books</h5>
                            <p class="card-text fs-3"><?php echo $borrowed_books; ?></p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Overdue Books -->
            <div class="col-md-3">
                <a href="overdue_books.php" class="text-decoration-none">
                    <div class="card text-white bg-danger mb-3 d-flex justify-content-center align-items-center">
                        <div class="card-body text-center">
                            <h5 class="card-title">Overdue Books</h5>
                            <p class="card-text fs-3"><?php echo $overdue_books; ?></p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Total Fines -->
            <div class="col-md-3">
                <a href="view_fines.php" class="text-decoration-none">
                    <div class="card text-white bg-info mb-3 d-flex justify-content-center align-items-center">
                        <div class="card-body text-center">
                            <h5 class="card-title">Total Fines Collected</h5>
                            <p class="card-text fs-3">₹<?php echo number_format($total_fines, 2); ?></p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        <!-- Admin Quick Actions -->
        <h3 class="mt-5 text-center">Admin Actions</h3>
        <div class="row text-center">
            <div class="col-md-4 mb-3">
                <a href="admin_search_books.php" class="btn btn-info w-100">Search Books</a>
            </div>
            <div class="col-md-4 mb-3">
                <a href="view_borrowed_books.php" class="btn btn-warning w-100">View Borrowed Books</a>
            </div>
            <div class="col-md-4 mb-3">
                <a href="borrow_books.php" class="btn btn-primary w-100">Borrow Books</a>
            </div>
            <div class="col-md-4 mb-3">
                <a href="return_books.php" class="btn btn-success w-100">Return Books</a>
            </div>
            <div class="col-md-4 mb-3">
                <a href="manage_users.php" class="btn btn-secondary w-100">Manage Users</a>
            </div>
            <div class="col-md-4 mb-3">
                <a href="view_logs.php" class="btn btn-danger w-100">View Logs</a>
            </div>
        </div>
    </div>
</body>

</html>

<?php include 'footer.php'; ?>