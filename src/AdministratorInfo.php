<?php
    require("Config.php");

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
            <a href="Home.php" class="brand">Hospital Management</a>
            <div class="nav-collapse">
                <ul class="nav pull-right">
                    <li><a href="Home.php">Home</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="container hero-unit">
    <h1>Administrator Info:</h1> <br />
    <form action="PatientInfo.php" method="post">
        <!-- TODO: add form here to enter the info. -->

        <input type="submit" class="btn btn-info" value="Save" />
    </form>
</div>

</body>
</html>
