# Student Registration System

This system allows you to manage student registrations with a user-friendly form and backend database.

## Features

- Student registration form with validation
- Secure data storage in a MySQL database
- Print functionality for student records
- Support for both individual and bulk printing

## Setup Instructions

1. Ensure you have PHP and MySQL installed on your server.
2. Copy all files to your web server directory.
3. Update database connection settings in:
   - `config.php`
   - `backend/db_connection.php`
   - `setup_database.php`
   
   Make sure the username and password match your MySQL credentials.
   
4. Run the database setup script by visiting:
   ```
   http://your-server/path-to-app/setup_database.php
   ```

5. Once setup is complete, you can access the registration form at:
   ```
   http://your-server/path-to-app/form.html
   ```

## File Structure

- `form.html` - The main student registration form
- `backend/api/students_create.php` - API endpoint for student registration
- `backend/db_connection.php` - Database connection file for API
- `students.sql` - SQL file with table structure
- `student_print.php` - Script for printing student information
- `setup_database.php` - Database setup script
- `config.php` - Main configuration file

## Usage

1. Fill out the student registration form with required information
2. Submit the form to register the student
3. Use the print section at the bottom of the form to print student records
   - "All Students" prints information for all registered students
   - "Selected Students" allows you to print specific students by ID

## Security Notes

- Form validation is implemented both client-side and server-side
- Password fields are securely hashed before storage
- All user inputs are sanitized to prevent SQL injection

## Troubleshooting

If you encounter any issues:

1. Check that your database credentials are correct
2. Ensure the web server has write permissions to the necessary directories
3. Check PHP error logs for detailed error messages

For further assistance, please contact the system administrator. 