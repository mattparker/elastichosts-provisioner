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
        $this->assertContains('tier disk', $output[1]);
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

        $this->setExpectedException('RuntimeException');
        $builder->image($drive, EHDriveBuilder::DEBIAN_74);

    }


    public function test_info_a_drive_with_no_id () {
        $cfg = new stdClass();
        $drive = new EHDrive($cfg);
        $builder = new EHDriveBuilder();

        $this->setExpectedException('RuntimeException');
        $builder->info($drive);
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

    public function test_we_can_specify_a_ssd () {
        $cfg = (object)[
            'name' => 'a',
            'size' => '123213',
            'ssd' => '1'
        ];
        $drive = new EHDrive($cfg);
        $builder = new EHDriveBuilder();

        $output = $builder->create($drive);
        $this->assertContains('tier ssd', $output[1]);
    }


    public function test_we_can_specify_a_ssd_using_tier () {
        $cfg = (object)[
            'name' => 'a',
            'size' => '123213',
            'tier' => 'ssd'
        ];
        $drive = new EHDrive($cfg);
        $builder = new EHDriveBuilder();

        $output = $builder->create($drive);
        $this->assertContains('tier ssd', $output[1]);
    }

    public function test_parse_response_throws_when_invalid_action () {

        $builder = new EHDriveBuilder();
        $drive = new EHDrive((object)[]);
        $this->setExpectedException('InvalidArgumentException');
        $builder->parseResponse($drive, [], 9834);

    }


    public function test_we_see_when_imaging_complete_when_it_says_status_active () {
        $builder = new EHDriveBuilder();
        $drive = new EHDrive((object)[]);
        $response = [
            'name bob123',
            'cpu 123',
            'mem 100000',
            'status active',
            'nic:0:model e1000'
        ];
        $done = $builder->parseResponse($drive, $response, EHDriveBuilder::IS_IMAGING_COMPLETE);
        $this->assertEquals('false', $done);

    }



    public function test_we_see_when_imaging_complete_when_active_and_imaging_are_absent () {
        $builder = new EHDriveBuilder();
        $drive = new EHDrive((object)[]);
        $response = [
            'name bob123',
            'cpu 123',
            'mem 100000',
            'nic:0:model e1000'
        ];
        $done = $builder->parseResponse($drive, $response, EHDriveBuilder::IS_IMAGING_COMPLETE);
        $this->assertEquals('', $done);

    }
}
 