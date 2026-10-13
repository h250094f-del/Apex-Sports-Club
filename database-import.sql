-- ==============================================================
-- Apex Sports Club — phpMyAdmin Import Script
-- ISE 2102: Web Technologies Semester Project
-- Specifically prepared for InfinityFree phpMyAdmin:
-- * Does NOT contain "CREATE DATABASE" or "USE" (since InfinityFree
--   assigns a fixed database name such as epiz_XXXX_apex).
-- * Safe to import directly into your assigned database tab.
-- ==============================================================

-- --------------------------------------------------------------
-- Table: members (Student membership form submissions)
-- --------------------------------------------------------------
DROP TABLE IF EXISTS `members`;
CREATE TABLE `members` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `submitted_at` DATETIME NOT NULL,
  `full_name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `student_id` VARCHAR(20) DEFAULT NULL,
  `course` VARCHAR(150) NOT NULL,
  `study_year` VARCHAR(50) NOT NULL,
  `preferred_activity` VARCHAR(100) NOT NULL,
  `experience_level` VARCHAR(50) NOT NULL DEFAULT 'Beginner',
  `form_purpose` VARCHAR(100) NOT NULL DEFAULT 'Join the club',
  `reason` TEXT DEFAULT NULL,
  `consent` VARCHAR(10) NOT NULL DEFAULT 'yes',
  `status` VARCHAR(30) NOT NULL DEFAULT 'Pending Review',
  INDEX `idx_email` (`email`),
  INDEX `idx_activity` (`preferred_activity`),
  INDEX `idx_submitted_at` (`submitted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------
-- Table: admins (Staff & Admin portal users)
-- --------------------------------------------------------------
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `role` VARCHAR(50) NOT NULL DEFAULT 'Administrator',
  `created_at` DATETIME NOT NULL,
  `last_login` DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------
-- Default Administrator Account
-- Username: admin
-- Password: Password123!
-- --------------------------------------------------------------
INSERT INTO `admins` (`username`, `password_hash`, `full_name`, `role`, `created_at`)
VALUES (
  'admin',
  '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm',
  'Sports Club Director',
  'Lead Administrator',
  NOW()
);

-- --------------------------------------------------------------
-- Initial Sample Members (Demonstrates retrieval in admin.php)
-- --------------------------------------------------------------
INSERT INTO `members` (
  `submitted_at`, `full_name`, `email`, `student_id`, `course`,
  `study_year`, `preferred_activity`, `experience_level`,
  `form_purpose`, `reason`, `consent`, `status`
) VALUES
(
  NOW() - INTERVAL 2 DAY,
  'Tadiwa Makoni',
  'h250101a@hit.ac.zw',
  'h250101a',
  'Software Engineering',
  'Year 2',
  'Football',
  'Intermediate',
  'Join the club',
  'Played central midfield in high school. Looking forward to interfaculty fixtures.',
  'yes',
  'Approved'
),
(
  NOW() - INTERVAL 1 DAY,
  'Ruvimbo Chidziwa',
  'h250204b@hit.ac.zw',
  'h250204b',
  'Information Technology',
  'Year 1',
  'Basketball',
  'Beginner',
  'Join the club',
  'Eager to learn fundamentals and participate in weekly 3v3 friendly shootarounds.',
  'yes',
  'Pending Review'
),
(
  NOW() - INTERVAL 5 HOUR,
  'Kudakwashe Moyo',
  'h250315c@hit.ac.zw',
  'h250315c',
  'Electronic Commerce',
  'Year 3',
  'Fitness',
  'Experienced',
  'Event enquiry',
  'Inquiring about personal gym induction sessions at H2 gym and circuit training.',
  'yes',
  'Pending Review'
);
