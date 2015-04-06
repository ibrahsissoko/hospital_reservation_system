<?php

    include_once('../AutoLoader.php');
    AutoLoader::registerDirectory('../src/classes');

    require("config.php");

    if(empty($_SESSION['user'])) {
        header("Location: ../index.php");
        die("Redirecting to index.php");
    } else if (!empty($_POST)) {
        // Reset the session variables.
        $_SESSION['user']['appointment_confirm_email'] = $_POST['appointment_confirm_email'];
        $_SESSION['user']['appointment_deleted_email'] = $_POST['appointment_deleted_email'];
        
        $query = "
            UPDATE users
            SET
                appointment_confirm_email = :appointment_confirm_email,
                appointment_deleted_email = :appointment_deleted_email
            WHERE
                id = :id
        ";

        $query_params = array(
            ":appointment_confirm_email" => $_POST['appointment_confirm_email'],
            ":appointment_deleted_email" => $_POST['appointment_deleted_email'],
            ":id" => $_SESSION['user']['id']
        );

        try {
            $stmt = $db->prepare($query);
            $result = $stmt->execute($query_params);
        } catch(PDOException $ex) {
            die("Failed to run query: " . $ex->getMessage());
        }
        if($result) {
            $success = "Preferences successfully updated.";
        } else {
            $error = "An error occurred. Please try again soon.";
        }
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
    <script>$(function() {$( "#datepicker1, #datepicker2, #datepicker3" ).datepicker();});</script>
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
    <h1>Email Notifications:</h1> <br />
    <p>Click yes or no for whether you would like to receive email notification for the
        following situations.</p>
    <form action="email_preferences.php" method="post">	
        Appointment Confirmation:&nbsp;
        <input type="radio" name="appointment_confirm_email" value="Yes" <?php echo ($_SESSION['user']['appointment_confirm_email'] == 'Yes' || $_SESSION['user']['appointment_confirm_email'] == NULL) ? 'checked="checked"' : ''; ?> />Yes
        <input type="radio" name="appointment_confirm_email" value="No" <?php echo ($_SESSION['user']['appointment_confirm_email'] == 'No') ? 'checked="checked"' : ''; ?> /> No<br/><br/>
        Deleted Appointment:&nbsp;
        <input type="radio" name="appointment_deleted_email" value="Yes" <?php echo ($_SESSION['user']['appointment_deleted_email'] == 'Yes'  || $_SESSION['user']['appointment_deleted_email'] == NULL) ? 'checked="checked"' : ''; ?> />Yes
        <input type="radio" name="appointment_deleted_email" value="No" <?php echo ($_SESSION['user']['appointment_deleted_email'] == 'No') ? 'checked="checked"' : ''; ?> /> No<br/><br/>
        <input type="submit" name = "submit" class="btn btn-info" value="Submit" /><br/><br/>
        Diagnosis Confirmation:&nbsp;
        <input type="radio" name="diagnosis_confirm_email" value="Yes" <?php echo ($_SESSION['user']['diagnosis_confirm_email'] == 'Yes' || $_SESSION['user']['diagnosis_confirm_email'] == NULL) ? 'checked="checked"' : ''; ?> />Yes
        <input type="radio" name="diagnosis_confirm_email" value="No" <?php echo ($_SESSION['user']['diagnosis_confirm_email'] == 'No') ? 'checked="checked"' : ''; ?> /> No<br/><br/>
    </form>
    <span class="success"><?php echo $success;?></span>
    <span class="error"><?php echo $error;?></span>
</div>

</body>
</html>
