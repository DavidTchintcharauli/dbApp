<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$config = include 'config.php';
$servername = $config['db']['host'];
$username = $config['db']['username'];
$password = $config['db']['password'];
$dbname = $config['db']['dbname'];

// Create connection to MySQL server without selecting a database
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]);
    exit();
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if (!$conn->query($sql)) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database creation failed: " . $conn->error]);
    exit();
}

// Select the newly created or existing database
$conn->select_db($dbname);

// Create table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS messages (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    message TEXT NOT NULL
)";
if (!$conn->query($sql)) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Table creation failed: " . $conn->error]);
    exit();
}

// Handle POST and GET requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['message']) || empty($data['message'])) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Invalid input"]);
        exit();
    }

    $message = $conn->real_escape_string($data['message']);

    $sql = $conn->prepare("INSERT INTO messages (message) VALUES (?)");
    $sql->bind_param("s", $message);

    if ($sql->execute()) {
        echo json_encode(["success" => true, "message" => "Record added successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Error: " . $conn->error]);
    }

    $sql->close();
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT message FROM messages ORDER BY id DESC LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode(["success" => true, "message" => $row['message']]);
    } else {
        echo json_encode(["success" => false, "message" => "No records found"]);
    }
}

$conn->close();
?>
