<?php

include_once('../AutoLoader.php');
AutoLoader::registerDirectory('../src/classes');

require("config.php");

if(empty($_SESSION['user'])) {
    header("Location: ../index.php");
    die("Redirecting to index.php");
} else {
    switch($_SESSION['user']['user_type_id']) {
        case 3: // nurse
            $type_id = "nurse";
            break;
        case 2: // doctor
            $userType = "doctor";
            break;
        case 4: // admin
            $userType = "administrator";
            break;
        default:
            $userType = "patient";
            break;
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
                    <?php AccountDropdownBuilder::buildDropdown($_SESSION) ?>
                    <li><a href="logout.php">Log Out</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="container hero-unit">

    <form action="advanced_doctor_search.php" method="GET" >
        <?php
        if (isset($_GET['search']) && $_GET['search'] != "") {
            echo "<input type=\"text\" name=\"search\" placeholder=\"" . $_GET['search'] . "\" >";
        } else {
            echo "<input type=\"text\" name=\"search\" placeholder=\"" . "Doctor's Name" . "\" >";
        }
        ?>

        <select name="department_id">
            <?php

            $query = "
                SELECT *
                FROM department
            ";

            // execute the statement
            try {
                $stmt = $db->prepare($query);
                $result = $stmt->execute();

                $i = 1;

                // loop through, adding the options to the spinner
                echo "<option value=\"Department\" selected=\"selected\">Department</option>";;
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    if ($row['id'] == $_GET['department_id']) {
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
        <select name="sex">
            <option value="Gender" selected="selected">Gender</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
        </select>
        <input type="submit" class="btn btn-info" value="Search" />
    </form>

    <ul>
        <?php
        $queryVals = array();
        if(!empty($_GET['department_id']) && $_GET['department_id'] != "Department") {
            array_push($queryVals, "department_id");
        }
        if(!empty($_GET['sex']) && $_GET['sex'] != "Gender") {
            array_push($queryVals, "sex");
        }
        array_push($queryVals, "user_type_id");
        
        $query = "SELECT * FROM users WEHRE
                (first_name LIKE '%" . $_GET['search'] . "%' OR
                    last_name LIKE '%" . $_GET['search'] . "%' OR
                    CONCAT(first_name, ' ', last_name) LIKE '%" . $_GET['search'] . "%' OR
                    CONCAT(last_name, ' ', first_name) LIKE '%" . $_GET['search'] . "%' OR
                    email LIKE '%" . $_GET['search'] . "%')";
        
        $query_params = array();
        foreach($queryVals as $param) {
            $query .= " AND (" . $param . "= :" . $param . ")";
            array_push($query_params, ": " . $param);
            if ($param != "user_type_id") {
                $query_params[":" . $param] = $_GET[$param];
            } else {
                $query_params[":" . $param] = '2';
            }
        }

        try {
            $stmt = $db->prepare($query);
            $result = $stmt->execute($query_params);

            $i = 0;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                $query1 = "
                SELECT *
                FROM user_types
                WHERE
                  id = :type_id
                ";

                $query_params1 = array(
                    ':type_id' => $row['user_type_id']
                );
                try {
                    $stmt1 = $db->prepare($query1);
                    $result1 = $stmt1->execute($query_params1);
                    $type = $stmt1->fetch(PDO::FETCH_ASSOC);
                    $name = $row['first_name'] . " " . $row['last_name'] . " (" . $type['type_name'] . ")";
                } catch(Exception $ex) {
                    $name = $row['first_name'] . " " . $row['last_name'];
                }

                $link = "http://wal-engproject.rhcloud.com/src/user_page.php?id=" . $row['id'];
                echo "<li>" . "<a href=\"". $link . "\">" . $name . "</a>" . "</li>";
                $i = $i + 1;
            }

            if ($i == 0 && isset($_GET['search'])) {
                echo "<li>" . "No search results!" . "</li>";
            }
        } catch(PDOException $ex) {
            die("Failed to run query: " . $ex->getMessage() . " Query: " . $query . " Query Params count: " . count($query_params));
        }

        ?>
    </ul>

</div>

</body>
</html>
