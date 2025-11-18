<html>
<head>
    <title>Account</title>
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
        #container {
            border:5px solid black;
            background-color: #ffef89;
            margin: 20px auto;
            padding: 30px 10px;
            width: 80%;
            border-radius: 15px;
            text-align: center;
        }
        table {
            background-color: white;
            border: 2px solid black;
            text-align: center;
            margin: 20px auto;
        }
        form {
            display: inline;
        }
    </style>
</head>
<body>
    <h1 id="heading">Account Details</h1>
    <div id="container">
        <?php
        session_start();
        if (!isset($_SESSION['UserID'])) {
            echo "<script>alert('Please login first'); window.location.href='../Login.php';</script>";
            exit();
        }

        $conn = mysqli_connect("localhost", "root", "", "database1");

        if(!$conn) {
            echo "Connection error";
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deleteAccount'])) {
            $accountID = $_POST['deleteAccount'];
            $userid = $_SESSION['UserID'];

            $checkQuery = "SELECT AccountID FROM account WHERE AccountID='$accountID' AND UserID='$userid'";
            $checkResult = mysqli_query($conn, $checkQuery);

            if (mysqli_num_rows($checkResult) > 0) {
                $deleteQuery = "DELETE FROM account WHERE AccountID='$accountID' AND UserID='$userid'";
                if (mysqli_query($conn, $deleteQuery)) {
                    echo "<script>alert('Account deleted successfully'); window.location.href='Account.php';</script>";
                    exit();
                } else {
                    echo "<script>alert('Error deleting account: " . mysqli_error($conn) . "');</script>";
                }
            } else {
                echo "<script>alert('You can only delete your own accounts'); window.location.href='Account.php';</script>";
                exit();
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editAccount'])) {
            $editID = $_POST['editAccount'];
            $_SESSION['EditAccountID'] = $editID; 
            echo "<script>window.location.href='Account/EditAccount.php';</script>";
            exit();
        }

        $userid = $_SESSION['UserID'];
        $q1 = "SELECT * FROM account WHERE UserID = '$userid'";
        $r1 = mysqli_query($conn, $q1);
        $n = mysqli_num_rows($r1);

        echo "<br>Number of records = " . $n;

        if($r1) {
            echo "<table border='2'>";
            echo "<tr>
                    <th>Account ID</th>
                    <th>User ID</th>
                    <th>Account Type</th>
                    <th>Balance</th>
                    <th>Created Date</th>
                    <th>Action</th>
                  </tr>";

            while($info = mysqli_fetch_array($r1)) {
                echo "<tr>";
                echo "<td>" . $info['AccountID'] . "</td>";
                echo "<td>" . $info['UserID'] . "</td>";
                echo "<td>" . $info['AccountType'] . "</td>";
                echo "<td>₹" . $info['Balance'] . "</td>";
                echo "<td>" . $info['CreatedDate'] . "</td>";
                echo "<td>";

                echo "<form method='POST' action='Account.php' style='display:inline;'>
                        <input type='hidden' name='editAccount' value='" . $info['AccountID'] . "'>
                        <button type='submit' style='background-color:#28a745; padding:5px 10px; font-size:14px; margin:2px;'>Edit</button>
                      </form>";

                echo "<form method='POST' action='Account.php' style='display:inline;' onsubmit=\"return confirm('Are you sure you want to delete this account?');\">
                        <input type='hidden' name='deleteAccount' value='" . $info['AccountID'] . "'>
                        <button type='submit' style='background-color:#dc3545; padding:5px 10px; font-size:14px; margin:2px;'>Delete</button>
                      </form>";

                echo "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<br>Error in display operation";
        }

        mysqli_close($conn);
        ?>
        <br><br>
        <a href="Account/AddAccount.php"><button type="button">Add Account</button></a>
        <a href="../Dashboard.php"><button type="button">Back to Dashboard</button></a>
    </div>
</body>
</html>
