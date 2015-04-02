<?php

    include_once('../AutoLoader.php');
    AutoLoader::registerDirectory('../src/classes');

    require("config.php");
    require("MailFiles/PHPMailerAutoload.php");
    
    
    if(empty($_SESSION['user'])) {
        header("Location: ../index.php");
        die("Redirecting to index.php");
    } else if(!empty($_POST['doctor_first_name']) && !empty($_POST['doctor_last_name']) && 
        !empty($_POST['patient_first_name']) &&!empty($_POST['patient_last_name']) && 
        !empty($_POST['observations']) && !empty($_POST['diagnosis'])) {
        // Send an email to the doctor and/or patient about the diagnosis.
        $d = new Diagnosis($_POST['doctor_first_name'],$_POST['patient_first_name'],
            $_SESSION["user"]["email"],$_POST['diagnosis'], $db);
        $d->sendEmailToPatient();
        $d->sendEmailToDoctor();
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
                    <li><a href="home.php">Home</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="container hero-unit">
    <h1>Diagnosis Form:</h1> <br />
    <form action="diagnosis.php" method="post">
        Doctor First Name:<br/>
        <input type="text" name="doctor_first_name" value="<?php echo $_SESSION["user"]["first_name"];?>" /><br/>
        Doctor Last Name:<br/>
        <input type="text" name="doctor_last_name" value="<?php echo $_SESSION["user"]["last_name"];?>" /><br/>
        Patient First Name:<br/>
        <input type="text" name="patient_first_name" value="<?php echo htmlspecialchars($_POST["patient_first_name"]);?>" /><br/>
        Patient Last Name:<br/>
        <input type="text" name="patient_last_name" value="<?php echo htmlspecialchars($_POST["patient_last_name"]);?>" /><br/>
        Observations:<br/>
        <textarea name="Observations" cols="40" rows="5" value ="<?php echo htmlspecialchars($_POST["observations"]);?>" ></textarea><br/>
        Diagnosis:<br/>
        <input type="text" name="diagnosis" value="<?php echo htmlspecialchars($_POST["diagnosis"]);?>" /><br/>
        <br/><br/>
        <input type="submit" name = "submit" class="btn btn-info" value="Save" />
    </form>
    <br/>
    <a href="prescription.php">Prescribe Medication</a>
</div>

</body>
</html>