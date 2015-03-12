<?php

class Register {
    private $noEmail;

    function _construct() {
        $this->noEmail = "test";
    }

    public function initializeValues() {
        $this->noEmail = $incorrectEmail = $noPassword = $registeredEmail = $noConfirmPassword = $noPasswordMatch = $noAccessCode = $registrationSuccess = "";
    }

    public function getNoEmail() {
        return $this->noEmail;
    }
}