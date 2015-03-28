<?php

include_once('../AutoLoader.php');
AutoLoader::registerDirectory('../src/classes');

require("config.php");

if(empty($_SESSION['user'])) {
    header("Location: ../index.php");
    die("Redirecting to index.php");
} else {
    $query = "
        SELECT *
        FROM users
        WHERE
          id = :id
    ";
    $query_params = array(
        ':id' => $_GET['id']
    );

    try {
        $stmt = $db->prepare($query);
        $result = $stmt->execute($query_params);

        $row = $stmt->fetch();
        if ($row) {
            $userProfile = $row;
        }

    } catch(PDOException $ex) {
        die("Failed to run query: " . $ex->getMessage());
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
                    <li><a href="my_account.php">Account</a></li>
                    <li><a href="logout.php">Log Out</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="container hero-unit">
    <h1><?php echo $userProfile['first_name'] . " " . $userProfile['last_name'] ?>:</h1> <br/><br/>
    <p>No information available yet.</p>

</div>

</body>
</html>
