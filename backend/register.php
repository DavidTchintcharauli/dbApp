<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$config = include 'config.php';
$servername = $config['db']['host'];
$username = $config['db']['username'];
$password = $config['db']['password'];
$dbname = $config['db']['dbname'];

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['username']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid input"]);
    exit();
}

$username = $conn->real_escape_string($data['username']);
$password = password_hash($conn->real_escape_string($data['password']), PASSWORD_BCRYPT);

$sql = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
$sql->bind_param("ss", $username, $password);

if ($sql->execute()) {
    echo json_encode(["success" => true, "message" => "User registered successfully"]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error: " . $conn->error]);
}

$sql->close();
$conn->close();
?>