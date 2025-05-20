<?php
session_start();
$host = "localhost";
$user = "root";
$password = "";
$dbname = "expense_tracker";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'] ?? 0;

$sql = "SELECT amount, category, reason FROM finance WHERE user_id = $user_id AND type='expense' ORDER BY created_at DESC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>₹" . number_format($row['amount'], 2) . "</td>
                <td>" . ucfirst($row['category']) . "</td>
                <td>" . htmlspecialchars($row['reason']) . "</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='3'>No expenses recorded yet.</td></tr>";
}
$conn->close();
?>