<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.html");
    exit();
}

$host = "localhost";
$user = "root";
$password = "";
$dbname = "expense_tracker";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Handle income submission
if (isset($_POST['submit_income'])) {
    $income = $_POST['income'];
    $current_month = $_POST['current_month'];
    
    // Check if user already has income for this month
    $checkStmt = $conn->prepare("SELECT id FROM finance WHERE user_id = ? AND type = 'income' AND DATE_FORMAT(created_at, '%Y-%m') = ?");
    $checkStmt->bind_param("is", $user_id, $current_month);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        // User already has income for this month
        $_SESSION['message'] = "You've already recorded income for this month. Only one income entry is allowed per month.";
        $_SESSION['message_type'] = "error";
        header("Location: ../pages/personaltracker.php");
        exit();
    }
    
    // Insert new income entry
    $stmt = $conn->prepare("INSERT INTO finance (user_id, type, amount, created_at) VALUES (?, 'income', ?, NOW())");
    $stmt->bind_param("id", $user_id, $income);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Income added successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error adding income: " . $conn->error;
        $_SESSION['message_type'] = "error";
    }
    
    $stmt->close();
    header("Location: ../pages/personaltracker.php");
    exit();
}

// Handle expense submission
if (isset($_POST['submit_expense'])) {
    $amount = $_POST['amount'];
    $category = $_POST['category'];
    $reason = $_POST['reason'];
    
    $stmt = $conn->prepare("INSERT INTO finance (user_id, type, amount, category, reason, created_at) VALUES (?, 'expense', ?, ?, ?, NOW())");
    $stmt->bind_param("idss", $user_id, $amount, $category, $reason);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Expense added successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error adding expense: " . $conn->error;
        $_SESSION['message_type'] = "error";
    }
    
    $stmt->close();
    header("Location: ../pages/personaltracker.php");
    exit();
}

$conn->close();
header("Location: ../pages/personaltracker.php");
?>