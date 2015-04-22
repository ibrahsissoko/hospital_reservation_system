<?php

class NurseInfoTest extends PHPUnit_Framework_TestCase {

    function test_paramsSize() {
        $nurse = new NurseInfo();

        $queryParams = $nurse->getQueryParams(null, null);
        $this->assertEquals(count($queryParams), 16);
    }
}