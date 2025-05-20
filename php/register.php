<?php
// Force content type to JSON
header('Content-Type: application/json');

// Enable error reporting (only during development — remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database credentials
$host = "localhost";
$user = "root";
$password = "";
$dbname = "expense_tracker";

// Create DB connection
$conn = new mysqli($host, $user, $password, $dbname);

// Connection check
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed: " . $conn->connect_error
    ]);
    exit;
}

// Check if POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validate required fields
    if (!isset($_POST['name']) || !isset($_POST['email']) || !isset($_POST['password'])) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "Missing required fields."
        ]);
        exit;
    }

    // Sanitize input
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "Invalid email format."
        ]);
        exit;
    }

    // Check if email already exists
    $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
    if (!$checkEmail) {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "SQL Error: " . $conn->error
        ]);
        exit;
    }

    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $result = $checkEmail->get_result();

    if ($result && $result->num_rows > 0) {
        echo json_encode([
            "status" => "error",
            "message" => "Email already registered."
        ]);
        $checkEmail->close();
        $conn->close();
        exit;
    }
    $checkEmail->close();

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "SQL Error: " . $conn->error
        ]);
        exit;
    }

    $stmt->bind_param("sss", $name, $email, $hashedPassword);

    if ($stmt->execute()) {
        echo json_encode([
            "status" => "success",
            "message" => "Registration successful!"
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "Failed to register user: " . $stmt->error
        ]);
    }

    $stmt->close();
    $conn->close();
} else {
    // Handle non-POST requests
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request method. POST required."
    ]);
    exit;
}
?>