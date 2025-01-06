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

// Initialize variables
$search_query = "";
$books = [];
$message = "";

// Handle search query
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $search_query = $conn->real_escape_string($_POST['search_query']); // Prevent SQL injection
    $sql = "SELECT * FROM books 
            WHERE (title LIKE '%$search_query%' 
                   OR author LIKE '%$search_query%' 
                   OR barcode LIKE '%$search_query%') 
            AND is_removed = 0";
    $result = $conn->query($sql);

    // Log the search action
    $user_id = $_SESSION['user_id'];
    $log_action = "Search";
    $log_details = "Searched for: $search_query";
    $log_sql = "INSERT INTO activity_logs (user_id, action, details) VALUES ('$user_id', '$log_action', '$log_details')";
    $conn->query($log_sql);

    if ($result && $result->num_rows > 0) {
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container my-5">
        <h2 class="text-center mb-4">Search Books</h2>

        <!-- Search Form -->
        <form method="post" action="" class="mb-4">
            <div class="input-group">
                <input type="text" name="search_query" class="form-control" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Enter title, author, or barcode">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </form>

        <!-- Search Results -->
        <?php if (!empty($books)): ?>
            <h3 class="mb-3">Search Results:</h3>
            <ul class="list-group">
                <?php foreach ($books as $book): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>
                            <strong><?php echo $book['title']; ?></strong> by <?php echo $book['author']; ?>
                        </span>
                        <span class="badge bg-<?php echo $book['availability'] ? 'success' : 'danger'; ?>">
                            <?php echo $book['availability'] ? "Available" : "Borrowed"; ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php elseif ($message): ?>
            <div class="alert alert-warning"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php
        $dashboard_url = ($_SESSION['role'] === 'student') ? 'student_dashboard.php' : 'faculty_dashboard.php';
        ?>
        <a href="<?php echo $dashboard_url; ?>" class="btn btn-secondary mt-3">Back to Dashboard</a>

    </div>
</body>

</html>