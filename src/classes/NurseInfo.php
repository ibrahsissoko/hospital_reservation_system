<?php

class NurseInfo extends UserInfo {

    protected function insertIntoDatabase($_POST, $_SESSION, $db) {
        // TODO: insert post data into database (just like PatientInfo).
    }

    function getQueryParams($_POST, $_SESSION) {
        return array(

        );
    }
}