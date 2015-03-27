<?php

    include_once('../AutoLoader.php');
    AutoLoader::registerDirectory('../src/classes');

    require("config.php");

    if(empty($_SESSION['user'])) {
        header("Location: ../index.php");
        die("Redirecting to index.php"); 
    } else {
        switch($_SESSION['user']['user_type_id']) {
                            case 3: // nurse
                                $userType = "nurse";
                                break;
                            case 2: // doctor
                                $userType = "doctor";
                                break;
                            case 4: // admin
                                $userType = "administrator";
                                break;
                            default:
                                $userType = "patient";
                                break;
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
          <li><a href="my_account.php">Account</a></li>
            <li><a href="logout.php">Log Out</a></li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="container hero-unit">
    <h2>Welcome!</h2>
    <p>
    <?php
        if ($userType == "patient") {
            echo "<a href=\"schedule_appointment.php\">Schedule an Appointment</a>";
        } else if ($userType == "doctor") {
            echo "<a href=\"diagnosis.php\">Diagnosis Form</a>";   
        }
    ?><br/>
        <br>Name:           <?php echo htmlentities($_SESSION['user']['first_name'], ENT_QUOTES, 'UTF-8') . " " . htmlentities($_SESSION['user']['last_name'], ENT_QUOTES, 'UTF-8'); ?>
        <br>Email:          <?php echo htmlentities($_SESSION['user']['email'], ENT_QUOTES, 'UTF-8'); ?>
        <br>Sex:            <?php echo htmlentities($_SESSION['user']['sex'], ENT_QUOTES, 'UTF-8') ?>
        <br>Age:            <?php echo htmlentities($_SESSION['user']['age'], ENT_QUOTES, 'UTF-8'); ?>
        <br>Phone Number:   <?php echo htmlentities($_SESSION['user']['phone'], ENT_QUOTES, 'UTF-8'); ?>
        <br>State:          <?php echo htmlentities($_SESSION['user']['state'], ENT_QUOTES, 'UTF-8'); ?>
        <br>User Type:      <?php
                                $query = "
                                        SELECT *
                                        FROM user_types
                                        WHERE
                                            id = :id
                                        ";
                                $query_params = array(
                                    ':id' => $_SESSION['user']['user_type_id']
                                );

                                try {
                                    $stmt = $db->prepare($query);
                                    $result = $stmt->execute($query_params);
                                } catch(PDOException $ex) {
                                    die("Failed to run query: " . $ex->getMessage());
                                }

                                $row = $stmt->fetch();
                                if ($row) {
                                    echo $row['type_name'];
                                }
                        ?>
    </p>

</div>

</body>
</html>
