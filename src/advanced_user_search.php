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
    <h1>Advanced Search</h1><br/>
    <form action="advanced_user_search.php" method="GET" >
        <?php
        if (isset($_GET['search']) && $_GET['search'] != "") {
            echo "<input type=\"text\" name=\"search\" placeholder=\"" . $_GET['search'] . "\" ><br/>";
        } else {
            echo "<input type=\"text\" name=\"search\" placeholder=\"" . "User's Name" . "\" ><br/>";
        }
        ?>
        
        <select name="user_type_id">
            <?php

            $query = "
                SELECT *
                FROM user_types
            ";

            // execute the statement
            try {
                $stmt = $db->prepare($query);
                $result = $stmt->execute();

                // loop through, adding the options to the spinner
                echo "<option value=\"All Users\" selected=\"selected\">All Users</option>";
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    if ($row['id'] == $_GET['user_type_id']) {
                        echo "<option value=\"" . $row["id"] . "\" selected=\"selected\">" . $row["type_name"] . "</option>";
                    } else {
                        echo "<option value=\"" . $row["id"] . "\">" . $row["type_name"] . "</option>";
                    }
                }
            } catch(Exception $e) {

            }

            ?>
        </select><br/>
        <select name="sex">
            <option value="Gender" <?php if(empty($_GET['sex'])||$_GET['sex']=="Gender"){echo 'selected=selected';}?> >Gender</option>
            <option value="Male" <?php if($_GET['sex']=="Male"){echo 'selected=selected';}?>>Male</option>
            <option value="Female" <?php if($_GET['sex']=="Female"){echo 'selected=selected';}?>>Female</option>
        </select><br/>
        <select name="age">
            <option value="Age" <?php if(empty($_GET['age'])||$_GET['age']=="Age"){echo 'selected=selected';}?> >Age</option>
            <option value="1" <?php if($_GET['age']=="1"){echo 'selected=selected';}?> >&lt;30</option>
            <option value="2" <?php if($_GET['age']=="2"){echo 'selected=selected';}?> >30-39</option>
            <option value="3" <?php if($_GET['age']=="3"){echo 'selected=selected';}?> >40-49</option>
            <option value="4" <?php if($_GET['age']=="4"){echo 'selected=selected';}?> >50-59</option>
            <option value="5" <?php if($_GET['age']=="5"){echo 'selected=selected';}?> >&gt;59</option>
        </select>
        <input type="submit" class="btn btn-info" value="Search" />
    </form>

    <ul>
        <?php
        
        $queryVals = array();
        if(!empty($_GET['user_type_id']) && $_GET['user_type_id'] != "All Users") {
            array_push($queryVals, "user_type_id");
        }
        if(!empty($_GET['sex']) && $_GET['sex'] != "Gender") {
            array_push($queryVals, "sex");
        }
        if(!empty($_GET['age']) && $_GET['age'] != "Age") {
            array_push($queryVals, "age");
        }
        
        $query = "
        SELECT *
        FROM users
        WHERE (first_name LIKE '%" . $_GET['search'] . "%' OR
                last_name LIKE '%" . $_GET['search'] . "%' OR
                CONCAT(first_name, ' ', last_name) LIKE '%" . $_GET['search'] . "%' OR
                CONCAT(last_name, ' ', first_name) LIKE '%" . $_GET['search'] . "%' OR
                email LIKE '%" . $_GET['search'] . "%')";
        
        $query_params = array();
        
        foreach($queryVals as $param) {
            if ($param == "age") {
                switch($_GET['age']) {
                case "1":
                    $query .= " AND (age < 30) ";
                    break;
                case "2":
                    $query .= " AND (age >= 30) AND (age < 40) ";
                    break;
                case "3":
                    $query .= " AND (age >= 40) AND (age < 50) ";
                    break;
                case "4":
                    $query .= " AND (age >= 50) AND (age < 60) ";
                    break;
                case "5":
                    $query .= " AND (age >= 60) ";
                }
                continue;
            } else {
                $query .= " AND (" . $param . "= :" . $param . ")";
            }
            $query_params[":" . $param] = $_GET[$param];
        }

        try {
            $stmt = $db->prepare($query);
            $result = $stmt->execute($query_params);

            if ($stmt->rowCount() > 0 && isset($_GET['search'])) {
                echo '<table border="1" style="width:100%">';
                echo '<tr><td>Name</td><td>Age</td><td>Sex</td></tr>';
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                    $query = "
                    SELECT *
                    FROM user_types
                    WHERE
                      id = :type_id
                    ";

                    $query_params = array(
                        ':type_id' => $row['user_type_id']
                    );
                    try {
                        $stmt1 = $db->prepare($query);
                        $result1 = $stmt1->execute($query_params);
                        $type = $stmt1->fetch(PDO::FETCH_ASSOC);
                        $name = $row['first_name'] . " " . $row['last_name'] . " (" . $type['type_name'] . ")";
                    } catch(Exception $ex) {
                        $name = $row['first_name'] . " " . $row['last_name'];
                    }

                    $link = "http://wal-engproject.rhcloud.com/src/user_page.php?id=" . $row['id'];
                    echo "<tr><td><a href=\"". $link . "\">" . $name . "</a></td><td>" . $row['age'] . "</td><td>" . $row['sex'] 
                            . "</td></tr>";
                }
                echo '</table><br/><br/>';
            } else if (isset($_GET['search'])) {
                echo "<li>" . "No search results!" . "</li>";
            }
        } catch(PDOException $ex) {
            die("Failed to run query: " . $ex->getMessage());
        }

        ?>
    </ul>

</div>

</body>
</html>
