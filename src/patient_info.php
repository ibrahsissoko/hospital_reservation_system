<?php

    include_once('../AutoLoader.php');
    AutoLoader::registerDirectory('../src/classes');

    require("config.php");

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
        <!-- TODO: add form here to enter the info. -->	
		
        First Name:<br/>
        <input type="text" name="first_name" value="" />
		<br/>
		Last Name:<br/>
        <input type="text" name="last_name" value="" />
		<br/>
		Sex:<br/>
		<input type="radio" name="sex" value="Female"/> Female<br/>
		<input type="radio" name="sex" value="Male"> Male<br/>
		DOB(yyyymmdd):<br/>
		<input type="text" name = "dob" pattern="(19|20)[0-9]{2}(0[1-9]|1[012])(0[1-9]|[12][0-9]|3[01])"><br/>
		Age:<br/>
		<input type="number" name="age" min="1" max="120" value=""><br>
		Marital Status:<br/>
		<input type="radio" name="marital_status" value="Single"/> Single<br/>
		<input type="radio" name="marital_status" value="Married"> Married<br/>
		<input type="radio" name="marital_status" value="In a relationship"/> In a relationship<br/>
		<input type="radio" name="marital_status" value="Divorced"> Divorced<br/>
		<input type="radio" name="marital_status" value="Widowed"/> Widowed<br/>
		Address:<br/>
		<input type="text" name="address" value="" />
		<br/>
		City:<br/>
		<input type="text" name="city" value="" />
		<br/>
                State:<br/>
                <input type="text" name="state" value="" />
                <br/>
		Zip:<br/>
		<input type="text" name="zip" pattern="[0-9]{5}"><br/>
		Phone:<br/>
		<input type="text" name="phone" pattern="[0-9]{10}"><br/>
		Insurance Provider:<br/>
		<input type="text" name="insurance_provider" value="" />
		<br/>
		Insurance Beginning Date(yyyymmdd):<br/>
		<input type="text" name="insurance_begin"pattern="(19|20)[0-9]{2}(0[1-9]|1[0-2])(0[1-9]|[1-2][0-9]|3[01])"><br/>
		Insurance Ending Date(yyyymmdd):<br/>	
		<input type="text" name="insurance_end" pattern="(19|20)[0-9]{2}(0[1-9]|1[012])(0[1-9]|[12][0-9]|3[01])"><br/>		
		Allergies:<br/>
		<input type="text" name="allergies" value="" />
		<br/>
		Diseases:<br/>
		<input type="text" name="diseases" value="" />
		<br/>
		Previous Surgeries:<br/>
		<input type="text" name="previous_surgeries" value="" />
		<br/>
		Other Medical History:<br/>
		<textarea name="other_medical_history" cols="40" rows="5"></textarea>
		<br/><br/>
        <input type="submit" name = "submit" class="btn btn-info" value="Save" />
    </form>
</div>

</body>
</html>
