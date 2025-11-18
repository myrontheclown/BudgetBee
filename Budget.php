<html>
    <head>
        <title>Budget</title>
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
            #container{
                border:5px solid black;
                background-color: #ffef89;
                margin: 20px auto;
                padding: 30px 10px;
                width: 80%;
                border-radius: 15px;
                text-align: center;
            }
            #form-container {
                background-color: #ffef89;
                margin: 20px auto;
                padding: 30px 10px;
                width: 50%;
                border: 5px solid black;
                border-radius: 15px;
                text-align: center;
            }


            #submit {
                border: 2px solid black;
                background-color: #007bff; 
                color: white;
                font-size: medium;
                padding: 10px 20px;
                margin: 10px;
                border-radius: 5px;
                cursor: pointer;
            }
            #submit:hover {
                background-color: #0056b3;
            }
            .exceeded {
                color: #dc3545;
                font-weight: bold;
            }
            .within-budget {
                color: #28a745;
                font-weight: bold;
            }
            table{
                background-color: white;
                border: 2px solid black;
                text-align: center;
                margin: 20px auto;
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
            }
        </style>
    </head>
    <body>
        <h1 id="heading">Budget Management</h1>
        <div id="container">
            <?php
            session_start();
            $conn = mysqli_connect("localhost", "root", "", "database1");
            
            if($conn)
                echo "Connection successful";
            else
            {
                echo "Connection error";
                exit();
            }
            
            if (!isset($_SESSION['UserID'])) {
                echo "<script>alert('Please login first'); window.location.href='../Login.php';</script>";
                exit();
            }
            $userid = $_SESSION['UserID'];
            
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['deleteBudget'])) {
                $budgetID = $_POST['deleteBudget'];
                $deleteQuery = "DELETE FROM budget WHERE BudgetID = '$budgetID' AND UserID = '$userid'";
                if (mysqli_query($conn, $deleteQuery)) {
                    echo "<script>alert('Budget deleted successfully'); window.location.href='Budget.php';</script>";
                    exit();
                } else {
                    echo "<script>alert('Error deleting budget: " . mysqli_error($conn) . "');</script>";
                }
            }
            
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editBudget'])) {
                $editID = $_POST['editBudget'];
                $_SESSION['EditBudgetID'] = $editID;
                echo "<script>window.location.href='Budget/EditBudget.php';</script>";
                exit();
            }
            
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
                $budgetAmount = $_POST['budgetAmount'];
                $startDate = $_POST['startDate'];
                $endDate = $_POST['endDate'];
                
                if (empty($budgetAmount) || empty($startDate) || empty($endDate)) {
                    echo "<script>alert('Please fill all the details');</script>";
                } else if ($endDate < $startDate) {
                    echo "<script>alert('End Date must be after Start Date');</script>";
                } else if ($budgetAmount <= 0) {
                    echo "<script>alert('Budget Amount must be greater than 0');</script>";
                } else {
                    $createTable = "CREATE TABLE IF NOT EXISTS budget (
                        BudgetID INT AUTO_INCREMENT PRIMARY KEY,
                        UserID INT,
                        BudgetAmount DECIMAL(10, 2),
                        StartDate DATE,
                        EndDate DATE
                    )";
                    mysqli_query($conn, $createTable);
                    
                    $q1 = "INSERT INTO budget (UserID, BudgetAmount, StartDate, EndDate) 
                           VALUES ('$userid', '$budgetAmount', '$startDate', '$endDate')";
                    $r1 = mysqli_query($conn, $q1);
                    
                    if ($r1) {
                        $totalExpensesQuery = "SELECT SUM(Amount) AS total_spent FROM transactions WHERE UserID = '$userid' AND TransactionType = 'Expense' AND TransactionDate >= '$startDate' AND TransactionDate <= '$endDate'";
                        $totalExpensesRes = mysqli_query($conn, $totalExpensesQuery);
                        $totalExpensesRow = mysqli_fetch_assoc($totalExpensesRes);
                        $totalExpenses = $totalExpensesRow['total_spent'];

                        $alertMsg = 'Budget added successfully';
                        if ($totalExpenses > $budgetAmount) {
                            $alertMsg .= '!\\n\\nBUDGET ALERT: Your existing expenses already exceed this budget limit!\\nBudget: ₹' . $budgetAmount . '\\nCurrent Expenses: ₹' . $totalExpenses . '\\nOverage: ₹' . ($totalExpenses - $budgetAmount);
                        }
                        echo "<script>alert('" . addslashes($alertMsg) . "');</script>";
                    } else {
                        echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
                    }
                }
            }
            
            $q2 = "SELECT * FROM budget WHERE UserID = '$userid'";
            $r2 = mysqli_query($conn, $q2);
            $n = mysqli_num_rows($r2);
            
            echo "<br>Number of budget records = " . $n;
            
            if($r2 && $n > 0)
            {
                echo "<table border='2'>";
                echo "<tr>";
                echo "<th>Budget ID</th>";
                echo "<th>Budget Amount</th>";
                echo "<th>Start Date</th>";
                echo "<th>End Date</th>";
                echo "<th>Total Expenses</th>";
                echo "<th>Status</th>";
                echo "<th></th>";
                echo "</tr>";
                
                $budgetQuery = "SELECT 
                                b.BudgetID,
                                b.BudgetAmount,
                                b.StartDate,
                                b.EndDate,
                                (SELECT SUM(Amount) FROM transactions 
                                 WHERE UserID = '$userid'
                                 AND TransactionType = 'Expense' 
                                 AND TransactionDate >= b.StartDate 
                                 AND TransactionDate <= b.EndDate) AS total_expenses
                                FROM budget b
                                WHERE b.UserID = '$userid'";
                
                $budgetResult = mysqli_query($conn, $budgetQuery);
                
                while($info = mysqli_fetch_array($budgetResult))
                {
                    $budgetId = $info['BudgetID'];
                    $budgetAmount = $info['BudgetAmount'];
                    $startDate = $info['StartDate'];
                    $endDate = $info['EndDate'];
                    
                    if (isset($info['total_expenses']) && $info['total_expenses'] !== null) {
                        $totalExpenses = $info['total_expenses'];
                    } else {
                        $totalExpenses = 0;
                    }
                    
                    if ($totalExpenses > $budgetAmount) {
                        $status = "Exceeded";
                        $statusClass = "exceeded";
                    } else {
                        $status = "Within Budget";
                        $statusClass = "within-budget";
                    }
                    
                    echo "<tr>";
                    echo "<td>" . $budgetId . "</td>";
                    echo "<td>₹" . $budgetAmount . "</td>";
                    echo "<td>" . $startDate . "</td>";
                    echo "<td>" . $endDate . "</td>";
                    echo "<td>₹" . $totalExpenses . "</td>";
                    echo "<td class='" . $statusClass . "'>" . $status . "</td>";
                    echo "<td>";
                    echo "<form method='POST' action='Budget.php' style='display:inline;'>
                            <input type='hidden' name='editBudget' value='" . $budgetId . "'>
                            <button type='submit' class='edit-btn'>Edit</button>
                          </form>";
                    echo "<form method='POST' action='Budget.php' style='display:inline;' onsubmit=\"return confirm('Are you sure you want to delete this budget?');\">
                            <input type='hidden' name='deleteBudget' value='" . $budgetId . "'>
                            <button type='submit' class='delete-btn'>Delete</button>
                          </form>";
                    echo "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
            else if($r2)
                echo "<br>No budget records found";
            else
                echo "<br>Error in display operation";
            
            ?>
            <?php
            $hasAccount = false;
            $accCheck = mysqli_query($conn, "SELECT AccountID FROM account WHERE UserID = '$userid' LIMIT 1");
            if ($accCheck && mysqli_num_rows($accCheck) > 0) {
                $hasAccount = true;
            }
            mysqli_close($conn);
            ?>

            <div id="form-container">
                <h2>Add New Budget</h2>
                <?php if (!$hasAccount) { ?>
                    <p>You need to create an account before adding a budget.</p>
                    <a href="Account/AddAccount.php"><button type="button">Create Account</button></a>
                <?php } else { ?>
                    <form method="POST" action="">
                        <label>Budget Amount: </label>
                        <input type="number" id="budgetAmount" name="budgetAmount" step="0.01" min="0" required><br><br>

                        <label>Start Date: </label>
                        <input type="date" id="startDate" name="startDate" required><br><br>

                        <label>End Date: </label>
                        <input type="date" id="endDate" name="endDate" required><br><br>

                        <input type="submit" id="submit" name="submit" value="Add Budget">
                    </form>
                <?php } ?>
            </div>
            
            <br><br>
            <a href="../Dashboard.php"><button type="button">Back to Dashboard</button></a>
        </div>
    </body>
</html>
