<!DOCTYPE html>
<html lang="en">
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
if ($conn->connect_error) die("DB connection error");

$user_id = $_SESSION['user_id'];
$current_month = date('Y-m');

// Check if user already has income for current month
$hasIncomeThisMonth = false;
$incomeThisMonth = 0;
$incomeDate = '';

$checkIncomeQuery = "SELECT amount, created_at FROM finance 
                    WHERE user_id = ? AND type = 'income' 
                    AND DATE_FORMAT(created_at, '%Y-%m') = ?";
$stmt = $conn->prepare($checkIncomeQuery);
$stmt->bind_param("is", $user_id, $current_month);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $hasIncomeThisMonth = true;
    $incomeThisMonth = $row['amount'];
    $incomeDate = date("j F Y", strtotime($row['created_at']));
}
$stmt->close();

// Total income
$totalIncome = 0;
$res = $conn->query("SELECT SUM(amount) AS total FROM finance WHERE user_id=$user_id AND type='income'");
if ($row = $res->fetch_assoc()) {
    $totalIncome = $row['total'] ?? 0;
}

// Total expenses
$totalExpenses = 0;
$res = $conn->query("SELECT SUM(amount) AS total FROM finance WHERE user_id=$user_id AND type='expense'");
if ($row = $res->fetch_assoc()) {
    $totalExpenses = $row['total'] ?? 0;
}

$savings = $totalIncome - $totalExpenses;

// Category-wise breakdown
$expenseBreakdown = ["food" => 0, "rent" => 0, "utilities" => 0, "entertainment" => 0];
$res = $conn->query("SELECT category, SUM(amount) AS total FROM finance WHERE user_id=$user_id AND type='expense' GROUP BY category");
while ($row = $res->fetch_assoc()) {
    $cat = strtolower(trim($row['category']));
    if (array_key_exists($cat, $expenseBreakdown)) {
        $expenseBreakdown[$cat] = $row['total'];
    }
}

// Get monthly history for journal display
$journal_entries = [];
$journal_query = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, 
                 SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
                 SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense
                 FROM finance 
                 WHERE user_id = ?
                 GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                 ORDER BY month DESC";
$stmt = $conn->prepare($journal_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $journal_entries[] = $row;
}
$stmt->close();
?>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Personal Finance Overview</title>
  <link rel="stylesheet" href="../css/personaltracker.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    /* Additional styles for journal/diary look */
    .journal-container {
        max-width: 800px;
        margin: 30px auto;
        background-color: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .journal-title {
        text-align: center;
        color: #3797db;
        margin-bottom: 20px;
        font-family: 'Courier New', Courier, monospace;
        font-size: 1.6em;
        border-bottom: 2px solid #3797db;
        padding-bottom: 10px;
    }
    
    .journal-entry {
        background-color: #f9f9f9;
        border-left: 4px solid #4CAF50;
        margin-bottom: 15px;
        padding: 15px;
        border-radius: 0 5px 5px 0;
        transition: all 0.3s ease;
    }
    
    .journal-entry:hover {
        transform: translateX(5px);
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .journal-date {
        font-weight: bold;
        color: #4CAF50;
        margin-bottom: 8px;
        font-size: 1.1em;
    }
    
    .journal-details {
        display: flex;
        justify-content: space-between;
        margin-top: 10px;
    }
    
    .journal-income, .journal-expense, .journal-savings {
        flex: 1;
        padding: 5px;
        text-align: center;
    }
    
    .journal-income {
        color: #4CAF50;
    }
    
    .journal-expense {
        color: #F44336;
    }
    
    .journal-savings {
        color: #3797db;
        font-weight: bold;
    }
    
    .monthly-income-notice {
        background-color: #e8f5e9;
        padding: the 15px;
        border-radius: 5px;
        margin-bottom: 15px;
        border-left: 4px solid #4CAF50;
    }
    
    .income-date {
        font-style: italic;
        color: #666;
        font-size: 0.9em;
    }
  </style>
</head>
<body>
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
    <h3 class="fade-in">Enter Your Income</h3>
    <?php if ($hasIncomeThisMonth): ?>
        <div class="monthly-income-notice">
            <p>You've already recorded your income for this month (<?= date('F Y') ?>).</p>
            <p>Current income: ₹<?= number_format($incomeThisMonth, 2) ?></p>
            <p class="income-date">Recorded on: <?= $incomeDate ?></p>
        </div>
    <?php else: ?>
        <form id="incomeForm" action="../php/save_finance.php" method="POST">
          <label for="income">Total Income for <?= date('F Y') ?> (₹):</label>
          <input type="number" id="income" name="income" required min="1" placeholder="Enter your income" />
          <input type="hidden" name="current_month" value="<?= $current_month ?>">
          <button type="submit" name="submit_income">Set Income</button>
        </form>
    <?php endif; ?>
  </div>

  <table id="summaryTable" class="fade-in fade-in-left">
    <tr>
      <th>Total Income</th>
      <th>Total Expenses</th>
      <th>Savings</th>
    </tr>
    <tr>
      <td id="totalIncome">₹<?= number_format((float)$totalIncome, 2) ?></td>
      <td id="totalExpenses">₹<?= number_format((float)$totalExpenses, 2) ?></td>
      <td id="savings">₹<?= number_format((float)$savings, 2) ?></td>
    </tr>
  </table>

  <div class="exp">
    <div class="exp_breakdown">
      <h3 class="fade-in fade-in-right">Expenses Breakdown</h3>
      <table class="fade-in fade-in-right">
        <tr><th>Category</th><th>Amount</th></tr>
        <tr><td>Food</td><td id="foodExpense">₹<?= number_format((float)$expenseBreakdown['food'], 2) ?></td></tr>
        <tr><td>Rent</td><td id="rentExpense">₹<?= number_format((float)$expenseBreakdown['rent'], 2) ?></td></tr>
        <tr><td>Utilities</td><td id="utilitiesExpense">₹<?= number_format((float)$expenseBreakdown['utilities'], 2) ?></td></tr>
        <tr><td>Entertainment</td><td id="entertainmentExpense">₹<?= number_format((float)$expenseBreakdown['entertainment'], 2) ?></td></tr>
      </table>
    </div>

    <div class="addexp">
      <h3 class="fade-in">Add Expense</h3>
      <form id="incomeForm" action="../php/save_finance.php" method="POST">
        <label for="category">Select Category:</label>
        <select id="category" name="category" required>
          <option value="food">Food</option>
          <option value="rent">Rent</option>
          <option value="utilities">Utilities</option>
          <option value="entertainment">Entertainment</option>
        </select>
        <label for="amount">Amount (in ₹):</label>
        <input type="number" id="amount" name="amount" required min="1" />
        <label for="reason">Reason:</label>
        <input type="text" id="reason" name="reason" required placeholder="e.g. Bought burger" />
        <button type="submit" name="submit_expense">Add Expense</button>
      </form>
    </div>
  </div>

  <div class="exp_history">
    <h3 class="fade-in">Expense History</h3>
    <table id="historyTable" class="fade-in">
      <thead>
        <tr><th>Date</th><th>Type</th><th>Rupees</th><th>Category</th><th>Reason</th></tr>
      </thead>
      <tbody id="historyTableBody">
        <?php
        // Modified to include date
        $history_query = "SELECT type, amount, category, reason, created_at FROM finance 
                         WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $conn->prepare($history_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $date = date("j M Y", strtotime($row['created_at']));
            $type = ucfirst($row['type']);
            $amount = number_format((float)$row['amount'], 2);
            $category = $row['category'] ? ucfirst($row['category']) : "-";
            $reason = $row['reason'] ? htmlspecialchars($row['reason']) : "-";
            
            echo "<tr>
                <td>{$date}</td>
                <td>{$type}</td>
                <td>₹{$amount}</td>
                <td>{$category}</td>
                <td>{$reason}</td>
            </tr>";
        }
        $stmt->close();
        ?>
      </tbody>
    </table>
  </div>

  <div class="chart-container">
    <canvas id="expenseChart"></canvas>
    <br><br>
  </div>
  
  <!-- New Journal/Diary Section -->
  <div class="journal-container">
    <h3 class="journal-title">My Financial Journal</h3>
    
    <?php if (empty($journal_entries)): ?>
        <p>No entries in your financial journal yet. Start by adding your income and expenses!</p>
    <?php else: ?>
        <?php foreach ($journal_entries as $entry): 
            $month_year = date("F Y", strtotime($entry['month'] . "-01"));
            $month_savings = $entry['income'] - $entry['expense'];
            $savings_class = $month_savings >= 0 ? "positive" : "negative";
        ?>
            <div class="journal-entry">
                <div class="journal-date"><?= $month_year ?></div>
                <div class="journal-details">
                    <div class="journal-income">
                        <div>Income</div>
                        <div>₹<?= number_format((float)$entry['income'], 2) ?></div>
                    </div>
                    <div class="journal-expense">
                        <div>Expenses</div>
                        <div>₹<?= number_format((float)$entry['expense'], 2) ?></div>
                    </div>
                    <div class="journal-savings <?= $savings_class ?>">
                        <div>Savings</div>
                        <div>₹<?= number_format((float)$month_savings, 2) ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
  </div>
  <br><br>
  
  <script>
    const totalIncomeEl = document.getElementById("totalIncome");
    const totalExpensesEl = document.getElementById("totalExpenses");
    const savingsEl = document.getElementById("savings");

    const foodExpenseEl = document.getElementById("foodExpense");
    const rentExpenseEl = document.getElementById("rentExpense");
    const utilitiesExpenseEl = document.getElementById("utilitiesExpense");
    const entertainmentExpenseEl = document.getElementById("entertainmentExpense");

    const historyTableBody = document.getElementById("historyTableBody");

    let expenses = {
      food: <?= (float)$expenseBreakdown['food'] ?>,
      rent: <?= (float)$expenseBreakdown['rent'] ?>,
      utilities: <?= (float)$expenseBreakdown['utilities'] ?>,
      entertainment: <?= (float)$expenseBreakdown['entertainment'] ?>
    };

    let totalIncome = <?= (float)$totalIncome ?>;
    let totalExpenses = <?= (float)$totalExpenses ?>;
    let savings = <?= (float)$savings ?>;

    function formatCurrency(num) {
      return "₹" + num.toLocaleString("en-IN", { minimumFractionDigits: 2 });
    }

    const expenseData = {
      labels: ["Food", "Rent", "Utilities", "Entertainment"],
      datasets: [{
        label: "Expenses Breakdown",
        data: [expenses.food, expenses.rent, expenses.utilities, expenses.entertainment],
        backgroundColor: ["#4CAF50", "#F44336", "#FF9800", "#2196F3"],
        borderColor: "#fff",
        borderWidth: 1,
      }],
    };

    const config = {
      type: "pie",
      data: expenseData,
      options: {
        responsive: true,
        plugins: {
          legend: { position: "top" },
          title: { display: true, text: "Expenses Breakdown" },
        },
      },
    };

    const expensesChart = new Chart(document.getElementById("expenseChart"), config);
  </script>
</body>
</html>