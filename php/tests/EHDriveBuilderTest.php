<?php
/**
 * User: matt
 * Date: 23/06/14
 * Time: 10:26
 */
require_once 'phpunit-bootstrap.php';


class EHDriveBuilderTest extends PHPUnit_Framework_TestCase {


    public function test_instance () {
        new EHDriveBuilder();

    }


    public function test_create_a_drive_with_no_size_throws_logic_exception () {
        $cfg = new stdClass();
        $cfg->name = 'bob123';
        //$cfg->size = 123456;

        $drive = new EHDrive($cfg);
        $builder = new EHDriveBuilder();

        $this->setExpectedException('LogicException');
        $builder->create($drive);
    }

    public function test_create_a_drive_gives_expected_api_call () {
        $cfg = new stdClass();
        $cfg->name = 'bob123';
        $cfg->size = 123456;

        $drive = new EHDrive($cfg);
        $builder = new EHDriveBuilder();

        $output = $builder->create($drive);
        $this->assertContains('drives create', $output[0]);
        $this->assertContains('name bob123', $output[1]);
        $this->assertContains('size 123456', $output[1]);
    }

    public function  test_create_a_drive_avoiding_other_drives () {
        $cfg = new stdClass();
        $cfg->name = 'bob123';
        $cfg->size = 123456;

        $drive = new EHDrive($cfg);
        $builder = new EHDriveBuilder();

        $output = $builder->create($drive, array('tom987', 'j85'));
        $this->assertContains('drives create', $output[0]);
        $this->assertContains('name bob123', $output[1]);
        $this->assertContains('size 123456', $output[1]);
        $this->assertContains('avoid tom987 j85', $output[1]);

    }

    public function test_image_a_drive_that_has_no_id () {
        $cfg = new stdClass();
        $drive = new EHDrive($cfg);
        $builder = new EHDriveBuilder();

        $this->setExpectedException('LogicException');
        $builder->image($drive, EHDriveBuilder::DEBIAN_74);

    }


    public function test_image_a_drive () {
        $cfg = new stdClass();
        $drive = new EHDrive($cfg);
        $drive->setIdentifier('123098');
        $builder = new EHDriveBuilder();


        $out = $builder->image($drive, EHDriveBuilder::DEBIAN_74);
        $this->assertContains('drives 123098 image ' . EHDriveBuilder::DEBIAN_74, $out[0]);

    }


    public function test_info_for_a_drive () {
        $cfg = new stdClass();
        $drive = new EHDrive($cfg);
        $drive->setIdentifier('123098');
        $builder = new EHDriveBuilder();


        $out = $builder->info($drive, EHDriveBuilder::DEBIAN_74);
        $this->assertContains('drives 123098 info', $out[0]);

    }
}
 