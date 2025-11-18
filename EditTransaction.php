<!DOCTYPE html>
<html>
<head>
    <title>Edit Transaction</title>
    <style>
        body {
            background-color: #fff9cc;   
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 0;
            padding: 0;
        }
        #heading {
            font-size:70px; 
            color: azure; 
            background-color: blueviolet;
            border-bottom: 5px solid black;
            border-right: 5px solid black; 
            border-left: 5px solid black; 
            text-align:center; 
            margin: 0 auto; 
            padding: 20px 30px; 
            width:50%; 
            border-radius:0px 0px 15px 15px;
        }
        #container {
            background-color: #ffef89;
            margin: 20px auto;
            padding: 30px 10px;
            width: 60%;
            border:5px solid black;
            border-radius: 15px;
            text-align: center;
        }
        #submit {
            background-color: #007bff; 
            color: white;
            font-size: medium;
            border:2px solid black;
            padding: 10px 20px;
            margin: 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        #submit:hover {
            background-color: #0056b3;
        }
        .cancel-btn {
            background-color: #6c757d; 
            color: white;
            font-size: medium;
            border:2px solid black;
            padding: 10px 20px;
            margin: 10px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .cancel-btn:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <h1 id="heading">Edit Transaction</h1>
    <div id="container">
        <?php
        session_start();
        $conn = mysqli_connect("localhost", "root", "", "database1");
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }

        if (!isset($_SESSION['UserID'])) {
            echo "<script>alert('Please login first'); window.location.href='../../Login.php';</script>";
            exit();
        }

        if (!isset($_SESSION['EditTransactionID'])) {
            echo "<script>alert('No transaction selected for editing'); window.location.href='../Transactions.php';</script>";
            exit();
        }

        $transactionID = $_SESSION['EditTransactionID'];
        $userid = $_SESSION['UserID'];
        $transactionData = null;

        $fetchQuery = "SELECT t.* FROM transactions t 
                       JOIN account a ON t.AccountID = a.AccountID 
                       WHERE t.TransactionID = '$transactionID' AND a.UserID = '$userid'";
        $fetchResult = mysqli_query($conn, $fetchQuery);

        if ($fetchResult && mysqli_num_rows($fetchResult) > 0) {
            $transactionData = mysqli_fetch_assoc($fetchResult);
        } else {
            echo "<script>alert('Transaction not found or you do not have permission to edit it'); window.location.href='../Transactions.php';</script>";
            exit();
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $transactionName = $_POST['transactionName'];
            $transactionType = $_POST['transactionType'];
            $amount = $_POST['amount'];
            $paymentMethod = $_POST['paymentMethod'];
            $category = $_POST['category'];
            $transactionDate = $_POST['transactionDate'];

            if (empty($transactionName) || empty($transactionType) || empty($amount) || empty($paymentMethod) || empty($category) || empty($transactionDate)) {
                echo "<script>alert('Please fill all the details');</script>";
            } else if ($amount <= 0) {
                echo "<script>alert('Amount must be greater than 0');</script>";
            } else {
                $oldAmount = $transactionData['Amount'];
                $oldType = $transactionData['TransactionType'];
                $accountID = $transactionData['AccountID'];

                $updateQuery = "UPDATE transactions 
                                SET transactionName = '$transactionName', TransactionType = '$transactionType', Amount = '$amount', PaymentMethod = '$paymentMethod', Category = '$category', TransactionDate = '$transactionDate'
                                WHERE TransactionID = '$transactionID'";

                if (mysqli_query($conn, $updateQuery)) {
                    if ($oldType == 'Expense') {
                        $reversalQuery = "UPDATE account SET Balance = Balance + '$oldAmount' WHERE AccountID = '$accountID'";
                    } else {
                        $reversalQuery = "UPDATE account SET Balance = Balance - '$oldAmount' WHERE AccountID = '$accountID'";
                    }
                    mysqli_query($conn, $reversalQuery);

                    if ($transactionType == 'Expense') {
                        $applyQuery = "UPDATE account SET Balance = Balance - '$amount' WHERE AccountID = '$accountID'";
                    } else {
                        $applyQuery = "UPDATE account SET Balance = Balance + '$amount' WHERE AccountID = '$accountID'";
                    }
                    mysqli_query($conn, $applyQuery);

                    $getBalance = "SELECT Balance FROM account WHERE AccountID = '$accountID'";
                    $balRes = mysqli_query($conn, $getBalance);
                    $balRow = mysqli_fetch_assoc($balRes);
                    $currentBalance = $balRow['Balance'];

                    $alertMsg = 'Transaction updated successfully';
                    if ($currentBalance == 0) {
                        $alertMsg .= '!\n\nALERT: Account balance is now ZERO (₹0). Consider adding funds.';
                    } elseif ($currentBalance < 0) {
                        $alertMsg .= '!\n\nWARNING: Account balance is now NEGATIVE (₹' . $currentBalance . '). Please add funds immediately.';
                    }

                    $budgetCheckQuery = "SELECT SUM(t.Amount) AS total_spent FROM transactions t WHERE t.AccountID = '$accountID' AND t.TransactionType = 'Expense'";
                    $budgetCheckRes = mysqli_query($conn, $budgetCheckQuery);
                    $budgetCheckRow = mysqli_fetch_assoc($budgetCheckRes);
                    $totalSpent = $budgetCheckRow['total_spent'];

                    $activeBudgetQuery = "SELECT BudgetAmount FROM budget WHERE UserID = '$userid' AND CURDATE() BETWEEN StartDate AND EndDate LIMIT 1";
                    $activeBudgetRes = mysqli_query($conn, $activeBudgetQuery);
                    if (mysqli_num_rows($activeBudgetRes) > 0) {
                        $activeBudgetRow = mysqli_fetch_assoc($activeBudgetRes);
                        $budgetLimit = $activeBudgetRow['BudgetAmount'];
                        if ($totalSpent > $budgetLimit) {
                            $alertMsg .= '\n\nBUDGET ALERT: You have exceeded your budget limit!\nBudget: ₹' . $budgetLimit . '\nSpent: ₹' . $totalSpent . '\nOverage: ₹' . ($totalSpent - $budgetLimit);
                        }
                    }

                    echo "<script>alert('" . addslashes($alertMsg) . "'); window.location.href='../Transactions.php';</script>";
                    exit();
                } else {
                    echo "<script>alert('Error updating transaction: " . mysqli_error($conn) . "');</script>";
                }
            }
        }
        ?>
        
        <form method="post">
            <label>Transaction ID: </label>
            <input type="text" value="<?php echo $transactionData['TransactionID']; ?>" disabled style="background-color: #e9ecef;"><br><br>

            <label>Transaction Name: </label>
            <input type="text" id="transactionName" name="transactionName" value="<?php echo htmlspecialchars($transactionData['transactionName']); ?>" required><br><br>

            <label>Transaction Type: </label>
            <select id="transactionType" name="transactionType" required>
                <option value="">Select Type</option>
                <option value="Income" <?php echo ($transactionData['TransactionType'] == 'Income') ? 'selected' : ''; ?>>Income</option>
                <option value="Expense" <?php echo ($transactionData['TransactionType'] == 'Expense') ? 'selected' : ''; ?>>Expense</option>
            </select><br><br>

            <label>Amount: </label>
            <input type="number" id="amount" name="amount" value="<?php echo htmlspecialchars($transactionData['Amount']); ?>" step="0.01" min="0" required><br><br>

            <label>Payment Method: </label>
            <input type="text" id="paymentMethod" name="paymentMethod" value="<?php echo htmlspecialchars($transactionData['PaymentMethod']); ?>" required><br><br>

            <label>Category: </label>
            <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($transactionData['Category']); ?>" required><br><br>

            <label>Transaction Date: </label>
            <input type="date" id="transactionDate" name="transactionDate" value="<?php echo htmlspecialchars($transactionData['TransactionDate']); ?>" required><br><br>

            <input type="submit" id="submit" value="Update Transaction">
            <a href="../Transactions.php" class="cancel-btn">Back</a>
        </form>
    </div>
    <?php mysqli_close($conn); ?>
</body>
</html>
