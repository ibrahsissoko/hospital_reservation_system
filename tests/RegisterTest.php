<?php

class RegisterTest  extends PHPUnit_Framework_TestCase {

    public function testInitialize() {
        $r = new Register();
        $r->initializeValues();

        $string = $r->getNoEmail();
        $this->assertTrue(empty($string));
    }
}

?>
