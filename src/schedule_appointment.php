<?php

    include_once('../AutoLoader.php');
    AutoLoader::registerDirectory('../src/classes');

    require("config.php");
    require("MailFiles/PHPMailerAutoload.php");
    
    if(empty($_SESSION['user'])) {
        header("Location: ../index.php");
        die("Redirecting to index.php");
    } else if (!empty($_POST['time']) && !empty($_POST['date']) && !empty($_POST['doctor_name'])) {
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
    } else if (!empty($_POST['date']) && !empty($_POST['doctor_name'])) {
        
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
    <script>
        function doctorNameUpdated() {
            var dateField = document.getElementById("date");
            dateField.type = 'text';
            var timeField = document.getElementById("time");
            timeField.type = 'hidden';
            document.getElementById("mainForm").submit();
        }
        function dateUpdated() {
            var timeField = document.getElementById("time");
            timeField.type = 'text';
            document.getElementById("mainForm").submit();
        }
    </script>
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
    <form action="schedule_appointment.php" method="post" id="mainForm">
        Which Doctor Would You Like?<br/>
        <select name="doctor_name" onchange="doctorNameUpdated()">
            <?php
            if(!empty($_GET['id'])) {
                $query = "
                        SELECT *
                        FROM users
                        WHERE
                            id = " . $_GET['id']
                        ;
                try {
                    $stmt = $db->prepare($query);
                    $result = $stmt->execute();
                    $docInfo = $stmt->fetch();
                } catch(PDOException $e) {
                    die("Failed to run query: " . $e->getMessage());
                }
            }
            // Only select doctors.
            $query = "SELECT * FROM users WHERE user_type_id=2";
            try {
                $stmt = $db->prepare($query);
                $result = $stmt->execute();
                
                if (!$docInfo && empty($_POST['doctor_name'])) {
                    // Create a blank entry and select it.
                    echo "<option value=\"\" selected=\"selected\"></option>";
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value=\"" . $row["first_name"] . " " . $row["last_name"] 
                                    . " " . $row["degree"] . "\">" . $row["first_name"] . " " 
                                    . $row["last_name"] . " " . $row["degree"] . "</option>";
                    }
                } else {
                    // Create a blank entry.
                    echo "<option value=\"\"></option>";
                    $docName = $docInfo['first_name'] . " " . $docInfo['last_name'];
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        // If it is the doctor's name, select them in the drop down menu.
                        if (!empty($_POST['doctor_name']) && $_POST['doctor_name'] == $row["first_name"] . " " . $row["last_name"] . " " . $row["degree"]) {
                            echo "<option value=\"" . $row["first_name"] . " " . $row["last_name"]
                                    . " " . $row["degree"] . "\" selected=\"selected\">" 
                                    . $row["first_name"] . " " . $row["last_name"] . " " 
                                    . $row["degree"] . "</option>";
                        } else if ($docName == $row["first_name"] . " " . $row["last_name"]) {
                            echo "<option value=\"" . $row["first_name"] . " " . $row["last_name"]
                                    . " " . $row["degree"] . "\" selected=\"selected\">" 
                                    . $row["first_name"] . " " . $row["last_name"] . " " 
                                    . $row["degree"] . "</option>";
                            // Set the post value of the doctor's name.
                            $_POST['doctor_name'] = $docName . " " . $row['degree'];
                        } else {
                            echo "<option value=\"" . $row["first_name"] . " " . $row["last_name"] 
                                    . " " . $row["degree"] . "\">" . $row["first_name"] . " " 
                                    . $row["last_name"] . " " . $row["degree"] . "</option>";
                        }
                    }
                }
            } catch(PDOException $e) {
                die("Failed to gather doctor's names.");
            }?></select><br/>
        <?php
            if(!empty($_POST['doctor_name'])) {
                echo "Date:<br/>";
                echo '<input type="text" id="datepicker" name ="date" readonly="readonly" value="' . $_POST["date"] . '" onchange="dateUpdated()"/><br/>';
            }
            if (!empty($_POST['doctor_name']) && !empty($_POST['date'])) {
                if (empty($docInfo)) {
                    $query2 = "
                            SELECT *
                            FROM users
                            WHERE
                                user_type_id = :id
                                AND
                                first_name = :doctorFirstName
                                AND
                                last_name = :doctorLastName
                             ";
                     $name = explode(" ", $_POST['doctor_name']);
                     $query_params2 = array(
                         ":id" => 2,
                         ":doctorFirstName" => $name[0],
                         ":doctorLastName" => $name[1]
                     );
                     try {
                         $stmt2 = $db->prepare($query2);
                         $result2 = $stmt2->execute($query_params2);
                     } catch(PDOException $ex) {
                         die("Failed to run query: " . $ex->getMessage());
                     }
                     $docInfo = $stmt2->fetch();
                }
                $query = '
                        SELECT *
                        FROM shift
                        WHERE
                            id = :id
                        ';
                $query_params = array(
                    ':id' => $docInfo['shift_id']
                );
                try {
                    $stmt = $db->prepare($query);
                    $result = $stmt->execute($query_params);
                    $shift = $stmt->fetch();
                } catch(PDOException $e) {
                    die("Failed to run query: " . $e->getMessage());
                }
                echo "Time:<br/>";
                echo '<select name="time">';
                $beginTime = intval($shift['start_time']);
                $endTime = intval($shift['end_time']);
                if ($endTime < $beginTime) {
                    // E.g 19-3 => 19-27 for simplicity.
                    $endTime += 24;
                }
                // Determine appointments that have already been scheduled.
                $query = '
                        SELECT *
                        FROM appointment
                        WHERE
                            doctor_name = :doctorName
                            AND
                            date = :date
                        ';
                $query_params = array(
                    ':doctorName' => $_POST['doctor_name'],
                    ':date' => $_POST['date']
                );
                try {
                    $stmt = $db->prepare($query);
                    $result = $stmt->execute($query_params);
                } catch(PDOException $e) {
                    die("Failed to run query: " . $e->getMessage());
                }
                $preBookedTimes = array();
                while ($alreadyBookedTimes = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $times = explode(":", $alreadyBookedTimes["time"]);
                    array_push($preBookedTimes, intval($times[0]));
                }
                // Determine which days go in the select field.
                for($i = $beginTime; $i < $endTime; $i++) {
                    $alreadyBooked = false;
                    if ($i < 12) {
                        foreach($preBookedTimes as $hour) {
                            if($i == $hour) {
                                $alreadyBooked = true;
                                break;
                            }
                        }
                        if (!$alreadyBooked) {
                            echo "<option value =\"" . $i . ":00 am\">" . $i . ":00 am</option>";
                        }
                    } else if ($i == 12) {
                        foreach($preBookedTimes as $hour) {
                            if($i == $hour) {
                                $alreadyBooked = true;
                                break;
                            }
                        }
                        if(!$alreadyBooked) {
                            echo "<option value =\"" . $i . ":00 pm\">" . $i . ":00 pm</option>";
                        }
                    } else if ($i > 12 && $i < 24) {
                        $val = $i - 12;
                        foreach($preBookedTimes as $hour) {
                            if($val == $hour) {
                                $alreadyBooked = true;
                                break;
                            }
                        }
                        if(!$alreadyBooked) {
                            echo "<option value =\"" . $val . ":00 pm\">" . $val . ":00 pm</option>";
                        }
                    } else if ($i == 24) {
                        $val = $i - 12;
                        foreach($preBookedTimes as $hour) {
                            if($val == $hour) {
                                $alreadyBooked = true;
                                break;
                            }
                        }
                        if(!$alreadyBooked) {
                            echo "<option value =\"" . $val . ":00 am\">" . $val . ":00 am</option>";
                        }
                    } else {
                        $val = $i - 24;
                        foreach($preBookedTimes as $hour) {
                            if($val == $hour) {
                                $alreadyBooked = true;
                                break;
                            }
                        }
                        if(!$alreadyBooked) {
                            echo "<option value =\"" . $val . ":00 am\">" . $val . ":00 am</option>";
                        }
                    }
                }
                echo "</select><br/><br/>";
                echo '<input type="submit" name = "submit" class="btn btn-info" value="Submit" /><br/><br/>';
            }
        ?>
        <span class="success"><?php echo $appointment->success;?></span>
        <span class="error"><?php echo $appointment->error;?></span>
        
    </form>
</div>

</body>
</html>
