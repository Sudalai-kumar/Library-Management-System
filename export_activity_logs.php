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

// Fetch activity log data
$sql = "SELECT al.id, u.name AS user_name, al.action, al.details, al.timestamp 
        FROM activity_logs al 
        JOIN users u ON al.user_id = u.register_number 
        ORDER BY al.timestamp DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Set headers for the CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=activity_logs.csv');

    // Open output stream
    $output = fopen('php://output', 'w');

    // Add column headers
    fputcsv($output, ['Log ID', 'User Name', 'Action', 'Details', 'Timestamp']);

    // Add rows to the CSV
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }

    // Close output stream
    fclose($output);
    exit;
} else {
    echo "No activity logs found.";
}
?>
