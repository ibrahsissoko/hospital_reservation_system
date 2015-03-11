<?php

    require("config.php");

    $user_type = $_SESSION['user']['user_type_id'];

    if(!empty($_POST)) {

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
    <h1>Nurse Info:</h1> <br />
    <form action="nurse_info.php" method="post">
        <!-- TODO: add form here to enter the info. -->
		First Name:<br/>
        <input type="text" name="first_name" value="" />
		<br/>
		Last Name:<br/>
        <input type="text" name="last_name" value="" />
		<br/>
		Sex:<br/>
		<input type="radio" name="sex" value=""/> Female<br/>
		<input type="radio" name="sex" value=""> Male<br/>
		Department(ENT, Dentistry, etc.):<br/>
		<input type="text" name="department" value="" />
		<br/>
		Years Of Experience:<br/>
		<input type="text" name="years_of_experience" value="" />
		<br/>
		Shift:<br/>
		<input type="radio" name="shift" value=""/> Morning<br/>
		<input type="radio" name="shift" value=""> Regular<br/>
		<input type="radio" name="shift" value=""> Night<br/>
		Address:<br/>
		<input type="text" name="address" value="" />
		<br/>
		City:<br/>
		<input type="text" name="city" value="" />
		<br/>
		Zip:<br/>
		<input type="text" name="zip" pattern="[0-9]{5}"><br/>
		Phone:<br/>
		<input type="text" name="phone" pattern="[0-9]{10}"><br/>

        <input type="submit" class="btn btn-info" value="Save" />
    </form>
</div>

</body>
</html>
