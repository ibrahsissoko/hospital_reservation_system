<?php

    include_once('../AutoLoader.php');
    AutoLoader::registerDirectory('../src/classes');

    require("config.php");
    
    if(!empty($_POST)) {
        $email = $_SESSION['user']['email'];
        $query = "
            SELECT *
            FROM users
            WHERE
                email = :email
        ";
        $query_params = array(
            ':email' => $email
        );
        try {
            $stmt = $db->prepare($query);
            $result = $stmt->execute($query_params);
        } catch(PDOException $ex) {
            die("Failed to run query: " . $ex->getMessage());
        }
        $row = $stmt->fetch();
        if ($row) {
            $check_password = PasswordUtils::hashPassword(htmlspecialchars($_POST['password']), $row['salt']);
            if($check_password == $row['password']) {
                $query = "
                        DELETE
                        FROM users
                        WHERE
                          email = :email
                    ";
                $query_params = array(
                    ':email' => $_SESSION['user']['email']
                );
                try {
                    $stmt = $db->prepare($query);
                    $result = $stmt->execute($query_params);
                } catch(PDOException $ex) {
                        die("Failed to run query: " . $ex->getMessage());
                }   
                unset($_SESSION['user']);
                $success = "Account deleted.";
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "Error fetching user information. Try again soon.";
        }
    } else {
        $error = "Please enter your password.";
    }       
    
?>

<!doctype html>
<html lang="en">
<head>
    <style>.error {color: #FF0000;}</style>
    <style>.success {color: #00FF00;</style>
    <meta charset="utf-8">
    <title>Hospital Management</title>
    <meta name="description" content="Hospital management system for Intro to Software Engineering">
    <meta name="author" content="WAL Consulting">
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>
    <script src="../assets/bootstrap.min.js"></script>
    <link href="../assets/bootstrap.min.css" rel="stylesheet" media="screen">
    <link href="../assets/styles.css" rel="stylesheet" type="text/css">
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
      <a href="home.php" class="brand">Hospital Management</a>
      <div class="nav-collapse">
        <ul class="nav pull-right">
            <li><a href="../index.php">Login</a></li>
        </ul>
      </div>
    </div>
  </div>
</div>
 
<div class="container hero-unit">
    <h1><center>Delete Account</center></h1><br/>
    <form action="delete_account.php" method="post">
        Password:<br/>
        <input type="password" name="password" value=""><br/><br/>
        <input type="submit" name="submit" class="btn btn-info" value="Submit"/><br/><br/>
        <span class="success"><?php echo $success;?></span>
        <span class="error"><?php echo $error;?></span>
    </form>
</div>

</body>
</html>
