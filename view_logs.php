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

// Fetch activity logs
$sql = "SELECT al.*, u.name AS user_name FROM activity_logs al 
        JOIN users u ON al.user_id = u.register_number 
        ORDER BY al.timestamp DESC";
$result = $conn->query($sql);
$logs = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs</title>
    <link href="bootstrap.min.css" rel="stylesheet">
    <script src="jquery-3.6.0.min.js"></script>
    <style>
        .table-responsive {
            max-height: 500px;
            overflow-y: auto;
        }
        .sticky-header {
            position: sticky;
            top: 0;
            background-color: white;
            z-index: 100;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container my-5">
        <h2 class="text-center mb-4">Activity Logs</h2>

        <!-- Search Bar -->
        <div class="input-group mb-3">
            <input type="text" id="searchInput" class="form-control" placeholder="Search logs by user, action, or details...">
        </div>

        <!-- Logs Table -->
        <?php if (!empty($logs)): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light sticky-header">
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Details</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?php echo $log['id']; ?></td>
                                <td><?php echo $log['user_name']; ?></td>
                                <td><?php echo $log['action']; ?></td>
                                <td><?php echo $log['details']; ?></td>
                                <td><?php echo $log['timestamp']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-warning text-center">No activity logs found.</div>
        <?php endif; ?>

        <!-- Export and Back Buttons -->
        <div class="d-flex justify-content-between align-items-center mt-4">
            <form method="get" action="export.php">
                <input type="hidden" name="type" value="activity_logs">
                <button type="submit" class="btn btn-success">Export Activity Logs</button>
            </form>
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>

    <!-- Search Script -->
    <script>
        $(document).ready(function () {
            $("#searchInput").on("input", function () {
                const query = $(this).val().toLowerCase();
                $("tbody tr").each(function () {
                    const rowText = $(this).text().toLowerCase();
                    $(this).toggle(rowText.includes(query));
                });
            });
        });
    </script>
</body>

</html>

<?php include 'footer.php'; ?>
