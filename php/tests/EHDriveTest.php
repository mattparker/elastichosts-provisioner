<?php
/**
 * User: matt
 * Date: 21/06/14
 * Time: 10:41
 */
require_once 'phpunit-bootstrap.php';


class EHDriveTest extends PHPUnit_Framework_TestCase {


    public function test_instance () {
        $cfg = new stdClass();
        new EHDrive($cfg);
    }

    public function test_a_drive_with_no_name () {
        $drive = new EHDrive((object)[]);
        $this->assertEquals('', $drive->getName());
    }

}
 