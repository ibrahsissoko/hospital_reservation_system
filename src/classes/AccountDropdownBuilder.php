<?php

class AccountDropdownBuilder {

    public static function buildDropdown($session) {
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

        echo "<li class=\"dropdown\">";
        echo "<a class=\"dropdown-toggle\" href=\"#\" data-toggle=\"dropdown\">Account  <strong class=\"caret\"></strong></a>";
        echo "<div class=\"dropdown-menu\" style=\"padding: 15px; padding-bottom: 0px;\">";
        echo "<img border=\"0\" src=\"" . $session['user']['picture_url'] . "\" width=\"100\" height=\"100\"><br/>";
        echo "<b>" . $session['user']['first_name'] . " " . $session['user']['last_name'] . "</b><br/>";
        echo "<a href=\"change_password.php\">Change Password</a><br/>";
        echo "<a href=\"email_preferences.php\">Email Preferences</a><br/>";
        echo "<a href=\"" . $type . "_info.php\">Update information</a><br/>";
        echo "<a href=\"delete_account.php\">Delete Account</a><br/><br/>";
        echo "<a href=\"upload_photo.php\">Upload Photo</a><br/><br/>";
        echo "</div>";
        echo "</li>";
    }

}