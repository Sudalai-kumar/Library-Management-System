<?php include 'header.php'; ?>

<?php
session_start();

// Check if admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'library_management_system');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Popular Books
$sql_popular_books = "
SELECT b.title, b.author, COUNT(bb.id) AS borrow_count
FROM borrowed_books bb
JOIN books b ON bb.book_id = b.id
WHERE b.is_removed = 0
GROUP BY bb.book_id
ORDER BY borrow_count DESC
LIMIT 10";
$result_popular_books = $conn->query($sql_popular_books);
$popular_books = $result_popular_books->fetch_all(MYSQLI_ASSOC);
$book_titles = json_encode(array_column($popular_books, 'title'));
$borrow_counts = json_encode(array_column($popular_books, 'borrow_count'));

// Frequent Borrowers
$sql_frequent_borrowers = "
SELECT u.register_number, u.name AS student_name, COUNT(bb.id) AS borrow_count
FROM borrowed_books bb
JOIN users u ON bb.student_id = u.register_number
GROUP BY bb.student_id
ORDER BY borrow_count DESC
LIMIT 10";
$result_frequent_borrowers = $conn->query($sql_frequent_borrowers);
$frequent_borrowers = $result_frequent_borrowers->fetch_all(MYSQLI_ASSOC);
$borrower_names = json_encode(array_column($frequent_borrowers, 'student_name'));
$borrower_counts = json_encode(array_column($frequent_borrowers, 'borrow_count'));

// Monthly Activity
$sql_monthly_activity = "
SELECT DATE_FORMAT(borrow_date, '%Y-%m') AS month, COUNT(id) AS borrow_count
FROM borrowed_books
GROUP BY DATE_FORMAT(borrow_date, '%Y-%m')
ORDER BY month DESC";
$result_monthly_activity = $conn->query($sql_monthly_activity);
$monthly_activity = $result_monthly_activity->fetch_all(MYSQLI_ASSOC);
$months = json_encode(array_column($monthly_activity, 'month'));
$monthly_borrow_counts = json_encode(array_column($monthly_activity, 'borrow_count'));
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Reports</title>
    <link href="bootstrap.min.css" rel="stylesheet">
    <script src="chart.js"></script>
    <style>
        .card {
            height: 100%;
        }

        .card-header {
            font-size: 1.25rem;
            font-weight: bold;
        }

        .card-body {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .chart-container {
            height: 300px;
        }

        .list-group {
            max-height: 150px;
            overflow-y: auto;
        }

        @media (min-width: 992px) {
            .container {
                max-width: 1200px;
            }

            .row.g-4 > div {
                margin-bottom: 0;
            }
        }
    </style>
</head>

<body class="bg-light">
    <div class="container my-5">
        <h2 class="text-center mb-4">Library Reports</h2>
        <div class="row g-4">
            <!-- Popular Books -->
            <div class="col-lg-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        Popular Books
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="popularBooksChart"></canvas>
                        </div>
                        <ul class="list-group mt-3">
                            <?php foreach ($popular_books as $book): ?>
                                <li class="list-group-item">
                                    <strong><?php echo $book['title']; ?></strong> by <?php echo $book['author']; ?>
                                    (Borrowed <?php echo $book['borrow_count']; ?> times)
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <form method="get" action="export.php" class="text-center mt-3">
                            <input type="hidden" name="type" value="reports_popular_books">
                            <button type="submit" class="btn btn-success">Export Popular Books</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Frequent Borrowers -->
            <div class="col-lg-6">
                <div class="card shadow">
                    <div class="card-header bg-success text-white text-center">
                        Frequent Borrowers
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="frequentBorrowersChart"></canvas>
                        </div>
                        <ul class="list-group mt-3">
                            <?php foreach ($frequent_borrowers as $borrower): ?>
                                <li class="list-group-item">
                                    <?php echo $borrower['student_name']; ?>
                                    (Register Number: <?php echo $borrower['register_number']; ?>)
                                    - <?php echo $borrower['borrow_count']; ?> books borrowed
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <form method="get" action="export.php" class="text-center mt-3">
                            <input type="hidden" name="type" value="reports_frequent_borrowers">
                            <button type="submit" class="btn btn-success">Export Frequent Borrowers</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Activity -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-warning text-dark text-center">
                        Monthly Borrowing Activity
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="monthlyActivityChart"></canvas>
                        </div>
                        <ul class="list-group mt-3">
                            <?php foreach ($monthly_activity as $activity): ?>
                                <li class="list-group-item">
                                    <?php echo $activity['month']; ?>:
                                    <?php echo $activity['borrow_count']; ?> books borrowed
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <form method="get" action="export.php" class="text-center mt-3">
                            <input type="hidden" name="type" value="reports_monthly_activity">
                            <button type="submit" class="btn btn-success">Export Monthly Activity</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-5">
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>

    <!-- Chart.js Scripts -->
    <script>
        const popularBooksCtx = document.getElementById('popularBooksChart').getContext('2d');
        new Chart(popularBooksCtx, {
            type: 'bar',
            data: {
                labels: <?php echo $book_titles; ?>,
                datasets: [{
                    label: 'Borrow Count',
                    data: <?php echo $borrow_counts; ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        const frequentBorrowersCtx = document.getElementById('frequentBorrowersChart').getContext('2d');
        new Chart(frequentBorrowersCtx, {
            type: 'bar',
            data: {
                labels: <?php echo $borrower_names; ?>,
                datasets: [{
                    label: 'Books Borrowed',
                    data: <?php echo $borrower_counts; ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                indexAxis: 'y',
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });

        const monthlyActivityCtx = document.getElementById('monthlyActivityChart').getContext('2d');
        new Chart(monthlyActivityCtx, {
            type: 'line',
            data: {
                labels: <?php echo $months; ?>,
                datasets: [{
                    label: 'Books Borrowed',
                    data: <?php echo $monthly_borrow_counts; ?>,
                    borderColor: 'rgba(255, 99, 132, 1)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    fill: true,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>

</html>


<?php include 'footer.php'; ?>