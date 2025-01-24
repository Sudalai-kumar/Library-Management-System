<?php include 'header.php'; ?>

<?php
session_start();

// Check if admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'library_management_system');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch fines
$sql = "
SELECT u.name AS borrower_name, 
       u.register_number, 
       b.title AS book_title, 
       bb.due_date, 
       bb.return_date, 
       bb.fine
FROM borrowed_books bb
JOIN users u ON bb.student_id = u.register_number
JOIN books b ON bb.book_id = b.id
WHERE bb.fine > 0
ORDER BY bb.return_date DESC";
$result = $conn->query($sql);
$fines = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fines Collected</title>
    <link href="bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container my-5">
        <h2 class="text-center mb-4">Fines Collected</h2>
        
        <?php if (!empty($fines)): ?>
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Borrower Name</th>
                        <th>Register Number</th>
                        <th>Book Title</th>
                        <th>Due Date</th>
                        <th>Return Date</th>
                        <th>Fine Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($fines as $fine): ?>
                        <tr>
                            <td><?php echo $fine['borrower_name']; ?></td>
                            <td><?php echo $fine['register_number']; ?></td>
                            <td><?php echo $fine['book_title']; ?></td>
                            <td><?php echo $fine['due_date']; ?></td>
                            <td><?php echo $fine['return_date']; ?></td>
                            <td>₹<?php echo number_format($fine['fine'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-muted">No fines have been collected yet.</p>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>

<?php include 'footer.php'; ?>
