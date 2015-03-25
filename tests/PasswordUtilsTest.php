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
    
    function test_validPassword() {
        $result = PasswordUtils::testPassword("longPasswordlongPassword");
        $this->assertEquals($result, "Password cannot be longer than 20 characters.");
        $result = PasswordUtils::testPassword("password");
        $this->assertEquals($result, "Password must have at least one number.");
        $result = PasswordUtils::testPassword("123456789");
        $this->assertEquals($result, "Password must have at least one letter.");
    }
    
}