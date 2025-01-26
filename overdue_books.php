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

// Fetch overdue books
$sql = "SELECT b.title, u.name AS borrower_name, u.register_number, bb.due_date, u.role AS role_,
       CEIL(DATEDIFF(CURDATE(), bb.due_date) / 7) * 5 AS current_fine
FROM borrowed_books bb
JOIN books b ON bb.book_id = b.id
JOIN users u ON bb.student_id = u.register_number
WHERE bb.return_date IS NULL AND bb.due_date < CURDATE();";
$result = $conn->query($sql);
$overdue_books = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Overdue Books</title>
    <link href="bootstrap.min.css" rel="stylesheet">
    <script src="jquery-3.6.0.min.js"></script>
    <style>
        .table-responsive {
            max-height: 500px;
            overflow-y: auto;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container my-5">
        <h2 class="text-center mb-4">Overdue Books</h2>

        <!-- Search Bar -->
        <div class="input-group mb-3">
            <input type="text" id="searchInput" class="form-control" placeholder="Search books, borrowers, or register numbers...">
        </div>

        <!-- Overdue Books Table -->
        <?php if (!empty($overdue_books)): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Book Title</th>
                            <th>Borrower Name</th>
                            <th>Register Number</th>
                            <th>Due Date</th>
                            <th>Current Fine</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($overdue_books as $book): ?>
                            <tr>
                                <td><?php echo $book['title']; ?></td>
                                <td><?php echo $book['borrower_name']; ?></td>
                                <td><?php echo $book['register_number']; ?></td>
                                <td><?php echo $book['due_date']; ?></td>
                                <td>₹<?php 
                                    if ($book['role_'] === 'student') {
                                        echo number_format($book['current_fine'], 2); 
                                    } else {
                                        echo number_format(0, 2);
                                    }
                                ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted">No overdue books at the moment.</p>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            // Filter table rows based on search input
            $("#searchInput").on("input", function () {
                const query = $(this).val().toLowerCase();
                $("tbody tr").each(function () {
                    const rowText = $(this).text().toLowerCase();
                    $(this).toggle(rowText.includes(query));
                });
            });
        });
    </script>
</body>
</html>

<?php include 'footer.php'; ?>
