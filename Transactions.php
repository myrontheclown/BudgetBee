<html>
    <head>
        <title>Add Transaction</title>
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
            #form-container {
            background-color: #ffef89;
            margin: 20px auto;
            padding: 30px 10px;
            width: 90%;
            border:5px solid black;
            border-radius: 15px;
            text-align: center;
            }
            table{
                background-color: white;
                border: 2px solid black;
                text-align: center;
                margin: 20px auto;
            }
            button {
            background-color: #007bff; 
            color: white;
            font-size: medium;
            border:2px solid black;
            padding: 10px 20px;
            margin: 10px;
            border-radius: 5px;
            cursor: pointer;
            }
            button:hover {
            background-color: #0056b3;
        }
        .edit-btn {
            background-color: #ffc107;
            color: black;
        }
        .edit-btn:hover {
            background-color: #e0a800;
        }
        .delete-btn {
            background-color: #dc3545;
            color: white;
        }
        .delete-btn:hover {
            background-color: #c82333;
        }
        form {
            display: inline;
        }</style>
    </head>
    <body>
        <h1 id="heading">Transactions</h1>
        <div id="form-container">
            <h1>Transaction History</h1>
            <?php
            session_start();
            if (!isset($_SESSION['UserID'])) {
                echo "<script>alert('Please login first'); window.location.href='../Login.php';</script>";
                exit();
            }
            $conn = mysqli_connect("localhost", "root", "", "database1");
            
            if($conn)
                echo "Connection successful";
            else
            {
                echo "Connection error";
                exit();
            }
            
            $userid = $_SESSION['UserID'];
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deleteTransaction'])) {
                $transactionID = $_POST['deleteTransaction'];

                $fetchTrans = "SELECT TransactionType, Amount, AccountID FROM transactions WHERE TransactionID = '$transactionID'";
                $fetchRes = mysqli_query($conn, $fetchTrans);
                $transData = mysqli_fetch_assoc($fetchRes);
                $transType = $transData['TransactionType'];
                $transAmount = $transData['Amount'];
                $accountID = $transData['AccountID'];

                $deleteQuery = "DELETE FROM transactions WHERE TransactionID = '$transactionID' AND AccountID IN (SELECT AccountID FROM account WHERE UserID = '$userid')";
                if (mysqli_query($conn, $deleteQuery)) {
                    if ($transType == 'Expense') {
                        $reverseBalance = "UPDATE account SET Balance = Balance + '$transAmount' WHERE AccountID = '$accountID'";
                    } else {
                        $reverseBalance = "UPDATE account SET Balance = Balance - '$transAmount' WHERE AccountID = '$accountID'";
                    }
                    mysqli_query($conn, $reverseBalance);

                    $getBalance = "SELECT Balance FROM account WHERE AccountID = '$accountID'";
                    $balRes = mysqli_query($conn, $getBalance);
                    $balRow = mysqli_fetch_assoc($balRes);
                    $currentBalance = $balRow['Balance'];

                    $alertMsg = 'Transaction deleted successfully';
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
                            $alertMsg .= '\BUDGET ALERT: You have exceeded your budget limit!';
                        }
                    }

                    echo "<script>alert('" . addslashes($alertMsg) . "'); window.location.href='Transactions.php';</script>";
                    exit();
                } else {
                    echo "<script>alert('Error deleting transaction: " . mysqli_error($conn) . "');</script>";
                }
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editTransaction'])) {
                $editID = $_POST['editTransaction'];
                $_SESSION['EditTransactionID'] = $editID;
                echo "<script>window.location.href='Transactions/EditTransaction.php';</script>";
                exit();
            }
            
            $q1 = "SELECT t.* FROM transactions t INNER JOIN account a ON t.AccountID = a.AccountID WHERE a.UserID = '$userid'";
            $r1 = mysqli_query($conn, $q1);
            $n = mysqli_num_rows($r1);
            
            echo "<br>Number of records = " . $n;
            
            if($r1)
            {
                echo "<table border='2'>";
                echo "<tr>";
                echo "<th>Transaction ID</th>";
                echo "<th>Account ID</th>";
                echo "<th>Transaction Name</th>";
                echo "<th>Transaction Type</th>";
                echo "<th>Amount</th>";
                echo "<th>Payment Method</th>";
                echo "<th>Category</th>";
                echo "<th>Transaction Date</th>";
                echo "<th></th>";
                echo "</tr>";
                
                while($info = mysqli_fetch_array($r1))
                {
                    echo "<tr>";
                    echo "<td>" . $info['TransactionID'] . "</td>";
                    echo "<td>" . $info['AccountID'] . "</td>";
                    echo "<td>" . $info['transactionName'] . "</td>";
                    echo "<td>" . $info['TransactionType'] . "</td>";
                    echo "<td>₹" . $info['Amount'] . "</td>";
                    echo "<td>" . $info['PaymentMethod'] . "</td>";
                    echo "<td>" . $info['Category'] . "</td>";
                    echo "<td>" . $info['TransactionDate'] . "</td>";
                    echo "<td>";
                    echo "<form method='POST' action='Transactions.php' style='display:inline;'>
                            <input type='hidden' name='editTransaction' value='" . $info['TransactionID'] . "'>
                            <button type='submit' class='edit-btn'>Edit</button>
                          </form>";
                    echo "<form method='POST' action='Transactions.php' style='display:inline;' onsubmit=\"return confirm('Are you sure you want to delete this transaction?');\">
                            <input type='hidden' name='deleteTransaction' value='" . $info['TransactionID'] . "'>
                            <button type='submit' class='delete-btn'>Delete</button>
                          </form>";
                    echo "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
            else
                echo "<br>Error in display operation";
            
            mysqli_close($conn);
            ?>
            <br><br>
            <a href="Transactions/AddTransaction.php"><button>Add a transaction</button></a>
            <a href="../Dashboard.php"><button>Back to Dashboard</button></a>
            
        </div>
    </body>
</html>