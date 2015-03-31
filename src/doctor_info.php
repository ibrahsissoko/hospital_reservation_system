<?php

    include_once('../AutoLoader.php');
    AutoLoader::registerDirectory('../src/classes');

    require("config.php");

    if(empty($_SESSION['user'])) {
        header("Location: ../index.php");
        die("Redirecting to index.php");
    }
    
    $doctor = new DoctorInfo();
    $doctor->saveInfo($_POST, $_SESSION, $db);
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
    <h1>Doctor Info:</h1> <br />
    <form action="doctor_info.php" method="post">
        First Name:<br/>
        <input type="text" name="first_name" value="<?php echo htmlspecialchars($_SESSION['user']['first_name']);?>" /><br/>
        Last Name:<br/>
        <input type="text" name="last_name" value="<?php echo htmlspecialchars($_SESSION['user']['last_name']);?>" /><br/>
        Sex:<br/>
        <input type="radio" name="sex" value="Female" <?php echo ($_SESSION['user']['sex'] == 'Female') ? 'checked="checked"' : ''; ?>/> Female<br/>
        <input type="radio" name="sex" value="Male" <?php echo ($_SESSION['user']['sex'] == 'Male') ? 'checked="checked"' : ''; ?> > Male<br/>
        Degree(MBBS, MD, etc.):<br/>
        <input type="text" name="degree" value="<?php echo htmlspecialchars($_SESSION['user']['degree']);?>" />
        <br/>
        Specialization(Dentist, Epidemiologist, etc.):<br/>
        <input type="text" name="specialization" value="<?php echo htmlspecialchars($_SESSION['user']['specialization']);?>" />
        <br/>
        Years Of Experience:<br/>
        <input type="text" name="years_of_experience" value="<?php echo htmlspecialchars($_SESSION['user']['years_of_experience']);?>" />
        <br/>
        Shift:<br/>
        <input type="radio" name="shift" value="Morning" <?php echo ($_SESSION['user']['shift'] == 'Morning') ? 'checked="checked"' : ''; ?> /> Morning<br/>
        <input type="radio" name="shift" value="Regular" <?php echo ($_SESSION['user']['shift'] == 'Regular') ? 'checked="checked"' : ''; ?> > Regular<br/>
        <input type="radio" name="shift" value="Night" <?php echo ($_SESSION['user']['shift'] == 'Night') ? 'checked="checked"' : ''; ?> > Night<br/>
        Address:<br/>
        <input type="text" name="address" value="<?php echo htmlspecialchars($_SESSION['user']['address']);?>" />
        <br/>
        City:<br/>
        <input type="text" name="city" value="<?php echo htmlspecialchars($_SESSION['user']['city']);?>" />
        <br/>
        State:<br/>
        <input type="text" name="state" value="<?php echo htmlspecialchars($_SESSION['user']['state']);?>" />
        <br/>
        Zip:<br/>
        <input type="text" name="zip" value="<?php echo htmlspecialchars($_SESSION['user']['zip']);?>" pattern="[0-9]{5}"><br/>
        Phone:<br/>
        <input type="text" name="phone" value="<?php echo htmlspecialchars($_SESSION['user']['phone']);?>" pattern="[0-9]{10}"><br/>

        <input type="submit" class="btn btn-info" value="Save" />
    </form>
</div>

</body>
</html>
