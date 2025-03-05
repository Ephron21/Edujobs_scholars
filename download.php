<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit;
}

require_once 'config.php';

if (isset($_GET['id'])) {
    $fileId = $_GET['id'];

    // Fetch file details
    $stmt = $conn->prepare("SELECT * FROM uploaded_files WHERE id = ?");
    $stmt->execute([$fileId]);
    $file = $stmt->fetch();

    if ($file) {
        $filePath = $file['file_path'];
        if (file_exists($filePath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: ' . $file['file_type']);
            header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
        } else {
            die("File not found.");
        }
    } else {
        die("Invalid file ID.");
    }
} else {
    die("No file ID specified.");
}
?>