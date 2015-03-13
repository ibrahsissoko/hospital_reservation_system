<?php

abstract class UserInfo {

    public $status;

    function saveInfo($_POST, $_SESSION, $db) {
        if (!empty($_POST)) {
            $this->insertIntoDatabase($_POST, $_SESSION, $db);
            $this->status = "success";
        } else {
            $this->status = "failed";
        }
    }

    protected abstract function insertIntoDatabase($_POST, $_SESSION, $db);
}