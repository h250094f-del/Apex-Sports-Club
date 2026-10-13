-- ==============================================================
-- Apex Sports Club — Database Schema & Initial Data
-- ISE 2102: Web Technologies Semester Project
-- Target DBMS: MySQL 5.7 / 8.x / MariaDB (InfinityFree compatible)
-- ==============================================================

-- Create database if not exists (local use; on InfinityFree, create via cPanel)
CREATE DATABASE IF NOT EXISTS `apex_sports_club`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `apex_sports_club`;

-- --------------------------------------------------------------
-- Table 1: members
-- Stores all student applications submitted through join.html
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
-- Table 2: admins
-- Stores admin accounts for the staff portal (admin-login.php)
-- Passwords are encrypted using PHP password_hash() (Bcrypt)
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
-- Seed Data: Default Administrator Account
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
)
ON DUPLICATE KEY UPDATE `username` = `username`;

-- --------------------------------------------------------------
-- Sample Student Applications (Demonstrates initial retrieved records)
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
