<?php

class VerifyTest  extends PHPUnit_Framework_TestCase {

    function test_verifyComplete() {
        $verify = new Verify("11ad", "lklinker1@gmail.com", null);
        $verify->verifyUser();

        $this->assertTrue(!empty($verify->status));
    }
}