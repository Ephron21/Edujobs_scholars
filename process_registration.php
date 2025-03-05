<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "localhost"; // Change if using a remote database
$username = "root"; // Your database username
$password = "Diano21@Esron21%"; // Your database password
$dbname = "registration_system"; // Change to your actual database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("<div class='error'>❌ Database connection failed: " . $conn->connect_error . "</div>");
}

// Validate and retrieve form data
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Capture form data
    $firstname = $_POST['firstname'] ?? "";
    $lastname = $_POST['lastname'] ?? "";
    $email = $_POST['email'] ?? "";
    $phone = $_POST['phone'] ?? "";
    $gender = $_POST['gender'] ?? "";
    $father_name = $_POST['father_name'] ?? "";
    $father_phone = $_POST['father_phone'] ?? "";
    $mother_name = $_POST['mother_name'] ?? "";
    $mother_phone = $_POST['mother_phone'] ?? "";
    $province = $_POST['province'] ?? "";
    $district = $_POST['district'] ?? "";
    $sector = $_POST['sector'] ?? "";
    $cell = $_POST['cell'] ?? "";
    $village = $_POST['village'] ?? "";

    // File upload handling
    $uploadDir = "uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    function uploadFile($fileInputName) {
        global $uploadDir;
        if (!empty($_FILES[$fileInputName]["name"])) {
            $fileName = time() . "_" . basename($_FILES[$fileInputName]["name"]);
            $targetFilePath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES[$fileInputName]["tmp_name"], $targetFilePath)) {
                return $targetFilePath;
            } else {
                return false;
            }
        }
        return "";
    }

    $id_document = uploadFile("id_document");
    $diploma = uploadFile("diploma");
    $profile_image = uploadFile("profile_image");

    if (!$id_document || !$diploma || !$profile_image) {
        die("<div class='error'>❌ File upload failed. Please try again.</div>");
    }

    // SQL Insert Statement
    $stmt = $conn->prepare("INSERT INTO applicants 
        (firstname, lastname, email, phone, gender, 
         father_name, father_phone, mother_name, mother_phone, 
         province, district, sector, cell, village, 
         id_document, diploma, profile_image) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        die("<div class='error'>❌ Error preparing statement: " . $conn->error . "</div>");
    }

    $stmt->bind_param(
        "sssssssssssssssss",
        $firstname, $lastname, $email, $phone, $gender,
        $father_name, $father_phone, $mother_name, $mother_phone,
        $province, $district, $sector, $cell, $village,
        $id_document, $diploma, $profile_image
    );

    if ($stmt->execute()) {
        echo "<div class='success'>✅ Data inserted successfully!</div>";
        echo "<div class='center'><a href='index.php' class='button'>🏠 Back to Home</a></div>";
    } else {
        echo "<div class='error'>❌ Error inserting data: " . $stmt->error . "</div>";
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
} else {
    echo "<div class='error'>❌ Invalid request method.</div>";
}
?>

<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        text-align: center;
        padding: 50px;
    }
    .success {
        color: green;
        font-size: 20px;
        font-weight: bold;
        margin-bottom: 20px;
    }
    .error {
        color: red;
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 20px;
    }
    .center {
        margin-top: 20px;
    }
    .button {
        text-decoration: none;
        background-color: #007BFF;
        color: white;
        padding: 10px 20px;
        border-radius: 5px;
        font-size: 16px;
    }
    .button:hover {
        background-color: #0056b3;
    }
</style>
