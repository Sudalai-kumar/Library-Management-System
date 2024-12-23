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

// Fine per day
$daily_fine = 5; // Adjust the fine amount as needed

// Handle return process
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $register_number = $_POST['register_number'];
    $book_barcode = $_POST['book_barcode'];
    
    // Check if the book was borrowed by this user
    $sql = "SELECT bb.id AS borrow_id, bb.due_date, b.id AS book_id
            FROM borrowed_books bb
            JOIN books b ON bb.book_id = b.id
            JOIN users u ON bb.student_id = u.register_number
            WHERE u.register_number = '$register_number' AND b.barcode = '$book_barcode' AND bb.return_date IS NULL";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $borrow_id = $row['borrow_id'];
        $book_id = $row['book_id'];
        $due_date = $row['due_date'];
        
        // Calculate overdue fine
        $return_date = date('Y-m-d');
        $overdue_days = (strtotime($return_date) - strtotime($due_date)) / (60 * 60 * 24);
        $fine = $overdue_days > 0 ? $overdue_days * $daily_fine : 0;
        
        // Update the borrowed_books table
        $sql = "UPDATE borrowed_books SET return_date = '$return_date', fine = '$fine' WHERE id = '$borrow_id'";
        if ($conn->query($sql)) {
            // Mark the book as available
            $sql = "UPDATE books SET availability = 1 WHERE id = '$book_id'";
            $conn->query($sql);
            
            // Log the action in activity_logs
            $admin_id = $_SESSION['user_id'];
            $action = "Return Book";
            $details = "Returned Book ID: $book_id, Register Number: $register_number, Fine: ₹$fine";
            $log_sql = "INSERT INTO activity_logs (user_id, action, details)
                        VALUES ('$admin_id', '$action', '$details')";
            $conn->query($log_sql);
            
            $message = $fine > 0 ? 
                "<div class='alert alert-warning'>Book returned with a fine of ₹$fine.</div>" :
                "<div class='alert alert-success'>Book returned successfully with no fine.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error: Unable to process the return.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>No matching borrow record found for this register number and book.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Books</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container my-5">
        <h2 class="text-center mb-4">Return a Book</h2>
        
        <!-- Return Book Form -->
        <form method="post" action="" class="p-4 bg-white shadow rounded">
            <div class="mb-3">
                <label for="register_number" class="form-label">Scan Register Number:</label>
                <input type="text" id="register_number" name="register_number" class="form-control" placeholder="Enter Register Number" required>
            </div>
            <div class="mb-3">
                <label for="book_barcode" class="form-label">Scan Book Barcode:</label>
                <input type="text" id="book_barcode" name="book_barcode" class="form-control" placeholder="Enter Book Barcode" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Return Book</button>
        </form>

        <!-- Feedback Message -->
        <div class="mt-4">
            <?php echo $message; ?>
        </div>

        <!-- Back to Dashboard -->
        <div class="text-center mt-4">
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>

<?php include 'footer.php'; ?>
