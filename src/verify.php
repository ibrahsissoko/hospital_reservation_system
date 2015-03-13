<?php

    require("Config.php");

    if(!empty($_GET['email']) && !empty($_GET['hash'])) {
        $query = "
                SELECT *
                FROM users
                WHERE
                    email = :email
                AND
                    hash  = :hash
        ";

        $query_params = array(
            ':email' => $_GET['email'],
            ':hash' => $_GET['hash'],
        );

        try {
            $stmt = $db->prepare($query);
            $result = $stmt->execute($query_params);
        } catch(PDOException $ex) {
            die("Failed to run query: " . $ex->getMessage());
        }
        $row = $stmt->fetch();
        if($stmt->rowCount() > 0) {
            if ($row['active_user'] == 1) {
                $status = "This email account has already been registered.";
            } else {
                $query = "
                    UPDATE users
                    SET 
                        active_user = :active_user
                    WHERE
                        email = :email
                ";

                $query_params = array(
                    ':active_user' => 1,
                    ':email' => $_GET['email']
                );
                try {
                $stmt = $db->prepare($query);
                $result = $stmt->execute($query_params);
                } catch(PDOException $ex) {
                    die("Failed to run query: " . $ex->getMessage());
                }
                $status = "You are now registered!";
            }
        } else {
            $status = "The link you entered is invalid. Make sure that you copied it correctly.";
        }
    } else {
        $status = "Invalid method for account verification.";
    }

?>
<html lang="en">
<head>
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
      <a href="Home.php" class="brand">Hospital Management</a>
      <div class="nav-collapse collapse">
        <ul class="nav pull-right">
          <li><a href="../index.php">Login</a></li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="container hero-unit">
    <h3><center><?php echo $status;?></center></h3> <br/><br/>
</div>

</body>
</html>