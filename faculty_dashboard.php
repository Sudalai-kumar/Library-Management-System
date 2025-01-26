<?php
session_start();

// Check if the user is logged in and not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'admin') {
    header("Location: index.php");
    exit;
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'library_management_system');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user details
$user_id = $_SESSION['user_id'];
$role = ucfirst($_SESSION['role']);

// Fetch overdue books
$sql_overdue = "
    SELECT b.title, bb.due_date
    FROM borrowed_books bb
    JOIN books b ON bb.book_id = b.id
    WHERE bb.student_id = '$user_id' 
    AND bb.return_date IS NULL 
    AND bb.due_date < CURDATE()
";
$result_overdue = $conn->query($sql_overdue);
$overdue_books = $result_overdue->fetch_all(MYSQLI_ASSOC);

// Fetch user's name
$sql = "SELECT name FROM users WHERE register_number = '$user_id'";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

// Pagination setup
$results_per_page = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $results_per_page;

// Fetch borrowed books with pagination
$sql = "
    SELECT b.title, b.author, bb.borrow_date, bb.due_date, bb.return_date, bb.fine,
           CASE 
               WHEN bb.return_date IS NOT NULL AND bb.fine > 0 THEN 'Returned with Fine'
               WHEN bb.return_date IS NOT NULL THEN 'Returned'
               WHEN bb.due_date < CURDATE() THEN 'Overdue'
               ELSE 'Currently Borrowed'
           END AS status
    FROM borrowed_books bb
    JOIN books b ON bb.book_id = b.id
    WHERE bb.student_id = '$user_id'
    ORDER BY bb.borrow_date DESC
    LIMIT $results_per_page OFFSET $offset
";
$result = $conn->query($sql);
$borrowed_books = $result->fetch_all(MYSQLI_ASSOC);

// Fetch total books for pagination
$sql_total = "
    SELECT COUNT(*) AS total 
    FROM borrowed_books bb
    WHERE bb.student_id = '$user_id'
";
$result_total = $conn->query($sql_total);
$total_books = $result_total->fetch_assoc()['total'];
$total_pages = ceil($total_books / $results_per_page);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $role; ?> Dashboard</title>
    <link href="bootstrap.min.css" rel="stylesheet">
    <style>
        .table-responsive {
            max-height: 500px; /* Optional: Add a height limit */
            overflow-y: auto;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container my-5">
        <h2 class="text-center mb-4">Welcome to the Library Management System, <?php echo htmlspecialchars($user['name']); ?>!</h2>
        <!-- Navigation Links -->
        <nav class="nav justify-content-center mb-4">
            <a class="nav-link" href="search_books.php">Search Books</a>
            <a class="nav-link" href="profile.php">Profile</a>
            <a class="nav-link text-danger" href="logout.php">Logout</a>
        </nav>
        <!-- Overdue Notifications -->
        <div class="card shadow mb-4">
            <div class="card-header bg-danger text-white">
                <h5>Overdue Notifications</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($overdue_books)): ?>
                    <ul>
                        <?php foreach ($overdue_books as $book): ?>
                            <li>
                                <strong><?php echo $book['title']; ?></strong> was due on
                                <span class="text-danger"><?php echo $book['due_date']; ?></span>.
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted">No overdue books at the moment.</p>
                <?php endif; ?>
            </div>
        </div>
        <!-- Borrowed Books Section -->
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <h5>Borrowed Books</h5>
            </div>
            <div class="card-body">
                <!-- Search Bar -->
                <div class="input-group mb-3">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search borrowed books...">
                </div>
                <?php if (!empty($borrowed_books)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Borrow Date</th>
                                    <th>Due Date</th>
                                    <th>Return Date</th>
                                    <th>Status</th>
                                    <th>Fine</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($borrowed_books as $book): ?>
                                    <tr class="<?php echo $book['status'] === 'Returned with Fine' ? 'table-warning' : ($book['status'] === 'Overdue' ? 'table-danger' : ($book['status'] === 'Returned' ? 'table-success' : 'table-primary')); ?>">
                                        <td><?php echo $book['title']; ?></td>
                                        <td><?php echo $book['author']; ?></td>
                                        <td><?php echo $book['borrow_date']; ?></td>
                                        <td><?php echo $book['due_date']; ?></td>
                                        <td><?php echo $book['return_date'] ?? 'N/A'; ?></td>
                                        <td><?php echo $book['status']; ?></td>
                                        <td><?php echo $book['fine'] > 0 ? "₹" . number_format($book['fine'], 2) : '-'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination -->
                    <nav>
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php if ($i === $page) echo 'active'; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php else: ?>
                    <p class="text-muted">No books borrowed yet.</p>
                <?php endif; ?>
            </div>
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
