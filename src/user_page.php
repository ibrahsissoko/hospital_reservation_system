<?php

include_once('../AutoLoader.php');
AutoLoader::registerDirectory('../src/classes');

require("config.php");

if(empty($_SESSION['user'])) {
    header("Location: ../index.php");
    die("Redirecting to index.php");
} else if (isset($_GET['to_delete_id'])) {
    $query = "
        DELETE
        FROM users
        WHERE
          id = :id
    ";
    $query_params = array(
        ':id' => $_GET['to_delete_id']
    );

    try {
        $stmt = $db->prepare($query);
        $result = $stmt->execute($query_params);
    } catch(PDOException $ex) {
        die("Failed to run query: " . $ex->getMessage());
    }

    header("Location: home.php");
    die("Redirecting to home.php");
} else {

    $query = "
        SELECT *
        FROM users
        WHERE
          id = :id
    ";
    $query_params = array(
        ':id' => $_GET['id']
    );

    try {
        $stmt = $db->prepare($query);
        $result = $stmt->execute($query_params);

        $row = $stmt->fetch();
        if ($row) {
            $userProfile = $row;
        }

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
                    <li><a href="logout.php">Log Out</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="container hero-unit">
    <h1><?php echo $userProfile['first_name'] . " " . $userProfile['last_name'] ?></h1> <br/>

    <?php

    if ($_SESSION['user']['user_type_id'] == 4) {
        $link = "http://wal-engproject.rhcloud.com/src/user_page.php?to_delete_id=" . $userProfile['id'];
        echo '<a href="' . $link . '" class="confirmation">Delete User</a>';
    }

    ?>
    <div class="center_image_profile">
        <img src="<?php echo $userProfile['picture_url'] ?>" />
    </div><br/><br/>
    <?php
    $query = "
        SELECT *
        FROM insurance
        WHERE
          id = :id
    ";
    $query_params = array(
        ':id' => $userProfile['insurance_id']
    );

    try {
        $stmt = $db->prepare($query);
        $result = $stmt->execute($query_params);

        $row = $stmt->fetch();
        if ($row) {
            $insurance_company = $row['insurance_company'];
        }

    } catch(PDOException $ex) {
        die("Failed to run query: " . $ex->getMessage());
    }
    ?>

    <h2>Contact Info:</h2>
    <?php
    echo "<b>Email:</b> " . $userProfile['email'] . "<br/>";
    if (!empty($userProfile['phone'])) {
        echo "<b>Phone:</b> " . $userProfile['phone'] . "<br/>";
    }
    if (!empty($userProfile['address']) && !empty($userProfile['city']) && !empty($userProfile['state']) && !empty($userProfile['zip'])) {
        echo "<b>Address:</b> " . $userProfile['address'] . "<br/>&nbsp;" . $userProfile['city'] . ", " . $userProfile['state'] . " " . $userProfile['zip']. "<br/>";
    }
    ?>

    <?php
        switch($userProfile['user_type_id']) {
            case 1: // patient (sex, age, dob, marital status, insurance provider, insurance begin, insurance end, allergies, diseases, previous surgeries, other medical history)
                echo "<h2>Patient Info:</h2>";
                $info = array( 
                    "Sex" => "sex",
                    "Age" => "age",
                    "Date of Birth" => "dob",
                    "Marital Status" => "marital_status",
                   // "Insurance Provider" => $insurance_company,
                    "Insurance Begin Date" => "insurance_begin",
                    "Insurance End Date" => "insurance_end",
                    "Allergies" => "allergies",
                    "Disease" => "diseases",
                    "Previous Surgeries" => "previous_surgeries",
                    "Other Medical History" => "other_medical_history"
                    );
                break;
            case 2: // doctor (sex, degree, years of experience, specialization, shift)
                echo "<h2>Doctor Info:</h2>";
                $info = array( 
                    "Sex" => "sex",
                    "Degree" => "degree",
                    "Years of Experience" => "years_of_experience",
                    "Specialization" => "specialization",
                    "Shift" => "shift"
                    );
                break;
            case 3: // nurse (sex, department, years of experience, shift)
                echo "<h2>Nurse Info:</h2>";
                $info = array( 
                    "Sex" => "sex",
                    "Department" => "department",
                    "Years of Experience" => "years_of_experience",
                    "Shift" => "shift"
                    );
                break;
            case 4: // admin (sex)
                echo "<h2>Admin Info:</h2>";
                $info = array("Sex" => "sex");
                break;
        }
        
        foreach($info as $key => $value) {
            if(!empty($userProfile[$value])) {     
                echo "<b>" . $key . ":</b> " . $userProfile[$value] . "<br/>";
            }
        }
        if(!empty($insurance_company)){
        echo "<b>" . 'Insurance Provider' . ":</b> " . $insurance_company . "<br/>";
        }

        if ($_SESSION['user']['user_type_id'] == 4 && $userProfile['user_type_id'] != 4) {
            // admins should be able to the users past appointments
            echo "<h2>Appointments:</h2>";
            $tableBuilder = new AppointmentTableBuilder();
            $tableBuilder->showAppointments($userProfile, $db, true);
        } else {

        }
        // Only patients can schedule appointments with doctors.
        if($userProfile['user_type_id'] == 2 && $_SESSION['user']['user_type_id'] == 1) {
            $link = "http://wal-engproject.rhcloud.com/src/schedule_appointment.php?id=" . $userProfile['id'];
            echo "<a href=\"" . $link . "\">Schedule an appointment</a><br/>";
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