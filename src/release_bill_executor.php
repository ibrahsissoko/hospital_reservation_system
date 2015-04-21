<?php

include_once('../AutoLoader.php');
AutoLoader::registerDirectory('../src/classes');

require("config.php");

?>

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
                    <?php AccountDropdownBuilder::buildDropdown($db, $_SESSION) ?>
                    <li><a href="home.php">Home</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="container hero-unit">

    <?php

        if (isset($_GET['id'])) {
            $query = "
                UPDATE diagnosis
                SET
                    released_by_admin = 1
                WHERE
                    id = :id
                ";

            $query_params = array(
                ':id' => $_GET['id']
            );
            try {
                $stmt = $db->prepare($query);
                $stmt->execute($query_params);
            } catch(PDOException $ex) {
                die("Failed to run query: " . $ex->getMessage());
            }


            // todo: send the email of the bill notification here instead of after the doctor submits the diagnosis
        }


        echo "Released bill to patient.<br/><br/>";
        echo "<a href=\"release_bills.php\">Back to bills page</a>";

    ?>
</div>

</body>
</html>