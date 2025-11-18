<!DOCTYPE html>
<html>
<head>
    <title>Edit Account</title>
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
    <h1 id="heading">Edit Account</h1>
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

        if (!isset($_SESSION['EditAccountID'])) {
            echo "<script>alert('No account selected for editing'); window.location.href='../Account.php';</script>";
            exit();
        }

        $accountID = $_SESSION['EditAccountID'];
        $userid = $_SESSION['UserID'];
        $accountData = null;

        $fetchQuery = "SELECT * FROM account WHERE AccountID = '$accountID' AND UserID = '$userid'";
        $fetchResult = mysqli_query($conn, $fetchQuery);

        if ($fetchResult && mysqli_num_rows($fetchResult) > 0) {
            $accountData = mysqli_fetch_assoc($fetchResult);
        } else {
            echo "<script>alert('Account not found or you do not have permission to edit it'); window.location.href='../Account.php';</script>";
            exit();
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $accounttype = $_POST['accounttype'];
            $balance = $_POST['balance'];
            $createddate = $_POST['createddate'];

            if (empty($accounttype) || empty($balance) || empty($createddate)) {
                echo "<script>alert('Please fill all the details');</script>";
            } else if ($balance < 0) {
                echo "<script>alert('Balance cannot be negative');</script>";
            } else {
                $updateQuery = "UPDATE account 
                                SET AccountType = '$accounttype',
                                Balance = '$balance',
                                CreatedDate = '$createddate'
                                WHERE AccountID = '$accountID' AND UserID = '$userid'";

                if (mysqli_query($conn, $updateQuery)) {
                    unset($_SESSION['EditAccountID']);
                    echo "<script>alert('Account updated successfully'); window.location.href='../Account.php';</script>";
                    exit();
                } else {
                    echo "<script>alert('Error updating account');</script>";
                }
            }
        }
        ?>
        
        <form method="post">
            <label>Account ID: </label>
            <input type="text" value="<?php echo $accountData['AccountID']; ?>" disabled style="background-color: #e9ecef;"><br><br>

            <label>User ID: </label>
            <input type="text" value="<?php echo $accountData['UserID']; ?>" disabled style="background-color: #e9ecef;"><br><br>

            <label>Account Type: </label> 
            <select id="accounttype" name="accounttype" required>
                <option value="">Select Type</option>
                <option value="Savings" <?php echo ($accountData['AccountType'] == 'Savings') ? 'selected' : ''; ?>>Savings</option>
                <option value="Checking" <?php echo ($accountData['AccountType'] == 'Checking') ? 'selected' : ''; ?>>Checking</option>
                <option value="Credit" <?php echo ($accountData['AccountType'] == 'Credit') ? 'selected' : ''; ?>>Credit</option>
                <option value="Investment" <?php echo ($accountData['AccountType'] == 'Investment') ? 'selected' : ''; ?>>Investment</option>
            </select><br><br>

            <label>Balance: </label>
            <input type="number" id="balance" name="balance" value="<?php echo htmlspecialchars($accountData['Balance']); ?>" step="0.01" required><br><br>

            <label>Created Date: </label>
            <input type="date" id="createddate" name="createddate" value="<?php echo htmlspecialchars($accountData['CreatedDate']); ?>" required><br><br>

            <input type="submit" id="submit" value="Update Account">
            <a href="../Account.php" class="cancel-btn">Back</a>
        </form>
    </div>
    <?php mysqli_close($conn); ?>
</body>
</html>
