<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Check if the user is an admin
if(!isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
    // Not an admin, redirect to access denied page
    header("location: access_denied.php");
    exit;
}

// Include database configuration
require_once "config/db_connect.php";

// Set the backup file name with timestamp
$backup_file = 'backup_' . date("Y-m-d_H-i-s") . '.sql';
$backup_path = 'backups/';

// Create backups directory if it doesn't exist
if (!file_exists($backup_path)) {
    mkdir($backup_path, 0777, true);
}

// Get all tables from the database
$tables = array();
$result = $mysqli->query("SHOW TABLES");
while ($row = $result->fetch_row()) {
    $tables[] = $row[0];
}

$return = '';

// Cycle through each table
foreach ($tables as $table) {
    $result = $mysqli->query("SELECT * FROM " . $table);
    $num_fields = $result->field_count;
    $num_rows = $result->num_rows;

    $return .= 'DROP TABLE IF EXISTS ' . $table . ';';
    $row2 = $mysqli->query("SHOW CREATE TABLE " . $table)->fetch_row();
    $return .= "\n\n" . $row2[1] . ";\n\n";

    if ($num_rows > 0) {
        $return .= 'INSERT INTO ' . $table . ' VALUES';
        $counter = 0;

        while ($row = $result->fetch_row()) {
            if ($counter != 0) {
                $return .= ',';
            }
            $return .= "\n(";

            for ($j = 0; $j < $num_fields; $j++) {
                if (isset($row[$j])) {
                    $row[$j] = addslashes($row[$j]);
                    $row[$j] = str_replace("\n", "\\n", $row[$j]);
                    $return .= '"' . $row[$j] . '"';
                } else {
                    $return .= 'NULL';
                }
                if ($j < ($num_fields - 1)) {
                    $return .= ',';
                }
            }
            $return .= ")";
            $counter++;
        }
        $return .= ";\n\n";
    }
}

// Save the backup file
$handle = fopen($backup_path . $backup_file, 'w+');
fwrite($handle, $return);
fclose($handle);

// Force download the backup file
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename=' . basename($backup_file));
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($backup_path . $backup_file));
readfile($backup_path . $backup_file);

// Delete the backup file after download
unlink($backup_path . $backup_file);

exit; 