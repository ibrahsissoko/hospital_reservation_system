<?php

    include_once('../AutoLoader.php');
    AutoLoader::registerDirectory('../src/classes');

    require("config.php");
    
    if(empty($_SESSION['user'])) {
        header("Location: ../index.php");
        die("Redirecting to index.php");
    } else if (isset($_GET['submitButton']) && $_GET['amount_paying'] != "Pay") {
        $query = "
                UPDATE diagnosis
                SET
                    amount_due = :newTotal
                WHERE
                    id = :id
               ";
        $query_params = array(
            ':newTotal' => intval($_GET['current_bill']) - intval($_GET['amount_paying']),
            ':id' => $_GET['id']
        );
        try {
            $stmt = $db->prepare($query);
            $stmt->execute($query_params);
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
                    <?php AccountDropdownBuilder::buildDropdown($db, $_SESSION) ?>
                    <li><a href="home.php">Home</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="container hero-unit">
    <h1>Pay Bill:</h1> <br/><br/>
    <form action="pay_bill.php" method="get">
        <?php
            $query = "
                    SELECT *
                    FROM diagnosis
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
            $billInfo = $stmt->fetch();
            $currentTotal = intval($billInfo['amount_due']);
            if ($currentTotal == 0) {
                echo "Thank you for paying off this bill!";
                $query = "
                        DELETE
                        FROM diagnosis
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
            } else {
                echo '<input type="hidden" name="id" value="' . $_GET['id'] . '" />';
                echo 'Enter how much you would like to pay:<br/>';
                echo '<select name="amount_paying">';            
                echo '<option value="Pay" selected="selected" >Pay</option>';
                while($currentTotal > 100) {
                    echo "<option value=$currentTotal>$$currentTotal</option>";
                    $currentTotal -= 100;
                }
                echo "<option value=$currentTotal>$$currentTotal</option></select><br/>";
                echo 'Current Bill:<br/>';
                echo '<input type="text" name="current_bill" value="' . $billInfo['amount_due'] . '" readonly="readonly" /><br/><br/>';
                echo '<input type="submit" name="submitButton" class="btn btn-info" value="Submit"/>';
            }
        ?>
    </form>
</div>

</body>
</html>