<!DOCTYPE html>
<html>
<head>
    <title>Add an Account</title>
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
    </style>
</head>
<body>
    <h1 id="heading">Add Account</h1>
    <div id="container">
        <form method="post">

            <label>Date Created: </label>
            <input type="date" id="accountdate" name="accountdate" required><br><br>


            <label>Account Type: </label> 
            <select id="accounttype" name="accounttype" required>
                <option value="">Select Type</option>
                <option value="Savings">Savings</option>
                <option value="Checking">Checking</option>
                <option value="Credit">Credit</option>
                <option value="Investment">Investment</option>
            </select><br><br>

            <label>Initial Balance: </label>
            <input type="number" id="initialbalance" name="initialbalance" required><br><br>

            <input type="submit" id="submit" value="Add Account">
            <a href="../Account.php" class="back-btn">Back</a>
        </form>
    </div>

<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "database1");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $accountdate = $_POST['accountdate'];
    $accounttype = $_POST['accounttype'];
    $initialbalance = $_POST['initialbalance'];

   if (!isset($_SESSION['UserID'])) {
        echo "<script>alert('Please login first'); window.location.href='../../Login.php';</script>";
        exit();
    }
    $userid = $_SESSION['UserID'];


    if (!($accountdate) || !($accounttype) || !($initialbalance)) {
        echo "<script>alert('Please fill all the details');</script>";
        exit();
    }
    
    if ($initialbalance < 0) {
        echo "<script>alert('Initial balance cannot be negative');</script>";
        exit();
    }

    $q1 = "INSERT INTO account (UserID, AccountType, Balance, CreatedDate)
           VALUES ('$userid', '$accounttype', '$initialbalance', '$accountdate')";

    $r1 = mysqli_query($conn, $q1);

    if (!($r1)) {
        echo "<script>alert('Error while adding account:');</script>";
    } else {
        echo "<script>alert('Account added successfully'); window.location.href='../Account.php';</script>";
    }
    mysqli_close($conn);
} else {
    mysqli_close($conn);
}
?>
</body>
</html>
