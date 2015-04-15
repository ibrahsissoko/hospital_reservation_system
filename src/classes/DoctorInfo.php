<?php

class DoctorInfo extends UserInfo {

    private $availability;
    
    protected function insertIntoDatabase($post, $session, $db) {
        $query = "
            UPDATE users
            SET
                info_added = :info_added,
                first_name = :first_name,
                last_name = :last_name,
                sex = :sex,
		degree = :degree,
                department_id = :department_id,
		years_of_experience = :years_of_experience,
		shift_id = :shift_id,
                address = :address,
                city = :city,
                state = :state,
                zip = :zip,
                phone = :phone,
                challenge_question_id = :challenge_question_id,
                challenge_question_answer = :challenge_question_answer,
                avilability = :availability
            WHERE
                id = :id
        ";
        
        foreach($post['availability'] as $day) {
            $this->availability .= $day;
        }

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
            ':sex' => $post['sex'],
            ':degree' => ($post['degree']),
            ':department_id' => ($post['department_id']),
            ':years_of_experience' => $post['years_of_experience'],
            ':shift_id' => $post['shift_id'],
            ':address' => $post['address'],
            ':city' => $post['city'],
            ':state' => $post['state'],
            ':zip' => $post['zip'],
            ':phone' => $post['phone'],
            ':challenge_question_id' => $post['challenge_question_id'],
            ':challenge_question_answer' => $post['challenge_question_answer'],
            ':availability' => $this->availability
        );
    }
}