<?php

class NurseInfo extends UserInfo {

    protected function insertIntoDatabase($post, $session, $db) {
        // TODO: insert post data into database (just like PatientInfo).
    }

    function getQueryParams($post, $session) {
        return array(

        );
    }
}