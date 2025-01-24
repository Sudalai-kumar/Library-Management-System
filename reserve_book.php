<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'library_management_system');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id = $_POST['book_id'];
    $student_id = $_SESSION['user_id'];
    $expiration_date = date('Y-m-d', strtotime('+7 days')); // Reservation valid for 7 days

    // Insert reservation into the database
    $sql = "INSERT INTO reservations (book_id, student_id, expiration_date, status)
            VALUES ('$book_id', '$student_id', '$expiration_date', 'pending')";
    if ($conn->query($sql)) {
        $message = "<div class='alert alert-success'>Book reserved successfully!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserve Book</title>
    <link href="bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Reserve Book</h2>
        <?php echo $message; ?>
        <div class="text-center mt-4">
            <a href="search_books.php" class="btn btn-secondary">Back to Search</a>
        </div>
    </div>
</body>
</html>
