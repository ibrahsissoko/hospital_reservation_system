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
    $user_type = $_SESSION['user']['user_type_id'];

    if(!empty($_POST)) {
        // this will be called after they hit the submit button on the form.
        // TODO:
        //      Add the columns to the user database on the online phpMyAdmin site
        //      Update that user with the POST answers on here
        //      make sure the 'info_added' column gets set to 1 for that user as well so this doesn't run again.

        $query = "
            UPDATE users
            SET
                info_added = :info_added,
		first_name = :first_name,
                last_name = :last_name, 
                sex = :sex,
                dob = :dob,
		age = :age,
		marital_status = :marital_status,
		address = :address,
		city = :city,
                state = :state,
		zip = :zip,
		phone = :phone,
		insurance_provider = :insurance_provider,
		insurance_begin = :insurance_begin,
		insurance_end = :insurance_end,
		allergies = :allergies,
		diseases = :diseases,
		previous_surgeries = :previous_surgeries,
		other_medical_history = :other_medical_history
            WHERE
                id = :id
        ";

        $query_params = array(
            ':info_added' => 1,
            ':id' => $_SESSION['user']['id'],
			':first_name' => $_POST['first_name'],
			 ':last_name' => $_POST['last_name'], 
                ':sex' => isset($_POST['sex']),
                ':dob' => $_POST['dob'],
				':age' => $_POST['age'],
				':marital_status' => isset($_POST['marital_status']),
				':address' => $_POST['address'],
				':city' => $_POST['city'],
                                ':state' => $_POST['state'],
				':zip' => $_POST['zip'],
				':phone' => $_POST['phone'],
				':insurance_provider' => $_POST['insurance_provider'],
				':insurance_begin' => $_POST['insurance_begin'],
				':insurance_end' => $_POST['insurance_end'],
				':allergies' => $_POST['allergies'],
				':diseases' => $_POST['diseases'],
				':previous_surgeries' => $_POST['previous_surgeries'],
				':other_medical_history' => $_POST['other_medical_history'] 
			
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
                    <li><a href="home.php">Home</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="container hero-unit">
    <h1>Patient Info:</h1> <br />
    <form action="patient_info.php" method="post">
        <!-- TODO: add form here to enter the info. -->	
		
        First Name:<br/>
        <input type="text" name="first_name" value="" />
		<br/>
		Last Name:<br/>
        <input type="text" name="last_name" value="" />
		<br/>
		Sex:<br/>
		<input type="radio" name="sex" value=""/> Female<br/>
		<input type="radio" name="sex" value=""> Male<br/>
		DOB(yyyymmdd):<br/>
		<input type="text" name = "dob" pattern="(19|20)[0-9]{2}(0[1-9]|1[012])(0[1-9]|[12][0-9]|3[01])"><br/>
		Age:<br/>
		<input type="number" name="age" min="1" max="120" value=""><br>
		Marital Status:<br/>
		<input type="radio" name="marital_status" value=""/> Single<br/>
		<input type="radio" name="marital_status" value=""> Married<br/>
		<input type="radio" name="marital_status" value=""/> In a relationship<br/>
		<input type="radio" name="marital_status" value=""> Divorced<br/>
		<input type="radio" name="marital_status" value=""/> Widowed<br/>
		Address:<br/>
		<input type="text" name="address" value="" />
		<br/>
		City:<br/>
		<input type="text" name="city" value="" />
		<br/>
                State:<br/>
                <input type="text" name="state" value="" />
                <br/>
		Zip:<br/>
		<input type="text" name="zip" pattern="[0-9]{5}"><br/>
		Phone:<br/>
		<input type="text" name="phone" pattern="[0-9]{10}"><br/>
		Insurance Provider:<br/>
		<input type="text" name="insurance_provider" value="" />
		<br/>
		Insurance Beginning Date(yyyymmdd):<br/>
		<input type="text" name="insurance_begin"pattern="(19|20)[0-9]{2}(0[1-9]|1[0-2])(0[1-9]|[1-2][0-9]|3[01])"><br/>
		Insurance Ending Date(yyyymmdd):<br/>	
		<input type="text" name="insurance_end" pattern="(19|20)[0-9]{2}(0[1-9]|1[012])(0[1-9]|[12][0-9]|3[01])"><br/>		
		Allergies:<br/>
		<input type="text" name="allergies" value="" />
		<br/>
		Diseases:<br/>
		<input type="text" name="diseases" value="" />
		<br/>
		Previous Surgeries:<br/>
		<input type="text" name="previous_surgeries" value="" />
		<br/>
		Other Medical History:<br/>
		<textarea name="other_medical_history" cols="40" rows="5"></textarea>
		<br/><br/>
        <input type="submit" name = "submit" class="btn btn-info" value="Save" />
    </form>
</div>

</body>
</html>
