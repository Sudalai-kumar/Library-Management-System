<?php
// Define your password
$password = "satlas";

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Display the hashed password
echo "Hashed Password: " . $hashed_password;
?>
