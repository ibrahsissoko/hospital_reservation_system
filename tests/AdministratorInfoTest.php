<?php

class AdministratorInfoTest extends PHPUnit_Framework_TestCase {

    function test_paramsSize() {
        $admin = new AdministratorInfo();

        $queryParams = $admin->getQueryParams(null, null);
        $this->assertEquals(count($queryParams), 0);
    }
}