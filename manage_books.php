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

// Initialize message
$message = "";

// Pagination setup
$results_per_page = 10; // Number of books per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Current page
$offset = ($page - 1) * $results_per_page; // Calculate offset

// Handle Add Book
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_book'])) {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $genre = $_POST['genre'];
    $barcode = $_POST['barcode'];
    $sql = "INSERT INTO books (title, author, genre, barcode) VALUES ('$title', '$author', '$genre', '$barcode')";
    $message = $conn->query($sql) ? "Book added successfully!" : "Error: " . $conn->error;
}

// Handle Import Books
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['import_books'])) {
    if (isset($_FILES['excel_file']['tmp_name']) && $_FILES['excel_file']['tmp_name'] != '') {
        $file = $_FILES['excel_file']['tmp_name'];
        try {
            $spreadsheet = IOFactory::load($file);
            $sheetData = $spreadsheet->getActiveSheet()->toArray();
            foreach ($sheetData as $index => $row) {
                if ($index == 0 || empty($row[0]) || empty($row[1]) || empty($row[3])) continue; // Skip headers or incomplete rows
                $title = $conn->real_escape_string($row[0]);
                $author = $conn->real_escape_string($row[1]);
                $genre = isset($row[2]) ? $conn->real_escape_string($row[2]) : "";
                $barcode = $conn->real_escape_string($row[3]);
                $conn->query("INSERT INTO books (title, author, genre, barcode) VALUES ('$title', '$author', '$genre', '$barcode')");
            }
            $message = "Books imported successfully!";
        } catch (Exception $e) {
            $message = "Error importing books: " . $e->getMessage();
        }
    } else {
        $message = "No file selected.";
    }
}

// Handle Delete Book
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_book'])) {
    $book_id = $_POST['book_id'];
    $sql = "UPDATE books SET is_removed = 1 WHERE id = '$book_id'";
    $message = $conn->query($sql) ? "Book marked as removed." : "Error: " . $conn->error;
}

// Fetch Books with pagination
$sql = "SELECT * FROM books WHERE is_removed = 0 LIMIT $results_per_page OFFSET $offset";
$result = $conn->query($sql);
$books = $result->fetch_all(MYSQLI_ASSOC);

// Fetch total book count for pagination
$sql_total = "SELECT COUNT(*) AS total FROM books WHERE is_removed = 0";
$result_total = $conn->query($sql_total);
$total_books = $result_total->fetch_assoc()['total'];
$total_pages = ceil($total_books / $results_per_page);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Books</title>
    <link href="bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container my-5">
        <h2 class="text-center mb-4">Manage Books</h2>

        <!-- Messages -->
        <?php if ($message): ?>
            <div class="alert alert-info text-center"><?php echo $message; ?></div>
        <?php endif; ?>

        <!-- Add Book -->
        <h3>Add a New Book:</h3>
        <form method="post" action="" class="mb-4">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="title" class="form-control" placeholder="Title" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="author" class="form-control" placeholder="Author" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="genre" class="form-control" placeholder="Genre">
                </div>
                <div class="col-md-3">
                    <input type="text" name="barcode" class="form-control" placeholder="Barcode" required>
                </div>
                <div class="col-md-12 text-end mt-2">
                    <button type="submit" name="add_book" class="btn btn-primary">Add Book</button>
                </div>
            </div>
        </form>

        <!-- Import Books -->
        <h3>Import Books:</h3>
        <form method="post" action="" enctype="multipart/form-data" class="mb-4">
            <div class="input-group">
                <input type="file" name="excel_file" class="form-control" accept=".xlsx, .xls" required>
                <button type="submit" name="import_books" class="btn btn-success">Import Books</button>
            </div>
        </form>

        <!-- Current Books -->
        <h3>Current Books:</h3>
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Genre</th>
                    <th>Barcode</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($books as $book): ?>
                    <tr>
                        <td><?php echo $book['title']; ?></td>
                        <td><?php echo $book['author']; ?></td>
                        <td><?php echo $book['genre']; ?></td>
                        <td><?php echo $book['barcode']; ?></td>
                        <td>
                            <form method="post" action="" class="d-inline">
                                <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                <button type="submit" name="delete_book" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

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

        <!-- Export Books -->
        <div class="text-end mb-3">
            <form method="get" action="export.php">
                <input type="hidden" name="type" value="books">
                <button type="submit" class="btn btn-success">Export Books</button>
            </form>
        </div>

        <div class="text-center mt-4">
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>
</body>

</html>

<?php include 'footer.php'; ?>