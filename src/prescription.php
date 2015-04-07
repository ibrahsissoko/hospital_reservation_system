<?php

    include_once('../AutoLoader.php');
    AutoLoader::registerDirectory('../src/classes');

    require("config.php");
    
    if(empty($_SESSION['user'])) {
        header("Location: ../index.php");
        die("Redirecting to index.php");
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
                    <?php AccountDropdownBuilder::buildDropdown($_SESSION) ?>
                    <li><a href="home.php">Home</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="container hero-unit">
    <h1>Make a Prescription:</h1> <br />
    <form action="prescription.php" method="post">
        Doctor First Name:<br/>
        <input type="text" name="doctor_first_name" value="<?php echo $_SESSION["user"]["first_name"];?>" /><br/>
        Doctor Last Name:<br/>
        <input type="text" name="doctor_last_name" value="<?php echo $_SESSION["user"]["last_name"];?>" /><br/>
        Patient First Name:<br/>
        <input type="text" name="patient_first_name" value="<?php echo htmlspecialchars($_POST["patient_first_name"]);?>" /><br/>
        Patient Last Name:<br/>
        <input type="text" name="patient_last_name" value="<?php echo htmlspecialchars($_POST["patient_last_name"]);?>" /><br/>
        Prescription:<br/>
        <input type="text" name="prescription" value="<?php echo htmlspecialchars($_POST["prescription"]);?>" /><br/>
        Pharmacy:<br/>
        <input type="text" name="pharmacy" value="<?php echo htmlspecialchars($_POST["pharmacy"]);?>" /><br/>
        <br/><br/>
        <input type="submit" name = "submit" class="btn btn-info" value="Save" />
    </form>
</div>

</body>
</html>