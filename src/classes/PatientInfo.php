<?php

class PatientInfo extends UserInfo {

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