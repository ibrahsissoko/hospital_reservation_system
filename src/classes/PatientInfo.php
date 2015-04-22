<?php

class PatientInfo extends UserInfo {

    public $error;
    
    protected function validateInput($post) {
        $valid = true;
        $age = intval($post['age']);
        if ($age < 1 || $age > 120) {
            $this->error = "Please enter a valid age. ";
            $valid = false;
        }
        if (!empty($post['dob'])) {
            if (!preg_match("(0[1-9]|1[012])/(0[1-9]|[12][0-9]|3[01])/(19|20)[0-9]{2}", $post['dob'])) {
                $this->error .= "Please enter a valid date of birth. ";
                $valid = false;
            } else {
                $calcAge = intval(date("y")) - intval(substr($post['dob'],6,4));
                if (!($calcAge == $age || $calcAge == $age + 1)) {
                    $this->error .= "Please enter a date of birth that corresponds with your age. ";
                    $valid = false;
                }
            }
        }
        if(!empty($post['zip']) && (strlen($post['zip']) != 5 || !ctype_digit($post['zip']))) {
            $this->error = "Please enter a valid zip code. ";
            $valid = false;
        }
        if (!empty($post['phone']) && (strlen($post['phone']) != 10 || !ctype_digit($post['phone']))) {
            $this->error .= "Please enter a valid phone number. ";
            $valid = false;
        }
        return $valid;
    }
    
    protected function insertIntoDatabase($post, $session, $db) {
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
                insurance_id = :insurance_id,
                insurance_begin = :insurance_begin,
                insurance_end = :insurance_end,
                allergies = :allergies,
                diseases = :diseases,
                previous_surgeries = :previous_surgeries,
                other_medical_history = :other_medical_history,
                challenge_question_id = :challenge_question_id,
                challenge_question_answer = :challenge_question_answer
            WHERE
                id = :id
        ";

        $query_params = $this->getQueryParams($post, $session);

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

    function getQueryParams($post, $session) {
        return array(
            ':info_added' => 1,
            ':id' => $session['user']['id'],
            ':first_name' => $post['first_name'],
            ':last_name' => $post['last_name'],	
			':sex' =>$post['sex'] ,
            ':dob' => $post['dob'],
            ':age' => $post['age'],
            ':marital_status' => $post['marital_status'],
            ':address' => $post['address'],
            ':city' => $post['city'],
            ':state' => $post['state'],
            ':zip' => $post['zip'],
            ':phone' => $post['phone'],
            ':insurance_id' => $post['insurance_id'],
            ':insurance_begin' => $post['insurance_begin'],
            ':insurance_end' => $post['insurance_end'],
            ':allergies' => $post['allergies'],
            ':diseases' => $post['diseases'],
            ':previous_surgeries' => $post['previous_surgeries'],
            ':other_medical_history' => $post['other_medical_history'],
            ':challenge_question_id' => $post['challenge_question_id'],
            ':challenge_question_answer' => $post['challenge_question_answer']
        );
    }
}