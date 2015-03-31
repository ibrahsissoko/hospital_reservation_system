<?php

    include_once('../AutoLoader.php');
    AutoLoader::registerDirectory('../src/classes');

    require("config.php");

    if(empty($_SESSION['user'])) {
        header("Location: ../index.php");
        die("Redirecting to index.php");
    }
    
    $patient = new PatientInfo();
    $patient->saveInfo($_POST, $_SESSION, $db);
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
    <link rel="stylesheet" href="http://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    <script src="http://code.jquery.com/jquery-1.10.2.js"></script>
    <script src="http://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
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
                    <li><a href="home.php">Home</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="container hero-unit">
    <h1>Patient Info:</h1> <br />
    <form action="patient_info.php" method="post">
		
        First Name:<br/>
        <input type="text" name="first_name" value="<?php echo htmlspecialchars($_SESSION['user']['first_name']);?>" /><br/>
        Last Name:<br/>
        <input type="text" name="last_name" value="<?php echo htmlspecialchars($_SESSION['user']['last_name']);?>" /><br/>
        Sex:<br/>
        <input type="radio" name="sex" value="Female" <?php echo ($_SESSION['user']['sex'] == 'Female') ? 'checked="checked"' : ''; ?> /> Female<br/>
        <input type="radio" name="sex" value="Male" <?php echo ($_SESSION['user']['sex'] == 'Male') ? 'checked="checked"' : ''; ?>> Male<br/>
        DOB(yyyymmdd):<br/>
        <input type="text" name = "dob" value = "<?php echo htmlspecialchars($_SESSION['user']['dob']);?>" pattern="(19|20)[0-9]{2}(0[1-9]|1[012])(0[1-9]|[12][0-9]|3[01])"><br/>
        Age:<br/>
        <input type="number" name="age" min="1" max="120" value="<?php echo htmlspecialchars($_SESSION['user']['age']);?>"><br>
        Marital Status:<br/>
        <input type="radio" name="marital_status" value="Single" <?php echo ($_SESSION['user']['marital_status'] == 'Single') ? 'checked="checked"' : ''; ?> /> Single<br/>
        <input type="radio" name="marital_status" value="Married" <?php echo ($_SESSION['user']['marital_status'] == 'Married') ? 'checked="checked"' : ''; ?> > Married<br/>
        <input type="radio" name="marital_status" value="In a relationship" <?php echo ($_SESSION['user']['marital_status'] == 'In a relationship') ? 'checked="checked"' : ''; ?> /> In a relationship<br/>
        <input type="radio" name="marital_status" value="Divorced" <?php echo ($_SESSION['user']['marital_status'] == 'Divorced') ? 'checked="checked"' : ''; ?> > Divorced<br/>
        <input type="radio" name="marital_status" value="Widowed" <?php echo ($_SESSION['user']['marital_status'] == 'Widowed') ? 'checked="checked"' : ''; ?> /> Widowed<br/>
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
        <input type="text" name="zip" value = "<?php echo htmlspecialchars($_SESSION['user']['zip']);?>" pattern="[0-9]{5}"><br/>
        Phone:<br/>
        <input type="text" name="phone" value = "<?php echo htmlspecialchars($_SESSION['user']['phone']);?>" pattern="[0-9]{10}"><br/>
        Insurance Provider:<br/>
        <input type="text" name="insurance_provider" value="<?php echo htmlspecialchars($_SESSION['user']['insurance_provider']);?>" />
        <br/>
        Insurance Beginning Date(yyyymmdd):<br/>
        <input type="text" name="insurance_begin" pattern="(19|20)[0-9]{2}(0[1-9]|1[0-2])(0[1-9]|[1-2][0-9]|3[01])" value="<?php echo $_SESSION['user']['insurance_begin'];?>"><br/>
        Insurance Ending Date(yyyymmdd):<br/>	
        <input type="text" name="insurance_end" pattern="(19|20)[0-9]{2}(0[1-9]|1[012])(0[1-9]|[12][0-9]|3[01])" value="<?php echo $_SESSION['user']['insurance_end']?>"><br/>		
        Allergies:<br/>
        <input type="text" name="allergies" value="<?php echo htmlspecialchars($_SESSION['user']['allergies']);?>" />
        <br/>
        Diseases:<br/>
        <input type="text" name="diseases" value="<?php echo htmlspecialchars($_SESSION['user']['diseases']);?>" />
        <br/>
        Previous Surgeries:<br/>
        <input type="text" name="previous_surgeries" value="<?php echo htmlspecialchars($_SESSION['user']['previous_surgeries']);?>" />
        <br/>
        Other Medical History:<br/>
        <textarea name="other_medical_history" value = "<?php echo htmlspecialchars($_SESSION['user']['other_medical_history']);?>" cols="40" rows="5"></textarea>
        <br/><br/>
        <input type="submit" name = "submit" class="btn btn-info" value="Save" />
    </form>
</div>

</body>
</html>
