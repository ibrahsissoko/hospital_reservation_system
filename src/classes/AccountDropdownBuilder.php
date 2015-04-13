<?php

class AccountDropdownBuilder {

    public static function buildDropdown($db, $session) {
        switch($session['user']['user_type_id']) {
            case 1:
                $type = "patient";
                break;
            case 2:
                $type = "doctor";
                break;
            case 3:
                $type = "nurse";
                break;
            case 4:
                $type = "administrator";
                break;
        }

        $query = "
                SELECT picture_url
                FROM users
                WHERE
                    id = :id
                ";
        $query_params = array(
            ':id' => $session['user']['id']
        );

        try {
            $stmt = $db->prepare($query);
            $result = $stmt->execute($query_params);
        } catch(PDOException $ex) {
            die("Failed to run query: " . $ex->getMessage());
        }

        $row = $stmt->fetch();
        $pictureUrl = $row['picture_url'];

        $profileLink = "http://wal-engproject.rhcloud.com/src/user_page.php?id=" . $session['user']['id'];

        echo "<li class=\"dropdown\">";
        echo "<a class=\"dropdown-toggle\" href=\"#\" data-toggle=\"dropdown\">Account  <strong class=\"caret\"></strong></a>";
        echo "<div class=\"dropdown-menu\" style=\"padding: 15px; padding-bottom: 0px;\">";
        echo "<a href=\"" . $profileLink . "\">";
        echo "<div class=\"center_image_dropdown\"><img src=\"" . $pictureUrl . "\" /></div>";
        echo "</a>";
        echo "<b>" . $session['user']['first_name'] . " " . $session['user']['last_name'] . "</b><br/><br/>";
        echo "<a href=\"change_password.php\">Change Password</a><br/>";
        echo "<a href=\"email_preferences.php\">Email Preferences</a><br/>";
        echo "<a href=\"" . $type . "_info.php\">Update information</a><br/>";
        echo "<a href=\"delete_account.php\">Delete Account</a><br/><br/>";
        echo "<a href=\"upload_photo.php\">Upload Photo</a><br/><br/>";
        echo "</div>";
        echo "</li>";
    }

}