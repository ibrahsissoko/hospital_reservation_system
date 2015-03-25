<?php

class PasswordUtilsTest extends PHPUnit_Framework_TestCase {

    function test_confirmPasswordsTrue() {
        $result = PasswordUtils::checkMatchingPasswords("test", "test");
        $this->assertTrue($result);
    }

    function test_confirmPasswordsFalse() {
        $result = PasswordUtils::checkMatchingPasswords("test", "fail");
        $this->assertFalse($result);
    }
    
}