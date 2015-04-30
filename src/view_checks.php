<?php

    include_once('../AutoLoader.php');
    AutoLoader::registerDirectory('../src/classes');

    require("config.php");
    
    if(empty($_SESSION['user'])) {
        header("Location: ../index.php");
        die("Redirecting to index.php");
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
                    <?php AccountDropdownBuilder::buildDropdown($db, $_SESSION) ?>
                    <li><a href="home.php">Home</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="container hero-unit">
    <h1>View Checks:</h1> <br/><br/>
    <?php
        $query = "
                SELECT *
                FROM payout
                WHERE
                    doctor_id = :doctor_id
                AND
                    released_by_admin = :released_by_admin
               ";

        $query_params = array(
            ':doctor_id' => $_SESSION['user']['id'],
            ':released_by_admin' => 1
        );
        try {
            $stmt = $db->prepare($query);
            $stmt->execute($query_params);
        } catch(PDOException $ex) {
            die("Failed to run query: " . $ex->getMessage());
        }
        if ($stmt->rowCount() > 0) {
            echo '<table border="1" style="width:100%">';
            echo '<tr><td>Date</td><td>Amount</td><td>Receipt</td></tr>';
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo '<tr><td>' . $row['date'] . '</td><td>$' . $row['amount_due'] . '</td>';
                $link = "http://wal-engproject.rhcloud.com/src/check_receipt.php?id=" . $row['doctor_id'];
                echo '<td><a href="' . $link . '">Receipt</a></td></tr>';
            }
            echo '</table><br/><br/>';
        } else {
            echo "You have no checks yet.";
        }
    ?>
</div>

</body>
</html>