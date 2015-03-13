<?php

class PatientInfo extends UserInfo{

    protected function insertIntoDatabase($_POST, $_SESSION, $db) {
        if(!empty($_POST)) {
            // this will be called after they hit the submit button on the form.
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

            $query_params = $this->getQueryParams($_POST, $_SESSION);

            try {
                $stmt = $db->prepare($query);
                $result = $stmt->execute($query_params);
            } catch(PDOException $ex) {
                die("Failed to run query: " . $ex->getMessage());
            }

            if ($result) {
                header("Location: Home.php");
                die("Redirecting to: Home.php");
            }

        }
    }

    function getQueryParams($_POST, $_SESSION) {
        return array(
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
    }
}