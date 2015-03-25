<?php 
    $debug = false;
    $message = "";

    require("src/config.php");
    
    if(!empty($_POST)) {
      $email = $_POST['email'];

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
        if ($row) {
            $check_password = PasswordUtils::hashPassword($_POST['password'], $row['salt']);

            if($check_password == $row['password']) {
                if ($row['active_user'] == 0) {
                    $message = "You must activate your account first.";
                } else {
                    unset($row['salt']);
                    unset($row['password']);
                    $_SESSION['user'] = $row;

                    if ($row['info_added'] == 0) {
                        switch($row['user_type_id']) {
                            case 3: // nurse
                                header("Location: src/nurse_info.php");
                                die("Redirecting to: src/nurse_info.php");
                                break;
                            case 2: // doctor
                                header("Location: src/doctor_info.php");
                                die("Redirecting to: src/doctor_info.php");
                                break;
                            case 4: // admin
                                header("Location: src/administrator_info.php");
                                die("Redirecting to: src/administrator_info.php");
                                break;
                            default:
                                header("Location: src/patient_info.php");
                                die("Redirecting to: src/patient_info.php");
                                break;
                        }
                    } else {
                        header("Location: src/home.php");
                        die("Redirecting to: home.php");
                    }
                }
            } else {
                $message = "Invalid Password.";
            }
        } else {
            $message = "The email address is not registered.";
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
    <script src="assets/bootstrap.min.js"></script>
    <link href="assets/bootstrap.min.css" rel="stylesheet" media="screen">
    <link href="assets/styles.css" rel="stylesheet" type="text/css">
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
      <a href="src/home.php" class="brand">Hospital Management</a>
      <div class="nav-collapse collapse">
        <ul class="nav pull-right">
          <li><a href="src/register.php">Register</a></li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="container hero-unit">
    <h1>Login</h1> <br />
    <form action="index.php" method="post">
        <label>Email:</label>
        <input type="text" name="email" value="<?php echo $email?>" />
        <label>Password:</label>
        <input type="password" name="password" value="" /><br/>
        <span class="error"><?php echo $message;?></span>
        <br/> 
        <input type="submit" class="btn btn-info" value="Login" />
    </form> 
    <a href="src/forgot_password.php">Forgot Password?</a>
</div>

</body>
</html>
