<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'library_management_system');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle search query
$search_query = "";
$books = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $search_query = $_POST['search_query'];
    $sql = "SELECT * FROM books 
    WHERE (title LIKE '%$search_query%' 
           OR author LIKE '%$search_query%' 
           OR barcode LIKE '%$search_query%') 
    AND is_removed = 0";
    echo $sql;
    $result = $conn->query($sql);

    // Log the search action
    $user_id = $_SESSION['user_id'];
    $log_action = "Search";
    $log_details = "Searched for: $search_query";
    $log_sql = "INSERT INTO activity_logs (user_id, action, details) VALUES ('$user_id', '$log_action', '$log_details')";
    $conn->query($log_sql);

    if ($result->num_rows > 0) {
        $books = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $message = "No books found!";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Books</title>
</head>
<body>
    <h2>Search Books</h2>
    <form method="post" action="">
        <input type="text" name="search_query" value="<?php echo $search_query; ?>" placeholder="Enter title, author, or barcode">
        <button type="submit">Search</button>
    </form>

    <?php if (!empty($books)): ?>
        <h3>Search Results:</h3>
        <ul>
            <?php foreach ($books as $book): ?>
                <li>
                    <strong><?php echo $book['title']; ?></strong> by <?php echo $book['author']; ?> 
                    <?php echo $book['availability'] ? "(Available)" : "(Borrowed)"; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php elseif (isset($message)): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>

    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>
