<?php

class VerifyTest  extends PHPUnit_Framework_TestCase {

    function test_verifyComplete() {
        $verify = new Verify();
        $verify->verifyUser("11ad", "lklinker1@gmail.com", null);

        $this->assertTrue(!empty($verify->status));
    }
}