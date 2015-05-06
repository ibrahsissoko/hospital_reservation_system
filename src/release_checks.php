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
                    released_by_admin = 0
               ";
    try {
        $stmt = $db->prepare($query);
        $stmt->execute();
    } catch(PDOException $ex) {
        die("Failed to run query: " . $ex->getMessage());
    }
    if ($stmt->rowCount() > 0) {
        echo "Click the 'Release' button to have the doctor/nurse receive their payment.<br/><br/>";
        echo '<table border="1" style="width:100%">';
        echo '<tr><td>Doctor/Nurse</td><td>Last Paid</td><td>Total Payment Due</td><td>Receipt</td><td>Release</td></tr>';
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $query2 = "
                        SELECT *
                        FROM users
                        WHERE
                            id = :id
                       ";
            $query_params2 = array(
                ':id' => $row["doctor_id"]
            );
            try {
                $stmt2 = $db->prepare($query2);
                $stmt2->execute($query_params2);
            } catch(PDOException $ex) {
                die("Failed to run query: " . $ex->getMessage());
            }
            $doctorInfo = $stmt2->fetch();
            echo '<tr><td>' . $doctorInfo['first_name'] . ' ' . $doctorInfo['last_name'] .'</td><td>' . $row['date'] . '</td><td>$' . $row['amount_due'] . '</td>';
            $link1 = "http://wal-engproject.rhcloud.com/src/check_receipt.php?id=" . $row['doctor_id'];
            $link2 = "http://wal-engproject.rhcloud.com/src/release_check_executor.php?id=" . $row['id'];
            echo '<td><a href="' . $link1 . '">Receipt</a></td><td><a href="' . $link2 . '" class="confirmation">Release</a></td></tr>';
        }
        echo '</table><br/><br/>';
    } else {
        echo "You have no checks to view right now.";
    }
    ?>

    <script type="text/javascript">
        var elems = document.getElementsByClassName('confirmation');
        var confirmIt = function (e) {
            if (!confirm('Are you sure?')) e.preventDefault();
        };
        for (var i = 0, l = elems.length; i < l; i++) {
            elems[i].addEventListener('click', confirmIt, false);
        }
    </script>
</div>

</body>
</html>