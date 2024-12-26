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

// Handle borrowing process
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_POST['student_id'];
    $book_barcode = $_POST['book_barcode'];

    // Validate book availability
    $sql = "SELECT * FROM books WHERE barcode = '$book_barcode' AND availability = 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $book = $result->fetch_assoc();
        $book_id = $book['id'];

        // Record the borrowing
        $borrow_date = date('Y-m-d');
        $due_date = date('Y-m-d', strtotime('+14 days')); // 14-day borrowing period
        $sql = "INSERT INTO borrowed_books (book_id, student_id, borrow_date, due_date)
                VALUES ('$book_id', '$student_id', '$borrow_date', '$due_date')";
        if ($conn->query($sql)) {
            // Update book availability
            $sql = "UPDATE books SET availability = 0 WHERE id = '$book_id'";
            $conn->query($sql);

            // Log the action in activity_logs
            $admin_id = $_SESSION['user_id'];
            $action = "Borrow Book";
            $details = "Borrowed Book ID: $book_id, Student ID: $student_id";
            $log_sql = "INSERT INTO activity_logs (user_id, action, details)
                        VALUES ('$admin_id', '$action', '$details')";
            $conn->query($log_sql);

            $message = "<div class='alert alert-success'>Book borrowed successfully! Due date: $due_date.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error: Unable to process the borrowing.</div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>Book not found or already borrowed.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrow Books</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container my-5">
        <h2 class="text-center mb-4">Borrow a Book</h2>
        <form method="post" action="" class="p-4 bg-white shadow rounded">
            <div class="mb-3">
                <label for="student_id" class="form-label">Scan Student ID:</label>
                <input type="text" id="student_id" name="student_id" class="form-control" placeholder="Enter student ID" required>
            </div>
            <div class="mb-3">
                <label for="book_barcode" class="form-label">Scan Book Barcode:</label>
                <input type="text" id="book_barcode" name="book_barcode" class="form-control" placeholder="Enter book barcode" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Borrow Book</button>
        </form>

        <!-- Message Section -->
        <div class="mt-4"><?php echo $message; ?></div>
        <div class="text-end mb-3">
            <form method="post" action="export_borrowed_books.php">
                <button type="submit" class="btn btn-success">Export Borrowed Books</button>
            </form>
        </div>
        <div class="text-center mt-4">
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>
</body>

</html>

<?php include 'footer.php'; ?>