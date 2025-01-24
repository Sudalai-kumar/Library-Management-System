-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 17, 2025 at 09:55 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `library_management_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` varchar(20) NOT NULL,
  `action` varchar(50) NOT NULL,
  `details` text DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `details`, `timestamp`) VALUES
(1, 'A001', 'Borrow Book', 'Borrowed Book ID: 2, Student ID: s002', '2025-01-07 07:51:09'),
(2, 'A001', 'Borrow Book', 'Borrowed Book ID: 3, Student ID: f001', '2025-01-07 07:52:30'),
(3, 'A001', 'Borrow Book', 'Borrowed Book ID: 4, Student ID: f002', '2025-01-07 07:53:13'),
(4, 'A001', 'Return Book', 'Returned Book ID: 3, Register Number: f001, Fine: ₹0', '2025-01-07 07:53:50'),
(5, 'A001', 'Return Book', 'Returned Book ID: 1, Register Number: s001, Fine: ₹0', '2025-01-07 07:55:04'),
(6, 'A001', 'Borrow Book', 'Borrowed Book ID: 1, Student ID: s002', '2025-01-07 08:31:12'),
(7, 'A001', 'Return Book', 'Returned Book ID: 1, Register Number: s002, Fine: ₹0', '2025-01-07 08:31:35'),
(8, 'A001', 'Borrow Book', 'Borrowed Book ID: 3, Student ID: s002', '2025-01-07 08:32:10'),
(9, 'S002', 'Search', 'Searched for: 456', '2025-01-07 08:59:08'),
(10, 'A001', 'Return Book', 'Returned Book ID: 2, Register Number: s002, Fine: ₹5', '2025-01-07 13:22:29'),
(11, 'A001', 'Search', 'Searched for: 1', '2025-01-07 13:51:46'),
(12, 'S002', 'Search', 'Searched for: 1', '2025-01-07 13:53:13'),
(13, 'S002', 'Search', 'Searched for: 1', '2025-01-08 09:35:51'),
(14, 'A001', 'Search', 'Searched for: 1', '2025-01-08 09:39:11'),
(15, 'A001', 'Search', 'Searched for: 1', '2025-01-17 07:04:45'),
(16, 'A001', 'Search', 'Searched for: 1', '2025-01-17 07:05:40'),
(17, 'A001', 'Search', 'Searched for: ', '2025-01-17 07:05:49'),
(18, 'A001', 'Search', 'Searched for: 1', '2025-01-17 07:05:58'),
(19, 'A001', 'Search', 'Searched for: to', '2025-01-17 07:06:08'),
(20, 'A001', 'Search', 'Searched for: t', '2025-01-17 07:06:13'),
(21, 'A001', 'Search', 'Searched for: The Great ', '2025-01-17 07:06:44'),
(22, 'A001', 'Search', 'Searched for: The Great ', '2025-01-17 07:07:35'),
(23, 'A001', 'Search', 'Searched for: The Great ', '2025-01-17 07:07:48'),
(24, 'A001', 'Borrow Book', 'Borrowed Book ID: 6, Student ID: s002', '2025-01-17 08:11:55');

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `genre` varchar(100) DEFAULT NULL,
  `barcode` varchar(50) NOT NULL,
  `availability` tinyint(1) NOT NULL DEFAULT 1,
  `is_removed` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `title`, `author`, `genre`, `barcode`, `availability`, `is_removed`) VALUES
(1, 'To Kill a Mockingbird', 'Harper Lee', 'Classic', '1234567890', 1, 0),
(2, '1984', 'George Orwell', 'Dystopian', '9876543210', 1, 0),
(3, 'Pride and Prejudice', 'Jane Austen', 'Romance', '1122334455', 0, 0),
(4, 'The Great Gatsby', 'F. Scott Fitzgerald', 'Classic', '5566778899', 0, 0),
(5, 'The Catcher in the Rye', 'J.D. Salinger', 'Classic', '2233445566', 1, 0),
(6, 'Brave New World', 'Aldous Huxley', 'Dystopian', '3344556677', 0, 0),
(7, 'The Hobbit', 'J.R.R. Tolkien', 'Fantasy', '4455667788', 1, 0),
(8, 'Fahrenheit 451', 'Ray Bradbury', 'Dystopian', '5566778890', 1, 0),
(9, 'Moby-Dick', 'Herman Melville', 'Adventure', '6677889900', 1, 0),
(10, 'War and Peace', 'Leo Tolstoy', 'Historical', '7788990011', 1, 0),
(11, 'Anna Karenina', 'Leo Tolstoy', 'Romance', '8899001122', 1, 0),
(12, 'Crime and Punishment', 'Fyodor Dostoevsky', 'Crime', '9900112233', 1, 0),
(13, 'The Brothers Karamazov', 'Fyodor Dostoevsky', 'Classic', '1112223344', 1, 0),
(14, 'Jane Eyre', 'Charlotte Brontë', 'Romance', '1223344556', 1, 0),
(15, 'Wuthering Heights', 'Emily Brontë', 'Gothic', '1334455667', 1, 0),
(16, 'Frankenstein', 'Mary Shelley', 'Horror', '1445566778', 1, 0),
(17, 'Dracula', 'Bram Stoker', 'Horror', '1556677889', 1, 0),
(18, 'Great Expectations', 'Charles Dickens', 'Classic', '1667788990', 1, 0),
(19, 'A Tale of Two Cities', 'Charles Dickens', 'Historical', '1778899900', 1, 0),
(20, 'Don Quixote', 'Miguel de Cervantes', 'Adventure', '1889900011', 1, 0),
(21, 'The Divine Comedy', 'Dante Alighieri', 'Poetry', '1990011122', 1, 0),
(22, 'Les Misérables', 'Victor Hugo', 'Historical', '2001122233', 1, 0),
(23, 'The Iliad', 'Homer', 'Epic', '2112233344', 1, 0),
(24, 'The Odyssey', 'Homer', 'Epic', '2223344455', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `borrowed_books`
--

CREATE TABLE `borrowed_books` (
  `id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `borrow_date` date NOT NULL,
  `due_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `fine` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrowed_books`
--

INSERT INTO `borrowed_books` (`id`, `book_id`, `student_id`, `borrow_date`, `due_date`, `return_date`, `fine`) VALUES
(1, 1, 'S001', '2025-01-01', '2025-01-14', '2025-01-07', 0.00),
(2, 2, 'S002', '2024-12-15', '2024-12-29', '2024-12-20', 0.00),
(3, 2, 's002', '2024-12-18', '2025-01-01', '2025-01-07', 5.00),
(4, 3, 'f001', '2025-01-07', '2025-01-21', '2025-01-07', 0.00),
(5, 4, 'f002', '2025-01-07', '2025-01-21', NULL, 0.00),
(6, 1, 's002', '2025-01-07', '2025-01-21', '2025-01-07', 0.00),
(7, 3, 's002', '2025-01-07', '2025-01-21', NULL, 0.00),
(8, 6, 's002', '2024-12-20', '2025-01-03', NULL, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `reservation_date` date NOT NULL DEFAULT curdate(),
  `expiration_date` date NOT NULL,
  `status` enum('pending','fulfilled','expired') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `book_id`, `student_id`, `reservation_date`, `expiration_date`, `status`) VALUES
(1, 3, 'S002', '2025-01-07', '2025-01-14', 'fulfilled'),
(2, 3, 'S002', '2025-01-08', '2025-01-15', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `register_number` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','student','faculty') NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`register_number`, `name`, `email`, `role`, `password`) VALUES
('A001', 'Admin User', 'admin@example.com', 'admin', '$2y$10$zriT10jaDOu7C51jgrfYReSPQ/TdxRDs8FekD7IVf4Z6oh7uHaEKi'),
('F001', 'Dr. Alice Brown', 'alicebrown@example.com', 'faculty', '$2y$10$PaPKdC7Vf3NdAIi.PBWo8OmOJwye3.pwKocMHEInV/KW2wS8xPl0e'),
('F002', 'Prof. Bob White', 'bobwhite@example.com', 'faculty', '$2y$10$PaPKdC7Vf3NdAIi.PBWo8OmOJwye3.pwKocMHEInV/KW2wS8xPl0e'),
('S001', 'Sudalai', 'sudalai@example.com', 'student', '$2y$10$mCO.eT2czRciUMDmqkQZv.s/ctBVVbtxjuypqle7/Mb3vGsTWuJnq'),
('S002', 'vasu', 'vasu@example.com', 'student', '$2y$10$mCO.eT2czRciUMDmqkQZv.s/ctBVVbtxjuypqle7/Mb3vGsTWuJnq');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `barcode` (`barcode`);

--
-- Indexes for table `borrowed_books`
--
ALTER TABLE `borrowed_books`
  ADD PRIMARY KEY (`id`),
  ADD KEY `book_id` (`book_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `book_id` (`book_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`register_number`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `borrowed_books`
--
ALTER TABLE `borrowed_books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`register_number`);

--
-- Constraints for table `borrowed_books`
--
ALTER TABLE `borrowed_books`
  ADD CONSTRAINT `borrowed_books_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`),
  ADD CONSTRAINT `borrowed_books_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`register_number`);

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`),
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`register_number`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
