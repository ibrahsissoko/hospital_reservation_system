<?php

class UserInfoTest extends PHPUnit_Framework_TestCase {

    function test_nullPost() {
        $info = new PatientInfo();
        
        $info->saveInfo(null, null, null);
        $this->assertEquals($info->status, "failed");
    }
}