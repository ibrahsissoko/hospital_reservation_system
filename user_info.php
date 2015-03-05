<?php
    require("config.php");

    // this is the current session's user type. It will be:
    //      1 - patient
    //      2 - doctor
    //      3 - nurse
    //      4 - administrator
    // These values correspond to rows in the user_type table.
    // We will have to use this user type to generate the correct questions for each type of person.
    // They could go in a user_info_questions database that just stores the user_type and a question.
    // querying this database for the current user type would result in all the questions we need to ask to
    // get the info on that type of user. There is an example of generating html from a database in the
    // registration.php file. (it does it there for the drop down spinner.)
    $user_type = $_SESSION['user_type_id'];


    if(!empty($_POST)) {
        // this will be called after they hit the submit button on the form.
        // TODO:
        //      Add the columns to the user database on the online phpMyAdmin site
        //      Update that user with the POST answers on here
        //      make sure the 'info_added' column gets set to 1 for that user as well so this doesn't run again.

        $query = "
            UPDATE users
            SET
                info_added = :info_added
            WHERE
                id = :id
        ";

        $query_params = array(
            ':info_added' => '1',
            ':id' => $_SESSION['id']
        );

        try {
            $stmt = $db->prepare($query);
            $result = $stmt->execute($query_params);
        } catch(PDOException $ex) {
            die("Failed to run query: " . $ex->getMessage());
        }

        if ($result) {
            header("Location: home.php");
            die("Redirecting to: home.php");
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
    <script src="assets/bootstrap.min.js"></script>
    <link href="assets/bootstrap.min.css" rel="stylesheet" media="screen">
    <link href="assets/styles.css" rel="stylesheet" type="text/css">
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
                    <li><a href="home.php">Home</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="container hero-unit">
    <h1>User Info:</h1> <br />
    <form action="user_info.php" method="post">
        <!-- TODO: add form here to enter the info. -->
        <input type="submit" class="btn btn-info" value="Save" />
    </form>
</div>

</body>
</html>
