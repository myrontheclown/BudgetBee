<!DOCTYPE html>
<head>
  <title>BudgetBee</title>
  <style>
    body {
        margin: 0;
        background-color: #fff9cc; 
        font-family:Arial, sans-serif;
    }

    .sidebar {
        position: fixed;
        left: 0;
        top: 0;
        border-right: 2px solid black;
        height: 100%;
        width: 200px;
        background-color: #ffef89;
        padding-top: 20px;
    }

    .sidebar a {
        border: 2px solid black;
        margin: 10px;
        text-align: center;
        border-radius: 5px;
        text-decoration: none;
        display: block;
        background-color: #fdd835;
        color: black;
        padding: 12px 20px;
    }

    .sidebar a:hover {
        background-color: #f4b400;
        color: white;
    }

    .bottom-links {
        position: absolute;
        bottom: 20px; 
        width: 100%;
    }

    #settings {
        background-color: rgb(255, 74, 74);
    }

    #settings:hover {
        background-color: rgb(204, 0, 0);
        color: white;
    }
    .main {
        position: absolute;
        margin-left: 200px; 
        padding: 20px;
        text-align: center;
    }
    #total-amount {
        font-size: 48px;
        color: #1100ffff;
    }
    .amount-display {
        font-size: 36px;
        margin: 10px 0;
    }
    .income-amount {
        color: #28a745;
    }
    .expense-amount {
        color: #dc3545;
    }
    .budget-amount {
        color: #007bff;
    }
    .exceeded-amount {
        color: #dc3545;
        font-weight: bold;
    }
    .info-section {
        border: 2px solid black;
        border-radius: 10px;
        padding: 15px;
        margin: 15px auto;
        background-color: #f8f9fa;
        width: 80%;
    }
    .info-section h3 {
        margin: 5px 0;
        font-size: 24px;
    }

    #container{
        border: 2px solid black;
        border-radius: 20px;
        padding: 20px;
        margin-left: 300px;
        text-align: center;
        background-color: white;
        width: 100%;
    }
    .button-container {
        margin-top: 30px;
        display: flex;
        flex-direction: column;
        gap: 15px;
        align-items: center;
    }
    .dashboard-button {
        background-color: #007bff;
        color: white;
        font-size: 18px;
        border: 2px solid black;
        padding: 15px 30px;
        border-radius: 5px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        width: 250px;
    }
    .dashboard-button:hover {
        background-color: #0056b3;
    }
  </style>
</head>

<body>

    <div class="sidebar">
        <div class="top-links">
            <b>
                <a href="DashboardComponents/Transactions.php">Transactions</a>
                <a href="DashboardComponents/Budget.php">Budgets</a>
                <a href="DashboardComponents/Account.php">Accounts</a>
            </b>
        </div>

        <div class="bottom-links">
             <b><a href="Index.html" id="settings">Log Out</a></b>
        </div>
    </div>

    <div class="main">
        <div id="container">

        <?php 
        session_start();
        if (!isset($_SESSION['UserID'])) {
            echo "<script>alert('Please login first')</script>";
            exit();
        }

        $conn = mysqli_connect("localhost", "root", "", "database1");
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }

        $totalAmount = 0;
        $totalIncome = 0;
        $totalExpenses = 0;
        $budgetAmount = 0;
        $budgetExpenses = 0;
        $budgetExceeded = 0;
        $currentDate = date('Y-m-d');
        $userid = $_SESSION['UserID'];

        $query = "SELECT SUM(t.Amount) AS total FROM transactions t INNER JOIN account a ON t.AccountID = a.AccountID WHERE a.UserID = '$userid' AND t.TransactionType = 'Expense'";
        $result = mysqli_query($conn, $query);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            if (isset($row['total']) && $row['total'] !== null) {
                $totalAmount = $row['total'];
            } else {
                $totalAmount = 0;
            }
        }

        $incomeQuery = "SELECT SUM(t.Amount) AS total_income FROM transactions t INNER JOIN account a ON t.AccountID = a.AccountID WHERE a.UserID = '$userid' AND t.TransactionType = 'Income'";
        $incomeResult = mysqli_query($conn, $incomeQuery);
        if ($incomeResult) {
            $incomeRow = mysqli_fetch_assoc($incomeResult);
            if (isset($incomeRow['total_income']) && $incomeRow['total_income'] !== null) {
                $totalIncome = $incomeRow['total_income'];
            } else {
                $totalIncome = 0;
            }
        }

        $expenseQuery = "SELECT SUM(t.Amount) AS total_expenses FROM transactions t INNER JOIN account a ON t.AccountID = a.AccountID WHERE a.UserID = '$userid' AND t.TransactionType = 'Expense'";
        $expenseResult = mysqli_query($conn, $expenseQuery);
        if ($expenseResult) {
            $expenseRow = mysqli_fetch_assoc($expenseResult);
            if (isset($expenseRow['total_expenses']) && $expenseRow['total_expenses'] !== null) {
                $totalExpenses = $expenseRow['total_expenses'];
            } else {
                $totalExpenses = 0;
            }
        }

        if (isset($_SESSION['UserID'])) {
            $budgetQuery = "SELECT * FROM budget WHERE UserID = '$userid' AND StartDate <= '$currentDate' AND EndDate >= '$currentDate' ORDER BY BudgetID DESC";
            $budgetResult = mysqli_query($conn, $budgetQuery);

            if ($budgetResult && mysqli_num_rows($budgetResult) > 0) {
                $budgetRow = mysqli_fetch_assoc($budgetResult);
                if (isset($budgetRow['BudgetAmount']) && $budgetRow['BudgetAmount'] !== null) {
                    $budgetAmount = $budgetRow['BudgetAmount'];
                } else {
                    $budgetAmount = 0;
                }
                $budgetStartDate = $budgetRow['StartDate'];
                $budgetEndDate = $budgetRow['EndDate'];

                $budgetExpenseQuery = "
                    SELECT SUM(t.Amount) AS budget_expenses
                    FROM transactions t 
                    INNER JOIN account a ON t.AccountID = a.AccountID
                    WHERE a.UserID = '$userid'
                    AND t.TransactionType = 'Expense'
                    AND t.TransactionDate >= '$budgetStartDate'
                    AND t.TransactionDate <= '$budgetEndDate'
                ";
                $budgetExpenseResult = mysqli_query($conn, $budgetExpenseQuery);

                if ($budgetExpenseResult) {
                    $budgetExpenseRow = mysqli_fetch_assoc($budgetExpenseResult);
                    if (isset($budgetExpenseRow['budget_expenses']) && $budgetExpenseRow['budget_expenses'] !== null) {
                        $budgetExpenses = $budgetExpenseRow['budget_expenses'];
                    } else {
                        $budgetExpenses = 0;
                    }
                    if ($budgetExpenses > $budgetAmount) {
                        $budgetExceeded = $budgetExpenses - $budgetAmount;
                    }
                }
            }
        }
        ?>


            
            <h2>Total Transactions Amount: </h2>
            <h1 id="total-amount">₹<?php echo $totalAmount; ?></h1>
            
            <div class="info-section">
                <h3>Total Income</h3>
                <div class="amount-display income-amount">₹<?php echo $totalIncome; ?></div>
            </div>
            
            <div class="info-section">
                <h3>Total Expenses</h3>
                <div class="amount-display expense-amount">₹<?php echo $totalExpenses; ?></div>
            </div>
            
            <?php if($budgetAmount > 0): ?>
            <div class="info-section">
                <h3>Current Budget</h3>
                <div class="amount-display budget-amount">₹<?php echo $budgetAmount; ?></div>
                <?php if($budgetExceeded > 0): ?>
                    <div class="exceeded-amount" style="font-size: 24px; margin-top: 10px;">
                        Budget Exceeded by: ₹<?php echo $budgetExceeded; ?>
                    </div>
                <?php else: ?>
                    <div style="color: #28a745; font-size: 20px; margin-top: 10px; font-weight: bold;">
                        Within Budget
                    </div>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="info-section">
                <h3>Current Budget</h3>
                <div style="font-size: 20px; color: #6c757d;">No active budget set</div>
            </div>
            <?php endif; ?>
            
            <?php
            $today = date('Y-m-d');
            $weekStart = date('Y-m-d', strtotime('-7 days'));
            $monthStart = date('Y-m-d', strtotime('-1 month'));

            $weekExpenses = 0;
            $weekIncome = 0;
            $monthExpenses = 0;
            $monthIncome = 0;

            $weekExpQuery = "SELECT SUM(t.Amount) AS week_expenses FROM transactions t INNER JOIN account a ON t.AccountID = a.AccountID WHERE a.UserID = '$userid' AND t.TransactionType = 'Expense' AND t.TransactionDate BETWEEN '$weekStart' AND '$today'";
            $weekExpRes = mysqli_query($conn, $weekExpQuery);
            if ($weekExpRes) {
                $r = mysqli_fetch_assoc($weekExpRes);
                if (isset($r['week_expenses']) && $r['week_expenses'] !== null) {
                    $weekExpenses = $r['week_expenses'];
                }
            }

            $weekIncQuery = "SELECT SUM(t.Amount) AS week_income FROM transactions t INNER JOIN account a ON t.AccountID = a.AccountID WHERE a.UserID = '$userid' AND t.TransactionType = 'Income' AND t.TransactionDate BETWEEN '$weekStart' AND '$today'";
            $weekIncRes = mysqli_query($conn, $weekIncQuery);
            if ($weekIncRes) {
                $r = mysqli_fetch_assoc($weekIncRes);
                if (isset($r['week_income']) && $r['week_income'] !== null) {
                    $weekIncome = $r['week_income'];
                }
            }

            $monthExpQuery = "SELECT SUM(t.Amount) AS month_expenses FROM transactions t INNER JOIN account a ON t.AccountID = a.AccountID WHERE a.UserID = '$userid' AND t.TransactionType = 'Expense' AND t.TransactionDate BETWEEN '$monthStart' AND '$today'";
            $monthExpRes = mysqli_query($conn, $monthExpQuery);
            if ($monthExpRes) {
                $r = mysqli_fetch_assoc($monthExpRes);
                if (isset($r['month_expenses']) && $r['month_expenses'] !== null) {
                    $monthExpenses = $r['month_expenses'];
                }
            }

            $monthIncQuery = "SELECT SUM(t.Amount) AS month_income FROM transactions t INNER JOIN account a ON t.AccountID = a.AccountID WHERE a.UserID = '$userid' AND t.TransactionType = 'Income' AND t.TransactionDate BETWEEN '$monthStart' AND '$today'";
            $monthIncRes = mysqli_query($conn, $monthIncQuery);
            if ($monthIncRes) {
                $r = mysqli_fetch_assoc($monthIncRes);
                if (isset($r['month_income']) && $r['month_income'] !== null) {
                    $monthIncome = $r['month_income'];
                }
            }

            mysqli_close($conn);
            ?>

            <div class="info-section">
                <h3>Last 7 Days</h3>
                <div class="amount-display income-amount">Income: ₹<?php echo $weekIncome; ?></div>
                <div class="amount-display expense-amount">Expenses: ₹<?php echo $weekExpenses; ?></div>
            </div>

            <div class="info-section">
                <h3>Last 30 Days</h3>
                <div class="amount-display income-amount">Income: ₹<?php echo $monthIncome; ?></div>
                <div class="amount-display expense-amount">Expenses: ₹<?php echo $monthExpenses; ?></div>
            </div>

            <div class="button-container">
                <a href="DashboardComponents/TotalExpenses.php" class="dashboard-button">View All Expenses</a>
                <a href="DashboardComponents/TotalIncome.php" class="dashboard-button">View All Income</a>
                <a href="DashboardComponents/Budget.php" class="dashboard-button">Manage Budget</a>
            </div>
        </div>
    </div>

</body>
</html>
