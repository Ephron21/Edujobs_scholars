# Student Registration System

A comprehensive web-based application for managing student registrations with full CRUD (Create, Read, Update, Delete) functionality.

## Features

- **Student Registration**: Register new students with personal and academic information
- **Student Management**: View, edit, and delete student records
- **Batch Operations**: Delete multiple student records at once
- **Search Functionality**: Easily find students by name, email, or registration number
- **Print Support**: Generate printable reports for individual or all students
- **Grade Levels**: Supports 4 academic levels (1-4) with color coding
- **Responsive Design**: Works on mobile, tablet, and desktop devices

## System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache, Nginx, etc.)

## Installation

1. Clone or download this repository to your web server's document root
2. Create a MySQL database named `registration_system`
3. Import the `setup_database.php` file to set up the database structure
4. Update database connection settings in the following files if needed:
   - `config.php`
   - `backend/db_connection.php`
   - `simple_create_table.php`
   - All PHP files with direct database connections

## File Structure

- `form.html` - Main student registration form using AJAX
- `form_alt.html` - Alternative registration form using traditional form submission
- `proxy.php` - Proxy for handling AJAX form submissions
- `manage_students.php` - Main interface for viewing and managing students
- `view_student.php` - Detailed view of a single student
- `edit_student.php` - Form for editing student information
- `student_print.php` - Printer-friendly view of student records
- `batch_operations.php` - Interface for bulk operations
- `backend/api/students_create.php` - API endpoint for creating students
- `check_students.php` - Shows all registered students
- `update_table_structure.php` - Script for updating database structure

## Database Structure

The system uses a single `students` table with the following key fields:
- `id` (Auto-incremented primary key)
- `first_name` (Required)
- `last_name` (Required)
- `email` (Required, unique)
- `reg_number` (Required, unique)
- `password` (Stored as plaintext for demonstration purposes)
- `grade_level` (Integer values 1-4)
- Various optional fields for additional student information
- `created_at` and `updated_at` timestamps

## Usage

1. Open `form.html` in a web browser to register a new student
2. Access `manage_students.php` to view, search, edit, or delete student records
3. Use batch operations by selecting multiple students and clicking the "Delete Selected" button
4. Print student information using the print options in the management interface

## Security Notes

This system is designed for educational purposes and includes several practices that should be improved for production:

- Passwords are stored in plaintext (should use password hashing)
- Direct database connections in multiple files (should use a central config)
- Limited input validation and sanitization
- No user authentication/authorization system

## Credits

Created by [Your Name/Organization]

## License

This project is licensed under the MIT License - see the LICENSE file for details.
# Edujobs_scholars