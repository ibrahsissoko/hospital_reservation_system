<?php 
    require("config.php");
    if(!empty($_POST)) { 
      $username = $_POST['username'];
      $password = $_POST['password'];

      $query = " 
          SELECT id, username, password, salt, email 
          FROM users 
          WHERE username = " + $username; 

      $result = mysqli_query($connection, $query)

      if (mysql_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        
        if ($row && $password == $row['password']) {
          // login is ok!
          header("Location: home.php"); 
          die("Redirecting to: home.php"); 
        } else {
            echo "failed to login";
        }
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
      <a class="brand">Hostpital Management</a>
      <div class="nav-collapse collapse">
        <ul class="nav pull-right">
          <li><a href="register.php">Register</a></li>
          <li class="divider-vertical"></li>
          <li class="dropdown">
            <a class="dropdown-toggle" href="#" data-toggle="dropdown">Log In<strong class="caret"></strong></a>
            <div class="dropdown-menu" style="padding: 15px; padding-bottom: 0px;">
                <form action="index.php" method="post"> 
                    Username:<br/> 
                    <input type="text" name="username" value="" /> 
                    <br/><br/> 
                    Password:<br/> 
                    <input type="password" name="password" value="" /> 
                    <br/><br/> 
                    <input type="submit" class="btn btn-info" value="Login" /> 
                </form> 
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="container hero-unit">
    <h1>Main Homescreen</h1>
    <p>No user specific content because you haven't logged in.</p>
    <ul>
        <li>Use the default credentials to log in:<br/>
            <strong>user:</strong> admin<br />
            <strong>pass:</strong> password<br />
        </li>
    </ul>
</div>

</body>
</html>
