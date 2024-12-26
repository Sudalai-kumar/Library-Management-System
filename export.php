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

// Determine the export type
$type = isset($_GET['type']) ? $_GET['type'] : '';
$filename = '';
$headers = [];
$query = '';

switch ($type) {
    case 'users':
        $filename = 'users.csv';
        $headers = ['Register Number', 'Name', 'Email', 'Role'];
        $query = "SELECT register_number, name, email, role FROM users";
        break;
    case 'books':
        $filename = 'books.csv';
        $headers = ['Title', 'Author', 'Genre', 'Barcode', 'Availability'];
        $query = "SELECT title, author, genre, barcode, availability FROM books WHERE is_removed = 0";
        break;
    case 'borrowed_books':
        $filename = 'borrowed_books.csv';
        $headers = ['Title', 'Author', 'Barcode', 'Borrower Name', 'Borrow Date', 'Due Date'];
        $query = "
            SELECT b.title, b.author, b.barcode, u.name AS student_name, bb.borrow_date, bb.due_date 
            FROM borrowed_books bb
            JOIN books b ON bb.book_id = b.id
            JOIN users u ON bb.student_id = u.register_number
            WHERE bb.return_date IS NULL";
        break;
    case 'activity_logs':
        $filename = 'activity_logs.csv';
        $headers = ['Log ID', 'User Name', 'Action', 'Details', 'Timestamp'];
        $query = "
            SELECT al.id, u.name AS user_name, al.action, al.details, al.timestamp 
            FROM activity_logs al 
            JOIN users u ON al.user_id = u.register_number 
            ORDER BY al.timestamp DESC";
        break;
    case 'reports_popular_books':
        $filename = 'popular_books.csv';
        $headers = ['Title', 'Author', 'Borrow Count'];
        $query = "
            SELECT b.title, b.author, COUNT(bb.id) AS borrow_count
            FROM borrowed_books bb
            JOIN books b ON bb.book_id = b.id
            WHERE b.is_removed = 0
            GROUP BY bb.book_id
            ORDER BY borrow_count DESC";
        break;
    case 'reports_frequent_borrowers':
        $filename = 'frequent_borrowers.csv';
        $headers = ['Register Number', 'Student Name', 'Borrow Count'];
        $query = "
            SELECT u.register_number, u.name AS student_name, COUNT(bb.id) AS borrow_count
            FROM borrowed_books bb
            JOIN users u ON bb.student_id = u.register_number
            GROUP BY bb.student_id
            ORDER BY borrow_count DESC";
        break;
    case 'reports_monthly_activity':
        $filename = 'monthly_activity.csv';
        $headers = ['Month', 'Borrow Count'];
        $query = "
            SELECT DATE_FORMAT(borrow_date, '%Y-%m') AS month, COUNT(id) AS borrow_count
            FROM borrowed_books
            GROUP BY DATE_FORMAT(borrow_date, '%Y-%m')
            ORDER BY month DESC";
        break;
    default:
        echo "Invalid export type.";
        exit;
}

// Execute the query
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    // Set headers for the CSV download
    header('Content-Type: text/csv');
    header("Content-Disposition: attachment;filename=$filename");

    // Open output stream
    $output = fopen('php://output', 'w');

    // Add column headers
    fputcsv($output, $headers);

    // Add rows to the CSV
    while ($row = $result->fetch_assoc()) {
        // Convert availability to readable format (if applicable)
        if (isset($row['availability'])) {
            $row['availability'] = $row['availability'] ? 'Available' : 'Borrowed';
        }
        fputcsv($output, $row);
    }

    // Close output stream
    fclose($output);
    exit;
} else {
    echo "No data found for export.";
}
?>
