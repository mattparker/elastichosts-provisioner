<?php
/**
 * User: matt
 * Date: 20/06/14
 * Time: 16:22
 */

class EHServerBuilder {


    private $properties = [
        'name',
        'cpu',
        'smp',
        'mem',
        'persistent',
        'boot',
        'nic:0:model',
        'nic:0:dhcp',
        'nic:0:firewall:policy',
        'nic:0:firewall:accept',
        'nic:0:firewall:reject',
        'nic:1:model',
        'nic:1:vlan',
        'nic:1:mac',
        'vnc',
        'password',
        'vnc:tls',
        'tags',
        'avoid:drives',
        'avoid:servers'
    ];



    public function build (EHServer $server) {

        $uri = 'servers create';

        // initial properties
        foreach ($this->properties as $prop) {
            $value = $server->getConfigValue($prop);
            if ($value !== null) {
                $uri .= ' ' . $prop . ' ' . $value;
            }
        }

        // now drives
        $drives = $server->getDrives();
        $driveCount = 0;
        foreach ($drives as $drive) {
            /** @var EHDrive $drive */
            // get sequentially increasing ide:0:0, ide:0:1, ide:1:0, ide:1:1
            $deviceId = 'ide:' . (int)($driveCount & 4) . ':' . (int)($driveCount & 2);
            $uri .= ' ' . $deviceId . ' ' . $drive->getIdentifier();
            $driveCount++;
        }
        if ($driveCount >= 4) {
            throw new Exception("Don't think you can have more than four drives on one server through the API");
        }


        return $uri;
    }
} 