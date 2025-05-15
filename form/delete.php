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
$password = "rehan7123";
$dbname = "db_form";

try {
    // Create connection
    $conn = new mysqli($host, $user, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Check if ID was provided
    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        throw new Exception('ID tidak valid');
    }

    $id = intval($_POST['id']);

    // Prepare and bind
    $stmt = $conn->prepare("DELETE FROM kontak WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("i", $id);

    // Execute the statement
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Data berhasil dihapus!'
            ]);
        } else {
            throw new Exception('Data tidak ditemukan');
        }
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