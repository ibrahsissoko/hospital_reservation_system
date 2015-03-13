<?php

    require("config.php");
    require("MailFiles/PHPMailerAutoload.php");

    $noEmail = $success = "";
    
    if(!empty($_POST)) {
        // Set the email to the entered value. 
        $email = $_POST['email'];
        
        // Check if the email is in the database.
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
        if($stmt->rowCount() == 0){
            $noEmail = "This email is not recognized.";
        }

        // If the email was found, generate a new password and send them an email.
        if (empty($noEmail)) {
            
            // Generate new salt and password
            $salt = dechex(mt_rand(0, 2147483647)) . dechex(mt_rand(0, 2147483647));
            $password = hash('sha256', md5(rand(0,2147483647)) . $salt);

            for($round = 0; $round < 65536; $round++) {
                $password = hash('sha256', $password . $salt);
            }
            
            // Start writing the email.
            $mail = new PHPMailer();
            $mail->isSMTP();                  
            $mail->Host = 'smtp.mailgun.org'; 
            $mail->SMTPAuth = true;                               
            $mail->Username = 'postmaster@sandboxb958ed499fee4346ba3efcec39208a74.mailgun.org';
            $mail->Password = 'f285bbdde02a408823b9283cdd8d6958';                           
            $mail->From = 'postmaster@sandboxb958ed499fee4346ba3efcec39208a74.mailgun.org';
            $mail->FromName = 'No-reply Wal Consulting';
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->WordWrap = 70;
            $mail->Subject = "Password Retrieval";
            $mail->Body    = 'Hello!<br/><br/>'
                    . 'You recently requested a password retrieval.<br/><br/>'
                    . 'Here is a new password use it to login:'. $password
                    . '<br/><br/>Thank you,<br/>Wal Consulting';
            if(!$mail->send()) {
                $registrationFailure = "Verification email could not be sent. " . $mail->ErrorInfo;
            } else {
            $success = "An email has been sent to the address that you provided. "
                    . "Use the password included in the email to log in.";
            }
            // Update the users table.
            $query = "
                UPDATE users
                SET 
                    password = :password,
                    salt = :salt
                WHERE
                    email = :email
            ";

            $query_params = array(
                ':password' => $password,
                ':salt' => $salt,
                ':email' => $_POST['email']
            );

            try {
                $stmt = $db->prepare($query);
                $stmt->execute($query_params);
            } catch(PDOException $ex) {
                die("Failed to run query: " . $ex->getMessage());
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
    <h1>Password Retrieval</h1> <br />
    <form action="forgot_password.php" method="post">
        <label>Email:</label>
        <input type="text" name="email" value="<?php echo htmlspecialchars($_POST['email'])?>" />
        <span class="error"><?php echo $noEmail;?></span>
        <input type="submit" class="btn btn-info" value="Retrieve Password" /><br/><br/>
        <span class = "success"><?php echo $success;?></span>
    </form>
</div>

</body>
</html>
