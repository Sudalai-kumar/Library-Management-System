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

// Fetch book data
$sql = "SELECT title, author, genre, barcode, availability FROM books WHERE is_removed = 0";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Set headers for the CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=books.csv');

    // Open output stream
    $output = fopen('php://output', 'w');

    // Add column headers
    fputcsv($output, ['Title', 'Author', 'Genre', 'Barcode', 'Availability']);

    // Add rows to the CSV
    while ($row = $result->fetch_assoc()) {
        // Convert availability status to readable format
        $row['availability'] = $row['availability'] ? 'Available' : 'Borrowed';
        fputcsv($output, $row);
    }

    // Close output stream
    fclose($output);
    exit;
} else {
    echo "No books found.";
}
?>
