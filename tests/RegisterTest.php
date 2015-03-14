<?php

class RegisterTest extends PHPUnit_Framework_TestCase {

    public function test_initialize() {
        $r = new Register();

        $this->assertTrue(empty($r->incorrectEmail));
    }

    function test_emailError() {
        $r = new Register();
        $r->emailError("");
        $this->assertFalse(empty($r->noEmail));
        $this->assertFalse(empty($r->incorrectEmail));

        $r = new Register();
        $r->emailError("lklink");
        $this->assertTrue(empty($r->noEmail));
        $this->assertFalse(empty($r->incorrectEmail));

        $r = new Register();
        $r->emailError("lklinker@gmail.com");
        $this->assertTrue(empty($r->noEmail));
        $this->assertTrue(empty($r->incorrectEmail));
    }

    function test_passwordError() {
        $r = new Register();
        $r->passwordError("", "");
        $this->assertFalse(empty($r->noPassword));
        $this->assertFalse(empty($r->noConfirmPassword));
        $this->assertTrue(empty($r->noPasswordMatch));

        $r = new Register();
        $r->passwordError("klink", "klink");
        $this->assertTrue(empty($r->noPassword));
        $this->assertTrue(empty($r->noConfirmPassword));
        $this->assertTrue(empty($r->noPasswordMatch));

        $r = new Register();
        $r->passwordError("klink", "");
        $this->assertTrue(empty($r->noPassword));
        $this->assertFalse(empty($r->noConfirmPassword));
        $this->assertFalse(empty($r->noPasswordMatch));

        $r = new Register();
        $r->passwordError("", "klink");
        $this->assertFalse(empty($r->noPassword));
        $this->assertTrue(empty($r->noConfirmPassword));
        $this->assertFalse(empty($r->noPasswordMatch));
    }

    function test_userType() {
        $r = new Register();
        $r->userTypeError(1, "11", "");
        $this->assertTrue(empty($r->noAccessCode));

        $r = new Register();
        $r->userTypeError(2, "11", "11");
        $this->assertTrue(empty($r->noAccessCode));

        $r = new Register();
        $r->userTypeError(2, "11", "");
        $this->assertFalse(empty($r->noAccessCode));

        $r = new Register();
        $r->userTypeError(2, "", "11");
        $this->assertFalse(empty($r->noAccessCode));
    }
}
