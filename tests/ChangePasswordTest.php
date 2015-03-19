<?php

class ChangePasswordTest extends PHPUnit_Framework_TestCase {

    function test_confirmPasswordsTrue() {
        $changePasswords = new ChangePassword();

        $result = $changePasswords->checkMatchingPasswords("test", "test");
        $this->assertTrue($result);
    }

    function test_confirmPasswordsFalse() {
        $changePasswords = new ChangePassword();

        $result = $changePasswords->checkMatchingPasswords("test", "fail");
        $this->assertFalse($result);
    }
}