<?php
session_start();

$conn = mysqli_connect("localhost", "root", "", "database1");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_POST['submit'])) {
    $UserID = isset($_SESSION['UserID']) ? $_SESSION['UserID'] : '';
    $AccountID = $_POST['accountid'];
    $PaymentMethod = $_POST['paymentmethod'];
    $Amount = $_POST['amount'];
    $TransactionDate = $_POST['date'];
    $TransactionType = $_POST['type'];
    $Category = isset($_POST['category']) ? $_POST['category'] : '';
    $TransactionName = isset($_POST['transactionName']) ? $_POST['transactionName'] : '';

    if (empty($UserID) || empty($AccountID) || empty($PaymentMethod) || empty($Amount) || empty($TransactionDate) || empty($TransactionType) || empty($Category) || empty($TransactionName)) {
        echo "<script>alert('Please fill in all required fields');</script>";
        mysqli_close($conn);
        exit();
    }

    if ($Amount <= 0) {
        echo "<script>alert('Amount must be greater than 0');</script>";
        mysqli_close($conn);
        exit();
    }

    $q1 = "SELECT AccountID FROM account WHERE AccountID='$AccountID' AND UserID='$UserID'";
    $r1 = mysqli_query($conn, $q1);
    if (mysqli_num_rows($r1) == 0) {
        echo "<script>alert('Account ID does not exist or does not belong to you');</script>";
        mysqli_close($conn);
        exit();
    }

    $q3 = "INSERT INTO transactions (AccountID, PaymentMethod, Amount, TransactionDate, TransactionType, Category, transactionName, UserID)
            VALUES ('$AccountID', '$PaymentMethod', '$Amount', '$TransactionDate', '$TransactionType', '$Category', '$TransactionName', '$UserID')";

    $r3 = mysqli_query($conn, $q3);

    if ($r3) {
        if ($TransactionType == 'Expense') {
            $updateBalance = "UPDATE account SET Balance = Balance - '$Amount' WHERE AccountID = '$AccountID'";
        } else {
            $updateBalance = "UPDATE account SET Balance = Balance + '$Amount' WHERE AccountID = '$AccountID'";
        }
        mysqli_query($conn, $updateBalance);

        $getBalance = "SELECT Balance FROM account WHERE AccountID = '$AccountID'";
        $balRes = mysqli_query($conn, $getBalance);
        $balRow = mysqli_fetch_assoc($balRes);
        $currentBalance = $balRow['Balance'];

        $alertMsg = 'Transaction added successfully';
        if ($currentBalance == 0) {
            $alertMsg .= '!\n\nALERT: Account balance is now ZERO (₹0). Consider adding funds.';
        } elseif ($currentBalance < 0) {
            $alertMsg .= '!\n\nWARNING: Account balance is now NEGATIVE (₹' . $currentBalance . '). Please add funds immediately.';
        }

        $budgetCheckQuery = "SELECT SUM(t.Amount) AS total_spent FROM transactions t WHERE t.AccountID = '$AccountID' AND t.TransactionType = 'Expense'";
        $budgetCheckRes = mysqli_query($conn, $budgetCheckQuery);
        $budgetCheckRow = mysqli_fetch_assoc($budgetCheckRes);
        $totalSpent = $budgetCheckRow['total_spent'];

        $activeBudgetQuery = "SELECT BudgetAmount FROM budget WHERE UserID = '$UserID' AND CURDATE() BETWEEN StartDate AND EndDate LIMIT 1";
        $activeBudgetRes = mysqli_query($conn, $activeBudgetQuery);
        if (mysqli_num_rows($activeBudgetRes) > 0) {
            $activeBudgetRow = mysqli_fetch_assoc($activeBudgetRes);
            $budgetLimit = $activeBudgetRow['BudgetAmount'];
            if ($totalSpent > $budgetLimit) {
                $alertMsg .= '\n\nBUDGET ALERT: You have exceeded your budget limit!\nBudget: ₹' . $budgetLimit . '\nSpent: ₹' . $totalSpent . '\nOverage: ₹' . ($totalSpent - $budgetLimit);
            }
        }

        echo "<script>alert('" . addslashes($alertMsg) . "'); window.location.href='../Transactions.php';</script>";
    } else {
        echo "<script>alert('Error in insertion: " . mysqli_error($conn) . "');</script>";
    }

    mysqli_close($conn);
}

?>

<html>
    <head>
        <title>Add a Transaction</title>
        <style>
            body {
                    background-color: #fff9cc;
                    font-family: Arial, sans-serif;
                    text-align: center;
                    margin: 0;
                    padding: 0;
                }
            #heading {
                font-size:20px; 
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
        .back-btn {
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
        .back-btn:hover {
            background-color: #5a6268;
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


        </style>
        <script>
            function validate(){
                accountID = document.getElementById("accountid").value;
                transactionType = document.getElementsByName("type");
                amount = document.getElementById("amount").value;
                date = document.getElementById("date").value;
                paymentMethod = document.getElementById("paymentmethod").value;
                transactionName = document.getElementById("transactionName").value;
                categories = document.getElementsByName("category");
                
                if(accountID==""){
                    alert("Please enter Account ID");
                    return false;
                }

                if(transactionName==""){
                    alert("Please enter Transaction Name");
                    return false;
                }

                if(!(transactionType[0].checked || transactionType[1].checked)){
                    alert("Please select a transaction type");
                    return false;
                }
                if(date==""){
                    alert("Please select a date");
                    return false;
                }
                if(amount==""){
                    alert("Please enter an amount");
                    return false;
                }
                if(parseFloat(amount) <= 0){
                    alert("Amount must be greater than 0");
                    return false;
                }
                if(paymentMethod==""){
                    alert("Please select a payment method");
                    return false;
                }
                
                var categoryChecked = false;
                for(var i = 0; i < categories.length; i++){
                    if(categories[i].checked){
                        categoryChecked = true;
                        break;
                    }
                }
                if(!categoryChecked){
                    alert("Please select at least one category");
                    return false;
                }
                
                return true;

            }
        </script>
    </head>
    <body>
        <?php
        $conn = mysqli_connect("localhost", "root", "", "database1");
        if (!$conn) {
            echo "<div id=\"heading\"><h1>Connection error</h1></div>";
            exit();
        }
        $userid = isset($_SESSION['UserID']) ? $_SESSION['UserID'] : '';
        $accRes = mysqli_query($conn, "SELECT AccountID FROM account WHERE UserID = '$userid' LIMIT 1");
        if (!($accRes) || mysqli_num_rows($accRes) == 0) {
            mysqli_close($conn);
            echo "<div id=\"heading\"><h1>Add a Transaction</h1></div>";
            echo "<div id=\"form-container\"><p>You must create an account before adding transactions.</p>";
            echo "<a href=\"../Account/AddAccount.php\"><button type=\"button\">Create Account</button></a>";
            echo "<br><br><a href=\"../Transactions.php\"><button type=\"button\">Back to Transactions</button></a></div>";
            exit();
        }
        mysqli_close($conn);
        ?>

        <div id="heading"><h1>Add a Transaction</h1></div>
        <div id="form-container">
            <form onsubmit="return validate()" method="POST">

            <label>Account ID: </label>
            <input type="number" id="accountid" name="accountid"><br><br>

                <label>Transaction Name: </label>
                <input type="text" id="transactionName" name="transactionName"><br><br>
      
                <label>Transaction Type:</label>
                <input type="radio" name="type" value="Income">Income
                <input type="radio" name="type" value="Expense">Expense<br><br>

                <label>Payment Method:</label>
                <select id="paymentmethod" name="paymentmethod">
                    <option value="">Select Payment Method</option>
                    <option value="Cash">Cash</option>
                    <option value="Card">Card</option>
                    <option value="UPI">UPI</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                </select><br><br>

                <label>Date:</label>
                <input type="date" id="date" name="date"><br><br>

                <label>Amount:</label> 
                <input type="text" id="amount" name="amount"><br><br>
                
                <label> Category:</label> <br>
                <input type="checkbox" name="category" value="Food">Food<br>
                <input type="checkbox" name="category" value="Transport">Transport<br>
                <input type="checkbox" name="category" value="Entertainment">Entertainment<br>
                <input type="checkbox" name="category" value="Bills">Bills<br>
                <input type="checkbox" name="category" value="Shopping">Shopping<br>
                <input type="checkbox" name="category" value="Insurance">Insurance<br>
                <input type="checkbox" name="category" value="Investment">Investment<br>
                <input type="checkbox" name="category" value="Salary">Salary<br>
                <input type="checkbox" name="category" value="Recharge">Recharge<br>
                <input type="checkbox" name="category" value="Subscriptions">Subsriptions<br>

                <input type="submit" id="submit" name="submit" >
                <a href="../Transactions.php" class="back-btn">Back</a>

            </form>
        </div>
    </body>
</html>