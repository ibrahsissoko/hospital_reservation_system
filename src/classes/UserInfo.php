<?php

abstract class UserInfo {

    public $status;

    function saveInfo($post, $session, $db) {
        if (!empty($post)) {
            $this->insertIntoDatabase($post, $session, $db);
            $this->status = "success";
        } else {
            $this->status = "failed";
        }
    }

    protected abstract function getQueryParams($post, $session);
    protected abstract function insertIntoDatabase($post, $session, $db);
}