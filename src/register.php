<?php
    require("classes/Register.class.php");
    require("config.php");

    // Initialize error messages to blank.
    $r = new Register();
    $r->initializeValues();

    if(!empty($_POST)) {

        // Ensure that the user fills out fields.
        if (empty($_POST['email'])) {
            $noEmail = "Please enter an email address.";
        }
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $incorrectEmail = "Invalid E-Mail Address.";
        }
        if (empty($_POST['password'])) {
            $noPassword = "Please enter a password.";
        }
        if (empty($_POST['confirmPassword'])) {
            $noConfirmPassword = "Please confirm your password.";
        }
        if ($_POST['password'] != $_POST['confirmPassword'] && $noPassword == ""
            && $noConfirmPassword == "") {
            $noPasswordMatch = "Passwords do not match.";
        }
        if ($_POST['user_type_id'] != 1 && empty($_POST['access_code'])) {
            $noAccessCode = "Enter an access code";
        } else if ($_POST['user_type_id'] != 1) {
            $query = "
                SELECT *
                FROM user_types
                WHERE 
                  id = :type_id
            ";

            $query_params = array(
                ':type_id' => $_POST['user_type_id']
            );

            try {
                $stmt = $db->prepare($query);
                $result = $stmt->execute($query_params);
            } catch(PDOException $ex) {
                die("Failed to run query: " . $ex->getMessage());
            }
            $row = $stmt->fetch();
            if($row && $row['access_code'] != $_POST['access_code']) {
                $noAccessCode = "Invalid access code";
            }
        }

        // Only further process if there were no errors.
        if (empty($noEmail) && empty($incorrectEmail) && empty($noPassword) &&
                empty($noConfirmPassword) && empty($noPasswordMatch) && empty($noAccessCode)) {
            
            // Set the email to the entered value. 
            $email = $_POST['email'];
            
            // Generate a hash.
            $hash = md5(rand(0,2147483647));
            
            // Hash the password a ton so that it can't be un-done
            for($round = 0; $round < 65536; $round++){ 
                $password = hash('sha256', $password . $salt); 
            } 

            // check if the email exists

            $query = "
                SELECT *
                FROM users
                WHERE
                    email = :email
            ";

            $query_params = array(
                ':email' => $email
            );

            try {
                $stmt = $db->prepare($query);
                $result = $stmt->execute($query_params);
            } catch(PDOException $ex) {
                die("Failed to run query: " . $ex->getMessage());
            }
            $row = $stmt->fetch();
            if($stmt->rowCount() > 0){
                $registeredEmail = "This email address is already registered.";
            }

            // If the email is not registered yet, add it to the database.
            if (empty($registeredEmail)) {
                $query = " 
                    INSERT INTO users ( 
                        email,
                        password, 
                        salt,
                        user_type_id
                    ) VALUES (
                        :email,
                        :password,
                        :salt,
                        :user_type_id
                    )
                ";

                // Security measures
                $salt = dechex(mt_rand(0, 2147483647)) . dechex(mt_rand(0, 2147483647));
                $password = hash('sha256', $_POST['password'] . $salt);

                for($round = 0; $round < 65536; $round++) {
                    $password = hash('sha256', $password . $salt);
                }

                $query_params = array(
                    ':email' => $_POST['email'],
                    ':password' => $password,
                    ':salt' => $salt,
                    ':user_type_id' => $_POST['user_type_id']
                );

                try {
                    $stmt = $db->prepare($query);
                    $result = $stmt->execute($query_params);
                } catch(PDOException $ex) {
                    die("Failed to run query: " . $ex->getMessage());
                }

                // Redirect to login.
                // Email user with instructions of how to verify account
                $to = "william-tollefson@uiowa.edu";
                $subject = "Account Confirmation Request";
                $message = "";
                $headers = "From: wal-engproject@noreply.com";
                if (mail($to,$subject,$txt,$headers)) {
                    $registrationSuccess = "Confirmation Email Sent Successfully";
                } else {
                    die("An error occured sending the email verification of your account.");
                }
                
                header("Location: ../index.php");
                die("Redirecting to index.php");
            }
        }
    } 
?>

<!doctype html>
<html lang="en">
<head>
    <style>.error {color: #FF0000;}</style>
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
        <span class="error"><?php echo $noAccessCode; ?></span>
        <label>Email:</label> 
        <input type="text" name="email" value="<?php echo htmlspecialchars($_POST['email'])?>" />
        <span class="error"><?php echo $noEmail; echo $incorrectEmail; echo $registeredEmail;?></span>
        <label>Password:</label> 
        <input type="password" name="password" value="<?php echo htmlspecialchars($_POST['password'])?>" />
        <span class="error"><?php echo $noPassword;?></span>
        <label>Confirm Password:</label>
        <input type="password" name="confirmPassword" value="<?php echo htmlspecialchars($_POST['confirmPassword'])?>" />
        <span class="error"><?php echo $noConfirmPassword;?></span><br/>
        <span class="error"><?php echo $noPasswordMatch;?></span><br/>
        <input type="submit" class="btn btn-info" value="Register" /><br/><br/>
        <label><?php echo $registrationSuccess;?><label/>
    </form>
</div>

</body>
</html>
