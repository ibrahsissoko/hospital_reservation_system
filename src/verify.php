<?php
    if(!empty($_GET['email']) && !empty($_GET['hash'])) {
        $query = "
                SELECT *
                FROM users
                WHERE
                    email = :email
                AND
                    hash  = :hash
                AND
                    active_user = :active_user
        ";

        $query_params = array(
            ':email' => $_GET['email'],
            ':hash' => $_GET['hash'],
            ':active_user' => 0
        );

        try {
            $stmt = $db->prepare($query);
            $result = $stmt->execute($query_params);
        } catch(PDOException $ex) {
            die("Failed to run query: " . $ex->getMessage());
        }
        $row = $stmt->fetch();
        if($stmt->rowCount() == 1){
            echo "Lets get you registered.";
        } else {
            die("Either the email you entered was invalid, or you are already registered.");
        }
    } else {
        die("Invalid method for account verification.");
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
      <a href="src/home.php" class="brand">Hospital Management</a>
      <div class="nav-collapse collapse">
        <ul class="nav pull-right">
          <li><a href="src/register.php">Register</a></li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="container hero-unit">
    <h1>Verification Page</h1> <br />
</div>

</body>
</html>