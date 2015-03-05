<?php 
    $debug = false;
    $failed = false;
    $message = "";

    require("config.php");
    
    if(!empty($_POST)) { 
      $email = $_POST['email'];

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
            $check_password = hash('sha256', $_POST['password'] . $row['salt']);
            for($round = 0; $round < 65536; $round++){
                $check_password = hash('sha256', $check_password . $row['salt']);
            }

            if($check_password === $row['password']){
                unset($row['salt']);
                unset($row['password']);
                $_SESSION['user'] = $row;

                if ($row['info_added'] == 0) {
                    header("Location: user_info.php");
                    die("Redirecting to: user_info.php");
                } else {
                    header("Location: home.php");
                    die("Redirecting to: home.php");
                }
            } else {
                $failed = true;
                die("Invalid Password.");
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
      <a href="home.php" class="brand">Hostpital Management</a>
      <div class="nav-collapse collapse">
        <ul class="nav pull-right">
          <li><a href="register.php">Register</a></li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="container hero-unit">
    <p> <?php 
            if ($debug) {
              echo "query: " . $query . "</br>" . 
                    "message: " . $message . "</br>" .
                    "num_rows: " . $result->num_rows . "</br>" .
                    "password: " .  $row["password"];
            } 

            if ($failed) {
              echo "Login Failed!";
            }
        ?>
    </p>

    <h1>Login</h1> <br />
    <form action="index.php" method="post">
        Email:<br/>
        <input type="text" name="email" value="" />
        <br/>
        Password:<br/> 
        <input type="password" name="password" value="" /> 
        <br/><br/> 
        <input type="submit" class="btn btn-info" value="Login" /> 
    </form> 
</div>

</body>
</html>
