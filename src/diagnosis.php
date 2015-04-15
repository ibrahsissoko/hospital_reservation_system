<?php

    include_once('../AutoLoader.php');
    AutoLoader::registerDirectory('../src/classes');

    require("config.php");
    require("MailFiles/PHPMailerAutoload.php");
    require("fpdf17/fpdf.php");
      
    if(empty($_SESSION['user'])) {
        header("Location: ../index.php");
        die("Redirecting to index.php");
    } else if(!empty($_GET['diagnosis']) && isset($_GET['submitButton'])) {
        $query = "
                SELECT *
                FROM appointment
                WHERE
                    id = :id
                 ";
        $query_params = array(
            ':id' => $_GET['id']
        );
        try {
            $stmt = $db->prepare($query);
            $stmt->execute($query_params);
        } catch(PDOException $ex) {
            die("Failed to run query: " . $ex->getMessage());
        }
        $row = $stmt->fetch();
        // Send an email to the doctor and/or patient about the diagnosis.
        $d = new Diagnosis($row['doctor_name'],$row['patient_name'],$_SESSION["user"]["email"], $_GET['diagnosis'], $_GET['observations'],$row['date'],$row['time'],$db);
        $d->initiate($_SESSION);
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
    <h1>Diagnosis Form:</h1> <br />
    
    <?php 
        $query = '
                SELECT *
                FROM appointment
                WHERE
                    id = :id
                ';
        $query_params = array(
            ':id' => $_GET['id']
        );
        try {
            $stmt = $db->prepare($query);
            $stmt->execute($query_params);
        } catch(PDOException $ex) {
            die("Failed to run query: " . $ex->getMessage());
        }
        $appointmentInfo = $stmt->fetch();
        $patientFLName = explode(" ", $appointmentInfo["patient_name"]);
    ?>
    <form action="diagnosis.php" method="get">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($_GET['id']);?>" />
        Doctor First Name:<br/>
        <input type="text" name="doctor_first_name" value="<?php echo $_SESSION["user"]["first_name"];?>" readonly="readonly" /><br/>
        Doctor Last Name:<br/>
        <input type="text" name="doctor_last_name" value="<?php echo $_SESSION["user"]["last_name"];?>" readonly="readonly" /><br/>
        Patient First Name:<br/>
        <input type="text" name="patient_first_name" value="<?php echo (!empty($patientFLName[0])) ? $patientFLName[0] : htmlspecialchars($_GET['patient_first_name']);?>" readonly="readonly" /><br/>
        Patient Last Name:<br/>
        <input type="text" name="patient_last_name" value="<?php echo (!empty($patientFLName[1])) ? $patientFLName[1] : htmlspecialchars($_GET['patient_last_name']);?>" readonly="readonly" /><br/>
        Observations:<br/>
        <textarea name="observations" cols="40" rows="5" value ="<?php echo htmlspecialchars($_GET["observations"]);?>" ></textarea><br/>
        Diagnosis:<br/>
        <input type="text" name="diagnosis" value="<?php echo htmlspecialchars($_GET["diagnosis"]);?>" /><br/>
        <br/><br/>
        <input type="submit" name = "submitButton" class="btn btn-info" value="Save" />
    
        <span class="success"><?php echo $d->success;?></span>
        <span class="error"><?php echo $d->error;?></span>
        
    </form>
    <br/>
    <a href="prescription.php">Prescribe Medication</a>
</div>

</body>
</html>