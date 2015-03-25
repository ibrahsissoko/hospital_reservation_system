<?php

class NurseInfo extends UserInfo {

    protected function insertIntoDatabase($post, $session, $db) {
        // TODO: insert post data into database (just like PatientInfo).
		 // this will be called after they hit the submit button on the form.
        $query = "
            UPDATE users
            SET
                info_added = :info_added,
                first_name = :first_name,
                last_name = :last_name,
                sex = :sex,
				department = :department,
				years_of_experience = :years_of_experience,
				shift = :shift,
                address = :address,
                city = :city,
                state = :state,
                zip = :zip,
                phone = :phone
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
            ':sex' => $post['sex'],
            ':years_of_experience' => $post['years_of_experience'],
			':shift' => $post['shift'],
            ':department' => $post['department'],
            ':address' => $post['address'],
            ':city' => $post['city'],
            ':state' => $post['state'],
            ':zip' => $post['zip'],
            ':phone' => $post['phone']
         
        );
    }
}