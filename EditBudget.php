<!DOCTYPE html>
<html>
<head>
    <title>Edit Budget</title>
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
            width: 50%;
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
    <h1 id="heading">Edit Budget</h1>
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

        if (!isset($_SESSION['EditBudgetID'])) {
            echo "<script>alert('No budget selected for editing'); window.location.href='../Budget.php';</script>";
            exit();
        }

        $budgetID = $_SESSION['EditBudgetID'];
        $userid = $_SESSION['UserID'];
        $budgetData = null;

        $fetchQuery = "SELECT b.* FROM budget b WHERE b.BudgetID = '$budgetID' AND b.UserID = '$userid'";
        $fetchResult = mysqli_query($conn, $fetchQuery);

        if ($fetchResult && mysqli_num_rows($fetchResult) > 0) {
            $budgetData = mysqli_fetch_assoc($fetchResult);
        } else {
            echo "<script>alert('Budget not found or you do not have permission to edit it'); window.location.href='../Budget.php';</script>";
            exit();
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
                $updateQuery = "UPDATE budget 
                                SET BudgetAmount = '$budgetAmount', StartDate = '$startDate', EndDate = '$endDate'
                                WHERE BudgetID = '$budgetID' AND UserID = '$userid'";

                if (mysqli_query($conn, $updateQuery)) {
                    $totalExpensesQuery = "SELECT SUM(Amount) AS total_spent FROM transactions WHERE UserID = '$userid' AND TransactionType = 'Expense' AND TransactionDate >= '$startDate' AND TransactionDate <= '$endDate'";
                    $totalExpensesRes = mysqli_query($conn, $totalExpensesQuery);
                    $totalExpensesRow = mysqli_fetch_assoc($totalExpensesRes);
                    $totalExpenses = $totalExpensesRow['total_spent'];

                    $alertMsg = 'Budget updated successfully';
                    if ($totalExpenses > $budgetAmount) {
                        $alertMsg .= '!\\n\\nBUDGET ALERT: Your existing expenses already exceed this budget limit!\\nBudget: ₹' . $budgetAmount . '\\nCurrent Expenses: ₹' . $totalExpenses . '\\nOverage: ₹' . ($totalExpenses - $budgetAmount);
                    }
                    echo "<script>alert('" . addslashes($alertMsg) . "'); window.location.href='../Budget.php';</script>";
                    exit();
                } else {
                    echo "<script>alert('Error updating budget: " . mysqli_error($conn) . "');</script>";
                }
            }
        }
        ?>
        
        <form method="post">
            <label>Budget ID: </label>
            <input type="text" value="<?php echo $budgetData['BudgetID']; ?>" disabled style="background-color: #e9ecef;"><br><br>

            <label>Budget Amount: </label>
            <input type="number" id="budgetAmount" name="budgetAmount" value="<?php echo htmlspecialchars($budgetData['BudgetAmount']); ?>" step="0.01" min="0" required><br><br>

            <label>Start Date: </label>
            <input type="date" id="startDate" name="startDate" value="<?php echo htmlspecialchars($budgetData['StartDate']); ?>" required><br><br>

            <label>End Date: </label>
            <input type="date" id="endDate" name="endDate" value="<?php echo htmlspecialchars($budgetData['EndDate']); ?>" required><br><br>

            <input type="submit" id="submit" value="Update Budget">
            <a href="../Budget.php" class="cancel-btn">Back</a>
        </form>
    </div>
    <?php mysqli_close($conn); ?>
</body>
</html>
