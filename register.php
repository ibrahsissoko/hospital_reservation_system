<?php 
    require("config.php");

    if(!empty($_POST)) { 

        // Ensure that the user fills out fields
        if(empty($_POST['username'])) { 
            die("Please enter a username."); 
        } 
        if(empty($_POST['password'])) { 
            die("Please enter a password.");
        } 
        if(empty($_POST['email'])) { 
            die("Invalid E-Mail Address"); 
        } 

        $username = $_POST['username'];
        $email = $_POST['email'];

        $salt = dechex(mt_rand(0, 2147483647)) . dechex(mt_rand(0, 2147483647)); 
        $password = hash('sha256', $_POST['password'] . $salt); 

        // has the password a ton so that it can't be un-done
        for($round = 0; $round < 65536; $round++){ 
            $password = hash('sha256', $password . $salt); 
        } 

        $query = " 
            SELECT *
            FROM users 
            WHERE username = '" . $username . "'"; 

        $result = $conn->query($query);
        $array = mysqli_fetch_array($result);

        if (sizeof($array) > 0) {
            die("Username is taken");
        }

        // Add it to the database

        $query = " 
            INSERT INTO users ( 
                username, 
                password, 
                salt, 
                email 
            ) VALUES ( " .
                "'" . $username . "', " .
                "'" . $password . "', " .
                "'" . $salt . "', " .
                "'" . $email . "'" .
            ")"; 

        if (mysqli_query($conn, $query)){
    	    // redirect to login
            header("Location: index.php"); 
            die("Redirecting to index.php"); 
        }
    } 
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Hospital Management</title>
    <meta name="description" content="Hospital management system for Intro to Software Engineering">
    <meta name="author" content="WAL Consulting">

    <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>
    <script src="assets/bootstrap.min.js"></script>
    <link href="assets/bootstrap.min.css" rel="stylesheet" media="screen">
    <link href="assets/styles.css" rel="stylesheet" type="text/css">
</head>

<body>

<div class="navbar navbar-fixed-top navbar-inverse">
  <div class="navbar-inner">
    <div class="container">
      <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </a>
      <a class="brand">Hospital Management</a>
      <div class="nav-collapse">
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="container hero-unit">
    <h1>Register</h1> <br /><br />
    <form action="register.php" method="post"> 
        <label>Username:</label> 
        <input type="text" name="username" value="" /> 
        <label>Password:</label> 
        <input type="password" name="password" value="" /> <br /><br />
        <input type="submit" class="btn btn-info" value="Register" /> 
    </form>
</div>

</body>
</html>
