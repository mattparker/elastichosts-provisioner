<?php
/**
 * User: matt
 * Date: 21/06/14
 * Time: 10:15
 */
require_once 'phpunit-bootstrap.php';

class EHServerTest extends PHPUnit_Framework_TestCase {


    public function test_instance () {
        $eh = new EHServer([]);
    }

    public function test_a_server_with_no_drives_throws_exception () {
        $cfg = new stdClass();
        $eh = new EHServer($cfg);

        $this->setExpectedException('LogicException');
        $eh->getDrives();
    }

    public function test_a_server_with_one_drive () {

        $cfg = new stdClass();
        $driveCfg = new stdClass();
        $cfg->drives = [$driveCfg];

        $eh = new EHServer($cfg);
        $drives = $eh->getDrives();

        $this->assertEquals(1, count($drives));

    }

    public function test_we_get_values_from_config_object () {
        $cfg = (object)[
            'name' => 'server1',
            'mem' => '1024',
            'cpu' => '2000'
        ];
        $driveCfg = new stdClass();
        $cfg->drives = [$driveCfg];

        $eh = new EHServer($cfg);
        $this->assertEquals('server1', $eh->getConfigValue('name'));
        $this->assertEquals('1024', $eh->getConfigValue('mem'));
        $this->assertNull($eh->getConfigValue('doesnotexist'));
    }
}
 