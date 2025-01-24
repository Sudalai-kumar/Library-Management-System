<?php
$conn = new mysqli('localhost', 'root', '', 'library_management_system');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$search_query = isset($_POST['search_query']) ? $_POST['search_query'] : '';
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$results_per_page = 10;
$offset = ($page - 1) * $results_per_page;

// Fetch results
$sql = "
    SELECT b.*, 
        CASE 
            WHEN title LIKE '%$search_query%' THEN 3
            WHEN author LIKE '%$search_query%' THEN 2
            WHEN barcode LIKE '%$search_query%' THEN 1
            ELSE 0
        END AS relevance,
        IF(b.id IN (SELECT book_id FROM borrowed_books WHERE return_date IS NULL), 0, 1) AS availability
    FROM books b
    WHERE is_removed = 0
    ORDER BY relevance DESC
    LIMIT $offset, $results_per_page;
";
$result = $conn->query($sql);
$books = $result->fetch_all(MYSQLI_ASSOC);

// Total Results for Pagination
$sql_total = "
    SELECT COUNT(*) AS total
    FROM books
    WHERE is_removed = 0
";
$result_total = $conn->query($sql_total);
$total_results = $result_total->fetch_assoc()['total'];
$total_pages = ceil($total_results / $results_per_page);

// Return JSON
header('Content-Type: application/json');
echo json_encode(['books' => $books, 'total_pages' => $total_pages]);
