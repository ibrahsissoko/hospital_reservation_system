<?php

    include_once('../AutoLoader.php');
    AutoLoader::registerDirectory('../src/classes');

    require("config.php");

    if(empty($_SESSION['user'])) {
        header("Location: ../index.php");
        die("Redirecting to index.php");
    }
    
    if (!empty($_POST)) {
        $patient = new PatientInfo();
        if ($patient->validate($_POST)) {
            $patient->saveInfo($_POST, $_SESSION, $db);
            // Update session variables to reflect post values.
            $postParams = array('first_name','last_name','sex','dob','age','marital_status',
                'years_of_experience','availability','shift_id','address','city','state',
                'zip','phone','insurance_id','insurance_begin','insurance_end','allergies',
                'diseases','previous_surgeries','other_medical_history','challenge_question_id',
                'challenge_question_answer');
            foreach($postParams as $param) {
                $_SESSION['user'][$param] = htmlspecialchars($_POST[$param]);
            }
        }
    }
?>

<!doctype html>
<html lang="en">
<head>
    <style>.error {color: #FF0000;}</style>
    <meta charset="utf-8">
    <title>Hospital Management</title>
    <meta name="description" content="Hospital management system for Intro to Software Engineering">
    <meta name="author" content="WAL Consulting">

    <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>
    <script src="../assets/bootstrap.min.js"></script>
    <link href="../assets/bootstrap.min.css" rel="stylesheet" media="screen">
    <link href="../assets/styles.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="http://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    <script src="http://code.jquery.com/jquery-1.10.2.js"></script>
    <script src="http://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
    <script>$(function() {$( "#datepicker1, #datepicker2, #datepicker3" ).datepicker();});</script>
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
    <h1>Patient Info:</h1><br/>
    <form action="patient_info.php" method="post">	
        <span class="error"><?php echo $patient->error;?></span><br/>
        First Name:<br/>
        <input type="text" name="first_name" value="<?php echo htmlspecialchars($_SESSION['user']['first_name']);?>" /><br/>
        Last Name:<br/>
        <input type="text" name="last_name" value="<?php echo htmlspecialchars($_SESSION['user']['last_name']);?>" /><br/>
        Sex:<br/>
        <input type="radio" name="sex" value="Female" <?php echo ($_SESSION['user']['sex'] == 'Female') ? 'checked="checked"' : ''; ?> /> Female<br/>
        <input type="radio" name="sex" value="Male" <?php echo ($_SESSION['user']['sex'] == 'Male') ? 'checked="checked"' : ''; ?>> Male<br/>
        DOB (mm/dd/yyyy):<br/>
        <input type="text" id="datepicker1" name = "dob" value = "<?php echo htmlspecialchars($_SESSION['user']['dob']);?>" pattern="(0[1-9]|1[012])/(0[1-9]|[12][0-9]|3[01])/(19|20)[0-9]{2}"><br/>
        Age:<br/>
        <input type="number" name="age" min="1" max="120" value="<?php echo htmlspecialchars($_SESSION['user']['age']);?>"><br>
        Marital Status:<br/>
        <input type="radio" name="marital_status" value="Single" <?php echo ($_SESSION['user']['marital_status'] == 'Single') ? 'checked="checked"' : ''; ?> /> Single<br/>
        <input type="radio" name="marital_status" value="Married" <?php echo ($_SESSION['user']['marital_status'] == 'Married') ? 'checked="checked"' : ''; ?> > Married<br/>
        <input type="radio" name="marital_status" value="In a relationship" <?php echo ($_SESSION['user']['marital_status'] == 'In a relationship') ? 'checked="checked"' : ''; ?> /> In a relationship<br/>
        <input type="radio" name="marital_status" value="Divorced" <?php echo ($_SESSION['user']['marital_status'] == 'Divorced') ? 'checked="checked"' : ''; ?> > Divorced<br/>
        <input type="radio" name="marital_status" value="Widowed" <?php echo ($_SESSION['user']['marital_status'] == 'Widowed') ? 'checked="checked"' : ''; ?> /> Widowed<br/>
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
        <input type="text" name="zip" value = "<?php echo htmlspecialchars($_SESSION['user']['zip']);?>" pattern="[0-9]{5}"><br/>
        Phone:<br/>
        <input type="text" name="phone" value = "<?php echo htmlspecialchars($_SESSION['user']['phone']);?>" pattern="[0-9]{10}"><br/>
        Insurance Provider:<br/>
        <select name="insurance_id">
            <?php

            $query = "
                SELECT insurance_id
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
            $insuranceId = $row['insurance_id'];

            $query = "
                SELECT *
                FROM insurance
            ";

            // execute the statement
            try {
                $stmt = $db->prepare($query);
                $result = $stmt->execute();

                // loop through, adding the options to the spinner
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo $row['id'] . " " . $_SESSION['user']['insurance_id'];
                    if ($row['id'] == $insuranceId) {
                        echo "<option value=\"" . $row["id"] . "\" selected=\"selected\">" . $row["insurance_company"] . "</option>";
                    } else {
                        echo "<option value=\"" . $row["id"] . "\">" . $row["insurance_company"] . "</option>";
                    }
                }
            } catch(Exception $e) {
                die("Failed to get insurance information. " . $e->getMessage());
            }
            ?>
        </select>
        <br/>
        Insurance Beginning Date(mm/dd/yyyy):<br/>
        <input type="text" id="datepicker2" name="insurance_begin" pattern="(0[1-9]|1[012])/(0[1-9]|[12][0-9]|3[01])/(19|20)[0-9]{2}" value="<?php echo $_SESSION['user']['insurance_begin'];?>"><br/>
        Insurance Ending Date (mm/dd/yyyy):<br/>	
        <input type="text" id="datepicker3" name="insurance_end" pattern="(0[1-9]|1[012])/(0[1-9]|[12][0-9]|3[01])/(19|20)[0-9]{2}" value="<?php echo $_SESSION['user']['insurance_end']?>"><br/>		
        Allergies:<br/>
        <input type="text" name="allergies" value="<?php echo htmlspecialchars($_SESSION['user']['allergies']);?>" />
        <br/>
        Diseases:<br/>
        <input type="text" name="diseases" value="<?php echo htmlspecialchars($_SESSION['user']['diseases']);?>" />
        <br/>
        Previous Surgeries:<br/>
        <input type="text" name="previous_surgeries" value="<?php echo htmlspecialchars($_SESSION['user']['previous_surgeries']);?>" />
        <br/>
        Other Medical History:<br/>
        <textarea name="other_medical_history" value = "<?php echo htmlspecialchars($_SESSION['user']['other_medical_history']);?>" cols="40" rows="5"></textarea><br/>
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
        <input type="submit" name = "submit" class="btn btn-info" value="Save" />
    </form>
</div>

</body>
</html>
