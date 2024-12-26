<?php include 'header.php'; ?>

<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'admin') {
    header("Location: index.php");
    exit;
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'library_management_system');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get borrowed books for the logged-in user
$user_id = $_SESSION['user_id'];
$sql = "SELECT b.title, b.author, bb.borrow_date, bb.due_date
        FROM borrowed_books bb
        JOIN books b ON bb.book_id = b.id
        WHERE bb.user_id = '$user_id' AND bb.return_date IS NULL";
$result = $conn->query($sql);
$borrowed_books = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrowed Books</title>
</head>

<body>
    <h2>Your Borrowed Books</h2>

    <?php if (!empty($borrowed_books)): ?>
        <ul>
            <?php foreach ($borrowed_books as $book): ?>
                <li>
                    <strong><?php echo $book['title']; ?></strong> by <?php echo $book['author']; ?><br>
                    Borrowed on: <?php echo $book['borrow_date']; ?><br>
                    Due on: <?php echo $book['due_date']; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>You have not borrowed any books.</p>
    <?php endif; ?>

    <a href="dashboard.php">Back to Dashboard</a>
</body>

</html>
<?php include 'footer.php'; ?>