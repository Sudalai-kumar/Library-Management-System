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

// Optional: Fetch user-specific data like borrowed books
$sql = "SELECT b.title, b.author, bb.borrow_date, bb.due_date 
        FROM borrowed_books bb
        JOIN books b ON bb.book_id = b.id
        WHERE bb.student_id = '$user_id' AND bb.return_date IS NULL";
$result = $conn->query($sql);
$borrowed_books = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $role; ?> Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container my-5">
        <h2 class="text-center mb-4">Welcome to the Library Management System, <?php echo $role; ?></h2>
        
        <!-- Navigation Links -->
        <nav class="nav justify-content-center mb-4">
            <!-- <a class="nav-link" href="view_borrowed_books.php">View Borrowed Books</a> -->
            <a class="nav-link" href="search_books.php">Search Books</a>
            <a class="nav-link" href="profile.php">Profile</a>
            <a class="nav-link text-danger" href="logout.php">Logout</a>
        </nav>

        <!-- Borrowed Books Section -->
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <h5>Your Borrowed Books</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($borrowed_books)): ?>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Borrow Date</th>
                                <th>Due Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($borrowed_books as $book): ?>
                                <tr>
                                    <td><?php echo $book['title']; ?></td>
                                    <td><?php echo $book['author']; ?></td>
                                    <td><?php echo $book['borrow_date']; ?></td>
                                    <td><?php echo $book['due_date']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted">You have not borrowed any books.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
