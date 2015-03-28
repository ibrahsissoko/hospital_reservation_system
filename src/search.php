<?php

    include_once('../AutoLoader.php');
    AutoLoader::registerDirectory('../src/classes');

    require("config.php");

    if(empty($_SESSION['user'])) {
        header("Location: ../index.php");
        die("Redirecting to index.php");
    } else {

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
                <form class="navbar-search pull-left" action="search.php" method="POST" >
                    <input type="text" class="search-query" name="search" placeholder="<?php echo $_POST['search'] ?>" >
                </form>
                <ul class="nav pull-right">
                    <li><a href="my_account.php">Account</a></li>
                    <li><a href="logout.php">Log Out</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="container hero-unit">
    <ul>
    <?php
        $query = "
            SELECT *
            FROM users
            WHERE first_name LIKE '%" . $_POST['search'] . "%' OR
                    last_name LIKE '%" . $_POST['search'] . "%' OR
                    CONCAT(first_name, ' ', last_name) LIKE '%" . $_POST['search'] . "%' OR
                    CONCAT(last_name, ' ', first_name) LIKE '%" . $_POST['search'] . "%' OR
                    email LIKE '%" . $_POST['search'] . "%'
        ";
        $query_params = array( );

        try {
            $stmt = $db->prepare($query);
            $result = $stmt->execute($query_params);

            $i = 0;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $name = $row['first_name'] . " " . $row['last_name'];
                $link = "http://wal-engproject.rhcloud.com/src/user_page.php?id=" . $row['id'];
                echo "<li>" . "<a href=\"". $link . "\">" . $name . "</a>" . "</li>";
                $i = $i + 1;
            }

            if ($i == 0) {
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
