<?php
/**
 * User: matt
 * Date: 24/06/14
 * Time: 16:32
 */
require_once 'phpunit-bootstrap.php';


class EHServerGroupTest extends PHPUnit_Framework_TestCase {


    public function test_instance () {
        $group = new EHServerGroup('jen');
        $this->assertEquals('jen', $group->getName());
    }

    public function test_add_and_retrieve_servers () {

        $server1 = new EHServer((object)[]);
        $server2 = new EHServer((object)[]);

        $group = new EHServerGroup('jen');
        $group->addServer($server1);
        $group->addServer($server2);

        $servers = $group->getServers();
        $this->assertEquals(2, count($servers));
        $this->assertContains($server1, $servers);
        $this->assertContains($server2, $servers);


    }
}
 