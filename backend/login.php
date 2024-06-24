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

$data = json_encode(file_get_contents("php://input"), true);

if (!isset($data['username']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid input"]);
    exit();
}

$username = $conn->real_escape_string($data['username']);
$password = $data['password'];

$sql = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
$sql->bind_param("s", $username);
$sql->execute();
$sql->store_result();

if ($sql->num_rows > 0) {
    $sql->bind_result($user_id, $hashed_password);
    $sql->fetch();

    if (password_verify($password, $hashed_password)) {
        echo json_encode(["success" => true, "message" => "Login successful", "user_id" => $user_id]);
    } else {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Invalid username or password"]);
    }
} else {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Invalid username or password"]);
}

$sql->close();
$conn->close();
?>