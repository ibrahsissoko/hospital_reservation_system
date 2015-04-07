<?php

class PatientInfoTest extends PHPUnit_Framework_TestCase {

    function test_paramsSize() {
        $patient = new PatientInfo();

        $queryParams = $patient->getQueryParams(null, null);
        $this->assertEquals(count($queryParams), 22);
    }
}