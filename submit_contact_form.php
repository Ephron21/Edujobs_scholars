<?php
// Database connection
$servername = "localhost";
$username = "root"; // Replace with your database username
$password = "Diano21@Esron21%"; // Replace with your database password
$dbname = "registration_system"; // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$name = $_POST['name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$message = $_POST['message'];

// Insert data into the database
$sql = "INSERT INTO contact_form (name, email, phone, message) VALUES ('$name', '$email', '$phone', '$message')";

if ($conn->query($sql) === TRUE) {
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Thank You</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f7fc;
                color: #333;
                margin: 0;
                padding: 0;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                text-align: center;
            }
            .confirmation-container {
                background-color: #fff;
                padding: 40px;
                border-radius: 10px;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                max-width: 500px;
                width: 100%;
                animation: fadeIn 0.5s ease-in-out;
            }
            h2 {
                font-size: 2rem;
                margin-bottom: 20px;
                color: #007bff;
            }
            .back-to-home {
                margin-top: 20px;
            }
            .back-to-home a {
                background-color: #007bff;
                color: white;
                padding: 12px 24px;
                border-radius: 5px;
                text-decoration: none;
                font-size: 1rem;
                transition: background-color 0.3s ease-in-out, transform 0.2s ease-in-out;
            }
            .back-to-home a:hover {
                background-color: #0056b3;
                transform: translateY(-2px);
            }
            @keyframes fadeIn {
                0% { opacity: 0; transform: translateY(-20px); }
                100% { opacity: 1; transform: translateY(0); }
            }
        </style>
    </head>
    <body>
        <div class='confirmation-container'>
            <h2>Thank you for contacting us! 🎉</h2>
            <p>We will get back to you soon.</p>
            <div class='back-to-home'>
                <a href='index.php'>Back to Home</a>
            </div>
        </div>
    </body>
    </html>";
} else {
    echo "<div style='text-align: center; padding: 20px;'>
            <h2 style='color: red;'>Error: " . $sql . "<br>" . $conn->error . "</h2>
          </div>";
}

$conn->close();
?>