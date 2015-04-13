<?php

    include_once('../AutoLoader.php');
    AutoLoader::registerDirectory('../src/classes');

    require("config.php");
    require("MailFiles/PHPMailerAutoload.php");
    
    if(empty($_SESSION['user'])) {
        header("Location: ../index.php");
        die("Redirecting to index.php");
    } else if (!empty($_GET)) {
        $cancelAppointment = new CancelAppointment();
        
        if ($cancelAppointment->updateAppointmentTable($db, $_GET['id'])) {
            $cancelAppointment->success = "Appointment Deleted";
        } else {
            $cancelAppointment->error = "Error Deleting Appointment";
        }

        if($_SESSION['user']['appointment_deleted_email'] == "Yes" || $_SESSION['user']['appointment_deleted_email'] == NULL) {
            $cancelAppointment->sendEmailToUser($_SESSION['user']['email'], $_SESSION['user']['first_name'] . " " . $_SESSION['user']['last_name']);
        }
        
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
            <?php AccountDropdownBuilder::buildDropdown($db, $_SESSION) ?>
          <li><a href="home.php">Home</a></li>
        </ul>
      </div>
    </div>
  </div>
</div>
 
<div class="container hero-unit">
    <h3><center><?php echo $cancelAppointment->success; echo $cancelAppointment->error;?></center></h3>
    <a href="view_appointments.php">Back to List of Appointments</a>
</div>

</body>
</html>
