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
$sql_overdue = "SELECT b.title, bb.due_date, CEIL(DATEDIFF(CURDATE(), bb.due_date) / 7) * 0 AS current_fine
FROM borrowed_books bb
JOIN books b ON bb.book_id = b.id
WHERE bb.student_id = '$user_id' 
AND bb.return_date IS NULL 
AND bb.due_date < CURDATE()";

$result_overdue = $conn->query($sql_overdue);
$overdue_books = $result_overdue->fetch_all(MYSQLI_ASSOC);
// Fetch user's name
$user_id = $_SESSION['user_id'];
$sql = "SELECT name FROM users WHERE register_number = '$user_id'";
$result = $conn->query($sql);
$user = $result->fetch_assoc();


// Optional: Fetch user-specific data like borrowed books
$sql = "SELECT b.title,b.author,bb.borrow_date,bb.due_date,bb.return_date, bb.fine,
       CASE 
           WHEN bb.return_date IS NOT NULL AND bb.fine > 0 THEN 'Returned with Fine'
           WHEN bb.return_date IS NOT NULL THEN 'Returned'
           WHEN bb.due_date < CURDATE() THEN 'Overdue'
           ELSE 'Currently Borrowed'
       END AS status,
       CASE 
           WHEN bb.due_date < CURDATE() AND bb.return_date IS NULL 
           THEN CEIL(DATEDIFF(CURDATE(), bb.due_date) / 7) * 0
           ELSE 0
       END AS current_fine
FROM borrowed_books bb
JOIN books b ON bb.book_id = b.id
WHERE bb.student_id = '$user_id'
ORDER BY bb.borrow_date DESC";
$result = $conn->query($sql);
$borrowed_books = $result->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $role; ?> Dashboard</title>
    <link href="bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container my-5">
        <h2 class="text-center mb-4">Welcome to the Library Management System, <?php echo htmlspecialchars($user['name']); ?>!</h2>
        <!-- Navigation Links -->
        <nav class="nav justify-content-center mb-4">
            <!-- <a class="nav-link" href="view_borrowed_books.php">View Borrowed Books</a> -->
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
                                Current fine:
                                <span class="text-danger">₹<?php echo number_format($book['current_fine'], 2); ?></span>
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
                <?php if (!empty($borrowed_books)): ?>
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
                                    <td>
                                        <?php
                                        if ($book['status'] === 'Returned with Fine') {
                                            echo "₹" . number_format($book['fine'], 2);
                                        } elseif ($book['status'] === 'Overdue') {
                                            echo "₹" . number_format($book['current_fine'], 2);
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted">No books borrowed yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>