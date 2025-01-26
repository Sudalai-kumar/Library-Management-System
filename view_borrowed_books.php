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
    <link href="bootstrap.min.css" rel="stylesheet">
    <script src="jquery-3.6.0.min.js"></script>
    <style>
        .sticky-header {
            position: sticky;
            top: 0;
            background-color: white;
            z-index: 100;
        }

        .table-responsive {
            max-height: 500px;
            overflow-y: auto;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container my-5">
        <h2 class="text-center mb-4">Borrowed Books</h2>

        <!-- Search Bar -->
        <div class="input-group mb-3">
            <input type="text" id="searchInput" class="form-control" placeholder="Search books, students, or register numbers...">
        </div>

        <!-- Borrowed Books Table -->
        <?php if (!empty($borrowed_books)): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light sticky-header">
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
            </div>
        <?php else: ?>
            <div class="alert alert-warning text-center">No books are currently borrowed.</div>
        <?php endif; ?>

        <!-- Export and Back Buttons -->
        <div class="d-flex justify-content-between align-items-center mt-4">
            <form method="get" action="export.php">
                <input type="hidden" name="type" value="borrowed_books">
                <button type="submit" class="btn btn-success">Export Borrowed Books</button>
            </form>
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
