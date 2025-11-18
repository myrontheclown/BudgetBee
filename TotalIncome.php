<html>
    <head>
        <title>Total Income</title>
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
            #total-amount {
                font-size: 48px;
                color: #28a745;
                margin: 20px 0;
            }
            table{
                background-color: white;
                border: 2px solid black;
                text-align: center;
                margin: 20px auto;
            }
        </style>
    </head>
    <body>
        <h1 id="heading">Total Income</h1>
        <div id="container">
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
            
            $q1 = "SELECT SUM(t.Amount) AS total_income FROM transactions t INNER JOIN account a ON t.AccountID = a.AccountID WHERE a.UserID = '$userid' AND t.TransactionType = 'Income'";
            $r1 = mysqli_query($conn, $q1);
            
            if($r1)
            {
                $row = mysqli_fetch_array($r1);
                if (isset($row['total_income']) && $row['total_income'] !== null) {
                    $total_income = $row['total_income'];
                } else {
                    $total_income = 0;
                }
                echo "<h2>Total Income: </h2>";
                echo "<h1 id='total-amount'>₹" . $total_income . "</h1>";
            }
            else
                echo "<br>Error in calculation";
            
            $q2 = "SELECT t.* FROM transactions t INNER JOIN account a ON t.AccountID = a.AccountID WHERE a.UserID = '$userid' AND t.TransactionType = 'Income'";
            $r2 = mysqli_query($conn, $q2);
            $n = mysqli_num_rows($r2);
            
            echo "<br>Number of income records = " . $n;
            
            if($r2)
            {
                echo "<table border='2'>";
                echo "<tr>";
                echo "<th>Transaction ID</th>";
                echo "<th>Account ID</th>";
                echo "<th>Transaction Name</th>";
                echo "<th>Amount</th>";
                echo "<th>Payment Method</th>";
                echo "<th>Category</th>";
                echo "<th>Transaction Date</th>";
                echo "</tr>";
                
                while($info = mysqli_fetch_array($r2))
                {
                    echo "<tr>";
                    echo "<td>" . $info['TransactionID'] . "</td>";
                    echo "<td>" . $info['AccountID'] . "</td>";
                    echo "<td>" . $info['transactionName'] . "</td>";
                    echo "<td>₹" . $info['Amount'] . "</td>";
                    echo "<td>" . $info['PaymentMethod'] . "</td>";
                    echo "<td>" . $info['Category'] . "</td>";
                    echo "<td>" . $info['TransactionDate'] . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
            else
                echo "<br>Error in display operation";
            
            mysqli_close($conn);
            ?>
            <br><br>
            <a href="../Dashboard.php"><button type="button">Back to Dashboard</button></a>
        </div>
    </body>
</html>

