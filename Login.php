<html>
<head>
    <title>Login</title>
    <style>
        body {
            background-color: #fff9cc;
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 0; 
            padding: 0;
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
        .button {
            border: 2px solid black;
            background-color: #007bff; 
            color: white;
            font-size: medium;
            padding: 10px 20px;
            margin: 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .button:hover {
            background-color: #0056b3;
        }
    </style>
    <script>
        function validate(){
            username = document.getElementById("username").value;
            password = document.getElementById("password").value;

            if(username=="" || password==""){
                alert("Please fill all the details");
                return false;
            }
            
            if(username.trim().length<6 || username.trim().length>10){
                alert("Username must be between 6 to 10 characters");
                return false;
            }

            if(username.search(/[0-9]/)==-1){
                alert("Username must contain at least one digit");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>

    <?php
    session_start();
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $conn = mysqli_connect("localhost", "root", "", "database1");

        if (!$conn) {
            die("Connection failed: ");
        }

            $username = $_POST['username'];
            $password = $_POST['password'];

            if (!($username) || !($password)) {
                echo "<script>alert('Error: Please fill in all fields');</script>";
                exit();
            }

        $query = "SELECT * FROM user WHERE Username='$username'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            
            if ($row['Password'] == $password) {
                if (isset($row['UserID']) && $row['UserID'] !== null) {
                    $_SESSION['UserID'] = $row['UserID'];
                } elseif (isset($row['ID']) && $row['ID'] !== null) {
                    $_SESSION['UserID'] = $row['ID'];
                } else {
                    $_SESSION['UserID'] = null;
                }
                $_SESSION['Username'] = $username;
                echo "<script>alert('Login Successful'); window.location.href='Dashboard.php';</script>";
                exit();
            } else {
                echo "<script>alert('Incorrect password');</script>";
            }
        } else {
            echo "<script>alert('User not found');</script>";
        }
        mysqli_close($conn);
    }

    ?>

<h1 style="font-size:70px; color: azure; background-color: blueviolet;border-bottom: 5px solid black;
border-right: 5px solid black; border-left: 5px solid black; text-align:center; margin: 0 auto; padding: 20px 30px; width:50%; border-radius:0px 0px 15px 15px;">
    Welcome back
</h1>

<div id="form-container">
    <img src="Logo.png" alt="" id="logo" height =300px width=300px><br><br>
    <form id="form1" onsubmit="return validate()" method="POST" action="">
        <label>Enter your username: </label>
        <input type="text" id="username" name="username" placeholder="Username"><br><br>
        
        <label>Enter your password: </label>
        <input type="password" id="password" name="password" placeholder="Password"><br><br>

        <input class="button" type="submit" value="Login">
        <input class="button" type="reset" value="Reset">
    </form>
</div>

</body>
</html>
