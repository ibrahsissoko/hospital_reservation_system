<?php

    include_once('../AutoLoader.php');
    AutoLoader::registerDirectory('../src/classes');

    require("config.php");
    if (!empty($_POST)) {
        $appointment = new ScheduleAppointment();
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
    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    <script src="//code.jquery.com/jquery-1.10.2.js"></script>
    <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
    <link rel="stylesheet" href="/resources/demos/style.css">
    <script>$(function() {$( "#datepicker" ).datepicker();});</script>
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
          <li><a href="logout.php">Log Out</a></li>
        </ul>
      </div>
    </div>
  </div>
</div>
 
<p>Date: <input type="text" id="datepicker"></p>
    
<div class="container hero-unit">
    <h1>Schedule an Appointment</h1> <br />
    <form action="schedule_appointment.php" method="post">
        Date:<br/>
        <input type="text" id="datepicker" name ="date" /><br/>
        Time:<br/>
        <input type="text" name="time" value="" /><br/>
        Which Doctor Would You Like?<br/>
        <select name="doctor_name">
            <?php
            // Only select doctors.
            $query = "SELECT * FROM users WHERE user_type_id=2";
            try {
                $stmt = $db->prepare($query);
                $result = $stmt->execute();
                $i = 0;
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    if ($i == 0) {
                        echo "<option value=\"" . "Dr. " . $row["first_name"] . " " 
                                . $row["last_name"] . "\" selected=\"selected\">Dr. "
                                . $row["first_name"] . " " . $row["last_name"] . "</option>";
                        $i++;
                    } else {
                        echo "<option value=\"" . "Dr. " . $row["first_name"] . " " 
                                . $row["last_name"] . "\">Dr. " . $row["first_name"]
                                . " " . $row["last_name"] . "</option>";
                    }
                }
            } catch(PDOException $e) {
                die("Failed to gather doctor's names.");
            }?></select><br/><br/>
        <input type="submit" name = "submit" class="btn btn-info" value="Submit" />
    </form>
</div>

</body>
</html>
