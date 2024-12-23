<?php
$sql_overdue_books = "
SELECT bb.student_id, u.email, u.name AS student_name, b.title, b.author, bb.due_date
FROM borrowed_books bb
JOIN books b ON bb.book_id = b.id
JOIN users u ON bb.student_id = u.register_number
WHERE bb.return_date IS NULL AND bb.due_date < CURDATE()";
$result_overdue_books = $conn->query($sql_overdue_books);
$overdue_books = $result_overdue_books->fetch_all(MYSQLI_ASSOC);

foreach ($overdue_books as $overdue) {
    $to = $overdue['email'];
    $subject = "Overdue Book Reminder";
    $message = "
    Dear {$overdue['student_name']},

    This is a reminder that the following book(s) are overdue:

    Title: {$overdue['title']}
    Author: {$overdue['author']}
    Due Date: {$overdue['due_date']}

    Please return the book(s) as soon as possible to avoid additional fines.

    Thank you,
    Library Management Team
    ";
    $headers = "From: library@sankarsystem.edu"; // Update with your email address

    // Send the email
    mail($to, $subject, $message, $headers);
}
?>
