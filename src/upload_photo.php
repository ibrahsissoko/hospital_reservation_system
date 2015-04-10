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
                $userType = "nurse";
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
                <form class="navbar-search pull-left" action="search.php" method="GET" >
                    <input type="text" class="search-query" name="search" placeholder="<?php echo $_GET['search'] ?>" >
                </form>
                <ul class="nav pull-right">
                    <?php AccountDropdownBuilder::buildDropdown($_SESSION) ?>
                    <li><a href="logout.php">Log Out</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="container hero-unit">
    <h1>Upload Photo:</h1> <br />
    <form action="https://s3-bucket.s3.amazonaws.com/" method="post" enctype="multipart/form-data">
        <input type="hidden" name="key" value="uploads/${filename}">
        <input type="hidden" name="AWSAccessKeyId" value="YOUR_AWS_ACCESS_KEY"> 
        <input type="hidden" name="acl" value="private"> 
        <input type="hidden" name="success_action_redirect" value="http://localhost/">
        <input type="hidden" name="policy" value="YOUR_POLICY_DOCUMENT_BASE64_ENCODED">
        <input type="hidden" name="signature" value="YOUR_CALCULATED_SIGNATURE">
        <input type="hidden" name="Content-Type" value="image/jpeg">
        File to upload to S3: 
        <input name="file" type="file"> 
        <br> 
        <input type="submit" value="Upload File to S3"> 
    </form> 

</div>

</body>
</html>
