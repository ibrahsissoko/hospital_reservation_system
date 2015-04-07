<?php

class DoctorInfoTest extends PHPUnit_Framework_TestCase {

    function test_paramsSize() {
        $doctor = new DoctorInfo();

        $queryParams = $doctor->getQueryParams(null, null);
        $this->assertEquals(count($queryParams), 16);
    }
}