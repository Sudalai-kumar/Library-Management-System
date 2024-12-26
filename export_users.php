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

// Fetch user data
$sql = "SELECT register_number, name, email, role FROM users";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Set headers for the CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=users.csv');

    // Open output stream
    $output = fopen('php://output', 'w');

    // Add column headers
    fputcsv($output, ['Register Number', 'Name', 'Email', 'Role']);

    // Add rows to the CSV
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }

    // Close output stream
    fclose($output);
    exit;
} else {
    echo "No users found.";
}
?>
