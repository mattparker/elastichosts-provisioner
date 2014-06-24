<?php
/**
 * User: matt
 * Date: 23/06/14
 * Time: 15:56
 */
require_once 'phpunit-bootstrap.php';


class EHBuilderTest extends PHPUnit_Framework_TestCase {

    /**
     * @var MockRunner
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

        $guid = 'alaksdjfoaksdjf';
        $this->mockRunner->setGuidFor('drives create', 'drive1', $guid);

        $server = new EHServer($serverCfg);
        $this->builder->build($server);

        $driveCreateCall = $this->mockRunner->getCall('drives create');
        $this->assertEquals(1, count($driveCreateCall));
        $this->assertContains('name drive1', $driveCreateCall[0]);
        $this->assertContains('size 10000', $driveCreateCall[0]);
        $this->assertEquals($guid, $server->getDrives()[0]->getIdentifier());

        $serverCreateCall = $this->mockRunner->getCall('servers create');
        $this->assertEquals(1, count($serverCreateCall));
        $this->assertContains('ide:0:0 ' . $guid, $serverCreateCall[0]);

    }

    public function test_a_drive_with_image () {

        $serverCfg = (object)['name' => 'server2'];
        $drive = new stdClass();
        $drive->name = "drive2";
        $drive->size = "100000";
        $drive->image = "DEBIAN_74";
        $serverCfg->drives = array($drive);

        $guid = '{guid}';
        $this->mockRunner->setGuidFor('drives create', 'drive2', $guid);
        $guid2 = 'asdf98a0s9df8098fd';
        $this->mockRunner->setGuidFor('servers create', 'server2', $guid2);

        $server = new EHServer($serverCfg);
        $this->builder->build($server);

        $driveCreateCall = $this->mockRunner->getCall('drives create');
        $this->assertEquals(1, count($driveCreateCall));
        $this->assertContains('name drive2', $driveCreateCall[0]);
        $this->assertContains('size 100000', $driveCreateCall[0]);
        $this->assertEquals($guid, $server->getDrives()[0]->getIdentifier());
        $driveInfoCall = $this->mockRunner->getCall('drives info');
        $this->assertEquals(1, count($driveInfoCall));

        $serverCreateCall = $this->mockRunner->getCall('servers create');
        $this->assertEquals(1, count($serverCreateCall));
        $this->assertContains('ide:0:0 ' . $guid, $serverCreateCall[0]);
        $this->assertEquals($guid2, $server->getIdentifier());
    }

    public function test_we_keep_polling_if_drives_arent_ready () {
        $serverCfg = (object)['name' => 'server2'];
        $drive = (object)[
            'name' => "drive2",
            'size' => "100000",
            'image' => "DEBIAN_74"
        ];
        $serverCfg->drives = array($drive);

        $this->mockRunner->delayDriveImaging();
        $this->builder->setPollingTimeout(1);
        $now = microtime(true);

        $server = new EHServer($serverCfg);
        $this->builder->build($server);

        $this->assertGreaterThan(0.95, microtime(true) - $now, 'It waits a second to poll again');
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

        $serverCreateCall = $this->mockRunner->getCall('servers create');
        $this->assertEquals(1, count($serverCreateCall));
        $this->assertContains('ide:0:0 {guid}', $serverCreateCall[0]);
        $this->assertContains('ide:0:1 {guid}', $serverCreateCall[0]);
    }



    public function test_building_two_servers_that_avoid_each_other () {

        $server1cfg = (object)[
            'name' => 'app1',
            'drives' => [
                (object)[
                    'name' => 'drive1',
                    'size' => '1000000'
                ],
                (object)[
                    'name' => 'drive1b',
                    'size' => '80080808'
                ]
            ]
        ];
        $server2cfg = (object)[
            'name' => 'app2',
            'avoid' => ['app1'],
            'drives' => [
                (object)[
                    'name' => 'drive2',
                    'size' => '200000'
                ]
            ]
        ];

        $guid1 = 'asdf98fd9d8fd9f8d9';
        $this->mockRunner->setGuidFor('servers create', 'app1', $guid1);
        $guid2 = '98f098f098f';
        $this->mockRunner->setGuidFor('drives create', 'drive1', $guid2);
        $guid3 = 'd9f8989f8d';
        $this->mockRunner->setGuidFor('drives create', 'drive1b', $guid3);

        $server1 = new EHServer($server1cfg);
        $server2 = new EHServer($server2cfg);

        $this->builder->build($server1);
        $this->builder->build($server2);

        $serverCreateCall = $this->mockRunner->getCall('servers create');
        $this->assertEquals(2, count($serverCreateCall));

        $this->assertContains('avoid:servers ' . $guid1, $serverCreateCall[1]);
        $this->assertContains('avoid:drives ' . $guid2 . ' ' . $guid3, $serverCreateCall[1]);
    }


    public function test_we_ignore_servers_we_havent_created_when_avoiding () {
        $server1cfg = (object)[
            'name' => 'app1',
            'drives' => [
                (object)[
                    'name' => 'drive1',
                    'size' => '1000000'
                ],
                (object)[
                    'name' => 'drive1b',
                    'size' => '80080808'
                ]
            ]
        ];
        $server2cfg = (object)[
            'name' => 'app2',
            'avoid' => ['app1', 'unknownserver1', 'anotherwedontknow'],
            'drives' => [
                (object)[
                    'name' => 'drive2',
                    'size' => '200000'
                ]
            ]
        ];

        $guid1 = 'asdf98fd9d8fd9f8d9';
        $this->mockRunner->setGuidFor('servers create', 'app1', $guid1);
        $guid2 = '98f098f098f';
        $this->mockRunner->setGuidFor('drives create', 'drive1', $guid2);
        $guid3 = 'd9f8989f8d';
        $this->mockRunner->setGuidFor('drives create', 'drive1b', $guid3);

        $server1 = new EHServer($server1cfg);
        $server2 = new EHServer($server2cfg);

        $this->builder->build($server1);
        $this->builder->build($server2);

        $serverCreateCall = $this->mockRunner->getCall('servers create');
        $this->assertEquals(2, count($serverCreateCall));

        $this->assertContains('avoid:servers ' . $guid1, $serverCreateCall[1]);
        $this->assertContains('avoid:drives ' . $guid2 . ' ' . $guid3, $serverCreateCall[1]);
    }


    public function test_create_a_vlan () {
        $vlanBuilder = new EHVlanBuilder();
        $this->builder->setVlanBuilder($vlanBuilder);
        $this->builder->buildVlan('helen');

        $createCall = $this->mockRunner->getCall('resources vlan');
        $this->assertEquals(1, count($createCall));
        $this->assertContains('name helen', $createCall[0]);
    }


    public function test_we_require_the_vlan_builder () {
        $this->setExpectedException('RuntimeException');
        $this->builder->buildVlan('helen');
    }
}
 