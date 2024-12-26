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

// Fetch borrowed book data
$sql = "SELECT b.title, b.author, b.barcode, u.name AS student_name, bb.borrow_date, bb.due_date 
        FROM borrowed_books bb
        JOIN books b ON bb.book_id = b.id
        JOIN users u ON bb.student_id = u.register_number
        WHERE bb.return_date IS NULL";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Set headers for the CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=borrowed_books.csv');

    // Open output stream
    $output = fopen('php://output', 'w');

    // Add column headers
    fputcsv($output, ['Title', 'Author', 'Barcode', 'Borrower Name', 'Borrow Date', 'Due Date']);

    // Add rows to the CSV
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }

    // Close output stream
    fclose($output);
    exit;
} else {
    echo "No borrowed books found.";
}
?>
