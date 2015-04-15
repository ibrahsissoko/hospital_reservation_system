<?php

    include_once('../AutoLoader.php');
    AutoLoader::registerDirectory('../src/classes');

    require("config.php");
    require("MailFiles/PHPMailerAutoload.php");
    require("fpdf17/fpdf.php");
      
    if(empty($_SESSION['user'])) {
        header("Location: ../index.php");
        die("Redirecting to index.php");
    } else if(!empty($_POST['doctor_first_name']) && !empty($_POST['patient_first_name']) && !empty($_POST['patient_last_name']) && isset($_POST['submitButton'])) {
        $patient_name = $_POST['patient_first_name'] . " " . $_POST['patient_last_name'];
        $doctor_name = $_SESSION["user"]["first_name"] . " " . $_SESSION["user"]["last_name"] . " " . $_SESSION['user']['degree'];
        $query = "
                SELECT *
                FROM appointment
                WHERE
                    doctor_name = $doctor_name
                AND
                    patient_name = $patient_name
                 ";
        try {
            $stmt = $db->prepare($query);
            $stmt->execute();
        } catch(PDOException $ex) {
            die("Failed to run query: " . $ex->getMessage());
        }
        /* 
         * Currently just getting the first one in the list, even though the patient might
         * have more than one appointment with the same doctor.
         */
        $row = $stmt->fetch();
        // Send an email to the doctor and/or patient about the diagnosis.
        $d = new Diagnosis($doctor_name ,$patient_name ,$_SESSION["user"]["email"], $_POST['diagnosis'], $_POST['observations'],$row['date'],$row['time'],$db);
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
        <textarea name="observations" cols="40" rows="5" value ="<?php echo htmlspecialchars($_POST["observations"]);?>" ></textarea><br/>
        Diagnosis:<br/>
        <input type="text" name="diagnosis" value="<?php echo htmlspecialchars($_POST["diagnosis"]);?>" /><br/>
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