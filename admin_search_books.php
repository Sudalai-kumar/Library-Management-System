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
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container my-5">
            <h2 class="text-center mb-4">Search Books</h2>
            
            <!-- Search Form -->
            <form method="post" action="" class="d-flex mb-4">
                <input type="text" name="search_query" value="<?php echo $search_query; ?>" 
                       class="form-control me-2" placeholder="Enter title, author, or barcode">
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
            
            <!-- Results -->
            <?php if (!empty($books)): ?>
                <h3 class="mb-3">Search Results:</h3>
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Genre</th>
                            <th>Barcode</th>
                            <th>Availability</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($books as $book): ?>
                        <tr>
                            <td><?php echo $book['title']; ?></td>
                            <td><?php echo $book['author']; ?></td>
                            <td><?php echo $book['genre']; ?></td>
                            <td><?php echo $book['barcode']; ?></td>
                            <td><?php echo $book['availability'] ? "Available" : "Borrowed"; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php elseif (isset($message)): ?>
                <div class="alert alert-warning"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <a href="dashboard.php" class="btn btn-secondary mt-4">Back to Dashboard</a>
        </div>
    </body>
</html>

<?php include 'footer.php'; ?>
