<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Installing Dependencies</h1>";

// Check if composer.phar exists
if (!file_exists('composer.phar')) {
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin-top: 20px; border-radius: 5px;'>";
    echo "<h2>Composer Not Found</h2>";
    echo "<p>Please follow these steps to install Composer manually:</p>";
    echo "<ol>";
    echo "<li>Download Composer from: <a href='https://getcomposer.org/Composer-Setup.exe' target='_blank'>https://getcomposer.org/Composer-Setup.exe</a></li>";
    echo "<li>Run the downloaded installer</li>";
    echo "<li>After installation, open Command Prompt and run these commands:</li>";
    echo "<pre>cd \"D:\\Xampp App\\htdocs\\Edujobs_scholars\"\ncomposer install</pre>";
    echo "</ol>";
    echo "<p>If you can't download Composer, you can also:</p>";
    echo "<ol>";
    echo "<li>Download the latest composer.phar from: <a href='https://getcomposer.org/composer.phar' target='_blank'>https://getcomposer.org/composer.phar</a></li>";
    echo "<li>Save it in your project directory (D:\\Xampp App\\htdocs\\Edujobs_scholars)</li>";
    echo "<li>Then refresh this page</li>";
    echo "</ol>";
    echo "</div>";
    exit;
}

// Run composer install
echo "<p>Running Composer install...</p>";
$output = [];
$return_var = 0;
exec('php composer.phar install 2>&1', $output, $return_var);

if ($return_var === 0) {
    echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; margin-top: 20px; border-radius: 5px;'>";
    echo "<h2>Installation Complete</h2>";
    echo "<p>Dependencies installed successfully!</p>";
    echo "<p>Output:</p>";
    echo "<pre>" . implode("\n", $output) . "</pre>";
    echo "</div>";
} else {
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin-top: 20px; border-radius: 5px;'>";
    echo "<h2>Installation Failed</h2>";
    echo "<p>Error installing dependencies:</p>";
    echo "<pre>" . implode("\n", $output) . "</pre>";
    echo "<p>Please try running these commands manually in Command Prompt:</p>";
    echo "<pre>cd \"D:\\Xampp App\\htdocs\\Edujobs_scholars\"\ncomposer install</pre>";
    echo "</div>";
} 