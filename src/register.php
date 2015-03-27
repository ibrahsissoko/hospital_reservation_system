<?php

    include_once('../AutoLoader.php');
    AutoLoader::registerDirectory('../src/classes');

    require("config.php");
    require("MailFiles/PHPMailerAutoload.php");

    // Initialize error messages to blank.
    $r = new Register();

    if(!empty($_POST)) {
        // Ensure that the user fills out fields.
        if ($r->checkNoFormErrors($_POST, $db)) {
            $hash = md5(rand(0,2147483647));
            // check if the email exists
            $r->checkEmailExists($_POST['email'], $db);

            // If the email is not registered yet, send them a confirmation email
            // and add it to the database.
            if (empty($r->registeredEmail)) {
                $link = "http://wal-engproject.rhcloud.com/src/verify.php?email=" . $_POST['email'] . "&hash=" . $hash;
                if(!$r->sendRegistrationEmail($_POST['email'], $link)) {
                    $r->registrationFailure = "Verification email could not be sent.";
                } else {
                    $r->registrationSuccess = "A confirmation email has been sent to the email address that you provided";
                    $r->saveRegistration($_POST, $hash, $db);
                }
            }
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
          <li><a href="../index.php">Login</a></li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="container hero-unit">
    <h1>Register</h1> <br />
    <form action="register.php" method="post">

        <select name="user_type_id">
            <?php

            /**
             * This code is used to fill the spinner from the database user_types.
             * This database holds the different types of users, including: patient, doctor, nurse, administrator
             */

            $query = "
                SELECT *
                FROM user_types
            ";

            // execute the statement
            try {
                $stmt = $db->prepare($query);
                $result = $stmt->execute();

                $i = 0;

                // loop through, adding the options to the spinner
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    if ($i == 0) {
                        echo "<option value=\"" . $row["id"] . "\" selected=\"selected\">" . $row["type_name"] . "</option>";
                    } else {
                        echo "<option value=\"" . $row["id"] . "\">" . $row["type_name"] . "</option>";
                    }

                    $i = $i + 1;
                }
            } catch(Exception $e) {

            }

            ?>
        </select>
        <label>Access Code (not applicable for patients):</label>
        <input type="text" name="access_code" value="<?php echo htmlspecialchars($_POST['access_code'])?>" />
        <span class="error"><?php echo $r->noAccessCode; ?></span>
        <label>Email:</label> 
        <input type="text" name="email" value="<?php echo htmlspecialchars($_POST['email'])?>" />
        <span class="error"> * <?php echo $r->noEmail; echo $r->incorrectEmail; echo $r->registeredEmail;?></span>
        <label>Password:</label> 
        <input type="password" name="password" value="" />
        <span class="error"> * <?php echo $r->noPassword; echo $r->badPassword; ?></span>
        <label>Confirm Password:</label>
        <input type="password" name="confirmPassword" value="" />
        <span class="error"> * <?php echo $r->noConfirmPassword;?></span><br/>
        <span class="error"><?php echo $r->noPasswordMatch;?></span><br/>
        <span class="success"><?php echo $r->registrationSuccess;?></span>
        <span class="error"><?php echo $r->registrationFailure;?></span>
        <input type="submit" class="btn btn-info" value="Register" /><br/><br/>
        <p>Password must have at least one number and letter, and must be 20 characters long or fewer.</p> 
    </form>
</div>

</body>
</html>
