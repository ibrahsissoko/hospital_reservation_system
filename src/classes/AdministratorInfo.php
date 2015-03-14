<?php

class AdministratorInfo extends UserInfo {

    protected function insertIntoDatabase($post, $_SESSION, $db) {
        // TODO: insert post data into database (just like PatientInfo).
    }

    function getQueryParams($post, $_SESSION) {
        return array(

        );
    }
}