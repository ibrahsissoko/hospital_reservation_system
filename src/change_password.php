<?php

    include_once('../AutoLoader.php');
    AutoLoader::registerDirectory('../src/classes');

    require("config.php");

    $changer = new ChangePassword();
    $user = $_SESSION['user'];

    if(empty($_SESSION['user'])) {
        header("Location: ../index.php");
        die("Redirecting to index.php");
    } else if (!empty($_POST) && $changer->checkFieldsFilled($_POST)) {
        $query = "
                    SELECT *
                    FROM users
                    WHERE
                        email = :email
                ";
        $query_params = array(
            ':email' => $user['email']
        );

        try {
            $stmt = $db->prepare($query);
            $result = $stmt->execute($query_params);
        } catch(PDOException $ex) {
            die("Failed to run query: " . $ex->getMessage());
        }

        $row = $stmt->fetch();
        if ($row) {
            $check_password = hash('sha256', $_POST['current_password'] . $row['salt']);
            for ($round = 0; $round < 65536; $round++) {
                $check_password = hash('sha256', $check_password . $row['salt']);
            }

            if ($check_password == $row['password']) {
                $changer->makePasswordChange($db, $_POST['new_password'], $row['salt'], $row['id']);
            }
        }
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
                    <li><a href="logout.php">Log Out</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="container hero-unit">
    <h1>Change Password</h1> <br />
    <form action="change_password.php" method="post">
        <label>Current Password:</label>
        <input type="text" name="current_password" value="<?php echo $_POST['current_password']?>" />
        <label>New Password:</label>
        <input type="password" name="new_password" value="" /><br/>
        <label>Confirm New Password:</label>
        <input type="password" name="confirm_password" value="" /><br/>
        <span class="error"><?php echo $changer->errorMessage;?></span><br/>
        <input type="submit" class="btn btn-info" value="Change Password" />
    </form>
</div>

</body>
</html>
