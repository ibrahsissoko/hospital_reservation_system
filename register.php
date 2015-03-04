<?php 
    require("config.php");
    if(!empty($_POST)) { 
        // Ensure that the user fills out fields
        // Check if the username is already taken
        // Add it to the database
        // Hash the password
        
	// redirect to login
        header("Location: index.php"); 
        die("Redirecting to index.php"); 
    } 
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Bootstrap Tutorial</title>
    <meta name="description" content="Bootstrap Tab + Fixed Sidebar Tutorial with HTML5 / CSS3 / JavaScript">
    <meta name="author" content="Untame.net">

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
        <ul class="nav pull-right">
          <li><a href="index.php">Return Home</a></li>
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
