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

// Fetch all borrowed books
$sql = "SELECT bb.borrow_date, bb.due_date, b.title, b.author, b.barcode, u.name AS student_name, u.register_number
        FROM borrowed_books bb
        JOIN books b ON bb.book_id = b.id
        JOIN users u ON bb.student_id = u.register_number
        WHERE bb.return_date IS NULL";
$result = $conn->query($sql);
$borrowed_books = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrowed Books</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container my-5">
        <h2 class="text-center mb-4">Borrowed Books</h2>

        <?php if (!empty($borrowed_books)): ?>
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Book Title</th>
                        <th>Author</th>
                        <th>Barcode</th>
                        <th>Register Number</th>
                        <th>Student Name</th>
                        <th>Borrow Date</th>
                        <th>Due Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($borrowed_books as $book): ?>
                        <tr>
                            <td><?php echo $book['title']; ?></td>
                            <td><?php echo $book['author']; ?></td>
                            <td><?php echo $book['barcode']; ?></td>
                            <td><?php echo $book['register_number']; ?></td>
                            <td><?php echo $book['student_name']; ?></td>
                            <td><?php echo $book['borrow_date']; ?></td>
                            <td><?php echo $book['due_date']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-warning text-center">No books are currently borrowed.</div>
        <?php endif; ?>
        <div class="text-end mb-3">
            <form method="get" action="export.php">
                <input type="hidden" name="type" value="borrowed_books">
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