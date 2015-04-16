<?php

    include_once('../AutoLoader.php');
    AutoLoader::registerDirectory('../src/classes');

    require("config.php");
    require("MailFiles/PHPMailerAutoload.php");
    
    if(empty($_SESSION['user'])) {
        header("Location: ../index.php");
        die("Redirecting to index.php");
    } else if(!empty($_POST['doctor_name']) && !empty($_POST['date']) && !empty($_POST['time']) && isset($_POST['submitButton'])) {
        $appointment = new ScheduleAppointment($_POST["doctor_name"], $_SESSION["user"]["first_name"]
            . " " . $_SESSION["user"]["last_name"], $_SESSION["user"]["email"], $_POST["date"], $_POST["time"], $db);
        $appointment->initiate($_SESSION);
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
    <script>$(function() {
        $( "#datepicker" ).datepicker( {
            minDate: "+1D", 
            maxDate: "+6M", 
            beforeShowDay: function(date) {
                        var day = date.getDay();
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
                            $availability = "Dummy value";
                            if (!empty($_POST['doctor_name']) || !empty($docInfo)) {
                                $query = "SELECT * FROM users WHERE user_type_id=2";
                                try {
                                    $stmt = $db->prepare($query);
                                    $result = $stmt->execute();
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        // Currently assuming no doctors will have the same first name, last
                                        // name, and degree.
                                        $string1 = str_replace(' ', '', $row["first_name"] . $row["last_name"] . $row["degree"]);
                                        if (!empty($_POST['doctor_name'])) {
                                            $string2 = str_replace(' ', '', htmlspecialchars($_POST['doctor_name']));
                                        } else {
                                            $string2 = str_replace(' ', '', htmlspecialchars($docInfo['doctor_name']));
                                        }
                                        if(strcmp($string1, $string2) == 0) {
                                            $availability = $row['availability'];
                                            break;
                                        }
                                    }
                                } catch(PDOException $e) {
                                    die("Failed to gather doctor availability. " . $e->getMessage());
                                }
                                
                                $returnVal = "return [(";
                                
                                if (strpos($availability,'M') === false) {
                                    $returnVal .= "day != 1 && ";
                                }
                                if (strpos($availability,'T') === false) {
                                    $returnVal .= "day != 2 && ";
                                }
                                if (strpos($availability,'W') === false) {
                                    $returnVal .= "day != 3 && ";
                                }
                                if (strpos($availability,'R') === false) {
                                    $returnVal .= "day != 4 && ";
                                }
                                if (strpos($availability,'F') === false) {
                                    $returnVal .= "day != 5 && ";
                                }
                                $returnVal .= "day != 6 && day != 0)];";

                                echo $returnVal;
                            }
                        ?>    
                    }});});
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
            <?php AccountDropdownBuilder::buildDropdown($db, $_SESSION) ?>
          <li><a href="home.php">Home</a></li>
        </ul>
      </div>
    </div>
  </div>
</div>
 
<div class="container hero-unit">
    <h1>Schedule an Appointment</h1> <br />
    <?php echo "Availability " . $availability?>
    <form action="schedule_appointment.php" method="post" id="mainForm">
        Which Doctor Would You Like?<br/>
        <select name="doctor_name" id="doctor_name" onchange="doctorNameUpdated()">
            <?php
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
                        if ($_POST['doctor_name'] == $row["first_name"] . " " . $row["last_name"] . " " . $row["degree"]) {
                            echo "<option value=\"" . $row["first_name"] . " " . $row["last_name"]
                                    . " " . $row["degree"] . "\" selected=\"selected\">" 
                                    . $row["first_name"] . " " . $row["last_name"] . " " 
                                    . $row["degree"] . "</option>";
                        } else if ($docName == $row["first_name"] . " " . $row["last_name"]) {
                            echo "<option value=\"" . $row["first_name"] . " " . $row["last_name"]
                                    . " " . $row["degree"] . "\" selected=\"selected\">" 
                                    . $row["first_name"] . " " . $row["last_name"] . " " 
                                    . $row["degree"] . "</option>";
                        } else {
                            echo "<option value=\"" . $row["first_name"] . " " . $row["last_name"] 
                                    . " " . $row["degree"] . "\">" . $row["first_name"] . " " 
                                    . $row["last_name"] . " " . $row["degree"] . "</option>";
                        }
                    }
                }
            } catch(PDOException $e) {
                die("Failed to gather doctor's names.");
            }
            echo "</select><br/>";

            $doctorName = !empty($_POST['doctor_name']) || !empty($_GET['id']);
            if($doctorName) {
                echo "Date:<br/>";
                echo '<input type="text" id="datepicker" name ="date" readonly="readonly" value="' . $_POST["date"] . '" onchange="dateUpdated()"/><br/>';
            }
            if ($doctorName && !empty($_POST['date'])) {
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
                // Check if all appointment times have been booked for this day.
                if (sizeof($preBookedTimes) != 8) {
                    echo "Time:<br/>";
                    echo '<select name="time" id="time">';
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
                } else {
                    echo '</br><p>No appointments available</p><br/><br/>';
                }
                echo '<input type="submit" name ="submitButton" class="btn btn-info" value="Submit"/><br/><br/>';
            }
        ?>
        <script>
        function doctorNameUpdated() {
            document.getElementById("mainForm").submit();  
        }
        function dateUpdated() {
            document.getElementById("mainForm").submit();
        }
    </script>
        <span class="success"><?php echo $appointment->success;?></span>
        <span class="error"><?php echo $appointment->error;?></span>
        
    </form>
</div>

</body>
</html>
