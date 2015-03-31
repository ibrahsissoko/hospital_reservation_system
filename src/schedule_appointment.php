<?php

    include_once('../AutoLoader.php');
    AutoLoader::registerDirectory('../src/classes');

    require("config.php");
    require("MailFiles/PHPMailerAutoload.php");
    
    if(empty($_SESSION['user'])) {
        header("Location: ../index.php");
        die("Redirecting to index.php");
    } else if (!empty($_POST)) {
        $appointment = new ScheduleAppointment($_POST["doctor_name"], $_SESSION["user"]["first_name"]
                . " " . $_SESSION["user"]["last_name"], $_SESSION["user"]["email"], $_POST["date"], $_POST["time"], $db);
        if (empty($appointment->error)) {
            if($_SESSION['user']['appointment_confirm_email'] == "Yes" || $_SESSION['user']['appointment_confirm_email'] == NULL) {
                if($appointment->doctorInfo['appointment_confirm_email'] == "Yes" || $appointment->doctorInfo['appointment_confirm_email'] == NULL) {
                    if($appointment->sendEmailToPatient() && $appointment->sendEmailToDoctor()) {
                        $appointment->updateAppointmentTable($db);
                        $appointment->success = "Confirmation emails were sent to you and the doctor you requested!";
                    } else {
                        $appointment->error = "An error occurred sending confirmation emails. Try again soon.";
                    } 
                } else {
                    if($appointment->sendEmailToPatient()) {
                        $appointment->updateAppointmentTable($db);
                        $appointment->success = "A confirmation email was sent to you regarding your appointment.";
                    } else {
                        $appointment->error = "An error occurred sending you a confirmation email. Try again soon.";
                    }
                }
            } else {
                if($appointment->doctorInfo['appointment_confirm_email'] == "Yes" || $appointment->doctorInfo['appointment_confirm_email'] == NULL) {
                    if($appointment->sendEmailToDoctor()) {
                        $appointment->updateAppointmentTable($db);
                        $appointment->success = "Appointment booked!";
                    } else {
                        $appointment->error = "Appointment could not be booked. Try again soon.";
                    }
                } else {
                    $appointment->updateAppointmentTable($db);
                    $appointment->success = "Appointment booked!";
                }
            }
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
    <link rel="stylesheet" href="http://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    <script src="http://code.jquery.com/jquery-1.10.2.js"></script>
    <script src="http://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
    <script>$(function() {$( "#datepicker" ).datepicker({minDate: "+1D", maxDate: "+6M", beforeShowDay: $.datepicker.noWeekends});});</script>
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
          <li><a href="home.php">Home</a></li>
        </ul>
      </div>
    </div>
  </div>
</div>
 
<div class="container hero-unit">
    <h1>Schedule an Appointment</h1> <br />
    <form action="schedule_appointment.php" method="post">
        Date:<br/>
        <input type="text" id="datepicker" name ="date" readonly="readonly"/><br/>
        Time:<br/>
        <select name="time">
            <option value="8:00 am" selected="selected">8:00 am</option>
            <option value="9:00 am">9:00 am</option>
            <option value="10:00 am">10:00 am</option>
            <option value="11:00 am">11:00 am</option>
            <option value="1:00 pm">1:00 pm</option>
            <option value="2:00 pm">2:00 pm</option>
            <option value="3:00 pm">3:00 pm</option>
            <option value="4:00 pm">4:00 pm</option>
        </select><br/>
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
                        echo "<option value=\"" . $row["first_name"] . " " . $row["last_name"]
                                . " " . $row["degree"] . "\" selected=\"selected\">" 
                                . $row["first_name"] . " " . $row["last_name"] . " " 
                                . $row["degree"] . "</option>";
                        $i++;
                    } else {
                        echo "<option value=\"" . $row["first_name"] . " " . $row["last_name"] 
                                . " " . $row["degree"] . "\">" . $row["first_name"] . " " 
                                . $row["last_name"] . " " . $row["degree"] . "</option>";
                    }
                }
            } catch(PDOException $e) {
                die("Failed to gather doctor's names.");
            }?></select><br/><br/>
        <input type="submit" name = "submit" class="btn btn-info" value="Submit" /><br/><br/>
        <span class="success"><?php echo $appointment->success;?></span>
        <span class="error"><?php echo $appointment->error;?></span>
        
    </form>
</div>

</body>
</html>
