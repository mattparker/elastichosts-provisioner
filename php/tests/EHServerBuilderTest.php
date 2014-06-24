<?php
/**
 * User: matt
 * Date: 23/06/14
 * Time: 21:40
 */
require_once 'phpunit-bootstrap.php';

class EHServerBuilderTest extends PHPUnit_Framework_TestCase {



    public function test_instance () {
        new EHServerBuilder();
    }


    public function test_a_server_without_a_drive_throws_exception () {
        $cfg = [
            'name' => 'testserver1',
            'cpu' => '500',
            'mem' => '256',
            'nic:0:model' => 'e1000',
            'nic:0:dhcp' => 'auto',
            'boot' => 'ide:0:0'
        ];
        $server = new EHServer((object)$cfg);
        $builder = new EHServerBuilder();

        $this->setExpectedException('LogicException');
        $builder->create($server);
    }

    public function test_a_simple_server () {

        $driveCfg = ['name' => 'testdrive', 'size' => 1000000];

        $cfg = [
            'name' => 'testserver1',
            'cpu' => '500',
            'mem' => '256',
            'nic:0:model' => 'e1000',
            'nic:0:dhcp' => 'auto',
            'boot' => 'ide:0:0',
            'drives' => [$driveCfg]
        ];
        $server = new EHServer((object)$cfg);
        // set the id on the drive as if it'd been created
        $drives = $server->getDrives();
        $drives[0]->setIdentifier('abc123');

        $builder = new EHServerBuilder();

        $output = $builder->create($server);

        $this->assertContains('servers create', $output[0]);
        $this->assertContains('name testserver1', $output[1]);
        $this->assertContains('cpu 500', $output[1]);
        $this->assertContains('mem 256', $output[1]);
        $this->assertContains('nic:0:model e1000', $output[1]);
        $this->assertContains('nic:0:dhcp auto', $output[1]);
        $this->assertContains('boot ide:0:0', $output[1]);
        $this->assertContains('ide:0:0 abc123', $output[1]);
    }

    public function test_a_server_with_lots_of_drives () {
        $driveCfg1 = ['name' => 'testdrive1', 'size' => 1000000];
        $driveCfg2 = ['name' => 'testdrive2', 'size' => 2000000];
        $driveCfg3 = ['name' => 'testdrive3', 'size' => 3000000];
        $driveCfg4 = ['name' => 'testdrive4', 'size' => 4000000];

        $cfg = [
            'name' => 'testserver1',
            'cpu' => '500',
            'mem' => '256',
            'nic:0:model' => 'e1000',
            'nic:0:dhcp' => 'auto',
            'boot' => 'ide:0:0',
            'drives' => [$driveCfg1, $driveCfg2, $driveCfg3, $driveCfg4]
        ];
        $server = new EHServer((object)$cfg);
        // set the id on the drive as if it'd been created
        $i = 1;
        foreach ($server->getDrives() as $drive) {
            $drive->setIdentifier('driveid' . $i);
            $i++;
        }

        $builder = new EHServerBuilder();

        $output = $builder->create($server);
        $this->assertContains('ide:0:0 driveid1', $output[1]);
        $this->assertContains('ide:0:1 driveid2', $output[1]);
        $this->assertContains('ide:1:0 driveid3', $output[1]);
        $this->assertContains('ide:1:1 driveid4', $output[1]);
    }


    public function test_a_server_with_too_many_drives () {
        $driveCfg1 = ['name' => 'testdrive1', 'size' => 1000000];
        $driveCfg2 = ['name' => 'testdrive2', 'size' => 2000000];
        $driveCfg3 = ['name' => 'testdrive3', 'size' => 3000000];
        $driveCfg4 = ['name' => 'testdrive4', 'size' => 4000000];
        $driveCfg5 = ['name' => 'testdrive5', 'size' => 4000000];

        $cfg = [
            'name' => 'testserver1',
            'cpu' => '500',
            'mem' => '256',
            'nic:0:model' => 'e1000',
            'nic:0:dhcp' => 'auto',
            'boot' => 'ide:0:0',
            'drives' => [$driveCfg1, $driveCfg2, $driveCfg3, $driveCfg4, $driveCfg5]
        ];
        $server = new EHServer((object)$cfg);
        // set the id on the drive as if it'd been created
        $i = 1;
        foreach ($server->getDrives() as $drive) {
            $drive->setIdentifier('driveid' . $i);
            $i++;
        }

        $builder = new EHServerBuilder();

        $this->setExpectedException('Exception');
        $builder->create($server);
    }



    public function test_we_can_avoid_sharing_hardware () {
        $cfg = (object)[
            'name' => 'server1',
            'mem' => '1024',
            'cpu' => '2000'
        ];
        $driveCfg = new stdClass();
        $cfg->drives = [$driveCfg];

        $eh = new EHServer($cfg);
        $drive2 = '9fasd09f-as0d9f8sad';
        $drive1 = 'asdf-sadfasdf';
        $server1 = 'adf9a0sdf-asd9f8';
        $server2 = 'adfdaf-asdf9d9f8';
        $eh->avoidSharingHardwareWithDrives([$drive1, $drive2]);
        $eh->avoidSharingHardwareWithServers([$server1, $server2]);

        $builder = new EHServerBuilder();
        $output = $builder->create($eh);

        $this->assertContains('avoid:drives ' . $drive1 . ' ' . $drive2, $output[1]);
        $this->assertContains('avoid:servers ' . $server1 . ' ' . $server2, $output[1]);
    }


    public function test_we_can_get_ip_and_id_from_response () {

        $id = '55559c30-1f11-4363-ac54-dsd98sd98sd';
        $ip = '91.203.56.132';
        $response = [
            'boot ide:0:0',
            'cpu 500',
            'ide:0:0 6052916e-102f-4db7-abdd-fd98f0d9f8d',
            'ide:0:0:read:bytes 0',
            'ide:0:0:read:requests 0',
            'ide:0:0:write:bytes 0',
            'ide:0:0:write:requests 0',
            'mem 256',
            'name testserver1',
            'nic:0:dhcp auto',
            'nic:0:dhcp:ip ' . $ip,
            'nic:0:model e1000',
            'server ' . $id,
            'smp:cores 1',
            'started 1403554639',
            'status active',
            'user eeeeeee-1111-1111-ffff-6f6f6f6f6f6'
        ];
        $builder = new EHServerBuilder();
        $cfg = [
            'name' => 'testserver1',
            'cpu' => '500'
        ];
        $server = new EHServer((object)$cfg);

        $builder->parseResponse($server, $response, EHServerBuilder::CREATE);

        $this->assertEquals($ip, $server->getPublicIp());
        $this->assertEquals($id, $server->getIdentifier());

    }


    public function test_a_server_with_a_vlan_on_nic1 () {
        $driveCfg = ['name' => 'testdrive', 'size' => 1000000];

        $cfg = [
            'name' => 'testserver1',
            'cpu' => '500',
            'mem' => '256',
            'nic:0:model' => 'e1000',
            'nic:0:dhcp' => 'auto',
            'nic:1:model' => 'e1000',
            'boot' => 'ide:0:0',
            'drives' => [$driveCfg]
        ];
        $vlan_guid = 'asdflskjdflkjd';

        $server = new EHServer((object)$cfg);
        $server->setVlanId($vlan_guid);

        $builder = new EHServerBuilder();

        $output = $builder->create($server);

        $this->assertContains('servers create', $output[0]);
        $this->assertContains('nic:1:vlan ' . $vlan_guid, $output[1]);
    }

    public function test_a_server_with_a_specified_vlan_isnt_overwritten () {
        $driveCfg = ['name' => 'testdrive', 'size' => 1000000];
        $vlan_guid = 'asdflskjdflkjd';

        $cfg = [
            'name' => 'testserver1',
            'cpu' => '500',
            'mem' => '256',
            'nic:0:model' => 'e1000',
            'nic:0:dhcp' => 'auto',
            'nic:1:model' => 'e1000',
            'nic:1:vlan' => $vlan_guid,
            'boot' => 'ide:0:0',
            'drives' => [$driveCfg]
        ];


        $server = new EHServer((object)$cfg);
        $server->setVlanId('somethingelse');

        $builder = new EHServerBuilder();

        $output = $builder->create($server);

        $this->assertContains('servers create', $output[0]);
        $this->assertContains('nic:1:vlan ' . $vlan_guid, $output[1]);
        $this->assertNotContains('nic:1:vlan somethingelse', $output[1]);
    }



    public function test_a_server_with_nic1_but_no_vlan_doesnt_include_nic1 () {
        $driveCfg = ['name' => 'testdrive', 'size' => 1000000];

        $cfg = [
            'name' => 'testserver1',
            'cpu' => '500',
            'mem' => '256',
            'nic:0:model' => 'e1000',
            'nic:0:dhcp' => 'auto',
            'nic:1:model' => 'e1000',
            'boot' => 'ide:0:0',
            'drives' => [$driveCfg]
        ];


        $server = new EHServer((object)$cfg);

        $builder = new EHServerBuilder();

        $output = $builder->create($server);

        $this->assertContains('servers create', $output[0]);
        $this->assertNotContains('nic:1:vlan', $output[1]);
    }

    public function test_parse_response_for_unknown_action () {
        $builder = new EHServerBuilder();
        $server = new EHServer((object)[]);
        $this->setExpectedException('InvalidArgumentException');
        $builder->parseResponse($server, [], 4985);
    }


    public function test_parse_response_when_line_not_present () {
        $builder = new EHServerBuilder();
        $server = new EHServer((object)[]);
        $response = [];
        $builder->parseResponse($server, $response, EHServerBuilder::CREATE);

        $this->assertEquals(null, $server->getPublicIp());
        $this->assertEquals(null, $server->getIdentifier());
    }

}
 