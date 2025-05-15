<?php
// Set headers at the very beginning of the file
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

// Database configuration
$host = "localhost";
$user = "root";
$password = "rehan7123"; // If you have set a password, add it here
$dbname = "db_form";

try {
    // Create connection
    $conn = new mysqli($host, $user, $password);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Create database if it doesn't exist
    $conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
    
    // Select the database
    $conn->select_db($dbname);

    // Create table if it doesn't exist
    $createTable = "CREATE TABLE IF NOT EXISTS kontak (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        pesan TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($createTable)) {
        throw new Exception("Error creating table: " . $conn->error);
    }

    // Set charset to handle special characters properly
    $conn->set_charset("utf8mb4");

    // Validate and sanitize inputs
    $nama = isset($_POST['nama']) ? trim($_POST['nama']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $pesan = isset($_POST['pesan']) ? trim($_POST['pesan']) : '';

    // Input validation
    if (empty($nama) || empty($email) || empty($pesan)) {
        throw new Exception('Semua field harus diisi');
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Format email tidak valid');
    }

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO kontak (nama, email, pesan) VALUES (?, ?, ?)");
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("sss", $nama, $email, $pesan);

    // Execute the statement
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Pesan berhasil dikirim!'
        ]);
    } else {
        throw new Exception("Error executing statement: " . $stmt->error);
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>
