<?php

    include_once('../AutoLoader.php');
    AutoLoader::registerDirectory('../src/classes');

    require("config.php");

    if(empty($_SESSION['user'])) {
        header("Location: ../index.php");
        die("Redirecting to index.php");
    }
    
    $nurse = new NurseInfo();
    if ($nurse->validateInput($_POST)) {
        $nurse->saveInfo($_POST, $_SESSION, $db);
    }
?>

<!doctype html>
<html lang="en">
<head>
    <span class="error"><?php echo $nurse->error;?></span><br/>
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
    <h1>Nurse Info:</h1><br/>
    <span class="error"><?php echo $nurse->error;?></span><br/>
    <form action="nurse_info.php" method="post">
        First Name:<br/>
        <input type="text" name="first_name" value="<?php echo htmlspecialchars($_SESSION['user']['first_name']);?>" /><br/>
	Last Name:<br/>
        <input type="text" name="last_name" value="<?php echo htmlspecialchars($_SESSION['user']['last_name']);?>" /><br/>
        Sex:<br/>
        <input type="radio" name="sex" value="Female" <?php echo ($_SESSION['user']['sex'] == 'Female') ? 'checked="checked"' : ''; ?> /> Female<br/>
        <input type="radio" name="sex" value="Male" <?php echo ($_SESSION['user']['sex'] == 'Male') ? 'checked="checked"' : ''; ?> > Male<br/>
        Department:
        <select name="department_id">
            <?php

            $query = "
                SELECT department_id
                FROM users
                WHERE
                    id = :id
                ";
            $query_params = array(
                ':id' => $_SESSION['user']['id']
            );

            try {
                $stmt = $db->prepare($query);
                $result = $stmt->execute($query_params);
            } catch(PDOException $ex) {
                die("Failed to run query: " . $ex->getMessage());
            }

            $row = $stmt->fetch();
            $departmentId = $row['department_id'];

            $query = "
                SELECT *
                FROM department
            ";

            // execute the statement
            try {
                $stmt = $db->prepare($query);
                $result = $stmt->execute();

                $i = 0;

                // loop through, adding the options to the spinner
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    if ($row['id'] == $departmentId) {
                        echo "<option value=\"" . $row["id"] . "\" selected=\"selected\">" . $row["name"] . "</option>";
                    } else {
                        echo "<option value=\"" . $row["id"] . "\">" . $row["name"] . "</option>";
                    }

                    $i = $i + 1;
                }
            } catch(Exception $e) {

            }

            ?>
        </select>
        <br/>
        Years of Experience:<br/>
        <input type="text" name="years_of_experience" value="<?php echo htmlspecialchars($_SESSION['user']['years_of_experience']);?>" />
        <br/>
        Shift:
        <select name="shift_id">
            <?php

            $query = "
                SELECT shift_id
                FROM users
                WHERE
                    id = :id
                ";
            $query_params = array(
                ':id' => $_SESSION['user']['id']
            );

            try {
                $stmt = $db->prepare($query);
                $result = $stmt->execute($query_params);
            } catch(PDOException $ex) {
                die("Failed to run query: " . $ex->getMessage());
            }

            $row = $stmt->fetch();
            $shiftId = $row['shift_id'];

            $query = "
                SELECT *
                FROM shift
            ";

            // execute the statement
            try {
                $stmt = $db->prepare($query);
                $result = $stmt->execute();

                $i = 0;

                // loop through, adding the options to the spinner
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $text = $row['name'] . " (" . $row['start_time'] . ":00-" . $row['end_time'] . ":00)";
                    if ($row['id'] == $shiftId) {
                        echo "<option value=\"" . $row["id"] . "\" selected=\"selected\">" . $text . "</option>";
                    } else {
                        echo "<option value=\"" . $row["id"] . "\">" . $text . "</option>";
                    }

                    $i = $i + 1;
                }
            } catch(Exception $e) {

            }

            ?>
        </select><br/>
        Address:<br/>
        <input type="text" name="address" value="<?php echo htmlspecialchars($_SESSION['user']['address']);?>" />
        <br/>
        City:<br/>
        <input type="text" name="city" value="<?php echo htmlspecialchars($_SESSION['user']['city']);?>" />
        <br/>
        State:<br/>
        <input type="text" name="state" value="<?php echo htmlspecialchars($_SESSION['user']['state']);?>" />
        <br/>
        Zip:<br/>
        <input type="text" name="zip" value="<?php echo htmlspecialchars($_SESSION['user']['zip']);?>" pattern="[0-9]{5}"><br/>
        Phone:<br/>
        <input type="text" name="phone" value="<?php echo htmlspecialchars($_SESSION['user']['phone']);?>" pattern="[0-9]{10}"><br/>
        Challenge question:<br/>
        <select name="challenge_question_id">
            <?php
                $query = "
                    SELECT *
                    FROM challenge_question
                ";
                try {
                    $stmt = $db->prepare($query);
                    $result = $stmt->execute();
                    if (empty($_SESSION['user']['challenge_question_id'])) {
                        $i = 1;
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            if ($i == 1) {
                                echo "<option value=\"" . $row["id"] . "\" selected=\"selected\">" . $row["question"] . "</option>";
                                $i++;
                            } else {
                                echo "<option value=\"" . $row["id"] . "\">" . $row["question"] . "</option>";
                            }
                        }
                    } else {
                        $i = 1;
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            if ($i == $_SESSION['user']['challenge_question_id']) {
                                echo "<option value=\"" . $row["id"] . "\" selected=\"selected\">" . $row["question"] . "</option>";
                            } else {
                                echo "<option value=\"" . $row["id"] . "\">" . $row["question"] . "</option>";
                            }
                            $i++;
                        }
                    }
                } catch(Exception $e) {
                    die("Failed to gather challenge questions. " . $e->getMessage());
                }
            ?>
        </select><br/>
        <input type="password" name="challenge_question_answer" value="<?php echo htmlspecialchars($_SESSION['user']['challenge_question_answer'])?>" /><br/>
        <input type="submit" class="btn btn-info" value="Save" />
    </form>
</div>

</body>
</html>
