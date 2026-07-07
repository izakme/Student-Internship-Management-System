-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 07, 2026 at 01:29 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `internship_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `application_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `internship_id` int(11) DEFAULT NULL,
  `status` enum('Pending','Accepted','Rejected') DEFAULT 'Pending',
  `application_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`application_id`, `student_id`, `internship_id`, `status`, `application_date`) VALUES
(1, 1, 5, 'Accepted', '2026-07-01 13:10:42');

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `company_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `company_name` varchar(150) DEFAULT NULL,
  `location` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`company_id`, `user_id`, `company_name`, `location`, `phone`) VALUES
(1, 2, 'ABC Technologies', 'Dar es Salaam', '0712345678'),
(2, 3, 'Prime Cargo Logistics', 'Dodoma', '0755123456'),
(3, 4, 'Tech Solutions Ltd', 'Arusha', '0766123456');

-- --------------------------------------------------------

--
-- Table structure for table `internships`
--

CREATE TABLE `internships` (
  `internship_id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `title` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `deadline` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `internships`
--

INSERT INTO `internships` (`internship_id`, `company_id`, `title`, `description`, `requirements`, `deadline`) VALUES
(1, 1, 'Web Developer Intern', 'Develop and maintain web applications using PHP and MySQL.', 'Knowledge of HTML, CSS, JavaScript and PHP.', '2026-08-30'),
(2, 1, 'Network Administrator Intern', 'Assist in managing computer networks.', 'CCNA knowledge is an advantage.', '2026-09-15'),
(3, 1, 'Database Administrator Intern', 'Maintain MySQL databases.', 'Basic SQL knowledge.', '2026-09-20'),
(4, 1, 'Software Developer Intern', 'Develop software applications.', 'Knowledge of Java or PHP.', '2026-09-25'),
(5, 2, 'Softwre Engineer', 'Software Engineer Intern\r\n\r\nWe are looking for a motivated and enthusiastic Software Engineer Intern to join our development team. This internship offers hands-on experience in designing, developing, testing, and maintaining software applications while working alongside experienced software engineers. The successful candidate will gain practical exposure to real-world software development practices and modern technologies.\r\n\r\nKey Responsibilities\r\nAssist in designing, developing, and maintaining web or desktop applications.\r\nWrite clean, efficient, and well-documented code.\r\nTest, debug, and troubleshoot software issues.\r\nParticipate in code reviews and team meetings.\r\nCollaborate with developers, designers, and project managers to implement new features.\r\nAssist in maintaining databases and application documentation.\r\nLearn and apply software development best practices.', 'Requirements\r\nCurrently pursuing a Bachelor\'s degree in Computer Science, Information Technology, Software Engineering, or a related field.\r\nBasic knowledge of at least one programming language (e.g., Java, Python, C++, C#, PHP, or JavaScript).\r\nFamiliarity with HTML, CSS, and JavaScript.\r\nBasic understanding of databases such as MySQL or PostgreSQL.\r\nKnowledge of object-oriented programming (OOP) concepts.\r\nFamiliarity with version control systems such as Git is an advantage.\r\nBasic understanding of software development life cycle (SDLC).\r\nStrong analytical and problem-solving skills.\r\nGood written and verbal communication skills.\r\nWillingness to learn new technologies and work in a collaborative team environment.\r\nAbility to manage time effectively and meet project deadlines.\r\nMust be able to render the required internship hours as required by the educational institution.', '2026-09-01');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `report_id` int(11) NOT NULL,
  `generated_by` int(11) DEFAULT NULL,
  `report_type` varchar(100) DEFAULT NULL,
  `generated_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `report_name` varchar(255) NOT NULL,
  `report_data` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`report_id`, `generated_by`, `report_type`, `generated_date`, `report_name`, `report_data`) VALUES
(1, 1, 'Applications', '2026-07-02 17:23:02', 'Applications Report - 2026-07-02 19:23:02', '[]'),
(2, 1, 'Students', '2026-07-03 18:40:47', 'Students Report - 2026-07-03 20:40:47', '[{\"full_name\":\"Alvin Frank Changawa\",\"registration_no\":null,\"course\":null,\"year_of_study\":null,\"email\":\"changawalvin@gmail.com\",\"applications\":1},{\"full_name\":\"Isaack Changawa\",\"registration_no\":\"02.9198.01.01.2021\",\"course\":\"Diploma in Information Technology\",\"year_of_study\":2021,\"email\":\"changawaisaac016@gmail.com\",\"applications\":2}]');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `registration_no` varchar(100) DEFAULT NULL,
  `course` varchar(100) DEFAULT NULL,
  `year_of_study` int(11) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `user_id`, `registration_no`, `course`, `year_of_study`, `phone`) VALUES
(1, 5, '02.9198.01.01.2021', 'Diploma in Information Technology', 2021, '0754553483'),
(2, 6, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('student','company','admin') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'System Administrator', 'admin@internship.com', '$2b$12$uyhjd.em1dkpDe8uDNwfbOumdBzsGuzyHiiRT8.pq60/vcZgj.7IW', 'admin', '2026-07-01 11:33:17'),
(2, 'ABC Technologies', 'abc@gmail.com', '$2y$10$09LiCzZb.SzOfF/PWmA3AeE313vkN0SkNe3vaR7ii29zeYkFlcSKy', 'company', '2026-07-01 11:38:19'),
(3, 'Prime Cargo Logistics', 'prime@gmail.com', '$2y$10$5CSTq/5XjJZgem15fD/rLu0P0zi3kXarunYXRCd0wkgtu3h06OKu2', 'company', '2026-07-01 11:38:55'),
(4, 'Tech Solutions Ltd', 'tech@gmail.com', '$2y$10$cKuk.P6hOlySM0SPwAW4OecRvOGXre59nvCh4uzTabJmzIamU3Y9q', 'company', '2026-07-01 11:39:31'),
(5, 'Isaack Changawa', 'changawaisaac016@gmail.com', '$2y$10$BcaBurJmktL3mRBARLVH8u.k514P/qJxptaM4M/9QftAGB3FxeTBq', 'student', '2026-07-01 12:09:47'),
(6, 'Alvin Frank Changawa', 'changawalvin@gmail.com', '$2y$10$a7p9aU9mnMCIeAjWfLdldeMTI0.YyHYKxNhFCR4KA6P9oy8QRSSji', 'student', '2026-07-03 18:10:03');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`application_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `internship_id` (`internship_id`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`company_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `internships`
--
ALTER TABLE `internships`
  ADD PRIMARY KEY (`internship_id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`report_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `company_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `internships`
--
ALTER TABLE `internships`
  MODIFY `internship_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`internship_id`) REFERENCES `internships` (`internship_id`);

--
-- Constraints for table `companies`
--
ALTER TABLE `companies`
  ADD CONSTRAINT `companies_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `internships`
--
ALTER TABLE `internships`
  ADD CONSTRAINT `internships_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`);

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
