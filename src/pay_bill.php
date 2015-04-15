<?php

    include_once('../AutoLoader.php');
    AutoLoader::registerDirectory('../src/classes');

    require("config.php");
    
    if(empty($_SESSION['user'])) {
        header("Location: ../index.php");
        die("Redirecting to index.php");
    } else if (isset($_GET['submitButton']) && $_GET['amount_paying'] != "Pay") {
        $query = "
                UPDATE users
                SET
                    amount_due = :newTotal
                WHERE
                    id = :id
               ";
        $query_params = array(
            ':newTotal' => intval($_GET['amount_due']) - intval($_GET['amount_paying']),
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
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($_GET['id']);?>" />
        Enter how much you would like to pay:
        <select name="amount_paying">
            <option value="Pay" selected="selected" >Pay</option> 
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
                while($currentTotal > 100) {
                    echo "<option value=$$currentTotal>$$currentTotal</option>";
                    $currentTotal -= 100;
                }
                echo "<option value=$$currentTotal>$$currentTotal</option>";
            ?>
        </select><br/>
        Current Bill:
        <input type="text" name="current_bill" value="<?php echo $billInfo['amount_due'];?>" readonly="readonly" /><br/><br/>
        <input type="submit" name="submitButton" value="Submit"/>
    </form>
</div>

</body>
</html>