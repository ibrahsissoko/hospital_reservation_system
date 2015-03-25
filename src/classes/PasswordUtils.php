<?php

class PasswordUtils {

    static function checkMatchingPasswords($newPassword, $confirmed) {
        if ($newPassword == $confirmed) {
            return true;
        } else {
            return false;
        }
    }
    
    static function testPassword($password) {
        if (strlen($password) == 0) {
            // This case is caught later.
            return "";
        } elseif (strlen($password) > 20 ) {
            return "Password cannot be longer than 20 characters.";
        } elseif (preg_match("/\d/",$password) == 0) {
            return "Password must have at least one number.";
        } elseif (preg_match("/[A-Z,a-z]/",$password) == 0) {
            return "Password must have at least one letter.";
        }
        // No error message needed.
        return "";
    }

    static function hashPassword($password, $salt) {
        $hashedPassword = hash('sha256', $password . $salt);

        for($round = 0; $round < 65536; $round++) {
            $hashedPassword = hash('sha256', $hashedPassword . $salt);
        }

        return $hashedPassword;
    }
    
    static function generatePasswordSalt() {
        return dechex(mt_rand(0, 2147483647)) . dechex(mt_rand(0, 2147483647));
    }
    
    static function generateNewPassword() {
        return substr(md5(rand(0,2147483647)),10,10);
    }
}