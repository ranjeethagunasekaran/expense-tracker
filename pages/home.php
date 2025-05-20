<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$host = "localhost";
$user = "root";
$password = "";
$dbname = "expense_tracker";
$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Fetch the user's name
$user_name = "User";
$res = $conn->query("SELECT name FROM users WHERE id = $user_id");
if ($row = $res->fetch_assoc()) {
    $user_name = $row['name'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Financial Tools</title>
    <link rel="stylesheet" href="../css/home.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet" />
</head>
<body>
    <div class="main-container">
        <h1 class="welcome-heading">
            Hi <?= htmlspecialchars($user_name) ?>, Welcome to your 
            <span style="color: #043354">personal finance tracker!</span>
        </h1>
    </div>
    <nav class="navbar">
        <div class="nav-content">
            <h1>Expense Tracker</h1>
            <ul class="nav-links">
                <li><a href="home.php">Home</a></li>
                <li><a href="convertor.html">Currency Converter</a></li>
                <li><a href="bill_split.html">Bill Splitter</a></li>
                <li><a href="personaltracker.php">Personal Finance</a></li>
                <li><a href="savings_planner.html">Savings Planner</a></li>
                <li><a href="../php/dashboard.php">Profile</a></li>
                <li><a href="../php/logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>
    <div class="container">
        <div class="card">
            <div class="card-content">
                <a href="personaltracker.php">
                    <img src="../images/5208999.jpg" alt="Personal Finance" />
                    <h3>Personal Finance</h3>
                    <p>Manage your finances effectively with tools designed for budgeting and savings.</p>
                    <a href="personaltracker.php"><button class="btn">See more</button></a>
                </a>
            </div>
        </div>
        <div class="card">
            <div class="card-content">
                <a href="savings_planner.html">
                    <img src="../images/partner.png" alt="Family and Partner Saving" />
                    <h3>Family & Partner Saving</h3>
                    <p>Plan and save together for a brighter financial future for your family.</p>
                    <a href="savings_planner.html"><button class="btn">See more</button></a>
                </a>
            </div>
        </div>
        <div class="card">
            <div class="card-content">
                <a href="convertor.html">
                    <img src="../images/convertor.jpg" alt="Currency Converter" />
                    <h3>Currency Converter</h3>
                    <p>Convert currencies easily with real-time exchange rates at your fingertips.</p>
                    <a href="convertor.html"><button class="btn">See more</button></a>
                </a>
            </div>
        </div>
        <div class="card">
            <div class="card-content">
                <a href="bill_split.html">
                    <img src="../images/networthh.png" alt="Bill Splitter" />
                    <h3>Bill Splitter</h3>
                    <p>Calculate your net worth to get a better understanding of your financial health.</p>
                    <a href="bill_split.html"><button class="btn">See more</button></a>
                </a>
            </div>
        </div>
    </div>
</body>
</html>