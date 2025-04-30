<?php
// Database credentials
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'Diano21@Esron21%');
define('DB_NAME', 'edujobs_scholars');

// First connect without database selection
$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if ($mysqli->query($sql) === FALSE) {
    die("Error creating database: " . $mysqli->error);
}

// Select the database
if (!$mysqli->select_db(DB_NAME)) {
    die("Error selecting database: " . $mysqli->error);
}

// Create the student_reports table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS student_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_name VARCHAR(255) NOT NULL,
    registration_number VARCHAR(50) NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    class_name VARCHAR(50) NOT NULL,
    conduct_score INT,
    
    -- Core Subjects
    kinyarwanda_score DECIMAL(5,2),
    kiswahili_score DECIMAL(5,2),
    literature_score DECIMAL(5,2),
    entrepreneurship_score DECIMAL(5,2),
    gsc_score DECIMAL(5,2),
    
    -- Non-Core Subjects
    english_score DECIMAL(5,2),
    french_score DECIMAL(5,2),
    ict_score DECIMAL(5,2),
    physical_education_score DECIMAL(5,2),
    religion_score DECIMAL(5,2),
    
    -- Additional Fields
    total_score DECIMAL(5,2),
    percentage DECIMAL(5,2),
    position VARCHAR(20),
    class_teacher_remarks TEXT,
    parent_signature BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($mysqli->query($sql) === FALSE) {
    die("Error creating table: " . $mysqli->error);
}

// Set charset to UTF8
$mysqli->set_charset("utf8mb4");
?> 