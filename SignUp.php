<!DOCTYPE html>
<html lang="en">
<head>
    <title>Sign Up - BudgetBee</title>
    <style>
            body {
                background-color: #fff9cc;
                font-family: Arial, sans-serif;
                text-align: center;
                margin: 0; 
                padding: 0;
                }

            #form1 {
                text-align: center;
            }

            #form-container {
                border:5px solid black;
                background-color: #ffef89;
                margin: 20px auto;
                padding: 30px 10px;
                width: 50%;
                border-radius: 15px;
                text-align: center;
            }

            .button {
                background-color: #007bff; 
                color: white;
                font-size: medium;
                border: 2px solid black;
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

            fname = document.getElementById("fname").value;
            lname = document.getElementById("lname").value;
            dob = document.getElementById("dob").value;
            address = document.getElementById("address").value;
            phone = document.getElementById("phone").value;
            email = document.getElementById("email").value;
            username = document.getElementById("username").value;
            password = document.getElementById("password").value;

            if(fname=="" || lname=="" || dob=="" || address=="" || phone=="" || email=="" || password==""){
                alert("Please fill all the details");
                return false;
            }

            if(password.trim().length<6 || password.trim().length>10){
                alert("Password must be between 6 to 10 characters");
                return false;
            }

            if(password.search(/[A-Z]/)==-1){
                alert("Password must contain at least one uppercase alphabet");
                return false;
            }

            if(password.search(/[0-9]/)==-1){
                alert("Password must contain at least one digit");
                return false;
            }

            if(password.search(/[a-z]/)==-1){
                alert("Password must contain at least one lowercase alphabet");
                return false;
            }
            
            if(password.search(/[\@\#\$\%\&\!\*\.\;\,]/)==-1){
                alert("Password must contain at least one special character (@,#,$,%,&,!,*,.,;,)");
                return false;
            }

            var x = /^[789]{1}[0-9]{9}$/;
            if(x.test(phone)==false)
            {
            alert("Invalid phone number. Starting digit must be 7, 8 or 9.");
            return false;
            }

            var re_em = /^([a-zA-Z0-9\.-]+)@([a-zA-Z0-9-]+)\.([a-z]+)(\.[a-z]+)?$/;
            if(re_em.test(email)==false){
                alert("Invalid email address");
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

            alert("Sign-Up Successful");
            return true;
        }
    </script>
</head>
<body>
    <?php
    $conn = mysqli_connect("localhost", "root", "", "database1");
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $fname = $_POST['fname'];
        $lname = $_POST['lname'];
        $dob = $_POST['dob'];
        $address = $_POST['address'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];
        $username = $_POST['username'];
        $password = $_POST['password'];

        if (!($fname) || !($lname) || !($dob) || !($address) || !($phone) || !($email) || !($username) || !($password)) {
            echo "<script>alert('Please fill all the details');</script>";
            exit();
        }

        $q1 = "INSERT INTO user (FirstName, LastName, DOB, Address, Phone, Email, Username, Password) VALUES ('$fname', '$lname', '$dob', '$address', '$phone', '$email', '$username', '$password')";
        $r1 = mysqli_query($conn, $q1);
        if (!($r1)) {
                echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
        } else {
                echo "<script>alert('Sign-Up Successful'); window.location.href='Login.php';</script>";
        }
    }
    ?>
    
    <h1 style="font-size:70px; border-width: 0px 5px 5px 5px; border-style: solid; border-color:black; color: azure; background-color: blueviolet; text-align:center; margin: 0 auto; padding: 20px 30px; width:50%; border-radius:0px 0px 15px 15px;">Hey there! Welcome to <span style="color: rgb(245, 180, 0);">BudgetBee</span></h1><br><br>
    <img src="Logo.png" alt="" id="logo" height =300px width=300px>

    <div id="form-container">
        <h2 style="color:brown;">Let's get started by getting your details</h2><br><br>
        <form id="form1" onsubmit="return validate()" method="POST" action="">

            <label>Enter your First Name: </label>
            <input type="text" id="fname" name="fname" placeholder="First Name"><br><br>

            <label>Enter your Last Name: </label>
            <input type="text" id="lname" name="lname" placeholder="Last Name"><br><br>

            <label>Enter your Date Of Birth: </label>
            <input type="date" id="dob" name="dob"><br><br>

            <label>Enter your Address: </label>
            <input type="text" id="address" name="address" placeholder="Address"><br><br>

            <label>Enter your Phone Number: </label>
            <input type="tel" id="phone" name="phone"><br><br>

            <label>Enter your Email: </label>
            <input type="email" id="email" name="email" placeholder="example@example"><br><br>

            <label>Create a Username: </label>
            <input onclick="alert('Username should contain between 6 to 10 characters. Atleast one digit')" type="text" id="username" name="username"><br><br>

            <label>Create a Password: </label>
            <input type="password" id="password" name="password"><br><br>

            <input class="button" type="submit">
            <input class="button" type="reset">
        </form>
    </div>

</body>
</html>
