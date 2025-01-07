<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    .card:hover {
        transform: scale(1.05); /* Slight zoom effect */
        transition: all 0.3s ease-in-out; /* Smooth animation */
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); /* Add a shadow */
    }
</style>
</head>
<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card shadow-lg p-4" style="width: 100%; max-width: 400px;">
        <h2 class="text-center mb-4">Library Management System</h2>
            <form method="post" action="">
                <div class="mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password:</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
                <div class="mt-3 text-center text-danger">
                    <?php
                    session_start();
                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                        $conn = new mysqli('localhost', 'root', '', 'library_management_system');
                        if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

                        $email = $_POST['email'];
                        $password = $_POST['password'];

                        $query = "SELECT * FROM users WHERE email='$email'";
                        $result = $conn->query($query);

                        if ($result->num_rows > 0) {
                            $user = $result->fetch_assoc();
                            if (password_verify($password, $user['password'])) {
                                $_SESSION['user_id'] = $user['register_number'];
                                $_SESSION['role'] = $user['role'];
                        
                                // Redirect based on role
                                if ($user['role'] === 'admin') {
                                    header("Location: dashboard.php"); // Admin dashboard
                                } elseif ($user['role'] === 'student') {
                                    header("Location: student_dashboard.php"); // Student dashboard
                                } elseif ($user['role'] === 'faculty') {
                                    header("Location: faculty_dashboard.php"); // Faculty dashboard
                                } else {
                                    echo "Invalid role.";
                                }
                                exit;
                            } else {
                                echo "Invalid password.";
                            }
                        } else {
                            echo "No user found with this email.";
                        }
                        $conn->close();
                        
                    }
                    ?>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
