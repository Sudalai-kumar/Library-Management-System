<?php include 'header.php'; ?>
<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'library_management_system');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$role = ucfirst($_SESSION['role']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Books</title>
    <link href="bootstrap.min.css" rel="stylesheet">
    <script src="jquery-3.6.0.min.js"></script>
    <style>
        /* Sticky Search Bar */
        .sticky-search {
            position: sticky;
            top: 0;
            z-index: 1000;
            background-color: #fff;
            padding: 10px 0;
            border-bottom: 2px solid #ddd;
        }

        /* Responsive Table */
        .table-responsive {
            overflow-x: auto;
        }

        /* Pagination Styling */
        .pagination-btn {
            min-width: 40px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container my-5">
        <!-- Sticky Search Section -->
        <div class="sticky-search">
            <h2>Search Books</h2>
            <div class="input-group mb-3">
                <input type="text" id="searchInput" class="form-control" placeholder="Start typing to search...">
            </div>
        </div>

        <!-- Results Section -->
        <div id="searchResults" class="table-responsive mt-4">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Genre</th>
                        <th>Barcode</th>
                        <th>Availability</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Results will be dynamically inserted here -->
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div id="pagination" class="d-flex justify-content-center mt-3">
            <!-- Pagination buttons will be dynamically generated here -->
        </div>

        <!-- Back to Dashboard -->
        <div class="text-center mt-4">
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>

    <!-- AJAX Script -->
    <script>
        $(document).ready(function () {
            function fetchResults(query = '', page = 1) {
                $.ajax({
                    url: 'search_books_ajax.php',
                    type: 'POST',
                    data: {
                        search_query: query,
                        page: page
                    },
                    success: function (response) {
                        // Populate Results
                        let resultsHTML = '';
                        response.books.forEach(book => {
                            resultsHTML += `
                                <tr>
                                    <td>${book.title}</td>
                                    <td>${book.author}</td>
                                    <td>${book.genre}</td>
                                    <td>${book.barcode}</td>
                                    <td>${book.availability === "1" ? "Available" : "Borrowed"}</td>
                                </tr>
                            `;
                        });
                        $('#searchResults tbody').html(resultsHTML);

                        // Populate Pagination
                        let paginationHTML = '';
                        for (let i = 1; i <= response.total_pages; i++) {
                            paginationHTML += `
                                <button class="btn btn-sm btn-primary mx-1 pagination-btn" data-page="${i}">
                                    ${i}
                                </button>
                            `;
                        }
                        $('#pagination').html(paginationHTML);

                        // Add click event to pagination buttons
                        $('.pagination-btn').on('click', function () {
                            const page = $(this).data('page');
                            fetchResults(query, page);
                        });
                    }
                });
            }

            // Trigger search on typing
            $('#searchInput').on('input', function () {
                const query = $(this).val();
                fetchResults(query);
            });

            // Initial fetch
            fetchResults();
        });
    </script>
</body>

</html>
<?php include 'footer.php'; ?>