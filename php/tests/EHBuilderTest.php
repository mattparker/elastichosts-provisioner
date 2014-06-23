<?php
/**
 * User: matt
 * Date: 23/06/14
 * Time: 15:56
 */
require_once 'phpunit-bootstrap.php';


class EHBuilderTest extends PHPUnit_Framework_TestCase {

    /**
     * @var Runner
     */
    private $mockRunner;

    /**
     * @var EHBuilder
     */
    private $builder;


    public function setUp () {
        $this->mockRunner = new MockRunner();
        $this->builder = new EHBuilder(new EHServerBuilder(), new EHDriveBuilder(), $this->mockRunner);
        $mockLogger = new MockLogger();
        $this->builder->setLogger($mockLogger);
    }

    public function test_instance () {

    }

    public function test_simple_drives_setup () {

        $serverCfg = new stdClass();
        $drive = new stdClass();
        $drive->name = "drive1";
        $drive->size = "10000";
        $serverCfg->drives = array($drive);

        $server = new EHServer($serverCfg);
        $this->builder->build($server);

        $driveCreateCall = $this->mockRunner->getCall('drives create');
        $this->assertEquals(count($driveCreateCall), 1);
        $this->assertContains('name drive1', $driveCreateCall[0]);
        $this->assertContains('size 10000', $driveCreateCall[0]);
        $this->assertEquals('{guid}', $server->getDrives()[0]->getIdentifier(), 'Thats the placeholder from the MockRunner');

    }

    public function test_a_drive_with_image () {

        $serverCfg = new stdClass();
        $drive = new stdClass();
        $drive->name = "drive2";
        $drive->size = "100000";
        $drive->image = "DEBIAN_74";
        $serverCfg->drives = array($drive);

        $server = new EHServer($serverCfg);
        $this->builder->build($server);

        $driveCreateCall = $this->mockRunner->getCall('drives create');
        $this->assertEquals(1, count($driveCreateCall));
        $this->assertContains('name drive2', $driveCreateCall[0]);
        $this->assertContains('size 100000', $driveCreateCall[0]);
        $this->assertEquals('{guid}', $server->getDrives()[0]->getIdentifier(), 'Thats the placeholder from the MockRunner');
        $driveInfoCall = $this->mockRunner->getCall('drives info');
        $this->assertEquals(1, count($driveInfoCall));
    }


    public function test_with_two_drives () {

        $serverCfg = new stdClass();

        $drive = new stdClass();
        $drive->name = "drive1";
        $drive->size = "10000";
        $drive->image = "DEBIAN_74";

        $drive2 = new stdClass();
        $drive2->name = "drive2";
        $drive2->size = "100000";


        $serverCfg->drives = array($drive, $drive2);

        $server = new EHServer($serverCfg);
        $this->builder->build($server);

        $driveCreateCall = $this->mockRunner->getCall('drives create');
        $this->assertEquals(2, count($driveCreateCall));
    }
}
 